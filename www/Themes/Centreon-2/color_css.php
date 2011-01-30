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
 	require_once $centreon_path . "www/class/centreonDB.class.php";

	$pearDB = new CentreonDB();

 	/*
 	 * Get Options colors
 	 */
 	$options = array();
 	$DBRESULT = $pearDB->query("SELECT * FROM options");
 	while ($res = $DBRESULT->fetchRow())
 		$options[$res["key"]] = $res["value"];
 	unset($res);

?>

.ListTable a:link, #ListTable a:link			{color:<?php print $color2; ?>;}
.ListTable a:visited, #ListTable a:visited		{color:<?php print $color2; ?>;}
.ListTable a:hover, #ListTable a:hover			{color:<?php print $color3; ?>;}

.list_lvl_1{	background-color:<?php print $color4; ?>;}
.list_lvl_2{	background-color:<?php print $color5; ?>;}

.ListHeader{	background-image:url(<?php print $bg_image_header; ?>);
				background-position:top left;
				background-repeat:repeat-x;
				background-color:<?php print $color6; ?> ;
}

.ListSubHeader{
				background-color:#A9C5F2;
				font-weight:bold;
				background-position:top left;
}


.ListTable, .ListTableMedium, .ListTableSmall {border-color: <?php print $color6; ?>;}

.list_lvl_2 td, .list_lvl_1 td{
	border-top-color: <?php print $color7; ?>;
	border-left-color: <?php print $color7; ?>;
	border-bottom-color: <?php print $color7; ?>;
	border-right-color: <?php print $color7; ?>;
}

.list_one td, .list_two td, .list_three td, .list_four td, .list_up td, .list_down td, .line_ack td, .line_downtime td, .list_unreachable td, .row_disabled td {
	border-top-color: <?php print $color8; ?>;
	border-left-color: <?php print $color8; ?>;
	border-bottom-color: <?php print $color8; ?>;
	border-right-color: <?php print $color8; ?>;
}

.tab .list_one td, .tab .list_two td, .tab .list_three td, .tab .list_four td, .tab .list_up td, .tab .list_down td,.tab .line_ack td,.tab .line_downtime td,.tab .list_unreachable td, .tab .row_disabled td {
	border-top-color: <?php print $color8; ?>;
	border-left-color: <?php print $color8; ?>;
	border-bottom-color: <?php print $color8; ?>;
	border-right-color: <?php print $color8; ?>;
}

.tab .list_lvl_2 td, .tab .list_lvl_1 td{
	border-top-color: <?php print $color7; ?>;
	border-left-color: <?php print $color7; ?>;
	border-bottom-color: <?php print $color7; ?>;
	border-right-color: <?php print $color7; ?>;
}

.ListHeader td{
	border-top-color: <?php print $color9; ?>;
	border-left-color: <?php print $color9; ?>;
}

.ListColFooterRight, .ListColFooterLeft, .ListColFooterCenter{
	border-top-color: <?php print $color10; ?>;
}

.list_one_fixe		{	background-color:<?php print $color_list_1; ?>;}
.list_two_fixe 		{	background-color:<?php print $color_list_2; ?>;}

.list_one			{	background-color:<?php print $color_list_1; ?>;}
.list_one:hover 	{	background-color:<?php print $color_list_1_hover; ?>;}

.list_two 			{	background-color:<?php print $color_list_2; ?>; }
.list_two:hover 	{	background-color:<?php print $color_list_2_hover; ?>;}

.list_three 		{	background-color:<?php print $color_list_3; ?>;}
.list_three:hover 	{	background-color:<?php print $color_list_3_hover; ?>;}

.list_four 			{	background-color:<?php print $color_list_4; ?>;}
.list_four:hover 	{	background-color:<?php print $color_list_4_hover; ?>;}

.list_up			{	background-color:<?php print $color_list_up; ?>;}
.list_up:hover		{	background-color:<?php print $color_list_up_hover; ?>;}

.list_down 			{	background-color:<?php print $options["color_line_critical"]; ?>;}
.list_down:hover 	{	background-color:<?php print $options["color_line_critical"]; ?>;}

.list_unreachable 			{	background-color:<?php print $options["color_host_unreachable"]; ?>;}
.list_unreachable:hover 	{	background-color:<?php print $options["color_host_unreachable"]; ?>;}

