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
* File:     plugins/spots/home.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $main, $content_header, $bluebox, $section, $path, $bluebox_image;

$section = $_GET[$section];
$content = db_query("SELECT * FROM `site_content` WHERE `section`='$section'");

$main = $content['main'];

if (isset($content['photo'])) $bluebox_image = $path.'images/sections/'.$content['photo'];

$content_header = $content['heading'];

$bluebox = $content['side_panel'];

$bluebox .= '
    Newest spots:<br /><br />
    
    Most popular spots: <br /><br />
    
    Top ranked spots:
    
    <br /><br />
';

if (browser_is_ie()) $image_path = 'images/site/ie/'; else $image_path = 'images/site/mozilla/';
$main = '
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAghgR4PMad3gC9cWtPhjNhRRRYGFGKQ-PkrSydzFrcTlsq8qznRTTuuLpbHWV1DJ-bLkexiwgWfyb7w"
      type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(43.192036, -77.589741), 11);
      } 
    }
    //]]>
</script>

<body onload="load()" onunload="GUnload()">';

$main ='
    <table align="center" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            <div id="map" style="width: 500px; height: 400px></div>
        </td> 
        <td background="'.$path.$image_path.'image_shadow_right.png" valign="top"><img src="'.$path.$image_path.'image_shadow_right_top.png"></td> 
    </tr>
    <tr>
        <td align="left" height="6" style="background-repeat: repeat-x;" background="'.$path.$image_path.'image_shadow_bottom.png" valign="top">
        <img src="'.$path.$image_path.'image_shadow_bottom_left.png"></td> 
        <td height="6" valign="top" style="background-repeat: no-repeat" background="'.$path.$image_path.'image_shadow_bottom_right.png" height="6"></td> 
    </tr>
</table><br />
';

?>