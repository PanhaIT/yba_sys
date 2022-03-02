<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show_example.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2009 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 2.0.1 BETA (December 6th, 2009)
 * @package Securimage
 *
 */

include 'securimage.php';

//$img = new securimage();
$img = new securimage();

//Change some settings
$img->image_width = 250;
$img->image_height = 80;
$img->perturbation = 0.85;
//$img->image_bg_color = new Securimage_Color("#f6f6f6");
$img->multi_text_color = array(new Securimage_Color("#3c3c3c"),
        new Securimage_Color("#2a3f47"),
        new Securimage_Color("#081115"),
        new Securimage_Color("#3a1201"),
        new Securimage_Color("#063605")
);
$img->use_multi_text = true;
$img->text_angle_minimum = 0;
$img->text_angle_maximum = 0;
//$img->text_angle_minimum = -5;
//$img->text_angle_maximum = 5;
$img->use_transparent_text = true;
$img->text_transparency_percentage =10; // 100 = completely transparent
$img->num_lines = 7;
$img->line_color = new Securimage_Color("#fff");
//$img->image_signature = 'Udaya Technology Co, Ltd';
$img->signature_color = new Securimage_Color("#0042ff");
$img->use_wordlist = true; 

$img->num_lines = 2; // no lines, just the code

$img->show('backgrounds/captcha.png'); // alternate use:  $img->show('/path/to/background_image.jpg');
//$img->background_directory = dirname(__FILE__) . '/backgrounds/';

