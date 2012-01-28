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
* File:     class.email.php
* Purpose:  Email Class
* Author:   Matt West, http://mattdanger.net
*
****************************************************/

//require 'class.core.php';

class Email {

    // Public variables
    public $recipient;
    public $sender;
    public $reply_to;
    public $subject;
    public $body;
    public $cc;
    public $bcc;
    public $headers;

    /**
     * Constructor: Read in $file.
     * @param string $to        Recipient
     * @param string $subject   Email subject
     * @param string $body      Email body
     * @param array $from       "Sender's Name" => "Sender's Email"
     */
    public function __construct ($to, $subject, $body, $from = array()) {

        // Set receiver
        $this->receiver($to);

        // Set sender
        if ( !empty($from) ) {

            $this->sender = array( key($from) => $from[ key($from) ] );

        } else {

            $this->sender = array( $_SERVER['SERVER_ADMIN'] => $_SERVER['SERVER_ADMIN'] . '@' . $_SERVER['SERVER_NAME'] );

        }

        // Set subject
        $this->subject($subject);
        
        // Set body
        $this->body($body);

    }

    /**
     * receiver(): Set receiver of the email
     * @param array $send_to List of Names & Email addresses
     */
    public function receiver ($send_to) {

        foreach ($send_to as $name => $email) {

            if ( $this->email_is_valid($email) ) {

                if ( !is_numeric($name) ) { 

                    $this->recipient = ucfirst($name) . ' <' . $email . '>';

                } else {

                    $this->recipient = $email;

                }

                $this->recipient .= ', ';

            } else { 

                exit( $email . " is not a valid email address." ); 

            }

        }

        $this->recipient = preg_replace('/, $/', '', $this->recipient);

    }

    /**
     * sender(): Set send of the email
     * @param array $sender Name & Email address
     */
    public function sender ($sender) {

        if ( email_is_valid($sender[0]) ) {

            if ( !is_numeric( key($sender) ) ) { 
        
                $this->sender = ucfirst( key($sender) ) . ' <' . $email . '>';
        
            } else {
        
                $this->sender = $sender[0];
        
            }

        } else {

            exit( $sender[0] . " is not a valid email address." ); 
        
        }
    
    }

    /**
     * reply_to(): Set send of the email
     * @param array $reply_to Name & Email address
     */
    public function reply_to ($reply_to) {

        if ( email_is_valid($reply_to[0]) ) {

            if ( !is_numeric( key($reply_to) ) ) { 

                $this->reply_to = ucfirst( key($reply_to) ) . ' <' . $email . '>';

            } else {

                $this->reply_to = $sender[ key($reply_to) ];

            }

        } else {

            exit( $reply_to[0] . " is not a valid email address." ); 

        }

    }

    /**
     * cc(): Set Cc of the email
     * @param array $cc List of Names & Email addresses
     */
    public function cc ($cc) {

        foreach ($cc as $name => $email) {

            if ( email_is_valid($email) ) {

                if ( !is_numeric($name) ) { 
            
                    $this->cc = ucfirst($name) . ' <' . $email . '>';
            
                } else {
            
                    $this->cc = $email;
            
                }
            
                $this->cc .= ', ';
            
            } else { 

                exit( $email . " is not a valid email address." ); 

            }

        }
        
        $this->cc = preg_replace('/, $/', '', $this->to);

    }

    /**
     * bcc(): Set Bcc of the email
     * @param array $bcc List of Names & Email addresses
     */
    public function bcc ($bcc) {

        foreach ($bcc as $name => $email) {
        
            if ( !is_numeric($name) ) { 
        
                $this->bcc = ucfirst($name) . ' <' . $email . '>';
        
            } else {
        
                $this->bcc = $email;
        
            }
        
            $this->bcc .= ', ';
        
        }
        
        $this->bcc = preg_replace('/, $/', '', $this->to);

    }

    /**
     * set_headers(): Set email headers
     */
    public function set_headers () {

        $this->set_from();

        $this->headers  = "MIME-Version: 1.0\r\n"
                        . "From: ". $this->sender . "\r\n"
                        . "To: " . $this->recipient . "\r\n";

        if ( !empty($this->reply_to) ) $this->headers .= "Reply-To: " . $this->reply_to . "\r\n";
        if ( !empty($this->cc) ) $this->headers .= "Cc: " . $this->cc . "\r\n"; 
        if ( !empty($this->bcc) ) $this->headers .= "Bcc: " . $this->bcc . "\r\n"; 

        $this->headers .= "X-Priority: 1\r\n"
                        . "X-Mailer: PHP/" . phpversion() . "\r\n"
                        . "Content-type: text/html; charset=iso-8859-1\r\n";
        
    }

    /**
     * subject(): Sets the subject of the email.
     * @param string $subject The subject message.
     */
    public function subject ($subject) {

        // Strip any newlines
        $this->subject = str_replace('\n', '', $subject);

    }

    /**
     * body(): Sets the body message of the email.
     * @param string $body The body message.
     */
    public function body ($body) {

        $this->body = $body;

    }

    /**
     * send(): Send the email
     * @returns boolean True if successful, false if not.
     */
    public function send () {

        if (mail ( $this->recipient, $this->subject, $this->body, $this->headers)) {

            return true;

        } else {

            return false;

        }

    }

    /**
     * email_is_valid():    Check whether email address is valid
     * @param string $email Email address to check
     * @returns boolean     True if address is valid, false if not.
     */
    public function email_is_valid ($email) {

        if (eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$", $email)) {

            return true;

        } else {

            return false;

        }

    }

}

?>