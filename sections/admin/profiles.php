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
* File:     sections/admin/profiles.php
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
$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? ($_GET['id']) : null;
$action = (isset($_GET['action']) ) ? $_GET['action'] : null ; 

// Check a couple things if a post ID is specified
$user = $DB->query("SELECT `user_level` FROM `users` WHERE `id` = '" . $LOGIN->cookie[1] . "'");
if ($id) { 

    // Select that post from the database
    $profile = $DB->query("SELECT * FROM `profiles` WHERE `id`='" . $id . "'");

    // If the post doesn't even exist then print an error and quit
    if (!$profile) $CORE->print_error("Sorry that skater couldn't be found.");

    // Make sure the user has enough privileges to make and edit posts
    if (($user['user_level'] != 3 && $profile['first_name'] . ' ' . $profile['last_name'] != $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4])) $CORE->print_error("Sorry, you don't have authorization to edit this profile.");

} 


// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The profile was updated successfully.<div>';
            break;

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The new photo was uploaded successfully.<div>';
            break;

        case 'edited':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The photo was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The photo was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 

$CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; ';

switch ($action) {

    /*****************************/
    // Edit A Skater's Profile
    /*****************************/
    case 'profile':

        $profile = $DB->query("SELECT * FROM `profiles` WHERE `id` = '" . $id . "'");
        $CORE->content['heading'] .= '<a href="index.php?section=admin&page=profiles">Profile Manager</a> &#187; Edit ' . ucfirst($profile['first_name']) . '\'s Profile';
        if (isset($_POST['submitProfile'])) {
        
            $new_filename = (isset($profile['primary_photo'])) ? $profile['primary_photo']: null;
            if (!empty($_FILES['image']['tmp_name'])) {
                if ($_FILES['image']['type'] != 'image/jpeg' ) $CORE->print_error("The image you are trying to upload isn't a JPEG formatted image. ");
        
                if (isset($profile['primary_photo'])){
                    if ( !strstr("/", $_POST['current_photo']) ) {
                        $old_photo = $CORE->config['full_path'] . "images/primary_photos/" . escapeshellcmd($_POST['current_photo']);
                        shell_exec("rm -f $old_photo"); 
                    } else {
                        $CORE->print_error("There was an error trying to delete photo.");
                    }
                    $DB->update('profiles', array('primary_photo' => 'null'), array('user_id' => $id));
                    $new_filename = null;
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
        
                $ratio = $src_width / $src_height;
                $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                $dest_width = $src_width * $scale;
                $dest_height = $src_height * $scale;
                if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
        
                $src_image = imagecreatefromjpeg ($uploaded_image);
                $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                if (!imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height)) 
                    $CORE->print_error("There was an error trying to resize the image you uploaded.");
                $new_filename = $profile['first_name'].$profile['last_name']. '_'.time(). '.jpg';
                imagejpeg($dest_image, 'images/primary_photos/' . $new_filename, 95);
                imagedestroy($src_image);
                imagedestroy($dest_image);
            }
        
            $update['description'] = (isset($_POST['description'])) ? str_replace("'","&#39;",nl2br(($_POST['description']))) : null ;
            $update['primary_photo'] = (isset($_POST['primary_photo'])) ? str_replace("/","&#47;",($_POST['primary_photo'])) : null ;
            $update['msn_messenger'] = (isset($_POST['msn_messenger'])) ? ($_POST['msn_messenger']) : null ;
            $update['aim'] = (isset($_POST['aim'])) ? $_POST['aim'] : null ;
            $update['yahoo'] = (isset($_POST['yahoo'])) ? $_POST['yahoo'] : null ;
            $update['google'] = (isset($_POST['google'])) ? $_POST['google'] : null ;
            $update['facebook'] = (isset($_POST['facebook'])) ? $_POST['facebook'] : null ;
            $update['myspace'] = (isset($_POST['myspace'])) ? $_POST['myspace'] : null ;
            $update['flickr'] = (isset($_POST['flickr'])) ? $_POST['flickr'] : null ;
            $update['website'] = (isset($_POST['website'])) ? $_POST['website'] : null ;
            $update['delicious'] = (isset($_POST['delicious'])) ? $_POST['delicious'] : null ;
            $update['skype'] = (isset($_POST['skype'])) ? $_POST['skype'] : null ;
            $update['youtube'] = (isset($_POST['youtube'])) ? $_POST['youtube'] : null;
            
            if ($new_filename) $update['primary_photo'] = $new_filename;
            $DB->update('profiles', $update, array('id' => $id));
    
            if ($profile['first_name'] == $LOGIN->cookie[3]) {
                write_admin_log($LOGIN->cookie[3] . ' updated his profile. ', $LOGIN->cookie[3]);
            } else {
                write_admin_log($LOGIN->cookie[3] . ' updated ' . $profile['first_name'] . '\'s profile. ', $LOGIN->cookie[3]);
            }
        
            $CORE->redirect('index.php?section=admin&page=profiles&status=updated');
        }
        
        $profile = $DB->query("SELECT * FROM `profiles` WHERE id='" . $id . "'");
        $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/primary_photos/' . $profile['primary_photo'];
        print '
                <form action="index.php?section=admin&page=profiles&action=profile&id=' . $id. '" method="POST" enctype="multipart/form-data">
                <table width="100%" align="center" cellspacing="0" cellpadding="5" border="0">
                    <tr>
                        <td colspan="2" valign="top" align="left"><b>Description:</b><br /><textarea rows="10" cols="68" name="description">' . br2nl($profile['description']). '</textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Other Stuff:</b></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/website.gif"></td>
                        <td><input type="text" name="website" size="35" value="' . $profile['website']. '"> <small>(Include "http://")</small></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/aim.gif"></td>
                        <td><input type="text" name="aim" size="15" value="' . $profile['aim']. '"></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/msn.gif"></td>
                        <td><input type="text" name="msn_messenger" size="15" value="' . $profile['msn_messenger']. '"></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/yahoo.gif"></td>
                        <td><input type="text" name="yahoo" size="15" value="' . $profile['yahoo']. '"></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/google.gif"></td>
                        <td><input type="text" name="google" size="15" value="' . $profile['google']. '"></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/facebook.gif"></td>
                        <td><input type="text" name="facebook" size="15" value="' . $profile['facebook']. '"> <small>(Email Address)</small></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/myspace.gif"></td>
                        <td><input type="text" name="myspace" size="15" value="' . $profile['myspace']. '"> <small>(Username)</small></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/flickr.gif"></td>
                        <td><input type="text" name="flickr" size="15" value="' . $profile['flickr']. '"> <small>(Username)</small></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/delicious.gif"></td>
                        <td><input type="text" name="delicious" size="15" value="' . $profile['delicious']. '"> <small>(Username)</small></td>
                    </tr>
                    <tr>
                        <td width="75"><img src="images/icons/skype.gif"></td>
                        <td><input type="text" name="skype" size="15" value="' . $profile['skype']. '"> <small>(Username)</small></td>
                    </tr>
                    <tr> 
                        <td width="75"><img src="images/icons/youtube.gif"></td>
                        <td><input type="text" name="youtube" size="15" value="' . $profile['youtube']. '"> <small>(Username)</small></td>
                    </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td align="right" valign="top"><b>Photo:</b></td>
                    <td class="file_update">
                        <input type="hidden" name="current_photo" value="' . $profile['primary_photo']. '">
                        <input class="file_update" type="file" name="image" size="20"> <br /><small>Uploading a new photo will overwrite the old one.</small></td>
                </tr>
                    <tr><td height="5"></td><td></td></tr>
                    <tr>
                        <td colspan="2" align="center"><input type="submit" name="submitProfile" value="Submit Changes"> <input type="reset" name="resetProfile" value="Revert Changes"> <input type="button" name="resetProfile" value="Admin Home" onclick="javascript: document.location='."'index.php?section=admin'". '; return false;"> </td>
                    </tr>
                </table>
                </form>
                ';
        
        /* Random Questions */
    //    display_profile_questions($id);
    //    $bluebox .= '<a href="index.php?section=admin&page=editquestions&id=' . $id. '">Edit these questions</a><br /><br />';
        
        /* Profile Links */
    //    display_profile_links($profile);
    
            break;


    /*****************************/
    // Edit A Skater's Questions
    /*****************************/
    case 'questions':

        $profile = $DB->query("SELECT * FROM `profiles` WHERE `id` = '" . $id . "'");
        $CORE->content['heading'] .= '<a href="index.php?section=admin&page=profiles">Profile Manager</a> &#187; Edit ' . ucfirst($profile['first_name']) . '\'s Questions';

        if (isset($_POST['submitForm'])) {
            $question_ids = array();
            $questions = array();
            $answers = array();
        
            for ($i=1; $i<=10; $i++){
                array_push($questions, $_POST['question'.$i]);
                array_push($answers, $_POST['answer'.$i]);
            }

            /* Delete all old records to make way for the new ones */
            $DB->delete('profile_questions', array('belongs_to_id' => $id));
        
            /* Insert new records */
            $ans = current($answers);
            foreach ($questions as $question) {
                if ( !empty($question) ) { 
                    $DB->insert('profile_questions', array( 'belongs_to_id' => $id,
                                                            'question' => $question,
                                                            'answer' => $ans ));
                }
                $ans = next($answers);
            }
        
            $DB->query("SELECT `first_name` FROM `profiles` WHERE `id`='" . $id . "'");
            write_admin_log($LOGIN->cookie[3] . ' updated ' . ucfirst($DB->result['first_name']) . '\'s profile questions.', $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=profiles&status=updated');
        
        }
        
        print '        
                <form action="index.php?section=admin&page=profiles&action=questions&id='.$id.'" method="POST">
                <table width="100%" align="left" cellspacing="0" cellpadding="4" border="0">';
        
        $DB->query("SELECT * FROM `profile_questions` WHERE belongs_to_id='".$id."' LIMIT 1");
        for ($i=1; $i<=10; $i++) {
            if (!empty($row['question'])) print '<input type="hidden" name="id' . $i . '" value="' . $DB->result['id'] . '">';
        }

        $row = $DB->query("SELECT * FROM `profile_questions` WHERE belongs_to_id='" . $id . "' ORDER BY `id` ASC LIMIT 10");

        // If there's only one question in the DB
        if ( $DB->num_rows == 1) {
            $i = 1;
            print '
                        <tr>
                            <td align="right"> Question ' . $i . ':</td>
                            <td align="left"><input type="text" size="20" name="question' . $i . '" value="' . $row['question'] . '"></td>
                            <td align="right"> Answer ' . $i . ':</td>
                            <td align="left"><input type="text" size="25" name="answer' . $i . '" value="' . $row['answer'] . '"></td>
                        </tr>';

            for ($i=2; $i<=10; $i++) {
                print '
                            <tr>
                                <td align="right"> Question ' . $i . ':</td>
                                <td align="left"><input type="text" size="20" name="question' . $i . '" value=""></td>
                                <td align="right"> Answer ' . $i . ':</td>
                                <td align="left"><input type="text" size="25" name="answer' . $i . '" value=""></td>
                            </tr>';
            }


        } else { // If there's 0 or more than 1 question in the database

            for ($i=1; $i<=10; $i++) {
                print '
                            <tr>
                                <td align="right"> Question ' . $i . ':</td>
                                <td align="left"><input type="text" size="20" name="question' . $i . '" value="' . @$row[$i-1]['question'] . '"></td>
                                <td align="right"> Answer ' . $i . ':</td>
                                <td align="left"><input type="text" size="25" name="answer' . $i . '" value="' . @$row[$i-1]['answer'] . '"></td>
                            </tr>';
            }

        }

        print '
                    <tr>
                        <td></td>
                        <td colspan="3" ><input type="submit" name="submitForm" value="Update"> </td>
                    </tr>
                    <tr><td height="5"></td></tr>
                </table>
                </form>
                ';
        break;


    /*****************************/
    // Edit A Skater's Photos
    /*****************************/
    case 'photos':

        $profile = $DB->query("SELECT * FROM `profiles` WHERE `id` = '" . $id . "'");
        $CORE->content['heading'] .= '<a href="index.php?section=admin&page=profiles">Profile Manager</a> &#187; Edit ' . ucfirst($profile['first_name']) . '\'s Photos';

        switch ( ( isset($_GET['subaction']) ? $_GET['subaction'] : null )) {

            /*****************************/
            // Edit an existing photo
            /*****************************/
            case 'edit':

                $photo_result = $DB->query("SELECT * FROM `photos` WHERE `filename`='{$_GET['photo']}' AND `belongs_to_user_id`='$id'");

                if (!$photo_result) $CORE->print_error("Sorry, that photo was not found.");

                if (isset($_POST['submit'])) {

                    $missing_field = false;
                    
                    $photo['description'] = (!empty($_POST['description'])) ? ($_POST['description']) : null ;
                    $photo['date_taken'] = (!empty($_POST['date_taken'])) ? ($_POST['date_taken']) : $missing_field = true;
                    $photo['photographer'] = (!empty($_POST['photographer'])) ? ($_POST['photographer']) : $missing_field = true;
                    $photo['location'] = (!empty($_POST['location'])) ? ($_POST['location']) : $missing_field = true;

                    if ($missing_field) $CORE->print_error("You are missing one or more required fields.");
            
                    $date_array = explode('/', $photo['date_taken']);
                    $photo['date_taken'] = mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]);

                    $DB->update('photos', $photo, array('filename' => $photo_result['filename']));

                    if ($LOGIN->cookie[1] == $photo_result['belongs_to_user_id'])
                        write_admin_log($LOGIN->cookie[3] . ' updated one of ' . $profile['first_name'] . '\'s photos.', $LOGIN->cookie[3]);
                    else 
                        write_admin_log($LOGIN->cookie[3] . ' updated one of ' . $profile['first_name'] . '\'s photos.', $LOGIN->cookie[3]);

                    $CORE->redirect("index.php?section=admin&page=profiles&action=photos&id=" . $id . "&status=edited");

                } else {

                    print draw_shadow($CORE->config['photo_dir'] . 'display/' . $photo_result['filename']).'<br /><br />
                            <form action="index.php?section=admin&page=profiles&action=photos&subaction=edit&id=' . $id . '&photo=' . $photo_result['filename'] . '" method="POST">
                            Edit the information below and click submit to make changes.<br />
                            <table width="100%" align="center" cellspacing="0" cellpadding="5" border="0">
                                <tr>
                                    <td align="right" width="55">Photo</td>
                                    <td class="file_update">' . $photo_result['filename'] . '</td>
                                </tr>
                                <tr>
                                    <td align="right" width="55">Date Taken: </td>
                                    <td><input type="text" name="date_taken" size="15" value="'.date('n/j/Y', $photo_result['date_taken']).'"></td>
                                </tr>
                                <tr>
                                    <td align="right" width="55">Photographer: </td>
                                    <td><input type="text" name="photographer" size="15" value="' . $photo_result['photographer'] . '"></td>
                                </tr>
                                <tr>
                                    <td align="right" width="55">Location: </td>
                                    <td><input type="text" name="location" size="15" value="' . $photo_result['location'] . '"></td>
                                </tr>
                                <tr>
                                    <td align="right" valign="top" width="55">Description:<br /><small>(Keep it short)</small></td>
                                    <td><input type="text" name="description" size="50" maxlength="255" value="' . $photo_result['description'] . '"><br /><br /><small>* All fields except the description field are required</small></td>
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
            // Delete an existing photo
            /*****************************/
            case 'delete':
                $photo_result = $DB->query("SELECT * FROM `photos` WHERE `filename`='{$_GET['photo']}' AND `belongs_to_user_id`='$id'");
                if (!$photo_result) $CORE->print_error("Sorry, that photo was not found.");
            
                if (isset($_POST['submit'])) {
                    
                    $filename = $photo_result['filename'];
                    $DB->delete('photos', array('filename' => $filename));
                    shell_exec('rm -f ' . $CORE->config['photo_dir'] . 'display/' . escapeshellcmd($filename) . ' ' . 
                                $CORE->config['photo_dir'] . 'full_size/' . escapeshellcmd($filename) . ' ' . 
                                $CORE->config['photo_dir'] . 'thumbs/' . escapeshellcmd($filename) . ' ' . 
                                $CORE->config['photo_dir'] . 'tiny_thumbs/' . escapeshellcmd($filename));
                    write_admin_log($LOGIN->cookie[3] . ' deleted one of ' . $profile['first_name'] . "\'s photos.", $LOGIN->cookie[3]);
                    $CORE->redirect("index.php?section=admin&page=profiles&action=photos&id=" . $id . "&status=deleted");
    
                } else {
    
                    print draw_shadow($CORE->config['photo_dir'] . 'display/' . $photo_result['filename']) . '
                        <br />You are about to delete the photo <font color="#FF0000">' . $_GET['photo'] . '</font>. <br /><br />Are you sure you want to do this?
                        <br /><br />
                        <form method="POST" action="index.php?section=admin&page=profiles&action=photos&subaction=delete&id=' . $id . '&photo=' . $photo_result['filename'].'">
                        <div align="center">
                        <input type="submit" name="submit" value="Yes"> 
                        <input type="button" value="No" onclick="window.location=\'index.php?section=admin&page=profiles&action=photos&id=' . $id . '\'"> 
                        </div></form>';
                }
                break;

            /*****************************/
            // Add new photo
            /*****************************/
            case 'add':
                if (isset($_POST['submit'])) {
                    $missing_fields = false;
                    if (!isset($_FILES['image']['tmp_name'])) $missing_fields = true;
                    $new_photo['description'] = (!empty($_POST['description'])) ? $_POST['description'] : null ;
                    $new_photo['location'] = (!empty($_POST['location'])) ? $_POST['location'] : $missing_fields = true ;
                    $new_photo['date_taken'] = (!empty($_POST['date_taken'])) ? $_POST['date_taken'] : $missing_fields = true ;
                    $new_photo['photographer'] = (!empty($_POST['photographer'])) ? $_POST['photographer'] : $missing_fields = true ;
                    $new_photo['belongs_to_user_id'] = $profile['id'];
                    $new_photo['belongs_to'] = $profile['first_name'] . ' ' . $profile['last_name'];
    
                    $date_array = explode('/', $new_photo['date_taken']);
                    $new_photo['date_taken'] = mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]);

                    if ($missing_fields) $CORE->print_error("One or more fields are missing, please go back and fill them in.");
                    else {
                        if ($_FILES['image']['type'] != "image/jpeg") $CORE->print_error("You may only upload photos that are in the JPEG format.");
                        if (!is_uploaded_file($_FILES['image']['tmp_name'])) $CORE->print_error("It appears you attempted to inject an image, this event has been logged.");
    
                        /**************************************/
                        /* Upload, name, and resize the image */
                        /**************************************/
    
                        $new_photo['filename'] = $profile['first_name'] . $profile['last_name'] . '_' . time() . '.jpg';
                        $size = getimagesize($_FILES['image']['tmp_name']);
                        $src_width = $size[0];
                        $src_height = $size[1];
                        $dest_height  = 0;
                        $uploaded_image  = $_FILES['image']['tmp_name'];
    
                        // $photo_framing can be 1 or 2. 1 = landscape. 2 = portrait 
                        $photo_framing = ($src_width > $src_height) ? 1 : 2;
                        if ($photo_framing > 1) // Portrait
                            if ($src_width < 250) 
                                $CORE->print_error("The photo you uploaded is too small. Portrait framed photos must be at least 200 pixels wide. The photo you uploaded was $src_width wide.");
                        else if ($photo_framing == 1) // Landscape
                            if ($src_width < 400) 
                                $CORE->print_error("The photo you uploaded is too small. Landscape framed photos must be at least 400 pixels wide. The photo you uploaded was $src_width wide.");
                        else $CORE->print_error("There was an error determining the photo framing.");
    
    
                        // Upload the original photo.
                        $src_image = imagecreatefromjpeg ($uploaded_image);
                        imagejpeg($src_image, $CORE->config['photo_dir'] . 'full_size/' . $new_photo['filename'], 80);


                        // Resize for the display image (but keep ratio) and then upload.
                        $CORE->get_class_file('photo');
                        $PHOTO = new Photo ($_FILES['image']);
                        if ($PHOTO->photo_framing > 1) // Portrait
                            $dest_width = 250;
                        else // Landscape
                            $dest_width = 400;
                        $PHOTO->scale_to_width($dest_width);
                        $PHOTO->save( $CORE->config['photo_dir'] . 'display/' . $new_photo['filename'] );
                        unset($PHOTO);
            
            
                        // Resize for the thumb nail image (but keep ratio), 
                        // then crop to a square, and then upload.
                        $ratio = $src_width / $src_height;
                        $square_dimension = 70;
                        if ($photo_framing > 1) {
                            // Portrait
                            $dest_width = $square_dimension;
                
                            $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                            $dest_width = $src_width * $scale;
                            $dest_height = $src_height * $scale;
                            if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
                
                            $src_image = imagecreatefromjpeg ($uploaded_image);
                            $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                
                            imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
                            $dest_image2 = imagecreatetruecolor($square_dimension,$square_dimension);
                            imagecopy ($dest_image2, $dest_image, 0, 0, 0, 20, $square_dimension, $square_dimension);
                
                            imagejpeg($dest_image2, $CORE->config['photo_dir'] . 'thumbs/' . $new_filename, 80);
                        } else { 
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
                
                            imagejpeg($dest_image2, $CORE->config['photo_dir'] . 'thumbs/' . $new_photo['filename'], 80);
                        }

            
                        // Resize for the tiny thumb nail image (but keep ratio), 
                        // then crop to a square, and then upload.
                        $ratio = $src_width / $src_height;
                        $square_dimension = 35;
                        if ($photo_framing > 1) {
                            // Portrait
                            $dest_width = $square_dimension;
            
                            $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                            $dest_width = $src_width * $scale;
                            $dest_height = $src_height * $scale;
                            if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
                
                            $src_image = imagecreatefromjpeg ($uploaded_image);
                            $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                
                            imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
                            $dest_image2 = imagecreatetruecolor($square_dimension,$square_dimension);
                            imagecopy ($dest_image2, $dest_image, 0, 0, 0, 5, $square_dimension, $square_dimension);
                
                            imagejpeg($dest_image2, $CORE->config['photo_dir'] . 'tiny_thumbs/' . $new_filename, 80);
                        } else { 
                            // Landscape
                            $dest_width = 75;
                
                            $scale = ($ratio) ? $dest_width / $src_width : $dest_height / $src_height; 
                            $dest_width = $src_width * $scale;
                            $dest_height = $src_height * $scale;
                            if ($dest_width >= $src_width && $dest_width >= $src_width) $scale = 1;
                
                            $src_image = imagecreatefromjpeg ($uploaded_image);
                            $dest_image = imagecreatetruecolor($src_width * $scale, $src_height * $scale);
                
                            imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
                            $dest_image2 = imagecreatetruecolor($square_dimension,$square_dimension);
                            imagecopy ($dest_image2, $dest_image, 0, 0, 20, 0, $square_dimension, $square_dimension);
                
                            imagejpeg($dest_image2, $CORE->config['photo_dir'] . 'tiny_thumbs/' . $new_photo['filename'], 80);
                        }
    
    
                        // Finally, destroy the image.
                        imagedestroy($src_image);
                        imagedestroy($dest_image);
    
    
                        /***************************************/
                        /* Insert new record into the database */
                        /***************************************/
    
                        $DB->insert('photos', $new_photo);
                        write_admin_log($LOGIN->cookie[3] . ' added a new photo to ' . $profile['first_name'] . '\'s profile.', $LOGIN->cookie[3]);
                        $CORE->redirect('index.php?section=admin&page=profiles&action=photos&id=5&status=added');
                    }
                }
                break;

            default:
                $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=profiles">Profile Manager</a> &#187; Edit ' . $profile['first_name'] . '\'s Photos';
            
                /* Display existing photos here */
                $DB->query("SELECT * FROM `photos` WHERE `belongs_to_user_id`='$id' ORDER BY `date_taken` DESC, `filename` DESC");
                if ($DB->num_rows) {
                    print 'Click on a photo to make changes to it or delete it. <br /><br /> ';
                
                    print '<table cellspacing="2" width="95%" border="0">';
                    $loopCounter = 0;
                    foreach ($DB->result as $row) {
                        print '<tr><td><table cellspacing="2" border="0"><tr><td valign="top" align="center">' . 
                            draw_shadow($CORE->config['photo_dir'] . 'thumbs/' . $row['filename'], 
                            'index.php?section=admin&page=profiles&action=photos&subaction=edit&id=' . $id . '&photo=' . $row['filename']) . '</td><td valign="top">
                            <b>Date Taken:</b> ' . date('n/j/Y', $row['date_taken']) . ' <br />
                            <b>Photographer:</b> ' . $row['photographer'] . '  <br />
                            <b>Location:</b> ' . $row['location'] . ' <br />
                            <b>Description:</b> ' . $row['description'] . ' <br />
                            <a href="index.php?section=admin&page=profiles&action=photos&subaction=delete&id=' . $id . '&photo=' . $row['filename'] . '">Delete this photo</a>
                            </td></tr></table></td></tr>
                            ';
                    }
                    print '</table><br /><br />';
                }
            
            
                /* Upload photos form */
                print '<form action="index.php?section=admin&page=profiles&action=photos&subaction=add&id='.$id.'" method="POST" enctype="multipart/form-data">';
                if ($DB->result) print 'Or upload a new photo:<br />'; else print 'Upload a new photo:<br />';
                print '
                        <table width="100%" align="center" cellspacing="0" cellpadding="5" border="0">
                            <tr>
                                <td align="right" width="55">Photo: </td>
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
                                <td><input type="text" name="description" size="50" maxlength="255"><br /><br /><small>* All fields except the description field are required</small></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <input type="submit" name="submit" value="Upload"> 
                                </td>
                            </tr>
                        </table>
                        </form>
                        ';
        }
        break;

    /*****************************/
    // Display All Skaters 
    /*****************************/
    default:
        $CORE->content['heading'] .= 'Profile Manager';

        print '<table cellspacing="2" border="0">';

        // Get all the data and print them in a nice table
        $DB->query("SELECT * FROM `profiles` ORDER BY `id` ASC");
        foreach ($DB->result as $row) {

            print '<tr>
                        <td valign="top" class="admin_pages_display">
                            <a href="index.php?section=admin&page=profiles&action=profile&id=' . $row['id'] . '"><img src="' . $CORE->config['path'] . 'images/primary_photos/' . $row['primary_photo'] . '" class="admin_pages_image_thumbs" border="0"></a>
                        </td>
                        <td></td>
                        <td valign="top">
                            <b>' . ucfirst($row['first_name']) . ' ' . ucfirst($row['last_name']) . '</b> <small>[Edit: <a href="index.php?section=admin&page=profiles&action=profile&id=' . $row['id'] . '">Profile</a>|<a href="index.php?section=admin&page=profiles&action=questions&id=' . $row['id'] . '">Questions</a>|<a href="index.php?section=admin&page=profiles&action=photos&id=' . $row['id'] . '">Photos</a>] <br /><br /><span style="color: #888">' . $CORE->shorten_text($row['description'], 30, '... ') . '</span><small></td>
                    </tr>';
        }
        print"</table><br /><br />";

} // End switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>