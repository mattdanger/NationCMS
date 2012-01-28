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
* File:     photos/home/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

//die($_GET['category']);
//die($_SERVER['QUERY_STRING']);
//die($_SERVER['REQUEST_URI']);

ob_start();

// Get the category of photos to display
if ( @$_GET['category'] ) {

    $category       = ( $_GET['category'] >= 0 && is_numeric($_GET['category']) ) ? $_GET['category'] : null ;
    $category_value = $_GET['category'];

} else {

    $category       = null;
    $category_value = null;

}


// Get the photos by a certain photographer
$photographer = ( @$_GET['photographer'] ) ? $_GET['photographer'] : 'All';


// Determine how many thumbnails to display
if ( @$_GET['showpage'] > 1 ) {

    $page_number    = $_GET['showpage'];
    $start          = $_GET['showpage'] * 25;

} else {

    $page_number    = 1;
    $start          = 0;

}
//$start = ( isset($_GET['start']) && is_numeric($_GET['start']) ) ? $_GET['start'] : 0;

if ( @$_GET['display'] ) {

    $display_value  = $_GET['display'];
    if (is_numeric($_GET['display'])) {

        $display    = $_GET['display'];
        $limit      = array($start, $display);

    } else {

        $display    = 25;
        $limit      = null;
        $display    = $_GET['display'];

    }

} else {

    $display = $display_value = 25;
    $limit = array($start, $display);

}

// Query the thumbnail photos from the database 
$where = array();
if ( $photographer != 'All' ) $where['photographer'] = $photographer;
if ( $category) $where['belongs_to_user_id'] = $category;
$imageResults = $DB->select('photos', '*', $where, array('date_taken' => 'DESC'), $limit); 

