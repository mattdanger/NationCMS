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
* File:     sections/admin/friends.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

// Redirect if not logged in
$CORE->get_class_file('Login');
$LOGIN = new Login();
if ( !$LOGIN->is_admin_logged_in('ns_admin') ) { $CORE->redirect('index.php?section=admin'); }


$id = (isset($_GET['id'])) ? $_GET['id'] : null;
$action = (isset($_GET['action']) || $id) ? $_GET['action'] : null ; 

// Check a couple things if a friend ID is specified
$user = $DB->query("SELECT `user_level` FROM `users` WHERE `id` = '" . $LOGIN->cookie[1] . "'");
if ($id) { 

    // Select that friend from the database
    $friends = $DB->query("SELECT * FROM `friends` WHERE `id`='".$id."'");

    // If the post doesn't even exist then print an error and quit
    if (!$friends) $CORE->print_error("Sorry that friend couldn't be found.");

    // Make sure the user has enough privileges to make and edit posts
    if ($user['user_level'] != 3) $CORE->print_error("Sorry, you don't have authorization to " . $_GET['action'] . " this friend.");

} else if ($action == 'add' && $user['user_level'] < 2) { // If the user doesn't have a high enough user level print an error and exit

    $CORE->print_error("Sorry, you don't have authorization to add friends.");

}

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The new friend was added successfully.<div>';
            break;

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The friend\'s information was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The friend was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 


