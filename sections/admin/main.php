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
* File:     sections/admin/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

$CORE->content['heading'] = "Administration Control Panel";

$CORE->get_class_file('Login');
$LOGIN = new Login();

if (! $LOGIN->is_admin_logged_in('ns_admin') ) {

    if ( isset($_POST['submitLogin']) ) {

        if ( $LOGIN->login_admin($_POST['username'], $_POST['password'], $DB) ) {

            $CORE->write_log( ucfirst($_POST['username']) . ' logged in to the admin panel.');

            if ( !$LOGIN->set_cookie('ns_admin', $DB, true) ) {

                $CORE->print_error("Unable to set cookie");

            } else {

                $CORE->redirect('admin/');

            }

        }

    }

?>

        <table cellspacing="2" border="0" align="left">
        <form method="POST" action="index.php?section=admin">
            <tr>
                <td>Username: </td>
                <td><input name="username" type="text" size="15">
            </tr>
            <tr>
                <td>Password: </td> 
                <td><input name="password" type="password" size="15"></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Submit" name="submitLogin"></td>
            </tr>
        </table>
        </form>

<?php

} else {

    // Side panel links
    $CORE->content['sidepanel_top'] .= '<br /><br /><a href="index.php?section=admin&page=changepassword">Change Password</a> | <a href="index.php?section=admin&page=logout">Logout</a><br /><br />';

    //////////////////////////////
    // Admin Events Log
    //////////////////////////////
    $DB->query("SELECT * FROM `admin_event_log` ORDER BY `timestamp` DESC LIMIT " . $CORE->config['admin_events_display_num']);

    if ( $DB->num_rows > 0 ) {

        $CORE->content['sidepanel_top'] .= '<div align="left">Recent Changes:<br /><small>';
        $admin_events = array();

        foreach ($DB->result as $row) {

            $time_occured = (date('n/j/y') == date('n/j/y', $row['timestamp'])) ? 'Today' : date('n/j/y', $row['timestamp']);
            $current_event = array( 'time_occured' => $time_occured, 
                                    'event' => $row['event'] );

            $last_event = array_pop($admin_events);
            array_push($admin_events, $last_event);
            if ($last_event['event'] != $row['event']) {
                array_push($admin_events, $current_event);
            }

        }

        array_shift($admin_events);
        $current_time = '';
        foreach ($admin_events as $event) {

            if ($current_time == $event['time_occured']) {
                $CORE->content['sidepanel_top'] .= '<br /> &nbsp; ' . $event['event'];
            } else {
                if (!empty($current_time)) $CORE->content['sidepanel_top'] .= '<br /><br />';
                $CORE->content['sidepanel_top'] .= '<b>' . $event['time_occured'] . ':</b><br /> &nbsp; ' . $event['event'];
                $current_time = $event['time_occured'];
            }

        }

        $CORE->content['sidepanel_top'] .= '</small></div>';

    }


    //////////////////////////////
    // Show Links
    //////////////////////////////
    print '<a href="index.php?section=admin&page=news">News</a> | <a href="index.php?section=admin&page=friends">Friends</a> | <a href="index.php?section=admin&page=pages">Pages</a> | <a href="index.php?section=admin&page=articles">Articles</a> | <a href="index.php?section=admin&page=video">Videos</a> | <a href="index.php?section=admin&page=profiles">Profiles</a> | <a href="index.php?section=admin&page=photos">Photos</a> <br /><br />';

}


$CORE->content['main'] = ob_get_contents(); ob_end_clean();


?>
