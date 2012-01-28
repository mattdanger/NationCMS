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
* File:    index.php
* Purpose: Main engine file
*
*****************************************************/

require 'config.php';
global $config, $content;
require $config['core_path'] . '/func.main.php';
require $config['root_path'] . $config['core_path'] . '/class.core.php';
$CORE = new Core ($config, $content);


// Display splash page
function write_initial_access() {
	if ($handler = fopen('logs/initial_access.log', 'a')) {
		fwrite($handler, $_SERVER['REMOTE_ADDR'] . ' [' . date('M/d/Y H:i:s') . "]\n");
		fclose($handler);
	} else echo "..."; 
}

if ( !isset($_COOKIE['ns_splash']) && !isset($_GET['section']) ) { 

    write_initial_access();
    setcookie('ns_splash', 1);

?>
<html>
<head>
<title>Nation Skateboarding - Western New York</title>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
<style type="text/css">
<!--
body {
    background-color: #333;
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
}
-->
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%">
    <tr><td height="8%" bgcolor="#333333"></td></tr>
    <tr><td background="images/site/index_top.gif" height="8"></td></tr>
    <tr><td bgcolor="#FFFFFF" height="1"></td></tr>
    <tr><td bgcolor="#000000" height="386">
        <div onclick="javascript: document.location='/home/'" width="100%" style="cursor: pointer">
            <img src="images/site/index.jpg" border="0">
        </div>
    </td></tr>
    <tr><td bgcolor="#FFFFFF" height="1"></td></tr>
    <tr><td background="images/site/index_bottom.gif" height="8"></td></tr>
    <tr><td bgcolor="#333333"></td></tr>
</table>

<?php

} else {

//    if ( !isset($_GET['section'])) $CORE->redirect('/home/');

// Set up our DB information
$CORE->get_class_file('mysql');
$DB = new MySQL_Connection( $config['db_hostname'],
                            $config['db_username'],
                            $config['db_password'],
                            $config['db_database'] );
$CORE->DB = $DB;

// Set up content defaults
$CORE->content['title'] = $CORE->content['heading'] = '';
$CORE->content['link_tree'] .= '<a href="/">Home</a> &#187; ';


// Get the content for the section from the DB
$DB->select('site_content', '*', array('section' => $CORE->this_section));

if ( $DB->num_rows > 0 ) {

    $CORE->content['heading'] = ( !empty($DB->result['heading']) ) ? $DB->result['heading'] : null ;
    $CORE->content['main'] = ( !empty($DB->result['main']) ) ? $DB->result['main']  . "<br /><br />" : null ;
    $CORE->content['sidepanel_top'] = ( !empty($DB->result['side_panel']) ) ? $DB->result['side_panel'] . '<br />' : null ;
    if (isset($DB->result['photo'])) $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/sections/' . $DB->result['photo'];

    if ( !empty($DB->result['title']) ) {

        $CORE->content['title'] = $DB->result['title'];
        $CORE->content['link_tree'] .= $DB->result['title'];

    } else {

        $CORE->content['title'] = $CORE->content['heading'];

    }

} else {

    $CORE->content['link_tree'] .= ucfirst($CORE->this_section);

}

// Bullshit for header image
$header_images = array();
foreach (glob($config['header_photo_dir'] . "*.*") as $temp) {
    array_push($header_images, $temp);
}
$CORE->content['header_image'] = $config['path'] . $header_images[rand(0,sizeof($header_images)-1)];
$draw_shadow = false; 
foreach ($config['header_need_shadow'] as $temp) {
    $temp_array = explode("/", $CORE->content['header_image']);
    if ($temp == $temp_array[4]) $draw_shadow = true;
}
if ($draw_shadow) $CORE->content['header_bg'] = ' style="background-image: url(/images/site/mozilla/header_transparency.png)"';


// Include the section
$CORE->get_section();
if ($CORE->this_section == 'admin') $CORE->content['main'] = '<script src="js/admin.js" type="text/javascript" />' . $CORE->content['main'] ;



// Page title
if ( empty($CORE->content['heading']) ) {

    $CORE->content['title'] = ucfirst($CORE->this_section);

} else if ( $CORE->this_section == 'home' ) {

    $CORE->content['title'] = 'Skateboarding in Western NY';

} else if ( empty($CORE->content['title']) ) {

    $CORE->content['title'] = strip_tags($CORE->content['header']);

}

// If the site is currently in maintenance mode then print a message and exit
if ($config['maintenance_mode']) print_error("The site is currently under going some maintenance. It shouldn't last too long so come back in a few minutes and everything should be as good as new.");

// Send the content to the template for parsing and displaying
$CORE->get_class_file('template');
$PAGE = new Template ( ($CORE->browser_is_ie()) ? 'index-ie.html' : 'index-mozilla.html' );
$PAGE->parse_tags($CORE->content);
$PAGE->display();

unset($DB, $PAGE, $CORE);
}

?>