switch ($action) {

    /*****************************/
    // Add A Friend
    /*****************************/
    case 'add':
        // Set the content
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=friends">Friends</a> &#187; Add Friend';
        $CORE->content['sidepanel_top'] = "When adding a friend you are <b>required</b> to include a thumb nail photo make changes to the fields on your left and click Submit.<br /><br />&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, and &#60;a href&#62; tags are allowed in the description only.";

        // Set the filename for the photo if it already exists
        $friends['photo'] = (isset($friends['photo'])) ? $friends['photo']: null;

        // If form is submitted
        if (isset($_POST['submit'])) {

            // Set the data for the friends
            $friends['name'] =          (!empty($_POST['name'])) ?          $_POST['name'] :                    null ;
            $friends['description'] =   (!empty($_POST['description'])) ?   nl2br($_POST['description']) : null ;
            $friends['website'] =       (!empty($_POST['website']) && $_POST['website'] != 'http://') ?       nl2br($_POST['website']) :          null ;
            $friends['myspace'] =       (!empty($_POST['myspace'])) ?       nl2br($_POST['myspace']) :          null ;
    
            // If a photo was uploaded then do the image processing
            if ($friends['name']) {

                if (!empty($_FILES['image']['tmp_name'])) {

                    if ($_FILES['image']['type'] != 'image/jpeg') {

                        $CORE->print_error("You can only upload a JPEG image");
                    
                    }
    
                    if (isset($friends['photo'])){
                        $DB->update('friends', array('photo' => 'null'), array('id' => $id));
                        $old_photo = $CORE->config['full_path'] . "images/friends/" . escapeshellcmd($_POST['current_photo']);
                        shell_exec("rm -f $old_photo");
                        $new_filename = null;
                    }
    
                    // Resize photo and save it to 'images/' 
                    $size =             getimagesize($_FILES['image']['tmp_name']);
                    $src_width =        $size[0];
                    $src_height =       $size[1];
                    $dest_height  =     0;
                    $uploaded_image  =  $_FILES['image']['tmp_name'];
    
                    // $photo_framing can be 1 or 2. 1 = landscape. 2 = portrait 
                    if ($src_width > $src_height) $photo_framing = 1; else $photo_framing = 2;
                    if ($photo_framing > 1) $CORE->print_error("The photo you uploaded appears to be framed as a portrait style. For fitment purposes, you are only allowed to upload a photo that's framed in the landscape style.");
                    else if ($photo_framing == 1) {
                        if ($src_width < 170) 
                            $CORE->print_error("The photo you uploaded is too small. Photos must be at least 170 pixels wide so they can be resized properly.");
                    } else $CORE->print_error("There was an error determining the photo framing.");
    
                    // Calculate the size for the new image, keeping the ratio
                    if ($photo_framing == 1) $dest_width = 175;
                    $ratio = $src_width / $src_height;
                    $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                    $dest_width = $src_width * $scale;
                    $dest_height = $src_height * $scale;
                    if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
            
                    // Create image resource
                    $src_image = imagecreatefromjpeg ($uploaded_image);
                    $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
    
                    // Resample the image to the correct size
                    if (!imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height)) 
                        $CORE->print_error("There was an error trying to resize the image you uploaded.");
    
                    // Create filename from the name of friend                
                    $friends['photo'] = str_replace(' ', '' , $_POST['name']) . '_' . time() . '.jpg';
      
                    // Save the new image
                    imagejpeg($dest_image, 'images/friends/' . $friends['photo'], 95);
    
                    // Garbage collection
                    imagedestroy($src_image);
                    imagedestroy($dest_image);
    
                    // Display the friend image on the side panel (Will only show up if they have to make changes)
                    $CORE->content['sidepanel_top_image'] = $friends['photo'];

                } else { 
    
                    // User MUST upload a photo when creating a friend!
                    $friends['photo'] = null;

                // Display an error if no photo was uploaded name was given
                print '<div class="admin_box_message"><span class="admin_error">Error:</span> You need to upload a photo when adding a friend.<div><br /><br />';
            
                }    

            } else {

                // Display an error if no friend name was given
                print '<div class="admin_box_message"><span class="admin_error">Error:</span> You need to define a title for your friend.<div><br /><br />';

            }

            // If a name was given and a photo was uploaded
            if ($friends['name'] && $friends['photo']) {

                // Insert new friend into the database
                $DB->insert('friends', $friends);
//                db_alter("INSERT INTO `friends` (`name`, `description`, `photo`, `myspace`, `website`) VALUES ('" . $friends['name'] . "', '" . $friends['description'] . "', '" . $friends['photo'] . "','" . $friends['myspace'] . "', '" . $friends['website'] . "')");

                // Write this event to the admin log
                write_admin_log($LOGIN->cookie[3] . ' updated the ' . $friends['name'] . ' friends page.', $LOGIN->cookie[3]);

                // Redirect back to the Friends admin page
                $CORE->redirect('index.php?section=admin&page=friends&status=added');

            }

        } // End if (submit)

        // Set some variables if they are empty
        if (empty($friends['name']))        $friends['name'] = '';
        if (empty($friends['description'])) $friends['description'] = '';
        if (empty($friends['myspace']))     $friends['myspace'] = '';
        $friends['website'] =               (empty($friends['website'])) ? 'http://' : $friends['website'];

        // Draw the form
        print '
            <form method="POST" action="index.php?section=admin&page=friends&action=add" enctype="multipart/form-data">
            <table cellpadding="3" border="0" cellspacing="0" width="100%" align="left">
                <tr>
                    <td align="right"><b>Name: </b></td>
                    <td><input type="text" name="name" size="40" value="' . $friends['name'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Description: </b></td>
                    <td><textarea name="description" cols="50" rows="10">' . br2nl($friends['description']) . '</textarea></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Website: </b></td>
                    <td><input type="text" name="website" size="40" value="' . $friends['website'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Myspace: </b></td>
                    <td><input type="text" name="myspace" size="40" value="' . $friends['myspace'] . '"></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td align="right" valign="top"><b>Photo: </b></td>
                    <td class="file_update">
                        <input type="file" class="file_update" name="image"><br /><br /> <small>Uploading a new photo will overwrite the old one.</small></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td></td><td><input type="submit" name="submit" value="Add Friend"> </td>
                </tr>
            </table>';
        break;


    /*****************************/
    // Edit A Friend
    /*****************************/
    case 'edit':

        // Set the content
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=friends">Friends</a> &#187; Edit Friend';
        $CORE->content['sidepanel_top'] = "To edit the content that is displayed for the " . $friends['name'] . " section make changes to the fields on your left and click Submit.<br /><br />&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, and &#60;a href&#62; tags are allowed in the description only.";

        // If the friend has a photo (it most certainly should), set it as the sidepanel photo
        if (isset($friend['photo'])) $CORE->content['sidepanel_top_image'] = $friend['photo'];

        // If form is submitted
        if (isset($_POST['submit'])) {

            // Set the filename for the photo
            $new_filename = (isset($friends['photo'])) ? $friends['photo']: null;

            // If a new photo was uploaded then we'll need to remove the old one and resize & save the new one
            if (!empty($_FILES['image']['tmp_name'])) {

                if ( isset($friends['photo']) ){
                    $DB->update('friends', array('photo' => 'null'), array('id' => $id));
                    $old_photo = $CORE->config['full_path'] . "images/friends/" . escapeshellcmd($_POST['current_photo']);
                    shell_exec("rm -f $old_photo");
                    $new_filename = null;
                }

                // Resize photo and save it to 'images/' 
                $size =             getimagesize($_FILES['image']['tmp_name']);
                $src_width =        $size[0];
                $src_height =       $size[1];
                $dest_height  =     0;
                $uploaded_image  =  $_FILES['image']['tmp_name'];

                // $photo_framing can be 1 or 2. 1 = landscape. 2 = portrait 
                if ($src_width > $src_height) $photo_framing = 1; else $photo_framing = 2;
                if ($photo_framing > 1) $CORE->print_error("The photo you uploaded appears to be framed as a portrait style. For fitment purposes, you are only allowed to upload a photo that's framed in the landscape style.");
                else if ($photo_framing == 1) {
                    if ($src_width < 170) 
                        $CORE->print_error("The photo you uploaded is too small. Photos must be at least 170 pixels wide so they can be resized properly.");
                } else $CORE->print_error("There was an error determining the photo framing.");
        
                // Resize for the display image (but keep ratio) and then upload.
                if ($photo_framing == 1) 
                    $dest_width = 175;
        
                $ratio = $src_width / $src_height;
                $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                $dest_width = $src_width * $scale;
                $dest_height = $src_height * $scale;
                if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
        
                $src_image = imagecreatefromjpeg ($uploaded_image);
                $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                if (!imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height)) 
                    $CORE->print_error("There was an error trying to resize the image you uploaded.");
                $new_filename = str_replace(" ", "", $_POST['name']) . '_' . time() . '.jpg';
                imagejpeg($dest_image, 'images/friends/' . $new_filename, 95);
                imagedestroy($src_image);
                imagedestroy($dest_image);
            }

            // Set the data for the friends
            $plugin =                   $_POST['id'];
            $friends['name'] =          $_POST['name'];
            $friends['description'] =   nl2br($_POST['description']);
            $friends['website'] =       nl2br($_POST['website']);
            $friends['myspace'] =       nl2br($_POST['myspace']);
            $cookie_data =              explode(":", base64_decode($_COOKIE['ns_admin']));

            $update_data = array( 'name' => $friends['name'],
                                    'description' => $friends['description']);

            if ($new_filename) $friends['photo'] = $new_filename;
            else if (isset($_POST['deletePhoto'])) $friends['photo'] = 'NULL';

            $friends['description'] = str_replace("''", "'", $friends['description']);

            $DB->update('friends', $friends, array('id' => $id) );

            write_admin_log($LOGIN->cookie[3] . ' updated the ' . $friends['name'] . ' friends page.', $LOGIN->cookie[3]);

            $CORE->redirect('index.php?section=admin&page=friends&status=updated');
            
        } 

        $friends = $DB->query("SELECT * FROM `friends` WHERE `id`='".$id."'");
        if (isset($friends['photo'])) $CORE->content['sidepanel_image'] = $CORE->config['path'] . 'images/friends/' . $friends['photo'];
        
        $friends['website'] = (empty($friends['website'])) ? 'http://' : $friends['website'];
        
        print '
            <form method="POST" action="index.php?section=admin&page=friends&action=edit&id=' . $id . '" enctype="multipart/form-data">
            <input type="hidden" name="id" value="' . $friends['id'] . '">
            <table cellpadding="3" border="0" cellspacing="0" width="100%" align="left">
                <tr>
                    <td align="right"><b>Name: </b></td>
                    <td><input type="text" name="name" size="40" value="' . $friends['name'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Description: </b></td>
                    <td><textarea name="description" cols="50" rows="10">' . br2nl($friends['description']) . '</textarea></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Website: </b></td>
                    <td><input type="text" name="website" size="40" value="' . $friends['website'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Myspace: </b></td>
                    <td><input type="text" name="myspace" size="40" value="' . $friends['myspace'] . '"></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td align="right" valign="top"><b>Photo: </b></td>
                    <td class="file_update">
                        <input type="hidden" name="current_photo" value="' . $friends['photo'] . '">
                        <input class="file_update" type="file" name="image"><br /><br /> <small>Uploading a new photo will overwrite the old one.</small></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td colspan="2" align="center"><input type="submit" name="submit" value="Submit Changes"> <input type="reset" name="reset" value="Reset Changes"> <input type="button" name="delete" value="Delete" onclick="javascript: if (confirm(\'Are you sure you want to delete this friend? This action cannot be undone.\')) { document.location=\'index.php?section=admin&page=friends&action=delete&id=' . $id . '&confirm=yes\'; } else { return false; }"</td>
                </tr>
            </table>';
        break;


    /*****************************/
    // Delete A Friend
    /*****************************/
    case 'delete':
        $friend = $DB->query("SELECT * FROM `friends` WHERE `id` = '" . $id . "'");
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
            $DB->delete('friends', array('id' => $id));
            write_admin_log($LOGIN->cookie[3] . ' deleted the friend "' . $friend['name'] . '".', $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=friends&status=deleted');
        }
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=friends">Friends</a> &#187; Delete Friend';
        $CORE->content['sidepanel_top'] = "Think before you delete! Deleted friends cannot be recovered.";
        print '<br /> <div align="center"><img src="images/friends/' . $friend['photo'] . '"><br />' . $friend['name'] . '
            <br /><br /><div align="center">Are you sure you want to delete this friend?<br /><br />
            <input type="button" value="Yes" onclick="javascript: document.location=' . "'index.php?section=admin&page=friends&action=delete&id=" . $id . "&confirm=yes'".'"><input type="button" value="No" onclick="javascript: document.location=' . "'index.php?section=admin&page=friends'".'">
            </div>';
        break;


    /*****************************/
    // List Friends
    /*****************************/
    default:

        // Set up some content variables
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; Friends';
        $CORE->content['sidepanel_top'] = "To edit the information for one of our friends choose their thumb nail image.";
        print '<div align="right" onclick="document.location=\'index.php?section=admin&page=friends&action=add\'" class="friends_box_add">Add Friend</div><br />Click on one of the friends below to make changes. <br /><br /> <table border="0" align="center" cellpadding="5">';

        // Select all the friends from the database
        $result = $DB->query("SELECT * FROM `friends` ORDER BY `id` ASC");

        // Display the friends
        $row_counter = 1;
        foreach ($result as $row) {
            if ($row_counter % 2) print '<tr>';
            print '<td class="admin_friend_display"><a href="index.php?section=admin&page=friends&action=edit&id=' . $row['id'] . '"><img src="images/friends/' . $row['photo'] . '" border="0"></a><br /><a href="index.php?section=admin&page=friends&action=edit&id=' . $row['id'] . '">' . $row['name'] . '</a></td>';
            if (!($row_counter % 2)) print '</tr>';
            $row_counter++;
        }
        print '</table><br/>';


} // end switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>