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
* File:    class.core.php
* Author:  Matt West
* Purpose: Core class
*
*****************************************************/

class Core {

    // Public variables
    var $logs_table;
    var $this_dir;
    var $this_file;
    var $this_section;
    var $this_page;
    var $this_action;
    var $this_status;
    var $config = array();
    var $content = array();
    var $DB;

    // Private variables
    var $ip;
    var $date;
    var $user_agent;

    function Core($config, $content = array() ) {

        // Set the public variables
        $this->logs_table = 'log';
        $this->config = $config;
        $this->content = $content;

        // Set the private variables
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->date = time();    
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];

        $this->get_query_str_vars();

    }

    /**
     * get_query_str_vars(): Set URI variables based on the current GET query string
     */
    function get_query_str_vars () {

        $this->this_section = ( isset($_GET['section']) ) ? $_GET['section'] : 'home';
        $this->this_page = ( isset($_GET['page']) ) ? $_GET['page'] : 'main';
        $this->this_action = ( isset($_GET['action']) ) ? $_GET['action'] : null;
        $this->this_status = ( isset($_GET['status']) ) ? $_GET['status'] : null;

    }

    /**
     * get_class_file(): Fetch a class file and create an object from it.
     * @param string $class Name of class
     */
    function get_class_file ($class) {

        require $this->config['root_path'] . $this->config['core_path'] . '/class.' . strtolower($class) . '.php';

    }

    /**
     * get_section(): Fetch a page or section of the website
     */
    function get_section () {

        $file = 'sections/' . $this->this_section . '/' . $this->this_page . '.php';

        if ( file_exists($file) ) {

            require $file;

        } else {
        
                //$this->print_error("The page '$file' could not be found");
                $this->redirect('/404/');

        }

    }

    /**
     * get_section_old(): Fetch a page or section of the website. This function has been deprecated.
     */
    function get_section_old () {

        if( !isset($_GET['section']) || empty($_GET['section']) ) {

            require 'sections/' . $this->this_section . '/' . $this->this_page . '.php';

        } else {

            $this->include_section($_GET['section'], ( (isset($_GET['page']) && !empty($_GET['page'])) ? $_GET['page'] : NULL) );

        }

    }

    /**
     * get_section(): Loads the requested content sections and pages.  This function has been deprecated.
     * @param string $section  The 'section' HTTP request variable
     * @param string $section  The 'page' HTTP request variable
     */
    function include_section($section, $page = null) {
    
        if(file_exists('sections/' . $section . '/main.php')){

            if($page != null){
    
                if(!file_exists( 'sections/' . $section . '/' . $page . '.php')){
   
                    $this->write_log("User requested a page that didn't exist. Forwarded them to 404.", 'Error');
                    //$this->print_error("The page '$page' does not exist");
                    $this->redirect('/404/');
    
                }

                include 'sections/' . $section . '/' . $page . '.php';

            } else {   
    
                include 'sections/' . $section . '/main.php';
    
            }
    
        } else {
    
            $this->write_log("User requested a section that didn't exist. Forwarded them to 404.", 'Error');
            //$this->print_error("The section '$section' does not exist");
            $this->redirect('/404/');

    
        }
    
    }

    /**
     * get_action(): Get the action for a page or section to execute
     * @returns string The action to execute
     */
    function get_action () {

        return ( !empty($_GET['action']) ) ? $_GET['action'] : null ;

    }

    /**
     * get_status(): Get the status type for the page to display a message
     * @returns string The status type
     */
    function get_status () {

        return ( !empty($_GET['status']) ) ? $_GET['status'] : null ;

    }

    /**
     * update_link_tree(): Makes the previous node a link
     * @returns string $link Link destination
     */
    function update_link_tree($link) {

        $tmp = explode("&#187; ", $this->content['link_tree']);
        $this->content['link_tree'] = $tmp[0] . '&#187; <a href="' . $link . '">' . $tmp[1] . '</a> &#187; ';

    }

    /**
     * generate_link(): Generate an HTML link
     * @param array $link_parts GET vars to include
     * @param string $text The text part of the link
     * @returns string The full HTML link
     */
    function generate_link ($link_parts, $text) {

    }

    /**
     * write_log(): Record an entry in the logs database
     * @param string $message Log error message
     * @param string $title Log error title
     */
    function write_log ($message, $type = 'Notice') {

        $insert_data = array (  'date'      => $this->date,
                                'ip'        => $this->ip,
                                'user_agent' => $this->user_agent,
                                'type'      => $type,
                                'message'   => $message ) ;

        $this->DB->insert($this->logs_table, $insert_data);

    }

    /**
     * redirect(): Redirect user to another page
     * @param string $page Page to redirect to
     */
    function redirect ($page) {

        header( 'Location: ' . $page );

    }

    /**
     * email_is_valid():    Check whether email address is valid
     * @param string $email Email address to check
     * @returns boolean     True if address is valid, false if not.
     */
    function email_is_valid ($email) {

        if (eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$", $email)) {

            return true;

        } else {

            return false;

        }

    }

    /**
     * get_user_level():    Return the privilege level of the user
     * @param string $level 1 thru 4, value from user's record in the DB
     * @returns string      The user's level     
     */
    function get_user_level ($level) {

        switch ($level) {

            case '1': return 'Reviewer';
            case '2': return 'Restaurant';
            case '3': return 'Moderator';
            case '4': return 'Administrator';

        }
    

    }

    /**
     * print_error(): Print an error message
     * @param string $message The error message
     */
    function print_error ($message) {

        @ob_end_clean(); // Flush buffers
        $this->get_class_file('template');
        $TMP = new Template ( ($this->browser_is_ie()) ? 'index-ie.html' : 'index-mozilla.html' );
        $this->content['heading'] = 'Error!';
        $this->content['sidepanel_top_image'] = $this->config['path'] . 'images/site/error2.png';
        $this->content['sidepanel_top'] = '';
        $this->content['main'] = $message;
        $TMP->parse_tags($this->content);
        $TMP->display();
        unset($TMP);
        exit;

    }

    /**
     * generate_salt():    Generate an 8 byte hash
     * @returns string     Returns the hash value
     */
    function generate_salt() {

        return substr( md5( uniqid( rand(), true) ), 0, 8 );

    }

    /**
     * shorten_text():      Shorten a block of text
     * @param string $text  Block of text
     * @param string $length Number of words to shorten to
     * @param string $trail The trailing characters at the end
     * @param string $append The string to append, sometimes a link
     * @returns string      Shortened text with $trail
     */
    function shorten_text($text, $length, $trail = '...', $append = '') {

        $tmp_array = explode(" ", $text);
        $tmp_text = null;

        if ( sizeof($tmp_array) <= $length ) {

            return $text;

        } else {

            for ($i = 0; $i<$length; $i++) {

                $tmp_text .= $tmp_array[$i] . ' ';

            }

        return trim($tmp_text) . $trail . ((!empty($append)) ? ' ' . $append : '') ;

        }

    }

    /**
     * browser_is_ie7():    Check if user's browser is IE 7
     * @returns boolean     True = yes, false = no
     */
    function browser_is_ie7() {
        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 7.0")) 
            return true;
        else 
            return false;
    }

    /**
     * browser_is_ff():    Check if user's browser is Firefox
     * @params string $version Specify a version
     * @returns boolean     True = yes, false = no
     */
    function browser_is_ff($version = null) {
        if (strstr($_SERVER['HTTP_USER_AGENT'], "Firefox/" . $version))
            return true;
        else 
            return false;
    }

    /**
     * browser_is_safari():    Check if user's browser is Safari
     * @params string $version Specify a version
     * @returns boolean     True = yes, false = no
     */
    function browser_is_safari($version = null) {
        if (strstr($_SERVER['HTTP_USER_AGENT'], "Safari/" . $version))
            return true;
        else 
            return false;
    }

    /**
     * browser_is_ie():    Check if user's browser is IE
     * @returns boolean    True = yes, false = no
     */
    function browser_is_ie() {
        if ($this->browser_is_ff() || 
            $this->browser_is_ie7() || 
            $this->browser_is_safari() )
            return false;
        else 
            return true;
    }

    /**
     * clients_browser():   Print the client's browser
     * @returns string      Browser name
     */
    function clients_browser () {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = null;
        if (strchr($user_agent, 'Firefox') && strchr($user_agent, 'Windows')) $browser = 'Firefox-Win';
        if (strchr($user_agent, 'Firefox') && strchr($user_agent, 'Macintosh')) $browser = 'Firefox-Mac';
        if (strchr($user_agent, 'Safari')) $browser = 'Safari';
        return $browser;
    }


    /**
     * login(): Authenticate the user's username & password
     * @param string $username User's username in the DB
     * @param string $password User's password in the DB
     * @param object $DB    MySQL database class object
     * @param string $table Table to query from
     * @returns bool        True if login successful, false if not
     */




}

?>