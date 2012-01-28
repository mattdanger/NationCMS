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
* File:     sections/admin/photos.php
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
$video_filename = (isset($_GET['video'])) ? $_GET['video'] : null;
$action = (isset($_GET['action']) || $video_filename) ? $_GET['action'] : null ; 

/*
// Check a couple things if a post ID is specified
$user = $DB->query("SELECT `user_level` FROM `users` WHERE `id` = '" . $LOGIN->cookie[1] . "'");
if ($video_filename) { 

    // Select that post from the database
    $video = $DB->query("SELECT * FROM `video` WHERE `filename`='" . $video_filename . "'");

    // If the post doesn't even exist then print an error and quit
    if (!$video) $CORE->print_error("Sorry that video couldn't be found.");

    // Make sure the user has enough privileges to make and edit posts
    if (($user['user_level'] != 3 && $video['editor'] != $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4])) $CORE->print_error("Sorry, you don't have authorization to " . $_GET['action'] . " this video.");

} else if ($user['user_level'] < 2) { // If the user doesn't have a high enough user level print an error and exit

    $CORE->print_error("Sorry, you don't have authorization to upload video.");

}
*/

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> Your photo was uploaded successfully.<div>';
            break;

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The photo was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The photo was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 

switch ($action) {

    /*****************************/
    // Upload a new photo
    /*****************************/
    case 'add':

        if ( !isset($_POST['submit']) ) {

            $CORE->redirect('index.php?section=admin&page=photos');

        } else {

            $missing_fields = false;
            if (!isset($_FILES['image']['tmp_name'])) $missing_fields = true;
            $photo['description'] = (!empty($_POST['description'])) ? ($_POST['description']) : null ;
            $photo['location'] = (!empty($_POST['location'])) ? ($_POST['location']) : $missing_fields = true ;
            $photo['date_taken'] = (!empty($_POST['date_taken'])) ? ($_POST['date_taken']) : $missing_fields = true ;
            $photo['photographer'] = (!empty($_POST['photographer'])) ? ($_POST['photographer']) : $missing_fields = true ;
            $photo['belongs_to_user_id'] = 0;
            $photo['belongs_to'] = $photo['photographer'];

            $date_array = explode('/', $photo['date_taken']);
            $photo['date_taken'] = mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]);

            if ($missing_fields) $CORE->print_error("One or more fields are missing, please go back and fill them in.");
            else {
                if ($_FILES['image']['type'] != "image/jpeg") $CORE->print_error("You may only upload photos that are in the JPEG format.");
                if (!is_uploaded_file($_FILES['image']['tmp_name'])) $CORE->print_error("It appears you attempted to inject an image, this event has been logged.");
    
                /**************************************/
                /* Upload, name, and resize the image */
                /**************************************/

                $photo['filename'] = str_replace(' ', '', $photo['photographer']) . '_' . time() . '.jpg';
                
                $CORE->get_class_file('photo');
                $PHOTO = new Photo ($_FILES['image']);

                // Save original full size version
                $PHOTO->save( $CORE->config['photo_dir'] . 'full_size/' . $photo['filename'] );


                // Resize for the display image (but keep ratio) and then upload.
                $dest_width = ($PHOTO->photo_framing > 1) ? 250 : 400 ;
                $PHOTO->scale_to_width($dest_width);
                $PHOTO->save( $CORE->config['photo_dir'] . 'display/' . $photo['filename'] );

                // Resize for the thumb nail image (but keep ratio), 
                // then crop to a square, and then upload.
                $dest_width = ($PHOTO->photo_framing > 1) ? 70 : 125 ;
                $src_x      = ($PHOTO->photo_framing > 1) ? 0 : 25 ;
                $src_y      = ($PHOTO->photo_framing > 1) ? 20 : 0 ;
                $PHOTO->crop_square(70, $dest_width, $src_x, $src_y);
                $PHOTO->save( $CORE->config['photo_dir'] . 'thumbs/' . $photo['filename'] );    

                // Resize for the tiny thumb nail image (but keep ratio), 
                // then crop to a square, and then upload.
                $dest_width = ($PHOTO->photo_framing > 1) ? 35 : 75 ;
                $src_x      = ($PHOTO->photo_framing > 1) ? 0 : 20 ;
                $src_y      = ($PHOTO->photo_framing > 1) ? 5 : 0 ;
                $PHOTO->crop_square(35, $dest_width, $src_x, $src_y);
                $PHOTO->save( $CORE->config['photo_dir'] . 'tiny_thumbs/' . $photo['filename'] );    

                // Collect some garbage
                unset($PHOTO, $dest_width, $src_x, $src_y);

    
                /***************************************/
                /* Insert new record into the database */
                /***************************************/

                $DB->insert('photos', $photo);    
                write_admin_log($LOGIN->cookie[3] . ' added ' . $photo['filename'] . '.', $LOGIN->cookie[3]);
                $CORE->redirect('index.php?section=admin&page=photos&status=added');
            }
        }
        break;


    /*****************************/
    // Edit a photo
    /*****************************/
    case 'edit':

        if ( !isset($_GET['photo']) ) $CORE->redirect('index.php?section=admin&page=photos');

        $DB->query("SELECT * FROM `photos` WHERE `filename`='" . $_GET['photo'] . "'");
        if ( !$DB->num_rows ) $CORE->print_error("That photo was not found."); 
    
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=photos">Photo Manager</a> &#187; Edit a photo';
    
        if (isset($_POST['submit'])) {

            $missing_field = false;
            $photo['description'] = ( !empty($_POST['description']) ) ? $_POST['description'] : null ;
            $photo['date_taken'] = ( !empty($_POST['date_taken']) ) ? $_POST['date_taken'] : $missing_field = true;
            $photo['photographer'] = ( !empty($_POST['photographer']) ) ? $_POST['photographer'] : $missing_field = true;
            $photo['location'] = ( !empty($_POST['location']) ) ? $_POST['location'] : $missing_field = true;

            if ($missing_field) $CORE->print_error("You are missing one or more required fields.");
    
            $date_array = explode('/', $photo['date_taken']);
            $photo['date_taken'] = mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]);

            $orig_file = $DB->result['filename'];
            $tmp = explode("_", $orig_file);
            $file_ending = $tmp[1];
            $photo['filename'] = str_replace(' ', '', $photo['photographer']) . '_' . $file_ending;
            shell_exec ('mv -f ' . $CORE->config['photo_dir'] . 'display/' . $orig_file . ' ' . $CORE->config['photo_dir'] . 'display/' . escapeshellcmd($photo['filename']));
            shell_exec ('mv -f ' . $CORE->config['photo_dir'] . 'full_size/' . $orig_file . ' ' . $CORE->config['photo_dir'] . 'full_size/' . escapeshellcmd($photo['filename']));
            shell_exec ('mv -f ' . $CORE->config['photo_dir'] . 'tiny_thumbs/' . $orig_file . ' '.$CORE->config['photo_dir'] . 'tiny_thumbs/' . escapeshellcmd($photo['filename']));
            shell_exec ('mv -f ' . $CORE->config['photo_dir'] . 'thumbs/' . $orig_file . ' ' . $CORE->config['photo_dir'] . 'thumbs/' . escapeshellcmd($photo['filename']));

            $DB->update('photos', $photo, array('filename' => $DB->result['filename']) );
            write_admin_log($LOGIN->cookie[3] . ' edited the photo ' . $photo['filename'], $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=photos&status=updated');

        } else {

            if (isset($_GET['updated'])) {
                    write_admin_log($user_cookie[2] . ' updated ' . $photo_result['filename'] . '.');
                    $main .= '<font color="#FF0000">'.$photo_result['filename'].' was successfully updated.</font></br /><br /><br />';
            }
            print draw_shadow($CORE->config['photo_dir'] . 'display/' . $DB->result['filename']) . '<br /><br />
                    <form action="index.php?section=admin&page=photos&action=edit&photo=' . $DB->result['filename'] . '" method="POST">
                    Edit the information below and click submit to make changes.<br />
                    <table width="100%" align="center" cellspacing="0" cellpadding="5" border="0">
                        <tr>
                            <td align="right" width="55">Photo:</td>
                            <td class="file_update">' . $DB->result['filename'] . '</td>
                        </tr>
                        <tr>
                            <td align="right" width="55">Date Taken: </td>
                            <td><input type="text" name="date_taken" size="15" value="' . date('n/j/Y', $DB->result['date_taken']) . '"></td>
                        </tr>
                        <tr>
                            <td align="right" width="55">Photographer: </td>
                            <td><input type="text" name="photographer" size="15" value="' . $DB->result['photographer'] . '"></td>
                        </tr>
                        <tr>
                            <td align="right" width="55">Location: </td>
                            <td><input type="text" name="location" size="15" value="' . $DB->result['location'] . '"></td>
                        </tr>
                        <tr>
                            <td align="right" valign="top" width="55">Description:<br /><small>(Keep it short)</small></td>
                            <td><input type="text" name="description" size="50" maxlength="255" value="' . $DB->result['description'] . '"><br /><br /><small>* All fields except the description field are required</small></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" name="submit" value="Update"> 
                            </td>
                        </tr>
                    </table>
                    </form>
                    ';
        }
        break;


    /*****************************/
    // Delete a photo
    /*****************************/
    case 'delete':

        if ( !isset($_GET['photo']) ) $CORE->redirect('index.php?section=admin&page=photos');

        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=photos">Photo Manager</a> &#187; Delete a photo';

        $DB->query("SELECT * FROM `photos` WHERE `filename`='" . $_GET['photo'] . "' LIMIT 1");
        if ( !$DB->num_rows ) $CORE->print_error("Sorry, that photo was not found.");
    
        if ( isset($_GET['confirm']) && $_GET['confirm'] == 'yes' ) {

            $DB->delete('photos', array('filename' => $DB->result['filename']));
            shell_exec('rm -f ' . $CORE->config['photo_dir'] . 'display/' . escapeshellcmd($DB->result['filename']) . ' ' . 
                        $CORE->config['photo_dir'] . 'full_size/' . escapeshellcmd($DB->result['filename']) . ' ' . 
                        $CORE->config['photo_dir'] . 'thumbs/' . escapeshellcmd($DB->result['filename']) . ' ' . 
                        $CORE->config['photo_dir'] . 'tiny_thumbs/' . escapeshellcmd($DB->result['filename']) );
            write_admin_log($LOGIN->cookie[3] . ' deleted the photo ' . escapeshellcmd($DB->result['filename'] . '.', $LOGIN->cookie[3]));
            $CORE->redirect('index.php?section=admin&page=photos&status=deleted');

        } else {

            print draw_shadow($CORE->config['photo_dir'] . 'display/' . $DB->result['filename']) . '<br />
                You are about to delete the photo <font color="#FF0000">' . $_GET['photo'] . '</font>. <br /><br />
                Are you sure you want to do this?<br /><br />
                <div align="center">
                <input type="submit" onclick="window.location=\'index.php?section=admin&page=photos&action=delete&photo=' . $DB->result['filename'] . '&confirm=yes\'" value="Yes"> 
                <input type="button" value="No" onclick="window.location=\'index.php?section=admin&page=photos\'"> 
                </div>';
        }
        break;


    /*****************************/
    // Display Photos
    /*****************************/
    default:
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; Photo Manager';

        print '<table cellspacing="2" border="0" width="100%"><tr><td colspan="5" align="left">Click on a photo to edit it\'s properties. </td></tr><tr><td>';
        $DB->query("SELECT `filename`,`id` FROM `photos` WHERE `belongs_to_user_id`='0' ORDER BY `date_taken` DESC, `filename` DESC");
        foreach ($DB->result as $row) {
            print '<a href="index.php?section=admin&page=photos&action=edit&photo=' . $row['filename'] . '"><img src="' . $CORE->config['path'] . $CORE->config['photo_dir'] . 'thumbs/' . $row['filename'] . '" border="0"></a> ';
        }
        print '</td></tr></table> <a name="video"></a><br /> ';
    

        /* Upload photos form */
        print '<b>Upload a new photo:</b><br /> 
                <form action="index.php?section=admin&page=photos&action=add" method="POST" enctype="multipart/form-data">
                <table width="100%" align="center" cellspacing="0" cellpadding="5" border="0">
                    <tr>
                        <td align="right" width="55">Photo</td>
                        <td class="file_update"><input class="file_update" type="file" name="image" size="20"></td>
                    </tr>
                    <tr>
                        <td align="right" width="55">Date Taken: </td>
                        <td><input type="text" name="date_taken" size="15" value="' . date('m/d/Y') . '"></td>
                    </tr>
                    <tr>
                        <td align="right" width="55">Photographer: </td>
                        <td><input type="text" name="photographer" size="15"></td>
                    </tr>
                    <tr>
                        <td align="right" width="55">Location: </td>
                        <td><input type="text" name="location" size="15"></td>
                    </tr>
                    <tr>
                        <td align="right" valign="top" width="55">Description:<br /><small>(Keep it short)</small></td>
                        <td><input type="text" name="description" size="50"><br /><br /><small>* All fields except the description field are required</small></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" name="submit" value="Upload"> 
                        </td>
                    </tr>
                </table>
                </form>
                ';

} // end switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>
