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

/*  Main functions  */

/**
 * br2nl(): Convert <br />'s to newlines (\n)
 * @param string $data  String containing <br />'s
 * @returns string      String with <br />'s converted to newline (\n) characters
 */
function br2nl($data) {
    return str_replace('<br />', '', $data);
}

function write_admin_log($message, $user) {
    global $DB, $LOGIN;
    $DB->insert('admin_event_log', array ('timestamp' => time(),
                                            'name' => $user,
                                            'event' => $message));
}

function draw_shadow($image, $link = null, $supersize = false) {

    global $CORE;

    $image_path = 'images/site/' . ( ( $CORE->browser_is_ie() ) ? 'ie' : 'mozilla' ) . '/';

    $html = '
        <table align="center" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td>';

    if ( $link ) { 

        if ( $supersize) {
            $html .= '<script type="text/javascript" src="/js/js.js"></script>
                <script type="text/javascript" src="/js/prototype.js"></script>
                <script type="text/javascript" src="/js/scriptaculous.js?load=effects"></script>
                <script type="text/javascript" src="/js/Sound.js"></script>
                <script type="text/javascript" src="/js/lightboxEx.js"></script>
                <link rel="stylesheet" href="/css/lightboxEx.css" type="text/css" media="screen" />
                ';
        }

        $html .= '<a href="' . $link . '" ' . ( ( $supersize ) ? 'rel="lightbox" id="supersize_photo"' : '' ) . '>';

    }

    $image = str_replace('&#47;',"/", $image);
    $imagexy = getimagesize( $CORE->config['full_path'] . $image );
    $html .= '<div style="cursor: pointer; background-repeat: no-repeat; background-image: url(' . $image 
        . '); width: ' . $imagexy[0] . 'px; height: ' . $imagexy[1] . 'px;" id="mainImageIMG"></div>';

    if ( $link ) $html .= '</a>';

    $html .= '</td> 
            <td background="' . $CORE->config['path'] . $image_path . 'image_shadow_right.png" valign="top"><img src="' 
            . $CORE->config['path'] . $image_path . 'image_shadow_right_top.png"></td> 
        </tr>
        <tr>
            <td align="left" height="6" style="background-repeat: repeat-x;" background="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_bottom.png" valign="top">
            <img src="' . $CORE->config['path'] . $image_path . 'image_shadow_bottom_left.png"></td> 
            <td height="6" valign="top" style="background-repeat: no-repeat" background="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_bottom_right.png" height="6"></td> 
        </tr>
    </table>
    ';

    return $html;
}


function draw_video_shadow($video, $video_is_widescreen) {

    global $CORE;

    if ($CORE->browser_is_ie())

        $image_path = 'images/site/ie/';

    else

        $image_path = 'images/site/mozilla/';

    $html = '
        <table align="center" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td>';

    if ($video_is_widescreen)  {

    	$html .= '<div style="height: 225px; width: 420px; background-color: #000000;" id="mainImageIMG">
	   <embed src=' . $CORE->config['video_dir'] . $video . ' height="225" width="420" loop="false" kioskmode="true" cache="true">';

    } else {

    	$html .= '<div style="height: 255px; width: 320px; background-color: #000000;" id="mainImageIMG">
    	   <embed src="' . $CORE->config['video_dir'] . $video 
    	   . '" height="255" width="320" loop="false" kioskmode="true" cache="true" pluginspage="http://www.apple.com/quicktime/download/">';
    }

    $html .= '
                </embed></div>
            </td> 
            <td background="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_right.png" valign="top"><img src="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_right_top.png"></td> 
        </tr>
        <tr>
            <td align="left" height="6" style="background-repeat: repeat-x;" background="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_bottom.png" valign="top">
            <img src="' . $CORE->config['path'] . $image_path . 'image_shadow_bottom_left.png"></td> 
            <td height="6" valign="top" style="background-repeat: no-repeat" background="' . $CORE->config['path'] . $image_path 
            . 'image_shadow_bottom_right.png" height="6"></td> 
        </tr>
    </table>
    ';

    return $html;

}

