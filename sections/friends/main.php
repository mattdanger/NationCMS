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
* File:     sections/friends/main.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

global $CORE, $DB;

ob_start();

$friends = $DB->select('friends', '*', '', array('id' => 'ASC'));

print $CORE->content['main'] . '<table cellpadding="0" cellspacing="2" border="0">';

foreach ($DB->result as $row) {

    print '
    <tr>
        <td align="center" valign="top"><a name="' . $row['name'] . '">' 
        . draw_shadow('/images/friends/' . $row['photo'], $row['website']);

    if (!empty($row['website'])) print '<a href="' . $row['website'] . '" target="_blank"><img src="/images/icons/website.gif" border="0"></a> ';
    if (!empty($row['myspace'])) print '<a href="http://www.myspace.com/' . $row['myspace'] . '" target="_blank"><img src="/images/icons/myspace.gif" border="0"></a>';

    print '
        </td><td valign="top">
            <div class="header">' . $row['name'] . '</div><br />' . $row['description'] . '
        </td>
    </tr>
    <tr>
        <td height="30"></td>
    </tr>';

}

print '</table>';

$CORE->content['main'] = ob_get_contents(); ob_end_clean();

?>
