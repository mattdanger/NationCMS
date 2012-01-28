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
* File:    class.photo-resize.php
* Purpose: Photo Resizing Class
* Author:  Matt West, http://mattdanger.net
*
****************************************************/

class Photo {

    // Private variables
    private $orig_image;
    private $portrait_frame;    // 1 = Landscape, 2 = Portrait
    private $image_size;
    private $src_width;
    private $src_height;
    private $src_image;
    private $dest_height;
    private $dest_width;
    private $dest_image;
    private $ratio;
    private $scale;

    // Public variables
    public $allowed_types = array('jpeg', 'jpg', 'png', 'gif');

    /**
     * Constructor:         Contruct the photo object
     * @param array $photo  Array of photo data, usually $_FILES['photo_name']
     */
    public function __construct($photo = null) {

        if ( $photo ) {

            $this->photo($photo);

        }

    }

    /**
     * Destructor: Destroy the image data
     */
    public function __destruct() {

        $this->cleanup();

    }

    /**
     * photo():             Create new image resource
     * @param string $photo Array of photo data
     */
    public function photo($photo) {

        $this->orig_image = $photo;

        $this->src_image = imagecreatefromjpeg ($this->orig_image['tmp_name']);
        $this->dest_image = imagecreatefromjpeg ($this->orig_image['tmp_name']);

        // Confirm photo is an allowed type;
        if ( !in_array(ereg_replace('image/', '', $this->orig_image['type']), $this->allowed_types) ) {

            exit("Photo type is '" . $this->orig_image['type'] . "' which is not allowed");

        }

        // Get image X and Y values in order to determine photo framing
        $this->image_size = getimagesize($this->orig_image['tmp_name']);
        $this->src_width = $this->image_size[0];
        $this->src_height = $this->image_size[1];
        $this->dest_height = 0;

        // 1 = Landscape, 2 = Portrait
        $this->photo_framing = ($this->src_width > $this->src_height) ? 1 : 2 ;

        // Calculate the ratio at which to resize based on the dimentions
        $this->ratio = $this->src_width / $this->src_height;

    }

    /**
     * scale_to_width():    Sets the width of the resize value
     * @param int $width    Width in pixels
     * @returns boolean     Returns true if successful, false if it was not.
     */
    public function scale_to_width($width) {

        // Set the destination width value
        $this->dest_width = $width;

        // Calculate the value at which to scale the image
        $this->scale = ($this->ratio) ? $this->dest_width / $this->src_width : $this->dest_height / $this->src_height; 
        
        if ($this->scale_image()) {

            return true;

        } else {

            return false;

        }
        
    }

    /**
     * scale_to_height():   Sets the height of the resize value
     * @param int $height   Height in pixels
     * @returns boolean     Returns true if successful, false if it was not.
     */
    public function scale_to_height($height) {

        // Set the destination height value
        $this->dest_height = $height;

        // Calculate the ratio at which to resize based on the dimentions
        $this->ratio = $this->src_width / $this->src_height;

        // Calculate the value at which to scale the image
        $this->scale = ($this->ratio) ? $this->dest_height / $this->src_height : $this->dest_width / $this->src_width ; 
        
        if ($this->scale_image()) {

            return true;

        } else {

            return false;

        }
        
    }

    /**
     * scale_image():   Resize image proportionally
     * @returns boolean Returns true if successful, false if it was not.
     */
    private function scale_image () {

        if ($this->scale > 1) {

            exit ("The scale width supplied is larger than the original image. Please try a smaller image or change the scaling requirements.");

        }
        
        $this->dest_width = $this->src_width * $this->scale;
        $this->dest_height = $this->src_height * $this->scale;

        if ( $this->dest_width >= $this->src_width && $this->dest_width >= $this->src_width ) $this->scale = 1;

        $this->dest_image = imagecreatetruecolor ($this->src_width * $this->scale, $this->src_height * $this->scale);

        if ( imagecopyresampled(   $this->dest_image, 
                                    $this->src_image, 
                                    0, 0, 0, 0, 
                                    $this->dest_width, 
                                    $this->dest_height, 
                                    $this->src_width, 
                                    $this->src_height) ) { 

            return true;

        } else {

            return false;

        }

    }

    /**
     * crop_square():   Resize image proportionally & crop to square
     * @param int $dimention    Height/width dimention
     * @param int $dest_width    Height/width dimention
     * @param int $src_x    Height/width dimention
     * @param int $srx_y    Height/width dimention
     * @returns boolean Returns true if successful, false if it was not.
     */
    public function crop_square ($dimension, $dest_width, $src_x, $src_y) {

        $this->dest_width = $dest_width;
        $this->ratio = $this->src_width / $this->src_height;
        $this->scale = ($this->ratio) ? $this->dest_width / $this->src_width : $this->dest_height / $this->src_height; 
        $this->dest_width = $this->src_width * $this->scale;
        $this->dest_height = $this->src_height * $this->scale;
        if ($this->dest_width >= $this->src_width && $this->dest_width >= $this->src_width) $this->scale = 1;

        $this->dest_image = imagecreatetruecolor($this->src_width * $this->scale, $this->src_height * $this->scale);

        if ( ! imagecopyresampled(  $this->dest_image, 
                                    $this->src_image, 
                                    0, 0, 0, 0, 
                                    $this->dest_width, 
                                    $this->dest_height, 
                                    $this->src_width, 
                                    $this->src_height) ) {
        
            exit("An error occured while trying to resize the image.");

        }
        
        $dest_image2 = imagecreatetruecolor($dimension, $dimension);

        if ( ! imagecopy ($dest_image2, $this->dest_image, 0, 0, $src_x, $src_y, $dimension, $dimension) ) {
        
            exit("An error occured while trying to crop the image.");
        
        }

        imagedestroy($this->dest_image);
        $this->dest_image = imagecreatetruecolor($dimension, $dimension);

        if ( ! imagecopy ($this->dest_image, $dest_image2, 0, 0, 0, 0, $dimension, $dimension) ) {
        
            exit("An error occured while trying to copy the image.");
        
        }
        imagedestroy($dest_image2);

        unset($dimention, $dest_width, $src_x, $src_y);

        return true;

    }

    /**
     * save():            Saves the resized photo
     * @param string $filename  The path and filename to save the file (ex: "/path/to/image.jpg")
     * @param int $compression  A number from 1-100 representing the level of compression. 100 is the least compression.
     * @returns boolean         Returns true if save was successful, false if it was not.
     */
    public function save ($filename, $compression = 80) {

        if ( imagejpeg($this->dest_image, $filename, $compression) ) {

            $return_value = true;

        } else {

            $return_value = false;

        }

        return $return_value;

    }

    /**
     * cleanup():   Collect some garbage
     */
    public function cleanup() {

        imagedestroy($this->src_image);
        if ( isset($dest_image) ) {
            imagedestroy($this->dest_image);
        }

    }

}

?>