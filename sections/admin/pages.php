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
* File:     sections/admin/pages.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

// Redirect if not logged in
$CORE->get_class_file('Login');
$LOGIN = new Login();
if ( !$LOGIN->is_admin_logged_in('ns_admin') ) { $CORE->redirect('index.php?section=admin'); }

$section = ( isset($_GET['name'])) ? $_GET['name'] : null;
$action = ( isset($_GET['action']) && $section) ? $_GET['action'] : null ; 

// Check a couple things if a section specified
$user = $DB->query("SELECT `user_level` FROM `users` WHERE `id` = '" . $LOGIN->cookie[1] . "'");

if ($section) { 

    // Select that friend from the database
    $page = $DB->query("SELECT * FROM `site_content` WHERE `section`='" . $section . "'");

    // If the post doesn't even exist then print an error and quit
    if (!$page) $CORE->print_error("Sorry that section couldn't be found.");

} else if ($action == 'add' && $user['user_level'] < 2) { // If the user doesn't have a high enough user level print an error and exit

    $CORE->print_error("Sorry, you don't have authorization to edit any of the site's sections.");

}

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> That section\'s information was updated successfully.<div>';
            break;

    }

    print '<br /><br />';

}


switch ($action) {

    /*****************************/
    // Edit A Page
    /*****************************/
    case 'edit':

        // Set up content variables
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=pages">Content Manager</a> &#187; Edit the ' . ucfirst($page['section']) . " page";

        if (isset($page['photo'])) $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/sections/' . $page['photo'];

        // If form is submitted
        if (isset($_POST['submit'])) {

            if (!isset($page['photo'])) $page['photo'] = null;

            // If selected, delete the page's current photo
            if (isset($_POST['delete_photo'])) {
                $filename = $_POST['filename'];
                $DB->update('section', array('photo' => $filename), array('section' => $section['name'])) ;
                shell_exec("rm -f images/sections/" . escapeshellcmd($filename));
                $page['photo'] = 'NULL';
            }

            if ( !empty($_FILES['image']['tmp_name']) ) {
                if (isset($photo['filename'])){
                    $DB->update('section', array('photo' => $filename), array('section' => $section['name']));
                    shell_exec("rm -f images/sections/" . escapeshellcmd($filename));
                    $page['photo'] = 'NULL';
                }
                /* Resize photo and save it to 'images/' */
                $size = getimagesize($_FILES['image']['tmp_name']);
                $src_width = $size[0];
                $src_height = $size[1];
                $dest_height  = 0;
                $uploaded_image  = $_FILES['image']['tmp_name'];
                // $photo_framing can be 1 or 2. 1 = landscape. 2 = portrait 
                if ($src_width > $src_height) $photo_framing = 1; else $photo_framing = 2;
                if ($photo_framing > 1) $CORE->print_error("The photo you uploaded appears to be framed as a portrait style. For fitment purposes, you are only allowed to upload a photo that's framed in the landscape style.");
                else if ($photo_framing == 1) {
                    if ($src_width < 200) 
                        $CORE->print_error("The photo you uploaded is too small. Photos must be at least 200 pixels wide so they can be resized properly.");
                } else $CORE->print_error("There was an error determining the photo framing.");

                // Resize for the display image (but keep ratio) and then upload.
                if ($photo_framing == 1) 
                    $dest_width = 200;

                // Do calculations for new image
                $ratio = $src_width / $src_height;
                $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                $dest_width = $src_width * $scale;
                $dest_height = $src_height * $scale;
                if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
                $src_image = imagecreatefromjpeg ($uploaded_image);
                $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                if (!imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height)) 
                    $CORE->print_error("There was an error trying to resize the image you uploaded.");

                // Set the filename and save the image
                $page['photo'] = $page['section'].'.jpg';
                imagejpeg($dest_image, 'images/sections/' . $page['photo'], 80);
                $page['photo'] = "'" . $page['photo'] . "'";

                // Garbage collection
                imagedestroy($src_image);
                imagedestroy($dest_image);
                
            } 
        
            $page['section'] = $_POST['section'];
            $page['heading'] = $_POST['heading'];
            $page['main'] = nl2br($_POST['main']);
            $page['side_panel'] = nl2br($_POST['side_panel']);
            $cookie_data = explode(":", base64_decode($_COOKIE['ns_admin']));
            $author = $cookie_data[2];

//            db_alter("UPDATE `site_content` SET `photo` = " . $page['photo'] . ", `heading`='" . $page['heading'] . "', `main`='" . $page['main'] . "', `side_panel`='" . $page['side_panel'] . "', `last_edited`='" . time() . "', `last_edited_by`='" . $author . "' WHERE `section`='" . $page['section'] . "'");
            $DB->update('site_content', $page, array('section' => $page['section']));

            write_admin_log($LOGIN->cookie[3] . ' modified the ' . ucfirst($page['section']) . ' page.', $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=pages&status=updated');
        } 

        print '
            <form method="POST" action="index.php?section=admin&page=pages&action=edit&name=' . $page['section'] . '" enctype="multipart/form-data">
            <input type="hidden" name="section" value="' . $page['section'] . '">
            <table cellpadding="3" border="0" cellspacing="0" width="100%" align="left">
                <tr>
                    <td align="right"><b>Heading: </b></td>
                    <td><input type="text" name="heading" size="30" value="' . $page['heading'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Body: </b></td>
                    <td><textarea name="main" cols="50" rows="10">' . br2nl($page['main']) . '</textarea></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Side Panel</b></td>
                    <td><textarea name="side_panel" cols="50" rows="4">' . br2nl($page['side_panel']) . '</textarea></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td align="right" valign="top"><b>Photo:</b></td>
                    <td class="file_update">';

        if (isset($page['photo'])) {
            print '
                    <input type="checkbox" name="deletePhoto"> Delete current photo
                    <input type="hidden" name="filename" value="' . $page['photo'] . '"><br />';
        }

        print '
                    <input class="file_update" type="file" name="image" size="20"> <br /><small><i> &nbsp;* Uploading a new photo will overwrite the old one.<i></small></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td colspan="2" align="center"><input type="submit" name="submit" value="Submit Changes"> <input type="reset" name="reset" value="Reset Changes"></td>
                </tr>
            </table></form><br /><br />';
        break;


    /*****************************/
    // Display All Page Information
    /*****************************/
    default:

        // Set up page content
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; Content Manager';
        print '<table cellspacing="2" border="0">';

        // Get all the data and print them in a nice table
        $DB->query("SELECT * FROM `site_content` ORDER BY `section` ASC");
        foreach ($DB->result as $row) {

            // Grab the section's photo from the database (if there is one)
            $page = $DB->query("SELECT `photo` FROM `site_content` WHERE `section` = '" . $row['section'] . "'");
            $img = (!empty($page['photo'])) ? '<a href="index.php?section=admin&page=pages&action=edit&name=' . $row['section'] . '"><img src="' . $CORE->config['path'] . 'images/sections/' . $page['photo'] . '" class="admin_pages_image_thumbs" border="0"></a>' : '<div class="admin_pages_image_blank" onclick="document.location=\'index.php?section=admin&page=pages&action=edit&name=' . $row['section'] . '\'">?</div>';

            print '<tr>
                        <td valign="top" class="admin_pages_display">' . $img . '</td><td></td>
                        <td valign="top">
                            <b>' . ucfirst($row['section']) . '</b> <small>[<a href="index.php?section=admin&page=pages&action=edit&name=' . $row['section'] . '">Edit</a>] <br /> <span style="color: #888"> Last edited ';

            // Format the date edited in a nice format
            if ( !empty($row['last_edited']) ) {

                if ( date('m/j/Y', $row['last_edited']) == date('m/j/Y') ) {

                    print 'today at ' . date('g:ia', $row['last_edited']);

                } else if ( (date('j') - date('j', $row['last_edited'])) == 1 ) {

                    print 'yesterday at ' . date('g:ia', $row['last_edited']);

                } else {

                    print (int)((time() - $row['last_edited']) / 24 / 60 / 60) . ' days ago';

                }

                print ' by ' . $row['last_edited_by'];

            } else {

                print "Never";

            }

            print '<span></small></td>
                    </tr>';
        }
        print"</table><br /><br />";

} // end switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>
