<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

require_once "@CENTREON_ETC@/centreon.conf.php";

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

$menu1_bgcolor = "#6056e8";
$menu2_bgcolor = "#ebf5ff";

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

$bg_image_header = "../Images/bg_header.gif";
$menu1_bgimg = "../Images/menu_bg_blue.gif";

require_once $centreon_path . "www/Themes/Centreon-2/color_css.php";
?>
