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
* File:    class.mysql.php
* Purpose: MySQL Class
* Author:  Matt West, http://mattdanger.net
*
****************************************************/

class MySQL_Connection {

	// Private variables
    var $handler;
    var $hostname;
    var $username;
    var $password;
    var $database;

	// Public variables
    var $result;
    var $num_rows;

    /**
     * Constructor: Construct the object.
     * @param string $hostname  Hostname of MySQL server (Default: localhost) 
     * @param string $username  Your MySQL account username
     * @param string $password  Your MySQL account password
     * @param string $database  The MySQL database to connect to
     */
    function MySQL_Connection($hostname, $username, $password, $database) {

        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->connect();
        $this->select_db();

    }

    /**
     * Destructor: Clean up
     */
    function __destruct() {

        $this->db_close();

    }

    /**
     * throw_error(): Print an error message
     * @param string $message   A descriptive error message that will print when invoked
     */
    function throw_error($message, $line = 0){

        $line = ( !empty($line) ) ? $line = ' on line ' . $line : '' ;
        die ('There was an error on line' . $line .' in class "' . __CLASS__ . '": ' . $message);

    }

    /**
     * connect(): Connect to MySQL
     */
    function connect() {

        $this->handler = mysql_pconnect($this->hostname, $this->username, $this->password) or $this->throw_error(mysql_error(), __LINE__);

    }

    /**
     * select_db(): Select the MySQL database
     */
    function select_db() {

        mysql_select_db($this->database, $this->handler) or $this->throw_error(mysql_error(), __LINE__);

    }

    /**
     * select(): Select a row or rows from the DB
     * @param string $table     Table name
     * @param array $columns    Either '*' or a list of columns to select
     * @param array $where      column => value
     * @param array $order_by   column1 => DESC, column2 => ASC
     * @param array $limit      1 or 30 or 0, 30 or 30, 60 (etc)
     * @param bool $debug       Returns query string if true
     * @returns MySQL resource
     */
    function select($table, $columns, $where = '', $order_by = '', $limit = '', $debug = 0) {

        if ( $table == '' || !sizeof($columns)) { return; }

        // Set up columns
        $tmp = '';
        if ( $columns != '*' ) {

            if ( is_array($columns) ) {

                foreach($columns as $column) {
        
                    $data .= '`' . $column . '`, ';
        
                }
    
                $columns = preg_replace( "/, $/" , "" , $data);

            } else {

                $columns = '`' . $columns . '`';

            }

        }

        // Set up the WHERE
        if ( !empty($where) ) {
    
            $tmp = '';
            foreach ($where as $key => $val) {
    
                $tmp .= '`' . $key . "` = '" . $this->escape_str($val) . "' AND ";
    
            }
    
            $where = ' WHERE ' . preg_replace( "/ AND $/" , "" , $tmp);

        }

        // Set up the ORDER BY
        $tmp = '';
        if ( !empty($order_by) ) {

            if ( is_array($order_by) ) {

                foreach($order_by as $val => $order) {

                    $tmp .= '`' . $val . '` ' . $order . ', ';
    
                }
    
                $order_by = ' ORDER BY ' . preg_replace( "/, $/" , "" , $tmp);

            } else {

                $order_by = ' ORDER BY ' . $order_by . ' DESC';

            }

        }

        // Set up the LIMIT
        $tmp = '';
        if ( !empty($limit) ) {

            if ( is_array($limit) ) {
    
                foreach ($limit as $num) {
    
                    $tmp .= $num . ', ';
    
                }
    
                $limit = ' LIMIT ' . preg_replace( "/, $/" , "" , $tmp);
    
            } else {
    
                $limit = ' LIMIT ' . $limit;
    
            }

        }

        $query = 'SELECT ' . $columns . ' FROM `' . $table . '`' . $where . $order_by . $limit;

        // Return query string for debugging purposes or just do the query
        if ( $debug ) {

            return $query;
        
        } else {

            // Perform the query
            return $this->query($query);

        }


    }

    /**
     * insert(): Insert data into a table
     * @param string $table Table to insert into
     * @param array $data   The column name & data
     * @param boolean $slashes True if you want to add \'s to the string
     * @returns boolean True if successful, false if not.
     */
    function insert($table, $data, $slashes = false) {

        if ( $table == '' || $data == '' ) { return; }

        $fields = '';      
        $values = '';
        
        foreach($data as $key => $val) {

            $fields .= '`' . $key . '`, ';
            $val = ( $slashes == true ) ? addslashes($val) : $val;
            $values .= "'" . addslashes( stripslashes($val) ) . "'" . ', ';

        }
        
        $fields = preg_replace( "/, $/" , "" , $fields);
        $values = preg_replace( "/, $/" , "" , $values);

        mysql_query('INSERT INTO `' . $table . '` (' . $fields . ') VALUES (' . $values . ')', $this->handler) or $this->throw_error(mysql_error(), __LINE__);

    }

    /**
     * update(): Update data into a table
     * @param string $table Table to insert into
     * @param array $data   The column name & data
     * @param boolean $slashes True if you want to add \'s to the string
     * @returns boolean True if successful, false if not.
     */
    function update($table, $data, $where, $slashes = false) {

        if ( $table == '' || $data == '' || $where == '') { return; }

        $string  = '';
        $tmp = '';
        
        foreach($data as $key => $val) {
            $string .= '`' . $key . "` = '" . $this->escape_str($val) . "', ";
        }

        $string = preg_replace( "/, $/" , "" , $string);
        
        if ( is_array($where) ) {

            foreach ($where as $key => $val) {

                $tmp .= $key . " = '" . $this->escape_str($val) . "' AND ";

            }
            
            $where = preg_replace( "/AND $/" , "" , $tmp);

        } else {

            $where = $where;

        }

        mysql_query('UPDATE `' . $table . '` SET ' . $string . ' WHERE ' . $where, $this->handler) or $this->throw_error(mysql_error(), __LINE__);

    }

    /**
     * delete(): Delete a row from a table
     * @param string $table Table to insert into
     * @param array $data   The column name & data
     */
    function delete($table, $data) {

        if ( $table == '' || $data == '') { return; }

        mysql_query('DELETE FROM `' . $table . '` WHERE `' . key($data) . "` = '" . $data[key($data)] . "'", $this->handler) or $this->throw_error(mysql_error(), __LINE__);

    }

    /**
     * query(): Query from MySQL. 
     * @param string $query The MySQL query
     * @returns MySQL resource
     *
     * Warning: This function is only ment to be called manually if being used in 
     * a development environment because it does not do any input validation.
     */
    function query($query) {

        if ( $query == '') { return; }

        $this->result = mysql_query($query, $this->handler) or $this->throw_error(mysql_error(), __LINE__);
        $this->num_rows = mysql_num_rows($this->result);

        if ($this->num_rows > 1) {

            $tmp_array = array();
            while ($row = mysql_fetch_assoc($this->result)) {

                array_push($tmp_array, $row);

            }

            $this->result = $tmp_array;
            return $this->result;

        } else {

            $this->result = mysql_fetch_assoc($this->result);
            return $this->result;
        }

    }

    function escape_str($string) {

    	if ( is_array($string) ) {

    		foreach($string as $key => $val) {

    			$str[$key] = $this->escape_str($val);

    		}
    		
    		return $str;
    	}
    
    	if ( function_exists('mysql_escape_string') ) {

			return mysql_escape_string( stripslashes($string) );

		} else {

        	return addslashes( stripslashes($string) );

    	}

    }


    /**
     * close_connection(): Close connection to MySQL
     */
    function db_close(){

        mysql_close($this->handler);

    }

}

?>