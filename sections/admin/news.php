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
$id = (isset($_GET['id'])) && is_numeric($_GET['id']) ? $_GET['id'] : null;
$action = (isset($_GET['action']) || $id) ? $_GET['action'] : null ; 


// Check a couple things if a post ID is specified
$user = $DB->select('users', 'user_level', array('id' => $LOGIN->cookie[1]));
if ($id) { 

    // Select that post from the database
    $post = $DB->select('news', 'author', array('id' => $id));

    // If the post doesn't even exist then print an error and quit
    if (!$post) $CORE->print_error("Sorry that news entry couldn't be found.");

    // Make sure the user has enough privileges to make and edit posts
    if (($user['user_level'] != 3 && $post['author'] != $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4])) $CORE->print_error("Sorry, you don't have authorization to " . $_GET['action'] . " this news post.");

} else if ($user['user_level'] < 2) { // If the user doesn't have a high enough user level print an error and exit

    $CORE->print_error("Sorry, you don't have authorization to post news entries.");

}

// Display any status messages (I should get some icons for this)
if (isset($_GET['status'])) {

    switch ($_GET['status']) {

        case 'added':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> Your entry was added successfully.<div>';
            break;

        case 'updated':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The entry was updated successfully.<div>';
            break;

        case 'deleted':
            print '<div class="admin_box_message"><span class="admin_success">Success:</span> The entry was deleted successfully.<div>';
            break;

    }

    print '<br /><br />';

} 


