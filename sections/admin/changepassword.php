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
* File:     plugins/admin/changepassword.php
* Author:   Matt West, matt@mattdanger.net
*
*****************************************************/

if (!isset($_COOKIE['ns_admin'])) header("Location: /admin");
global $main, $content_header, $bluebox_image, $bluebox, $bluebox_content_alignment, $section, $salt;

$bluebox_content_alignment = "left";
$content_header = '<a href="z.php?yow=admin">Admin</a> <small>></small> Change Password';

if (isset($_POST['submit'])) {
    $missing_field = false;
    $old_password = (isset($_POST['old_password']) && !empty($_POST['old_password'])) ? clense($_POST['old_password']) : $missing_field = true;
    $new_password1 = (isset($_POST['new_password1']) && !empty($_POST['new_password1'])) ? clense($_POST['new_password1']) : $missing_field = true;
    $new_password2 = (isset($_POST['new_password2']) && !empty($_POST['new_password2'])) ? clense($_POST['new_password2']) : $missing_field = true;

    if($missing_field) 
        print_error("You are missing one or more fields, please go back and try again.");
    else {
        $user_cookie = explode(":", base64_decode($_COOKIE['ns_admin']));
        $user_id = $user_cookie[0];
        $profile = db_query("SELECT id,password FROM `users` WHERE `id`='$user_id'");
        if ($profile['password'] == md5($salt.$old_password)) {
            if ($new_password1 == $new_password2) { 
                $new_password = md5($salt.$new_password1);
                if (db_alter("UPDATE `users` SET `password`='$new_password' WHERE `id`='$user_id'")) {
                    write_admin_log($user_cookie[2] . ' changed his/her password.');
                    $main = 'Your password was changed successfully. <br /><br /><a href="z.php?'.$section.'=admin">Click here</a> to return to the Administrative Control Panel.';
                } else
                    print_error("There was an error while attempting to change your password."); 
            } else print_error("Your new passwords do not match, please go back and try again.");
        } else print_error("The current password you entered is incorrect, please go back and try again.");
        
    }

} else {

    $main .= '
        <form method="POST" action="z.php?'.$section.'=admin&page=changepassword">
        <table cellpadding="3" border="0" cellspacing="0" align="left">
            <tr>
                <td align="right">Current password</td>
                <td><input type="password" name="old_password" size="18"></td>
            </tr>
            <tr>
                <td align="right">New password</td>
                <td><input type="password" name="new_password1" size="18"></td>
            </tr>
            <tr>
                <td align="right">Confirm new password</td>
                <td><input type="password" name="new_password2" size="18"></td>
            </tr>
            <tr><td colspan="2" height="5"></td></tr>
            <tr>
                <td></td>
                <td align="center"><input type="submit" name="submit" value="Submit"> <input type="button" name="cancel" value="Cancel" onclick="javascript: document.location='."'z.php?yow=admin'".'; return false;"></td>
            </tr>
        </table>
        ';

}
?>