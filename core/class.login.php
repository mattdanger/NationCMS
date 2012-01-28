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
* File:    class.login.php
* Author:  Matt West
* Purpose: Login class
*
*****************************************************/

class Login {

    // Public variables
    var $username;
    var $cookie;

    function Login() { }

    /**
     * login(): Authenticate the user's username & password
     * @param string $username User's username in the DB
     * @param string $password User's password in the DB
     * @param object $DB    MySQL database class object
     * @param string $table Table to query from
     * @returns bool        True if login successful, false if not
     */
/*
    function login ($username, $password, $DB, $table = 'users') {

        $this->username = $username;

        $DB->query("SELECT `username`, `salt`, `password` FROM `$table` WHERE `username` = '$username' LIMIT 1");

        return ( $DB->result['password'] == sha1($DB->result['salt'] . $password) ) ? true : false ;

    }
*/
    /**
     * login_admin(): Authenticate the an admin's username & password
     * @param string $username User's username in the DB
     * @param string $password User's password in the DB
     * @param object $DB    MySQL database class object
     * @param string $table Table to query from
     * @returns bool        True if login successful, false if not
     */
    function login_admin ($username, $password, $DB, $table = 'users') {

        $this->username = $username;

        $DB->query("SELECT `username`, `salt`, `password`, `user_level` FROM `$table` WHERE `username` = '$username' LIMIT 1");

        if ( ( $DB->result['password'] == sha1($DB->result['salt'] . $password) ) && ( $DB->result['user_level'] ) == '3') {

            return true;
            
        } else {
        
            return false;

        }

    }

    /**
     * set_cookie():        Set the user's cookie
     * @param string $cookie_name   Name of the cookie
     * @param object $DB    MySQL database class object
     * @param bool $remember Whether to set for 30 days or just this session
     * @param string $table Table to query from
     * @returns bool        True if cookie was sent, false if not
     */
    function set_cookie ($cookie_name, $DB, $remember = true, $table = 'users') {

        $expiration = ( $remember ) ? ( time() + (60 * 60 * 24 * 30) ) : null ;

        $DB->query("SELECT * FROM `$table` WHERE `username` = '" . $this->username . "' LIMIT 1");

        return ( setcookie($cookie_name, base64_encode( 
                                $DB->result['username'] . ":" .
                                $DB->result['id'] . ":" .
                                $DB->result['user_level'] . ":" .
                                $DB->result['first_name'] . ":" .
                                $DB->result['last_name'] ), $expiration) ) ? true : false ;

    }

    /**
     * is_logged_in():      Check if user is currently logged in
     * @param string $cookie_name   Name of the cookie
     * @returns bool        True if cookie was sent, false if not
     */
    function is_logged_in ($cookie_name) {

        $this->cookie = explode(':', base64_decode($_COOKIE[$cookie_name]) );
    
    }

    /**
     * is_admin_logged_in(): Check if user is currently logged in
     * @param string $cookie_name   Name of the cookie
     * @returns bool True if cookie was sent, false if not
     */
    function is_admin_logged_in ($cookie_name) {

        if ( isset($_COOKIE[$cookie_name]) ) {

            $this->cookie = explode(':', base64_decode($_COOKIE[$cookie_name]) );
    
            if ( $this->cookie[2] == 3 ) {
    
                return true;
    
            } else {
    
                return false;
    
            }

        } else {
        
            return false;
        
        }

    }

    /**
     * logout(): Clear the user's cookie
     * @param string $cookie_name   Name of the cookie
     */
    function logout ($cookie_name) {

        setcookie($cookie_name, '');

    }

}

?>