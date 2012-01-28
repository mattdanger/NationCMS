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
* File:     articles/home/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

$id = (isset($_GET['id'])) ? clense($_GET['id']): null;
$article = $DB->select('articles', '*', array('id' => $id));

if ($id && !$article) $CORE->print_error("Sorry, that article could not be found.");

if ( $id ) {

    $CORE->content['sidepanel_top_image'] = $CORE->config['path'] . 'images/articles/' . $article['photo'];
    $CORE->content['heading'] = '<a href="/articles/">Articles</a> &#187; ' . $article['title'];
    $CORE->content['title'] = $article['title'];

    print '<table cellpadding="0" cellspacing="2">
        <tr>
        <td valign="top">' . $article['body'] .
        '<br /><br /><small>Written by ' . $article['author'] . ' on ' . date('n/j/Y', $article['date_added']);

    if (!empty($article['last_revised'])) print ' <i>Last revised on ' . date('n/j/Y', $article['last_revised']) . '</i></small>';

    print '</td>' . '</table>';

    $CORE->content['sidepanel_top_image_alignment'] = "left";

    if (isset($section_content['photo'])) $CORE->content['sidepanel_image'] = $CORE->config['path'] . 'images/sections/' . $section_content['photo'];

} else {

    $DB->select('articles', '*', '', array( 'id' => 'DESC' )); 

    print $CORE->content['main'] . '<table cellpadding="0" cellspacing="5">';

    foreach ($DB->result as $article) {

        print '
            <tr>
                <td valign="top">' . draw_shadow('/images/articles/thumbs/' . $article['photo'], '/articles/' . $article['id'] . '/') . '</td>
                <td valign="top"><div class="header">' . $article['title'] . '</div><br />'
                . $CORE->shorten_text(strip_tags($article['body']), 50, '... ', '<small><a href="/articles/' . $article['id'] . '/">[Read More]</a></small>')
                . '
                </td>
            <tr>
                <td height="20"></td>
            </tr>';

    }

    print '</table>';

}

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>