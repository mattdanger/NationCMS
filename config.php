<?php
// config.php

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

/***********************************/
/* General System Configuration */
/***********************************/
// Apache's path to files
$config['path'] = '/';

// Full system path to files
$config['full_path'] = '/path/to/public_html/';

// Location of header images
$config['header_photo_dir'] = 'images/site/headers/';

// Default alignment for the bluebox
$config['sidepanel_top_alignment'] = 'justify';

// Path to the photos directory
$config['photo_dir'] = '/files/photo/';

// Path to the video directory
$config['video_dir'] = '/files/video/';

// Header photos that are too light for the title, so the title will need a background.
$config['header_need_shadow'] = array('header1.jpg', 'header4.jpg'); 

// How many news posts to display on the main page.
$config['news_display_num'] = 10;

// How many admin log events to display on the admin page.
$config['admin_events_display_num'] = 15;

// How many days to display from the admin events log
$config['admin_events_days'] = 3;

// Is the site in maintenance mode?
$config['maintenance_mode'] = 0;

/***********************************/
/* Advanced System Information */
/***********************************/
$config['debug'] = 1;     // Warning: This should be OFF when in production
$config['root_path'] = '/path/to/public_html/';
$config['core_path'] = 'core';
$config['404'] = '/404';


/***********************************/
/* Content Information */
/***********************************/
$content = array( 
    'title' => '',
    'header_bg' => '',
    'header_image' => '',
    'main' => '', 
    'link_tree' => '',
    'header' => '', 
    'title' => '', 
    'sidepanel_top' => '',
    'sidepanel_top_image' => $config['path'] .'images/waterfall.jpg',
    'sidepanel_top_alignment' => $config['sidepanel_top_alignment'],
    'copyright' => '&copy; SiteName 1999 - '.date('Y').' <br />All content copyrighted unless otherwise stated.' );


/***********************************/
/* Database information */
/***********************************/
$config['db_hostname'] = "localhost";   // Database hostname
$config['db_database'] = "database";    // Datebase name
$config['db_username'] = "username";    // Database username
$config['db_password'] = "password";    // Database password


/***********************************/
/* Global Vars */
/***********************************/
define('DEBUG', $config['debug']);
define('SALT', 'saltysalt');
define('DATE', date('n/j/Y'));
define('TIME', date('g:ia'));
define('TIMESTAMP', date('n/j/Y \a\t g:ia'));
?>
