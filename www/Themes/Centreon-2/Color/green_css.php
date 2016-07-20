<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

require_once realpath(dirname(__FILE__) . "/../../../../config/centreon.config.php");

header("Content-type: text/css");

$color1 = "#F2F2F2";
$color2 = "#666666";
$color3 = "#E3A385";
$color4 = "#d5dfeb";
$color5 = "#ced3ed";
$color6 = "#BFD0E2";
$color7 = "#AAAAAA";
$color8 = "#D1DCEB";
$color9 = "#FFFFFF";
$color10 = "#AAAAEE";
$color11 = "#592bed";
$color12 = "#242af6";

$color13 = "#5e5e5e";
$color14 = "#E8AB5C";

$menu1_bgcolor = "#ADC7A1";
$menu2_bgcolor = "#43B02A";

$colorHeader_1 = "#cfedf9";

$footerline2 = "#dedede";

$color_list_1 = "#F7FAFF";
$color_list_1_hover = "#FDF0D5";

$color_list_2 = "#EDF4FF";
$color_list_2_hover = "#FDF0D5";
$color_list_3 = "#fada83";
$color_list_3_hover = "#bada83";
$color_list_4 = "#fdc11e";
$color_list_4_hover = "#bdc11e";
$color_list_up = "#B2F867";
$color_list_up_hover = "#B2A867";
$color_list_down = "#ffbbbb";
$color_list_down_hover = "#dfbbbb";

$bg_image_header = "../Images/bg_header_green.gif";
$menu1_bgimg = "#43B02A";

$menu2_color = "#e7ffb4";

require_once _CENTREON_PATH_ . "www/Themes/Centreon-2/color_css.php";
