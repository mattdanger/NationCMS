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
* File:     sections/home/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

$url_title = (isset($_GET['url_title'])) ? clense($_GET['url_title']) : null;

if ($url_title) {

    $DB->select('news', '*', array('url_title' => $url_title));
    if (!$DB->num_rows) $CORE->print_error("That news entry could not be found.");

    $CORE->content['heading'] = $DB->result['title'];
    $CORE->content['main'] = draw_news($DB->result);

} else {

    $DB->select('news', '*', '', array('id' => 'DESC'), $CORE->config['news_display_num']);

    foreach ($DB->result as $row) {

        $CORE->content['main'] .= draw_news($row);

    }

}

?>