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
* File:     sections/skaters/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

$name = array(0 => '', 1 => '');
$skater = (isset($_GET['skater'])) ? $_GET['skater'] : null;
if ($skater) {
    $name = explode("_", $skater);
}

$profile = $DB->select('profiles', '*', array('first_name' => $name[0], 'last_name' => $name[1]));

if ( !$skater || !$profile ) {

    $result = $DB->select('profiles', '*');

    print '<table cellpadding="10" cellspacing="0" width="90%" align="center" border="0"><tr>';
    $loopCounter = 1;
    foreach ($result as $profile) {

        if ($loopCounter % 2) print '</tr><tr>';

        print '<td align="center"';

        if ($loopCounter == 5) print ' colspan="2"';

        print '>' . draw_shadow('/images/primary_photos/' . $profile['primary_photo'], '/skaters/' . strtolower($profile['first_name']) . '_' . strtolower($profile['last_name']) . '/') . 
           '<a href="/skaters/' . strtolower($profile['first_name']) . '_' . strtolower($profile['last_name']) . '/" class="skatersLinks">' .
            $profile['first_name'] . " " . $profile['last_name'] .
            '</a></td>';
        $loopCounter++;

    }

    print '</tr></table><br />';

} else {

    $id = $profile['id'];    

    $CORE->content['sidepanel_top_alignment'] = "left";
    $CORE->content['title'] = $profile['first_name'] . ' ' . $profile['last_name'];
    $CORE->content['heading'] = '<a href="/skaters/">Skaters</a> &#187; ' . $profile['first_name'] . " " . $profile['last_name'];
    $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/primary_photos/' . $profile['primary_photo'];


    /***********************/
    /*   Display photos    */
    /***********************/
    if ($id) { 

        $DB->select('photos', '*', array('belongs_to_user_id' => $id), array('date_taken' => 'DESC', 'filename' => 'DESC'));

    } else { 

        $DB->select('photos', '*', '', array('date_taken' => 'DESC', 'filename' => 'DESC')); 
    
    }

    if ( $DB->num_rows ) {
    
        print '<script src="/js/js.js" type="text/javascript" language="javascript"></script>
                <table cellspacing="2" border="0"><tr>';
        $loopCounter = 0;
        $image_array = array();
        $CORE->content['sidepanel_top'] = "";

        foreach ($DB->result as $row) {
            array_push($image_array, $row['filename']);
            $description = (!empty($row['description'])) ? $row['description'] : "&nbsp;";
            $tmp = explode(" ", $row['photographer']);
            $photographer = $tmp[0];
            $CORE->content['sidepanel_top'] .= "<a onmouseout='return false' onclick=\"requestNewPhoto('".$row['filename']."')\"".
            '" class="showPointer"><img src="' . $CORE->config['photo_dir'] . 'tiny_thumbs/' . $row['filename']. '" border="1">';

            $CORE->content['sidepanel_top'] .= '</a>';

            if ( !$CORE->browser_is_ie() ) 
                $CORE->content['sidepanel_top'] .= ' ';
            else 
                $CORE->content['sidepanel_top'] .= '<img src="/images/site/ie/bluebox_bg.png" width="2">';


        }

        $CORE->content['sidepanel_top'] .= '<br /><br />';

        print '</tr></table><br />';

        $main_image = $image_array[rand(0, sizeof($image_array) - 1)];

        $photo = $DB->select('photos', '*', array('filename' => $main_image));
        $user_data = $DB->select('users', '*', array('id' => $photo['belongs_to_user_id']));

        print '<table align="center"><tr><td>';
        if (!$id) print '<div id="imageOwner" align="center">' . $user_data['first_name'] . ' ' . $user_data['last_name'] . '</div><br />';
            else print '<div id="imageOwner" align="center"> </div><br />';
        print draw_shadow($CORE->config['photo_dir'] . 'display/' . $main_image, $CORE->config['photo_dir'] . 'full_size/' . $main_image, 'mainImageLink') .
                '<div align="right" id="imagePhotographer">Photo: ' . $photo['photographer'] . ' &nbsp; </div></td></tr></table>
                <table align="center" width="400"><tr><td>';
        print '<div id="imageDate"><b>Date:</b> ' . date('n/j/Y', $photo['date_taken']) . '</div> 
            <div id="imageLocation"><b>Taken at:</b> ' . $photo['location'] . '</div>'.
            '</small>';
        if (!empty($photo['description'])) print '<div id="imageDescription"><b>Description:</b> ' . $photo['description'] . '</div>'; 
        else print '<div id="imageDescription"> </div>';

        print '</td></tr></table><br />';
    }

    print '<br />' . $profile['description'];


    /************************/
    /*   Random Questions   */
    /************************/
    $DB->select('profile_questions', '*', array('belongs_to_id' => $id), array('id' => 'ASC'));

    if ( $DB->num_rows == 1) {

        $CORE->content['sidepanel_top'] .= "<b>" . $DB->result['question'] . ":</b> " .    
                    $DB->result['answer'] . "<br /><br />\n";

    } else if ( $DB->num_rows ) {

        foreach ($DB->result as $profile_questions) {

            $CORE->content['sidepanel_top'] .= "<b>" . $profile_questions['question'] . ":</b> " .    
                        $profile_questions['answer'] . "<br />\n";

        }

        $CORE->content['sidepanel_top'] .= "<br />";

    }


    /************************/
    /*   Icons              */
    /************************/
    $CORE->content['sidepanel_top'] .= '<center><div style="horizontal-align: center; width: 188px">';
    $iconCount = 0;
    if (!empty($profile['website'])) { 
        $CORE->content['sidepanel_top'] .= '<a href="' . $profile['website'] . '" target="_blank"><img src="/images/icons/website.gif" border="0"></a> '; 
    }
    if (!empty($profile['myspace'])) { 
        $CORE->content['sidepanel_top'] .= '<a href="http://www.myspace.com/' . $profile['myspace'] . '" target="_blank"><img src="/images/icons/myspace.gif" border="0"></a> '; 
    }
    if (!empty($profile['facebook'])) {
        $CORE->content['sidepanel_top'] .= '<a href="http://dyc.facebook.com/s.php?q=' . $profile['facebook'] . '" target="_blank"><img src="/images/icons/facebook.gif" border="0"></a> '; 
    }
    if (!empty($profile['flickr'])) { 
        $CORE->content['sidepanel_top'] .= '<a href="http://www.flickr.com/search/people/?q=' . $profile['flickr'] . '" target="_blank"><img src="/images/icons/flickr.gif" border="0"></a> '; 
    }
    if (!empty($profile['aim'])) {
        $CORE->content['sidepanel_top'] .= '<a href="aim:goim?screename=' . str_replace(' ','+',$profile['aim']) . '&message=Hey+I+saw+you+on+nationskateboarding.com">
        <img src="http://big.oscar.aol.com/' . str_replace(' ','',$profile['aim']) .'?on_url=http://nationskateboarding.com/images/icons/aim-online.gif&off_url=http://nationskateboarding.com/images/icons/aim-offline.gif" border="0"> ';
    }
    if (!empty($profile['yahoo'])) {
        $CORE->content['sidepanel_top'] .= '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $profile['yahoo'] . '&.src=pg" target="_blank"><img src="/images/icons/yahoo.gif" border="0"></a> ';  
    }
    if (!empty($profile['google'])) { $CORE->content['sidepanel_top'] .= '<a href="#" target="_blank"><img src="/images/icons/google.gif" border="0"></a>\n';  
    }  
    if (!empty($profile['delicious'])) { $CORE->content['sidepanel_top'] .= '<a href="http://del.icio.us/' . $profile['delicious'] . '" target="_blank"><img src="/images/icons/delicious.gif" border="0"></a> ';  
    }  
    if (!empty($profile['skype'])) { $CORE->content['sidepanel_top'] .= '<a href="skype:' . $profile['skype'] . '?call" target="_blank"><img src="/images/icons/skype.gif" border="0"></a> ';  
    }  
    if (!empty($profile['youtube'])) { $CORE->content['sidepanel_top'] .= '<a href="http://www.youtube.com/profile?user=' . $profile['youtube'] . '" target="_blank"><img src="/images/icons/youtube.gif" border="0"></a> ';
    }
    $CORE->content['sidepanel_top'] .= '</div></center>';

}

$CORE->content['main'] = ob_get_contents() .  $CORE->content['main'] ; ob_end_clean();

?>
