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
* File:     request_photo_info.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/


require 'config.php';
require 'core/class.core.php';

$CORE = new Core($config);

if (isset($_GET['photo'])) { /* If photo exists */

    // Set up our DB information
    $CORE->get_class_file('mysql');
    $DB = new MySQL_Connection( $config['db_hostname'],
                                $config['db_username'],
                                $config['db_password'],
                                $config['db_database'] );
    $CORE->DB = $DB;
    
    $filename = ($_GET['photo']);

    /* Insert this event into the photos counter table */
    $DB->insert('view_counts_photos', array ('filename' => $filename, 'timestamp' => time() ) );

    /* Query the database to get the photo & photograher information */
    $photo = $DB->select('photos', '*', array('filename' => $filename));
    if ( !$DB->num_rows ) exit("The requested photo could not be found");

    $name = $DB->select('users', '*', array('id' => $photo['belongs_to_user_id'])); 

    /* Set the content type header so the browser will correctly interpret the XML file */
    header('Content-Type: text/xml');

    /* Set some variables */
    $image = getimagesize($config['full_path'] . $config['photo_dir'] . 'display/' . $filename);
    $width = $image[0];
    $height = $image[1];
    $photographer = $photo['photographer'];
    $first_name = $name['first_name'];
    $last_name = $name['last_name'];
    $owner = $first_name . ' ' . $last_name;
    $location = $photo['location'];
    $date_taken = date('n/j/Y', $photo['date_taken']);
    $description = (!empty($photo['description'])) ? $photo['description'] : 'None';
    
    /* Print the XML */
    echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <note>
        <filename>' . $filename . '</filename>
        <width>' . $width . '</width>
        <height>' . $height . '</height>
        <photographer>' . $photographer . '</photographer>
        <owner>' . $owner . '</owner>
        <location>' . $location . '</location>
        <date>' . $date_taken . '</date>
        <description>' . $description . ' </description>
    </note>';

}

?>