<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
?>

.ListTable a:link, #ListTable a:link			{color:<?php print $color2; ?>;}
.ListTable a:visited, #ListTable a:visited		{color:<?php print $color2; ?>;}
.ListTable a:hover, #ListTable a:hover			{color:<?php print $color3; ?>;}

/* Form */
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

	
.ListTable, .ListTableMedium, .ListTableSmall {
	border-color: <?php print $color6; ?>;
	}

.list_lvl_2 td, .list_lvl_1 td{
	border-top-color: <?php print $color7; ?>;
	border-left-color: <?php print $color7; ?>;
	border-bottom-color: <?php print $color7; ?>;
	border-right-color: <?php print $color7; ?>;
	}

.list_one td, .list_two td, .list_three td, .list_four td, .list_up td, .list_down td{
	border-top-color: <?php print $color8; ?>;
	border-left-color: <?php print $color8; ?>;
	border-bottom-color: <?php print $color8; ?>;
	border-right-color: <?php print $color8; ?>;
	}

.tab .list_one td, .tab .list_two td, .tab .list_three td, .tab .list_four td, .tab .list_up td, .tab .list_down td{
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

.list_one_fixe	{		
	background-color:<?php print $color_list_1; ?>;
	} 
.list_two_fixe {
	background-color:<?php print $color_list_2; ?>; 
	}


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

.list_down 			{	background-color:<?php print $color_list_down; ?>;}
.list_down:hover 	{	background-color:<?php print $color_list_down_hover; ?>;}

//#page {			color:<?php print $color11; ?>;}
//#page a{		color:<?php print $color11; ?>;}

/*Menu*/
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

/* General */
#contener{		/*background-color:<?php print $color1; ?>;*/}

#Tmenu{		/*background-color:<?php print $color1; ?>*/;
		border-right: 1px solid <?php print $menu1_bgcolor; ?>;
		}

#footerline1	{	background-color:<?php print $menu1_bgcolor; ?>;}
#footerline2	{	background-color:<?php print $footerline2; ?>;}

input, textarea {	font-size: 10px; border:1px solid #BBBBBB;}

input[type="submit"],input[type="button"],input[type="reset"]{
	background : white;	
	color : <?php print $menu1_bgcolor; ?>;
	border-color : <?php print $menu1_bgcolor; ?>;
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

.Toolbar_TDSelectAction_Top a{
	font-family:Arial, Helvetica, Sans-Serif;    font-size:11px;    color:#666;
	color:<?php print $color13; ?>;
	}

.Toolbar_TDSelectAction_Top a:hover{
	color:<?php print $color14; ?>;
	}

.Toolbar_TDSelectAction_Bottom a{
	font-family:Arial, Helvetica, Sans-Serif;    font-size:11px;    color:#666;
	color:<?php print $color13; ?>;
	}
.Toolbar_TDSelectAction_Bottom a:hover{
	color:<?php print $color14; ?>;
	}

.Toolbar_TableBottom{
	border-color:<?php print $menu1_bgcolor; ?>;
	}

#mainnav li{	background-image: url("<?php print $bg_image_header; ?>");}
