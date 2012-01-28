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
* File:     sections/admin/video.php
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

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> Your video was added successfully.<div>';
            break;

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The video was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The video was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 

switch ($action) {

    /*****************************/
    // Add A Video
    /*****************************/
    case 'add':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=video">Video Manager</a> &#187; Add Video';

        if (isset($_POST['submit'])) {

            $allowed_types = array( '.mov' => 'video/quicktime',
                                    '.mpg' => 'video/mpeg',
                                    '.mpg' => 'video/mpg',
                                    '.avi' => 'video/avi' );

            $valid_type = false;
            foreach ($allowed_types as $tmp_type) {
                if ($_FILES['video']['type'] == $tmp_type) $valid_type = true;
            }

            // Print error if the video is an invalid type
            if (!$valid_type) {
                $tmp = explode('/', $_FILES['video']['type']);
                $CORE->print_error('The file you uploaded is type "' . $tmp[1] . '" which is an invalid type. Allowed types are: Quicktime, MPEG, and Avi. Please check the file type and try again. <br /><br />Please note: Sometimes PHP pukes all over itself when trying to determine the file type of an uploaded file, if you think this is the case then contact Matt and make him deal with it.');
            } 

            // Set some variables
            $missing_fields = false;
            $video['title'] = (!empty($_POST['title'])) ? ucfirst($_POST['title']) : $missing_fields = true;
            $video['url_title'] = (!empty($_POST['url_title'])) ? $_POST['url_title'] : $missing_fields = true;
            $video['filmer'] = (!empty($_POST['filmer'])) ? $_POST['filmer'] : $missing_fields = true;
            $video['editor'] = (!empty($_POST['editor'])) ? $_POST['editor'] : $missing_fields = true;
            $video['featuring'] = (!empty($_POST['featuring'])) ? $_POST['featuring'] : $missing_fields = true;
            $video['date'] = date('n/j/Y');
            $video['music'] = (!empty($_POST['music'])) ? $_POST['music'] : 'NULL';
            $video['description'] = (!empty($_POST['description'])) ? $_POST['description'] : 'NULL';
            $video['widescreen'] = ($_POST['aspect_ratio'] == 'wide') ? 1 : 0 ;
            $video['filesize'] = $_FILES['video']['size'];
            $video['runtime'] = (!empty($_POST['runtime'])) ? $_POST['runtime'] : $missing_fields = true;
            $video['software_info'] = (!empty($_POST['software'])) ?  $_POST['software'] : 'NULL';
            $video['camera_info'] = (!empty($_POST['camera'])) ? $_POST['camera'] : 'NULL';
    
            // Print error if one of the required fields is empty
            if ( empty($_FILES['video']['name']) || $missing_fields ) $CORE->print_error('You are missing one or more required fields, please go back and try again.');
    
            // Determine proper file extension for the uploaded video
            reset($allowed_types);
            while ($type = current($allowed_types)) {
                if ($type == $_FILES['video']['type']) {
                   $file_ext = key($allowed_types);
                }
                next($allowed_types);
            }

            // Set some variables
            $video['filename'] = str_replace(' ', '', $title) . '_' . time() . $file_ext;

            // Save the video to the server
            move_uploaded_file($_FILES['video']['tmp_name'], $CORE->config['full_path'] . $CORE->config['video_dir'] . $filename) or $CORE->print_error('An error occurred while trying to upload your video.');
    
            // Set the appropriate permissions on the video
            chmod($CORE->config['full_path'] . $CORE->config['video_dir'] . $video['filename'], 0644);
    
            // Insert the new video into the database
            $DB->insert('video', $video);

            // Record this event in the admin log
            write_admin_log($LOGIN->cookie[3] . ' uploaded the video "' . $video['title'] . '."', $LOGIN->cookie[3]);

            // Redirect to the main video page
            $CORE->redirect('index.php?section=admin&page=video&status=added');

        }

        print '
        Please make sure the video you are uploading meets these dimension requirements: <br /><br />
        Normal aspect (4:3): 320 x 240px<br />
        Wide aspect (16:9): 420 x 209px.<br /><br />
        The software doesn\'t verify the dimension of what you upload so please use the honor system!<br /><br />
            <form method="POST" action="index.php?section=admin&page=video&action=add" enctype="multipart/form-data">
            <table cellpadding="3" border="0" cellspacing="0" align="left">
                <tr>
                    <td align="right"><b>File: </b></td>
                    <td><input type="file" name="video" size="22"></td>
                </tr>
                <tr>
                    <td align="right"><b>Title: </b></td>
                    <td><input type="text" onkeyup="liveUrlTitle()" id="title" name="title" size="30" maxlength="64"></td>
                </tr>
                <tr>
                <tr>
                    <td align="right"><b>URL: </b></td>
                    <td><input type="text" name="url_title" size="30" id="url_title"></td>
                </tr>
                    <td align="right"><b>Featuring: </b></td>
                    <td><input type="text" name="featuring" size="20" maxlength="64"> <small>Who\'s in the video</small></td>
                </tr>
                <tr>
                <td align="right"><b>Filmer: </b></td>
                <td><input type="text" name="filmer" size="20" maxlength="64"> <small>Who filmed the video</small></td>
                </tr>
                    <tr>
                        <td align="right"><b>Editor: </b></td>
                        <td><input type="text" name="editor" size="20" maxlength="32"> <small>Who made the video</small></td>
                    </tr>
                <tr>
                <td align="right"><b>Runtime: </b></td>
                <td><input type="text" name="runtime" size="10" maxlength="16"> <small>Format: 00:00 (minutes)</small></td>
                </tr>
                    <tr>
                        <td align="right"><b>Aspect Ratio:</b></td>
                        <td><select name="aspect_ratio"> <option value="normal">Normal (4:3)</option> <option value="wide">Wide (16:9)</option> </select></td>
                    </tr>
                <tr><td colspan="2" height="10"></td></tr>
                <tr><td colspan="2"><i>Optional information:</small></td></tr>
                    <tr>
                        <td align="right"><b>Music: </b></td>
                        <td><input type="text" name="music" size="20" maxlength="64"> <small>Format: Artist - Track</small></td>
                    </tr>
                    <tr>
                        <td align="right"><b>Description: </b></td>
                        <td><input type="text" name="description" size="30" maxlength="255"> <small></small></td>
                    </tr>
                <tr>
                <td align="right"><b>Software: </b></td>
                <td><input type="text" name="software" size="20" maxlength="128"> <small>Software used</small></td>
                </tr>
                    <tr>
                        <td align="right"><b>Camera: </b></td>
                        <td><input type="text" name="camera" size="20" maxlength="128"> <small>Camera details</small></td>
                    </tr>
                    <tr><td colspan="2" height="5"></td></tr>
                    <tr><td></td>
                        <td><input type="submit" name="submit" value="Upload"> </td>
                    </tr>
                <tr><td colspan="2" height="10"></td></tr>
                </table>';
        break;


    /*****************************/
    // Edit A Video
    /*****************************/
    case 'edit':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=video">Video Manager</a> &#187; Edit Video';
    
        // If form was submitted
        if (isset($_POST['submit'])) {

            // Set some variables
            $missing_fields = false;
            $id = (!empty($_POST['id'])) ? $_POST['id'] : $missing_fields = true;
            $video['filename'] = (!empty($_POST['filename'])) ? $_POST['filename'] : $missing_fields = true;
            $video['title'] = (!empty($_POST['title'])) ? ucfirst($_POST['title']) : $missing_fields = true;
            $video['url_title'] = (!empty($_POST['url_title'])) ? $_POST['url_title'] : $missing_fields = true;
            $video['filmer'] = (!empty($_POST['filmer'])) ? $_POST['filmer'] : $missing_fields = true;
            $video['editor'] = (!empty($_POST['editor'])) ? $_POST['editor'] : $missing_fields = true;
            $video['featuring'] = (!empty($_POST['featuring'])) ? $_POST['featuring'] : $missing_fields = true;
            $video['date'] = date('n/j/Y');
            $video['music'] = (!empty($_POST['music'])) ? $_POST['music'] : 'NULL';
            $video['description'] = (!empty($_POST['description'])) ? $_POST['description'] : 'NULL';
            $video['widescreen'] = ($_POST['aspect_ratio'] == 'wide') ? 1 : 0 ;
            $video['filesize'] = $_FILES['video']['size'];
            $video['runtime'] = (!empty($_POST['runtime'])) ? $_POST['runtime'] : $missing_fields = true;
            $video['software_info'] = (!empty($_POST['software'])) ?  $_POST['software'] : 'NULL';
            $video['camera_info'] = (!empty($_POST['camera'])) ? $_POST['camera'] : 'NULL';

    
            // If any of the required fields are missing print an error and exit
            if ($missing_fields) $CORE->print_error('You are missing one or more required fields, please go back and try again.');

            // Rename the video file on the server
            $tmp = explode("_", $video['filename']);
            $video['filename'] = str_replace(' ', '', $video['title']) . '_' . $tmp[1];
            shell_exec('mv -f '. $CORE->config['full_dir'] . $CORE->config['video_dir'] . escapeshellcmd($video['filename']) . ' ' . 
                        $CORE->config['full_dir'] . $CORE->config['video_dir'] . escapeshellcmd($video['filename']));

            // Update the video in the database
            $DB->update('video', $video, array('id' => $id) );

            // Record this event to the admin log
            write_admin_log($LOGIN->cookie[3] . ' modified the video "' . $video['title'] . '."', $LOGIN->cookie[3]);

            // Redirect to the main video page
            $CORE->redirect('index.php?section=admin&page=video&status=updated');

        }
    
        print '
            <form method="POST" action="index.php?section=admin&page=video&action=edit&video=' . $video['filename'] . '" enctype="multipart/form-data">
            <table cellpadding="3" border="0" cellspacing="0" align="left">
                <tr>
                    <td align="right"><b>File: </b></td>
                    <td>
                        <input type="hidden" name="filename" value="' . $video['filename'] . '">
                        <input type="hidden" name="id" value="' . $video['id'] . '">
                        ' . $video['filename'] . '
                    </td>
                </tr>
                <tr>
                    <td align="right"><b>Title: </b></td>
                    <td><input type="text" onkeyup="liveUrlTitle()" id="title" name="title" size="30" maxlength="64" value="' . $video['title'] . '></td>
                </tr>
                <tr>
                <tr>
                    <td align="right"><b>URL: </b></td>
                    <td><input type="text" name="url_title" size="30" id="url_title" value="' . $video['url_title'] . '></td>
                </tr>
                <tr>
                    <td align="right"><b>Featuring: </b></td>
                    <td><input type="text" name="featuring" size="20" maxlength="64" value="' . $video['featuring'] . '"> <small>Who\'s in the video</small></td>
                </tr>
                <tr>
                    <td align="right"><b>Filmer: </b></td>
                    <td><input type="text" name="filmer" size="20" maxlength="64" value="' . $video['filmer'] . '"> <small>Who filmed the video</small></td>
                </tr>
                <tr>
                    <td align="right"><b>Editor: </b></td>
                    <td><input type="text" name="editor" size="20" maxlength="32" value="' . $video['editor'] . '"> <small>Who made the video</small></td>
                </tr>
                <tr>
                    <td align="right"><b>Runtime: </b></td>
                    <td><input type="text" name="runtime" size="10" maxlength="16" value="' . $video['runtime'] . '"> <small>Format: 00:00 (minutes)</small></td>
                </tr>
                <tr>
                    <td align="right"><b>Aspect Ratio:</b></td>
                    <td><select name="aspect_ratio"> <option value="normal">Normal (4:3)</option> <option value="wide"';
    
        if ($video['widescreen']) print ' selected';
    
        print '>Wide (16:9)</option> </select></td>
                </tr>
            <tr>
            <td align="right"><b>Filesize:</b></td>
            <td><input type="text" name="filesize" size="5" maxlength="10" value="' . $video['filesize'] . '"> <small>Format: Size in bytes</small></td>
            </tr>
                <tr><td colspan="2" height="10"></td></tr>
                <tr><td colspan="2"><i>Optional information:</small></td></tr>
                <tr>
                    <td align="right"><b>Music: </b></td>
                    <td><input type="text" name="music" size="20" maxlength="64"  value="' . $video['music'] . '"> <small>Format: Artist - Track</small></td>
                </tr>
                <tr>
                    <td align="right"><b>Description: </b></td>
                    <td><input type="text" name="description" size="30" maxlength="255"  value="' . $video['description'] . '"> <small></small></td>
                </tr>
                <tr>
                    <td align="right"><b>Software: </b></td>
                    <td><input type="text" name="software" size="20" maxlength="128"  value="' . $video['software_info'] . '"> <small>Software used</small></td>
                </tr> 
                <tr>
                    <td align="right"><b>Camera: </b></td>
                    <td><input type="text" name="camera" size="20" maxlength="128"  value="' . $video['camera_info'] . '"> <small>Camera details</small></td>
                </tr>
    
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td colspan="2" align="center"><input type="submit" name="submit" value="Submit"> <input type="button" name="cancel" value="Delete Video" onclick="if (confirm(\'Are you sure you want to delete this video? This action cannot be undone.\')) { document.location=\'index.php?section=admin&page=video&action=delete&video=' . $video['filename'] . '&confirm=yes\'; }"> </td>
                </tr>
            <tr><td colspan="2" height="10"></td></tr>
            </table> <br />';
        break;


    /*****************************/
    // Delete A Video
    /*****************************/
    case 'delete':
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=video">Video Manager</a> &#187; Delete Video';

        if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {

            // Delete from database
            $DB->delete('video', array('id' => $video['id']));

            // Delete from server
            shell_exec('/bin/rm -f ' . $CORE->config['video_dir'] . escapeshellcmd($video['filename']));

            // Record this event in the admin log
            write_admin_log($LOGIN->cookie[3] . ' deleted the video "' . $video['title'] . '."', $LOGIN->cookie[3]);

            // Redirect to video main
            $CORE->redirect('index.php?section=admin&page=video&status=deleted');
        }
    
        print 'Are you sure you want to delete "' . $video['title'] .'"? This action cannot be undone.<br /><br />
            <div align="center">    
            <input type="button" value="Yes" onclick="document.location=\'index.php?section=admin&page=video&action=delete&video=' . $video['filename'] . '&confirm=yes\'"> 
            <input type="button" value="No" onclick="document.location=\'index.php?section=admin&page=video\'"> 
            </form>
            </div>';
        break;


    /*****************************/
    // Display All Videos
    /*****************************/
    default:
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; Video Manager';
        print '<div onclick="document.location=\'index.php?section=admin&page=video&action=add\'" class="video_box_add">Add Video</div><br />';
        print '<table cellspacing="2" border="0" width="100%">';
        print '<tr>
                    <td valign="top" width="75" align="left"><b>Title</b></td>
                    <td valign="top" width="90" align="left"><b>Edited By</b></td>
                    <td valign="top" width="90" align="left"><b>Featuring</b></td>
                </tr>';
    
        // Get each video from the database
        $DB->query("SELECT * FROM `video` ORDER BY `id` DESC LIMIT 0, 10");
        foreach ($DB->result as $row) {

            // Query the database to find out how many times the video has been viewed
            print '<tr>
                    <td valign="top" align="left"><a href="index.php?section=admin&page=video&action=edit&video=' . $row['filename'] . '">' . $row['title'] . '</a> <br /><font color="#666"><small>(Viewed ' . $DB->num_rows . ' times)</small></font></td>
                    <td valign="top" align="left">' . $row['editor'] . '</td>
                    <td valign="top" align="left">' . $row['featuring'] . '</td>
                    </tr>';

        }
        print '</table><br /><br />';


} // end switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>
