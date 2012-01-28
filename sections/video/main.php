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
* File:     video/home/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

// If a video file is specified display it
if (isset($_GET['video'])) {

    // Validate the video request sent
    $name = (isset($_GET['video'])) ? $_GET['video'] : null;

    // Get the video information from the database
    $video = $DB->select('video', '*', array('url_title' => $name));

    // Insert this event into the videos counter table
    $DB->insert('view_counts_videos', array( 'filename' => $video['filename'], 'timestamp' => time() ));

    // Make sure the video exists in the database and on the server
    if (!$video || !$name || !file_exists($CORE->config['full_path'] . $CORE->config['video_dir'] . $video['filename'])) $CORE->print_error("Sorry, that video couldn't be found.");

    // Set some variables
    $CORE->content['heading'] = '<a href="/video/">Video</a> &#187; ' . $video['title'];
    $video_div_width = ($video['widescreen']) ? '420px' : '320px' ;
    print '<br />' . draw_video_shadow($video['filename'], $video['widescreen']) . '<br />
        <center><div style="text-align: left; font-size: 8pt; width: ' . $video_div_width . '">
        <b>' . $video['title'] . '</b><br />
        Featuring: ' . $video['featuring'] . '<br />
        Primary Filmer: ' .$video['filmer'] . '<br />
        Edited by: ' . $video['editor'] . '<br />';

    if ($video['music']) print 'Music: ' . $video['music'] . '<br />';
    if ($video['camera_info']) print 'Camera: ' . $video['camera_info'] . '<br />';
    if ($video['software_info']) print 'Software: ' . $video['software_info'] . '<br />';
    if ($video['description']) print '<br />' . $video['description'] . '<br />';

    print '</div></center><br /><br />';

} else { 

    print '<center>Select a video from the list below to view.</center><br /><br />';

}

print '<table cellpadding="3" cellspacing="0" width="100%" bgcolor="#333333" border="0">
    <tr>
        <td width="190">Title</b></td>
        <td width="98">Featuring</b></td>
        <td width="55">Date Added</b></td>
    </tr>
    </table>
    <table cellpadding="0" cellspacing="5" width="100%" border="0">
    ';

$DB->select('video', '*', '', array('id' => 'DESC'));
foreach ($DB->result as $video) {
    print '<tr>
        <td width="230" valign="top"><a href="/video/' . $video['url_title'] . '">' . $video['title'] . '</a> <small><font color="#666">' . $video['runtime'] . ' min</font></small></td>
        <td width="120" valign="top">' . $CORE->shorten_text($video['featuring'], 2, ' and more') . '</td>
        <td width="30" valign="top">' . $video['date'] . '</td>
    </tr>';
}

print '</table><br /><br />';

$CORE->content['main'] = ob_get_contents() . $CORE->content['main']; ob_end_clean();


?>