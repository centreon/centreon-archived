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
//#outer	{		border-left-color:<?=$color1?>;   /* left column colour */}
//body	{		background-color:<?=$color1?>;}

.ListTable a:link, #ListTable a:link			{color:<?=$color2?>;}
.ListTable a:visited, #ListTable a:visited		{color:<?=$color2?>;}
.ListTable a:hover, #ListTable a:hover			{color:<?=$color3?>;}

/* Form */
.list_lvl_1{	background-color:<?=$color4?>;}
.list_lvl_2{	background-color:<?=$color5?>;}
.ListHeader{	background-image:url(<?=$bg_image_header?>);
	background-position:top left;
	background-repeat:repeat-x;
	background-color:<?=$color6?> ;
}

.ListSubHeader{	
	background-color:#A9C5F2;
	font-weight:bold;
	background-position:top left;
	}

	
.ListTable, .ListTableMedium, .ListTableSmall {
	border-color: <?=$color6?>;
	}

.list_lvl_2 td, .list_lvl_1 td{
	border-top-color: <?=$color7?>;
	border-left-color: <?=$color7?>;
	border-bottom-color: <?=$color7?>;
	border-right-color: <?=$color7?>;
	}

.list_one td, .list_two td, .list_three td, .list_four td, .list_up td, .list_down td{
	border-top-color: <?=$color8?>;
	border-left-color: <?=$color8?>;
	border-bottom-color: <?=$color8?>;
	border-right-color: <?=$color8?>;
	}

.tab .list_one td, .tab .list_two td, .tab .list_three td, .tab .list_four td, .tab .list_up td, .tab .list_down td{
	border-top-color: <?=$color8?>;
	border-left-color: <?=$color8?>;
	border-bottom-color: <?=$color8?>;
	border-right-color: <?=$color8?>;
	}
	
.tab .list_lvl_2 td, .tab .list_lvl_1 td{
	border-top-color: <?=$color7?>;
	border-left-color: <?=$color7?>;
	border-bottom-color: <?=$color7?>;
	border-right-color: <?=$color7?>;
	}
	
.ListHeader td{
	border-top-color: <?=$color9?>;
	border-left-color: <?=$color9?>;
	}

.ListColFooterRight, .ListColFooterLeft, .ListColFooterCenter{
	border-top-color: <?=$color10?>;
	}

.list_one_fixe	{		
	background-color:<?=$color_list_1?>;
	} 
.list_two_fixe {
	background-color:<?=$color_list_2?>; 
	}


.list_one			{	background-color:<?=$color_list_1?>;} 
.list_one:hover 	{	background-color:<?=$color_list_1_hover?>;}

.list_two 			{	background-color:<?=$color_list_2?>; }
.list_two:hover 	{	background-color:<?=$color_list_2_hover?>;}

.list_three 		{	background-color:<?=$color_list_3?>;}
.list_three:hover 	{	background-color:<?=$color_list_3_hover?>;}

.list_four 			{	background-color:<?=$color_list_4?>;}
.list_four:hover 	{	background-color:<?=$color_list_4_hover?>;}

.list_up			{	background-color:<?=$color_list_up?>;}
.list_up:hover		{	background-color:<?=$color_list_up_hover?>;}

.list_down 			{	background-color:<?=$color_list_down?>;}
.list_down:hover 	{	background-color:<?=$color_list_down_hover?>;}

//#page {			color:<?=$color11?>;}
//#page a{		color:<?=$color11?>;}

/*Menu*/
#menu1_bgimg	{	
	background-image: url(<?=$menu1_bgimg?>);
	background-position:top right;
	background-repeat:repeat-x;
	}
#menu1_bgcolor	{	background-color: <?=$menu1_bgcolor?>;}
#menu2_bgcolor	{	background-color: <?=$menu2_bgcolor?>;}


#menu_2			{	background-color:	<?=$menu1_bgcolor?>;}

.Tmenu3 .top .left 		{	background-color:  <?=$color1?>;}
.Tmenu3 .top .right 	{	background-color:  <?=$color1?>;}
.Tmenu3 .bottom .left 	{	background-color:  <?=$color1?>;}
.Tmenu3 .bottom .right 	{	background-color:  <?=$color1?>;}

/* General */
#contener{		/*background-color:<?=$color1?>;*/}

#Tmenu{		/*background-color:<?=$color1?>*/;
		border-right: 1px solid <?=$menu1_bgcolor?>;
		}

#footerline1	{	background-color:<?=$menu1_bgcolor?>;}
#footerline2	{	background-color:<?=$footerline2?>;}

input, textarea {	font-size: 10px; border:1px solid #BBBBBB;}

input[type="submit"],input[type="button"],input[type="reset"]{
	background : white;	
	color : <?=$menu1_bgcolor?>;
	border-color : <?=$menu1_bgcolor?>;
	}

input[type="submit"]:hover,input[type="button"]:hover,input[type="reset"]:hover{
	background : <?=$menu1_bgcolor?>;
	color : <?=$color9?>;
	border-color : <?=$menu1_bgcolor?>;
	}

.limitPage{
	background-image:url(<?=$bg_image_header?>);
	color:<?=$color9?>;
	background-position:top left;
	background-repeat:repeat-x;
	background-color:<?=$color6?> ;
	}

.pageNumber{
	background-image:url(<?=$bg_image_header?>);
	color:<?=$color9?>;
	background-position:top left;
	background-repeat:repeat-x;
	background-color:<?=$color6?> ;
	}

.a, .b{
	border-color:<?=$color10?>;
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
	color:<?=$color13?>;
	}

.Toolbar_TDSelectAction_Top a:hover{
	color:<?=$color14?>;
	}

.Toolbar_TDSelectAction_Bottom a{
	font-family:Arial, Helvetica, Sans-Serif;    font-size:11px;    color:#666;
	color:<?=$color13?>;
	}
.Toolbar_TDSelectAction_Bottom a:hover{
	color:<?=$color14?>;
	}

.Toolbar_TableBottom{
	border-color:<?=$menu1_bgcolor?>;
	}

#mainnav li{	background-image: url("<?=$bg_image_header?>");}
