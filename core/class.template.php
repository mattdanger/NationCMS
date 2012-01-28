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
* File:    class.template.php
* Purpose: Template Class
* Author:  Matt West, http://mattdanger.net
*
****************************************************/

class Template {

    var $page;

    /**
     * Constructor: 			Contruct the page object
     * @param string $template  The HTML template filename
     */
    function Template($template) {

        if (file_exists($template)) $this->page = join('', file($template));

    }

    /**
     * parse(): 			Read in $file.
     * @param string $file  Filename to include
     * @returns string      File contents
     */
    function parse($file) {

        ob_start(); 					// Turn on output buffering
        include($file);
        $buffer = ob_get_contents(); 	// Dump buffer to $buffer
        ob_end_clean(); 				// Clear buffer
        return $buffer;

    }

    /**
     * parse_tags(): 		Replace {value} tags in HTML template with data
     * @param array $tags  	Array of tags & content data
     */
    function parse_tags($tags) {

        if (sizeof($tags) > 0) {

            foreach ($tags as $tag => $data) {

                $this->page = eregi_replace('{' . $tag . '}', $data, $this->page);

            }

        } 

    }

    /**
     * display(): Output & display the parsed HTML template
     */
    function display() {

        // Edit (Sep 28 23:30:55 2007): I think there's a problem with PHP on this server, it's adding slashes to this output.
        // I'm adding stripslashes() to this as a quick hack.
        echo stripslashes($this->page);

    }

}

?>