function clense($data) {

    return strip_tags( str_replace('\\', '\\\\', str_replace("'", "''", $data) ) );

}

function clense_body($data) {

    return str_replace('\\', '\\\\', str_replace("'", "''", $data));

}

function draw_news($entry, $admin_display = false) {

    global $CORE;

    $a = explode(' ', $entry['author']);
    $author = $a[0];

    if ($CORE->browser_is_ie()) $CORE->config['image_path'] = 'images/site/ie/'; else $CORE->config['image_path'] = 'images/site/mozilla/';
    $html ='
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td>
                <div class="news_box">
                    <div class="news_body">
                        ' . $entry['body'] . '<br /><br />
                    </div>
                    <div class="news_footer"> 
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
                        <td class="news_footer_td">&nbsp; Posted by ' . $author . ' on ' . date('n/j/Y', $entry['timestamp']) . '</td>';

    if ($admin_display) {
        $html .= '<td class="news_footer_td" align="right"> 
                    &nbsp;[<a href="index.php?section=admin&page=news&action=edit&id=' . $entry['id'] . '">Edit</a> | 
                    <a href="index.php?section=admin&page=news&action=delete&id=' . $entry['id'] . '&confirm=yes" onclick="if (!confirm(\'Are you sure you want to delete this news post? This action cannot be undone!\')) { return false }">Delete</a>]&nbsp; </td>';
    } else {

//        $html .= '<td class="news_footer_td" align="right"> 
  //                  &nbsp;[<a href="/home/' . $entry['url_title'] . '/">Permalink</a>]&nbsp; </td>';
    
    }

    $html .= '          </tr></table>
                    </div>
                </div>
            </td> 
            <td background="' . $CORE->config['path'] . $CORE->config['image_path']
                . 'image_shadow_right.png" width="4" valign="top"><img src="' . $CORE->config['path'] . $CORE->config['image_path']
                . 'image_shadow_right_top.png"></td> 
        </tr>
        <tr>
            <td align="left" height="6" style="background-repeat: repeat-x;" background="' . 
                $CORE->config['path'] . $CORE->config['image_path'] . 'image_shadow_bottom.png" valign="top">
            <img src="' . $CORE->config['path'] . $CORE->config['image_path'] . 'image_shadow_bottom_left.png"></td> 
            <td height="6" valign="top" style="background-repeat: no-repeat" background="' . 
                $CORE->config['path'] . $CORE->config['image_path'] . 'image_shadow_bottom_right.png" height="6"></td> 
        </tr>
        <tr>
            <td colspan="2" height="5"></td>
        </tr>
    </table>
    ';
    return $html;
}

function update_feed() {
    global $DB;
    $DB->select('news', '*', '', array('id' => 'DESC'), 10);

    $data = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
  <channel>
    <title>Nation Skateboarding</title>
    <link>http://www.nationskateboarding.com/</link> 
    <description>Skateboarding in Western NY</description>
    <language>en-us</language>
    <pubDate>' . date('r') . '</pubDate>
    <lastBuildDate>' . date('r') . '</lastBuildDate>';

    foreach ($DB->result as $news) {
        $data .=   
'    <item>
      <title>' . $news['title'] . '</title>
      <author>' . $news['author'] . '</author>
      <link>http://www.nationskateboarding.com/home/' . $news['url_title'] . '/</link>
      <description>' . strip_tags($news['body']).'</description>
      <pubDate>' . date('r', $news['timestamp']) . '</pubdate>
    </item>';
}
$data .= '
  </channel>
</rss>';

    $file_handler = fopen('rss/index.xml', 'w');
    fwrite($file_handler, $data);
    fclose($file_handler);
}

function get_video_filesize($filesize) {
	return $filesize . ' bytes';
}

?>
