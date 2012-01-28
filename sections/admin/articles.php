<?php

#===============================================================================
#
# NationCMS
#
# Copyright (C) 2007 Matt West <matt at mattdanger dot net>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# For a copy of the GNU General Public License visit <http://www.gnu.org/licenses/>.
#
#===============================================================================

/****************************************************
*
* File:     sections/admin/news.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

// Redirect if not logged in
$CORE->get_class_file('Login');
$LOGIN = new Login();
if ( !$LOGIN->is_admin_logged_in('ns_admin') ) { $CORE->redirect('index.php?section=admin'); }

// Set our GET variables & get user's identity
$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : null;
$action = (isset($_GET['action']) || $id) ? $_GET['action'] : null ; 

// Check a couple things if a post ID is specified
$user = $DB->query("SELECT `user_level` FROM `users` WHERE `id` = '" . $LOGIN->cookie[1] . "'");
if ($id) { 

    // Select that post from the database
    $article = $DB->query("SELECT * FROM `articles` WHERE `id`='" . $id . "'");

    // If the post doesn't even exist then print an error and quit
    if (!$article) $CORE->print_error("Sorry that article couldn't be found.");

    // Make sure the user has enough privileges to make and edit posts
    if (($user['user_level'] != 3 && $article['author'] != $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4])) $CORE->print_error("Sorry, you don't have authorization to " . $_GET['action'] . " this article.");

} else if ($user['user_level'] < 2) { // If the user doesn't have a high enough user level print an error and exit

    $CORE->print_error("Sorry, you don't have authorization to post articles.");

}

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The article was added successfully.<div>';
            break;

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The article was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The article was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 


switch ($action) {

    /*****************************/
    // Add Article
    /*****************************/
    case 'add':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=articles">Article Manager</a> &#187; Add Article';
        $CORE->content['sidepanel_top'] = "&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, and &#60;a href&#62; tags are allowed in the description only.";
        print 'This feature has not yet been completed (sorry).';
        break;


    /*****************************/
    // Edit Article
    /*****************************/
    case 'edit':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=articles">Article Manager</a> &#187; Edit Article';
        $CORE->content['sidepanel_top'] = 'To edit "' . $article['title'] . '" make changes to the fields on your left and click Submit.<br /><br />&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, and &#60;a href&#62; tags are allowed in the description only.';

        if (isset($_POST['submit'])) {

            $insert['photo'] = (isset($article['photo'])) ? $article['photo']: null;

            if ( !empty($_FILES['image']['tmp_name']) ) {

                if (isset($article['photo'])){

                    $DB->update('articles', array('photo' => 'null'), array('id' => $id));
                    $photo = escapeshellcmd($_POST['current_photo']);
                    $old_photo1 = $CORE->config['full_path'] . "images/articles/" . $photo;
                    $old_photo2 = $CORE->config['full_path'] . "images/articles/thumbs/" . $photo;
                    if (!strstr("/",$_POST['current_photo'])) {
                        shell_exec('rm -f ' . $old_photo1 ); 
                        shell_exec('rm -f ' . $old_photo2);
                    } else $CORE->print_error("Knock it off, jackass.");
                    $insert['photo'] = null;
                }
        
                /* Resize photo and save it to 'images/articles/' */
                $size = getimagesize($_FILES['image']['tmp_name']);
                $src_width = $size[0];
                $src_height = $size[1];
                $dest_height  = 0;
                $uploaded_image  = $_FILES['image']['tmp_name'];
                // $photo_framing can be 1 or 2. 1 = landscape. 2 = portrait 
                if ($src_width > $src_height) $photo_framing = 1; else $photo_framing = 2;
                if ($photo_framing > 1) $CORE->print_error("The photo you uploaded appears to be framed as a portrait style. For fitment purposes, you are only allowed to upload a photo that's framed in the landscape style.");
                else if ($photo_framing == 1) {
                    if ($src_width < 170) 
                        $CORE->print_error("The photo you uploaded is too small. Photos must be at least 200 pixels wide so they can be resized properly.");
                } else $CORE->print_error("There was an error determining the photo framing.");
        
                // Resize for the display image (but keep ratio) and then upload.
                if ($photo_framing == 1) 
                    $dest_width = 200;
        
                $ratio = $src_width / $src_height;
                $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                $dest_width = $src_width * $scale;
                $dest_height = $src_height * $scale;
                if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
        
                $src_image = imagecreatefromjpeg ($uploaded_image);
                $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                if (!imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height)) 
                    $CORE->print_error("There was an error trying to resize the image you uploaded.");
                $bad_chars = array("'", '"', "\\", "/", ";");
                $good_chars = array('','','','','');
                $tmp_name = str_replace($bad_chars, $good_chars, $_POST['title']);
                $tmp_name = str_replace(" ","", $tmp_name);
                $new_filename = $tmp_name . '_' . time() . '.jpg';
                imagejpeg($dest_image, 'images/articles/' . $new_filename, 95);
        
                // Resize for the thumb nail image (but keep ratio), 
                // then crop to a square, and then upload.
                $ratio = $src_width / $src_height;
                $square_dimension = 70;
                // Landscape
                $dest_width = 125;
        
                $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                $dest_width = $src_width * $scale;
                $dest_height = $src_height * $scale;
                if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
        
                $src_image = imagecreatefromjpeg ($uploaded_image);
                $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
        
                imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
                $dest_image2 = imagecreatetruecolor($square_dimension,$square_dimension);
                imagecopy ($dest_image2, $dest_image, 0, 0, 25, 0, $square_dimension, $square_dimension);
                imagejpeg($dest_image, 'images/articles/thumbs/' . $insert['photo'], 80);
                imagedestroy($src_image);
                imagedestroy($dest_image);
            }
        
            $article_id = (isset($_POST['id'])) ? $_POST['id'] : null;
            $insert['title'] = $_POST['title'];
            $insert['body'] = nl2br($_POST['body']);
            $insert['last_revised'] = time();

            $DB->update('articles', $insert, array('id' => $id));
            write_admin_log($LOGIN->cookie[3] . ' updated the "' . $title . '" article.', $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=articles&status=updated');
        }
        
        $article = $DB->query("SELECT * FROM `articles` WHERE `id`='" . $id . "'");
        if (isset($article['photo'])) $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/articles/' . $article['photo'];
        
        if (isset($_GET['update'])) print '<font color="#FF0000">The article "'.$article['title'].'" was succesfully updated.<br /><br />';
        
        print '
            <form method="POST" action="index.php?section=admin&page=articles&action=edit&id=' . $article['id'] . '" enctype="multipart/form-data">
            <table cellpadding="3" border="0" cellspacing="0" width="100%" align="left">
                <tr>
                    <td align="right"><b>Title: </b></td>
                    <td><input type="text" name="title" size="50" value="'.$article['title'].'"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Body: </b></td>
                    <td><textarea name="body" cols="60" rows="10">'.br2nl($article['body']).'</textarea></td>
                </tr>
                <tr>
                    <td align="right" valign="top"><b>Photo: </b></td>
                    <td class="file_update">
                        <input type="hidden" name="id" value="'.$article['id'].'">
                        <input type="hidden" name="current_photo" value="'.$article['photo'].'">
                        <input type="hidden" name="date_added" value="'.$article['date_added'].'">
                        <input type="hidden" name="author" value="'.$article['author'].'">
                        <input class="file_update" type="file" name="image" size="20"> <br /><small>';
        if (isset($article['photo'])) print 'Uploading a new photo will overwrite the old one.';
                        
        print '</small></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr><td></td>
                    <td align="left">
                        <input type="submit" name="submit" value="Update"> 
                    </td>
                </tr>
            </table>
            ';

        break;


    /*****************************/
    // Delete Article
    /*****************************/
    case 'delete':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=articles">Article Manager</a> &#187; Delete Article';

        break;


    /*****************************/
    // List All Articles
    /*****************************/
    default:
        // Set up the content variables
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; Article Manager';
        print '<div class="articles_box_add" onclick="document.location=\'index.php?section=admin&page=articles&action=add\'">Add Article</div><br />';
        print '<table cellspacing="2" border="0" width="100%">';
    
        $DB->query("SELECT * FROM `articles` ORDER BY `id` DESC");

        foreach ($DB->result as $row) {
            $img = ( !empty($row['photo']) ) ? '<a href="index.php?section=admin&page=articles&action=edit&id=' . $row['id'] . '"><img src="' . $CORE->config['path'] . 'images/articles/' . $row['photo'] . '" class="admin_articles_image_thumbs" border="0"></a>' : '<div class="admin_articles_image_blank" onclick="document.location=\'index.php?section=admin&page=articles&action=edit&id=' . $row['id'] . '\'">?</div>';
            print '<tr>
                        <td valign="top" width="110">' . $img . '
                        </td>
                        <td valign="top">
                            <b> ' . $row['title'] . ' </b> <small>[<a href="index.php?section=admin&page=articles&action=edit&id=' . $row['id'] . '">Edit</a>]<br />
                            <span style="color: #888">' . $CORE->shorten_text(strip_tags($row['body']), 35) . '</span></small>
                        </td>
                    </tr>
                    <tr>
                        <td height="5"></td>
                    </tr>';  
        }
        print "</table><br />";
    

} // End Switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>