switch ($action) {

    /*****************************/
    // Add News Postings
    /*****************************/
    case 'add':

        // Set up the content variables
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=news">News Manager</a> &#187; Add News';

        $CORE->content['sidepanel_top'] = "Add whatever you want for the news. Try to keep everything (especially the title) short & to the point. <br /><br />&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, &#60;img&#62;, and &#60;a href&#62; tags are allowed in the body only.";
    
        // If the form was submitted
        if (isset($_POST['preview']) || isset($_POST['submit'])) {

            // Read in and set some variables
            $post['title'] = (!empty($_POST['title'])) ? ucfirst(urldecode($_POST['title'])) : null;
            $post['url_title'] = (!empty($_POST['url_title'])) ? $_POST['url_title'] : null;
            $post['body'] = (!empty($_POST['body'])) ? nl2br( ucfirst( urldecode( trim( $_POST['body'])))) : null;
            $post['author'] = $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4];
            $post['timestamp'] = time();
            $display_title = str_replace("\\\\", "\\", str_replace("''", "'", $post['title']));
            $display_body = str_replace("\\\\", "\\", str_replace("''", "'", $post['body']));

            // Show preview (And submit) if both forms are not empty and they don't already exist in the database.
            $duplicate_entry = $DB->query("SELECT `title`,`body` FROM `news` WHERE `title`='" . $post['title'] . "' OR `body`='" . $post['body'] . "'") ? true : false;
            if ($post['title'] && $post['url_title'] && $post['body'] && !$duplicate_entry) {

                // Submit new post after preview
                if (isset($_POST['submit'])) {

                    // Insert the new post into the database
                    $DB->insert('news', $post);

                    // Update RSS feed
                    update_feed(); 

                    // Write this event to the admin log
                    write_admin_log($LOGIN->cookie[3] . ' added a news entry titled "' . $post['title'] . '."', $LOGIN->cookie[3]);
                    
                    // Redirect back to the main news page
                    $CORE->redirect('index.php?section=admin&page=news&status=added');
                }

                // Draw post preview and form
                $entry = array( 'author' => $post['author'], 'title' => $display_title, 'url_title' => $post['url_title'], 'body' => $display_body, 'timestamp' => $post['timestamp']);
                print "\n\t\t\tThis is what your entry will look like when posted: <br /><br />" . draw_news($entry) . '<br />
                    <form action="index.php?section=admin&page=news&action=add" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="preview" value="Preview">
                    <input type="hidden" name="title" value="' . urlencode($display_title) . '">
                    <input type="hidden" name="url_title" value="' . $post['url_title'] . '">
                    <input type="hidden" name="body" value="' . urlencode(br2nl($display_body)) . '">
                    <input type="submit" name="submit" value="Submit"> or make changes to your entry below.<br /><hr style="border: 1px solid #AAA">
                    </form>
                ' . "\n";

            } else if ( !$post['title'] || !$post['url_title'] || !$post['body'] ) { 

                // if either the title or body are empty
                print '<div class="admin_box_message"><span class="admin_error">Error:</span> Both a title and body are required!</div> <br />';

            } else if ( $duplicate_entry ) { 

                // if there is a duplicate post in the database
                print '<div class="admin_box_message"><span class="admin_error">Error:</span> There is already a news entry with a similar title and/or body.</div> <br />';

            }

        }

        // Set some values if they don't already exist.
        if (!isset($post['title'])) $post['title'] = null;
        if (!isset($post['body'])) $post['body'] = null;
        if (!isset($display_title)) $display_title = null;
        if (!isset($post['url_title'])) $post['url_title'] = null;
        if (!isset($display_body)) $display_body = null;

        // Draw main form.
        print '
            <form method="POST" action="index.php?section=admin&page=news&action=add">
            <table cellpadding="3" border="0" cellspacing="0" align="left">
                <tr>
                    <td align="right"><b>Title: </b></td>
                    <td><input type="text" onkeyup="liveUrlTitle()" id="title" name="title" size="30" value="' . $display_title . '"></td>
                </tr>
                <tr>
                    <td align="right"><b>URL: </b></td>
                    <td><input type="text" name="url_title" size="30" id="url_title" value="' . $post['url_title'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Post:</b></td>
                    <td><textarea name="body" cols="60" rows="10">' . br2nl($display_body) . '</textarea></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td></td><td align="left"><input type="submit" name="preview" value="Preview"> </td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
            </table>
            </form>';
        break;



    /*****************************/
    // Edit News Postings
    /*****************************/
    case 'edit':

        // Set up the content variables
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=news">News Manager</a> &#187; Edit News';
        $CORE->content['sidepanel_top'] = "Add whatever you want for the news. Try to keep everything (especially the title) short & to the point. <br /><br />&#60;b&#62;, &#60;i&#62;, &#60;u&#62;, &#60;img&#62;, and &#60;a href&#62; tags are allowed in the body only.";

        // Grab the current post data from the database so we can work on it
        $post = $DB->select('news', '*', array('id' => $id));

        // If the form was submitted
        if (isset($_POST['submit']) || isset($_POST['preview'])) {

            // Read in and set some variables
            $post['title'] = (!empty($_POST['title'])) ? $_POST['title'] : null;
            $post['body'] = (!empty($_POST['body'])) ? nl2br($_POST['body']) : null;
            $post['last_edited_timestamp'] = time();
            $post['last_edited_by'] = $LOGIN->cookie[3] . ' ' . $LOGIN->cookie[4];

            // Clean up the title & body data to be displayed correctly
            $post['title'] = str_replace("\\\\", "\\", str_replace("''", "'", $post['title']));
            $post['body'] = str_replace("\\\\", "\\", str_replace("''", "'", $post['body']));

            // Show preview if both forms are not empty
            if ($post['title'] && $post['body']) {

                // Submit new post
                if (isset($_POST['submit'])) {

                    // Update the post in the database
                    $DB->update('news', array ('title' => urldecode($post['title']), 
                                                'body' => urldecode($post['body']),
                                                'last_edited_by' => $post['last_edited_by'],
                                                'last_edited_timestamp' => time()), 
                                                    array ('id' => $id) );

                    // Update RSS feed
                    update_feed(); 

                    // Write this event to the admin log
                    write_admin_log($LOGIN->cookie[3] . ' updated the "' . $post['title'] . '" news entry.', $LOGIN->cookie[3]);
                    
                    // Redirect back to the main news page
                    $CORE->redirect('index.php?section=admin&page=news&status=updated');

                }
                
                // Draw post preview and form
                print "\n\t\t\tThis is what your entry will look like when posted: <br /><br />" . draw_news($post) . '<br />
                        <form action="index.php?section=admin&page=news&action=edit&id=' . $id . '" method="POST">
                        <input type="hidden" name="preview" value="Preview">
                        <input type="hidden" name="entry_id" value="' . $id . '">
                        <input type="hidden" name="title" value="' . urlencode($post['title']) . '">
                        <input type="hidden" name="body" value="' . urlencode($post['body']) . '">
                        <input type="submit" name="submit" value="Submit"> or make changes to your entry below.<br /><hr style="border: 1px solid #AAA">
                        </form>
                    ';

            } else if (!$post['title'] || !$post['body']) {

                // if either the title or body are empty
                print '<div class="admin_box_message"><span class="admin_error">Error: Both the title and body are required!</span> <br /><br />';

            }
        
        }

        // Set some variables if they don't already exist
        if (!isset($post['title'])) $post['title'] = '';
        if (!isset($post['body'])) $post['body'] = '';

        // Draw main form
        print '
            <form method="POST" action="index.php?section=admin&page=news&action=edit&id=' . $id.'">
            <input type="hidden" name="entry_id" value="' . $id . '">
            <table cellpadding="3" border="0" cellspacing="0" align="left">
                <tr>
                    <td align="right"><b>Title: </b></td>
                    <td><input type="text" name="title" size="30" value="' . $post['title'] . '"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><b>Body:</b></td>
                    <td><textarea name="body" cols="60" rows="10">' . br2nl($post['body']) . '</textarea></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
                <tr>
                    <td></td><td align="left"><input type="submit" name="preview" value="Preview"> <input type="submit" name="submit" value="Submit"> <input type="reset" name="reset" value="Reset"></td>
                </tr>
                <tr><td colspan="2" height="5"></td></tr>
            </table>
            ';

    break;



    /*****************************/
    // Delete News Postings
    /*****************************/
    case 'delete':
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
            $entry = $DB->select('news', 'title', array('id' => $id));
            $DB->delete('news', array('id' => $id));
            write_admin_log($LOGIN->cookie[3] . ' deleted the "' . $entry['title'] . '" news entry.', $LOGIN->cookie[3]);
            $CORE->redirect('index.php?section=admin&page=news&status=deleted');
        }
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; <a href="index.php?section=admin&page=news">News Manager</a> &#187; Delete News';
        $CORE->content['sidepanel_top'] = "Think before you delete! Deleted entries cannot be recovered.";
        print '<br /> ' . draw_news($DB->select('news', '*', array('id' => $id))) . '
            <br /><div align="center">Are you sure you want to delete this entry?<br /><br />
            <input type="button" value="Yes" onclick="javascript: document.location=' . "'index.php?section=admin&page=news&action=delete&id=$id&confirm=yes'".'"><input type="button" value="No" onclick="javascript: document.location=' . "'index.php?section=admin&page=news'".'">
            </div>';
        break;



    /*****************************/
    // Display News Postings
    /*****************************/
    default:

        // Set up the content variables
        $CORE->content['title'] = 'News Manager';
        $CORE->content['heading'] = '<a href="index.php?section=admin">Admin</a> &#187; News Manager';
        $CORE->content['sidepanel_top'] = "News entries are displayed on the main page of the site. This is a good way to keep visitors up to date with what's going on in the community";
        print '<div onclick="document.location=\'index.php?section=admin&page=news&action=add\'" class="news_box_add">New Post</div><br />';

        // Get the 10 newest posts from the database and display them.
        $DB->select('news', '*', '', array('id' => 'DESC'), array(0, 10));
        foreach ($DB->result as $row) {
            print draw_news($row, true);
        }


} // End switch

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>