.line_downtime		{	background-color:<?php print $options["color_downtime"]; ?>;}
.line_downtime:hover{	background-color:<?php print $options["color_downtime"]; ?>;}

.line_ack			{	background-color:<?php print $options["color_ack"]; ?>;}
.line_ack:hover		{	background-color:<?php print $options["color_ack"]; ?>;}

/* Monitoring Side */
.host_down 			{-moz-border-radius:8px;background-color:<?php print $options["color_host_down"]; ?>;border:2px <?php print $options["color_host_down"]; ?> solid;}
.host_unreachable 	{-moz-border-radius:8px;background-color:<?php print $options["color_host_unreachable"]; ?>;border:2px <?php print $options["color_host_unreachable"]; ?> solid;}
.host_downtime 		{-moz-border-radius:8px;background-color:<?php print $options["color_downtime"]; ?>;border:2px <?php print $options["color_downtime"]; ?> solid;}


/* Menu */
#menu1_bgimg	{
	background-image: url(<?php print $menu1_bgimg; ?>);
	background-position:top right;
	background-repeat:repeat-x;
	}
#menu1_bgcolor	{	background-color: <?php print $menu1_bgcolor; ?>;}
#menu2_bgcolor	{	background-color: <?php print $menu2_bgcolor; ?>;}
#menu_2			{	background-color:	<?php print $menu1_bgcolor; ?>;}

.Tmenu3 .top .left 		{	background-color:  <?php print $color1; ?>;}
.Tmenu3 .top .right 	{	background-color:  <?php print $color1; ?>;}
.Tmenu3 .bottom .left 	{	background-color:  <?php print $color1; ?>;}
.Tmenu3 .bottom .right 	{	background-color:  <?php print $color1; ?>;}

#Tmenu	{	border-right: 1px solid <?php print $menu1_bgcolor; ?>;}
#footerline1	{	background-color:<?php print $menu1_bgcolor; ?>;}
#footerline2	{	background-color:<?php print $footerline2; ?>;}

input, textarea {	font-size: 10px; border:1px solid #BBBBBB;}

input[type="submit"],input[type="button"],input[type="reset"]{
	background : white;
	color : <?php print $menu1_bgcolor; ?>;
	border-color : <?php print $menu1_bgcolor; ?>;
	cursor: pointer;
	}

input[type="submit"]:hover,input[type="button"]:hover,input[type="reset"]:hover{
	background : <?php print $menu1_bgcolor; ?>;
	color : <?php print $color9; ?>;
	border-color : <?php print $menu1_bgcolor; ?>;
	}

.limitPage{
	background-image:url(<?php print $bg_image_header; ?>);
	color:<?php print $color9; ?>;
	background-position:top left;
	background-repeat:repeat-x;
	background-color:<?php print $color6; ?> ;
	}

.pageNumber{
	background-image:url(<?php print $bg_image_header; ?>);
	color:<?php print $color9; ?>;
	background-position:top left;
	background-repeat:repeat-x;
	background-color:<?php print $color6; ?> ;
	}

.a, .b{
	border-color:<?php print $color10; ?>;
	}

.msg_loading{
	position:absolute;
	top:20px;
	left:200px;
	width:200px;
	color:blue;
	font-size:18px;
	width:100%;
	height:100%;
	}

.msg_isloading{
	font-size:14px;
	position:absolute;
	top:20px;
	left:200px;
	background-color:red;
	color:white;
	width:200px;
	}

.Toolbar_TDSelectAction_Top a {	font-family:Arial, Helvetica, Sans-Serif;font-size:11px;color:<?php print $color13; ?>;}
.Toolbar_TDSelectAction_Top a:hover {color:<?php print $color14; ?>;}

.Toolbar_TDSelectAction_Bottom a {font-family:Arial, Helvetica, Sans-Serif;font-size:11px;color:<?php print $color13; ?>;}
.Toolbar_TDSelectAction_Bottom a:hover {color:<?php print $color14; ?>;}

.Toolbar_TableBottom {border-color:<?php print $menu1_bgcolor; ?>;}

#mainnav li{	background-image: url("<?php print $bg_image_header; ?>");}