if ( $total_display_photos = $DB->num_rows ) {

    $DB->select('photos', '*', $where, array('date_taken' => 'DESC', 'filename' => 'DESC'));
    $total = $DB->num_rows;

    $loopCounter = 0;
    $image_array = array();
    $show = $start + $display;
    if ($show > $total) $show = $total;
    $CORE->content['sidepanel_top'] = '
        <center><small>Displaying ' . ($start + 1) . ' - ' . $show . ' 
            of ' . $total . ' photos.
        <br /><br />';

    foreach ($imageResults as $row) {

        array_push($image_array, $row['filename']);
        $description = (!empty($row['description'])) ? $row['description'] : "&nbsp;";
        $tmp = explode(" ", $row['photographer']);
        $CORE->content['sidepanel_top'] .= '<a onmouseout="return false" onclick="requestNewPhoto(\'' . $row['filename'] . '\')" class="showPointer"><img src="' 
            . $CORE->config['photo_dir'] . 'tiny_thumbs/' . $row['filename']. '" border="1">';

        if ($CORE->clients_browser() == 'Firefox-Mac') {

            $CORE->content['sidepanel_top'] .= '<img src="/images/site/ie/bluebox_bg.png" width="1"> ';

        } else if ($CORE->clients_browser() == 'Safari') {

            $CORE->content['sidepanel_top'] .= ' ';

        } else {

            $CORE->content['sidepanel_top'] .= '<img src="/images/site/ie/bluebox_bg.png" width="3">';

        }

        $CORE->content['sidepanel_top'] .= '</a>';

    }

/* THIS CODE HAS NOT YET BEEN UPDATED FOR THE NEW OOP FORMAT
    // Page number links
    if ($total > $display){
    $CORE->content['sidepanel_top'] .= '
    <div align="right"> Page: ' . "\n";

    $next_counter = 0;
    $num_counter = 1;
    do {
        if (($show + $next_counter) == $start)
            $CORE->content['sidepanel_top'] .= ' ' . $next_counter . ' ' ;
        else 
            $CORE->content['sidepanel_top'] .= '
            <a href="index.php?section=photos&category=' . $category . '&photographer=' . $photographer . '&start=' . ($next_counter) . '">' . $num_counter . '</a> ' . "\n";

        $num_counter ++;
        $next_counter += $display;
    } while ($next_counter < $total);

    $CORE->content['sidepanel_top'] .= '</div>';
    }
*/

    // Next and previous links
    if ($total > $display){

        $CORE->content['sidepanel_top'] .= '</center><div align="right">' . "\n";
    
        if ($start > 0) {

            $CORE->content['sidepanel_top'] .= '<a href="/photos/page' . ($page_number - 1) . '/">Prev</a> ' . 
                ( ( $total > ($start + $display) ) ? '| ' : '') . '</a> ' . "\n";

        }

        if (($start + $display) < $total) 
            $CORE->content['sidepanel_top'] .= '
                <a href="/photos/page' . ($page_number + 1) . '/">Next</a> ' . "\n";
    
        $CORE->content['sidepanel_top'] .= ' &nbsp; </div>';

    }


    // Start form
    $CORE->content['sidepanel_top'] .= '<br /><br /><form action="/photos/" method="get" name="thumbs">';

/* THIS CODE HAS NOT YET BEEN UPDATED FOR THE NEW OOP FORMAT
    // Display number of photos
    $CORE->content['sidepanel_top'] .= 'Display <select name="display" onchange="javascript: document.thumbs.submit()">';
    $options = array(5, 10, 15, 25, 30, 40, 50, 60, 75, 'All');
    foreach ($options as $i) {
        $CORE->content['sidepanel_top'] .= '            <option';
        if ($i == $display_value || $display == 1000)  $CORE->content['sidepanel_top'] .= ' selected="true"';
        $CORE->content['sidepanel_top'] .= '>'.$i."</option>\n";
    }
    $CORE->content['sidepanel_top'] .= "</select> photos.<br /><br />\n";
*/


/*  This is the form to filter photos by Subject & Photographer.
    I wasn't sure how to get this to work properly with nicely formatted URIs
    for the mod_rewrite scheme so I decided to disable filtering for now.
    
    
    // Start form
    $CORE->content['sidepanel_top'] .= '
        <table cellpadding="1" cellspacing="1" border="0">
            <tr><td colspan="2"> <b>Filtering Options</b> </td></tr>
            <tr>
                <td class="photo_filter">Subject/Owner: </td>
                <td class="photo_filter"><select name="category" onchange="javascript: document.thumbs.submit()">';

        
    // Filter by categories
    $categories = array('0' => 'General Photos',
                        '1' => 'Matt West',
                        '2' => 'Evan Schapp',
                        '3' => 'Nate Schapp',
                        '4' => 'Gabe Gendreau',
                        '5' => 'Mike Raffard',
                        '6' => 'MK Morgan' );
    foreach ($categories as $tmp_id => $tmp_name) {
        $CORE->content['sidepanel_top'] .= '
            <option value="' . $tmp_id .'"';
        if ($tmp_id == $category_value) { $CORE->content['sidepanel_top'] .= ' selected="true"';}
//        $CORE->content['sidepanel_top'] .= '>' . $tmp_name .' (' . mysql_num_rows(db_get_result("SELECT `id` FROM `photos` WHERE `belongs_to_user_id` = '" . $tmp_id . "'")) . ')</option>';    
        $CORE->content['sidepanel_top'] .= '>' . $tmp_name .'</option>';    
    }

    $CORE->content['sidepanel_top'] .= '
            <option value="-1"';
    if ($category_value == -1) $CORE->content['sidepanel_top'] .= ' selected="true"';
//    $CORE->content['sidepanel_top'] .= '>All Categories (' . $total_photos . ')</option>
    $CORE->content['sidepanel_top'] .= '>All Categories</option>
        </select></td>
        </tr>';


    // Filter by photographers
    $CORE->content['sidepanel_top'] .= '
        <tr>
        <td class="photo_filter">Photographer: </td>
        <td class="photo_filter"><select name="photographer" onchange="javascript: document.thumbs.submit()">';
    $result = $DB->select('photos', 'photographer');
    $photographers = array('All');
    foreach ($result as $row) {
        array_push($photographers, $row['photographer']);
    }
    $photographers = array_unique($photographers);
    
    foreach ($photographers as $i) {
            $CORE->content['sidepanel_top'] .= '            <option value="' . $i . '"';
            if ($i == $photographer) $CORE->content['sidepanel_top'] .= ' selected="true"';

//            $db_count = ($i != 'All') ? mysql_num_rows(db_get_result("SELECT `id` FROM `photos` WHERE `photographer` = '" . $i . "'")) : $total_photos;
//            $CORE->content['sidepanel_top'] .= '>'.$i.' (' . $db_count .')</option>' . "\n";

            $CORE->content['sidepanel_top'] .= '>'.$i.'</option>' . "\n";
    }
    $CORE->content['sidepanel_top'] .= '
        </select></td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="photo_filter"><input type="button" value="Refresh" onclick="javascript: document.thumbs.submit()"></td>
        </tr>
        </table></form>
        '; // End form        
*/

    // Set up the main content

    // Determine which photo to display first
    $main_image = ( isset($_GET['photo']) ) ? $_GET['photo'] . '.jpg' : $image_array[rand(0,sizeof($image_array)-1)];

    // Query the DB for the photo's information
    $photo = $DB->select('photos', '*', array('filename' => $main_image));

    // Print error if photo doesn't exist
    if ( !$DB->num_rows ) { $CORE->print_error("Sorry that photo couldn't be found"); }

    // Get the name of the photo's owner
    $user_data = $DB->select('users', '*', array('id' => $photo['belongs_to_user_id']));

    // Insert this event into the photos counter table
    $DB->insert('view_counts_photos', array('filename' => $main_image,
                                            'timestamp' => time() ) );

    // HTML
    print '<table align="center"><tr><td>
            <div id="imageOwner" align="center">' . $user_data['first_name'] . ' ' . $user_data['last_name'] . '</div><br />'
            . draw_shadow($CORE->config['photo_dir'] . 'display/' . $main_image, $CORE->config['photo_dir'] . 'full_size/' . $main_image, 'mainImageLink') .
            '<div align="right" id="imagePhotographer">Photo: '.$photo['photographer'].' &nbsp; </div></td></tr></table>
            <table align="center" width="400"><tr><td>
            <div id="imageDate"><b>Date:</b> ' . date('n/j/Y', $photo['date_taken']) . '</div> 
            <div id="imageLocation"><b>Taken at:</b> ' . $photo['location'] . '</div>
            </small>
            <div id="imageDescription">' . ( ( !empty($photo['description']) ) ? '<b>Description:</b> ' . $photo['description'] : '' ) . '</div>
          </td></tr></table>
          <br />';

} else {

    $CORE->print_error('No photos were found with that search criteria.');

}

$CORE->content['main'] = ob_get_contents() . $CORE->content['main']; ob_end_clean();


?>
