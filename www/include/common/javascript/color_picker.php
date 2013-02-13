<?php
/*
 * Copyright 2005-2011 MERETHIS
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
  
	$n = "";
	$name = "";
	$title = "";
	$hcolor = "000000";
	
	function filter_get($str){
		if (preg_match("/([a-zA-Z0-9\_\-\%\ ]*)/", $str, $matches))
			return $matches[1];
		return NULL;	
	}
	
	if (function_exists("filter_var")){
		$n = filter_var($_GET["n"], FILTER_SANITIZE_SPECIAL_CHARS);
		$name = filter_var($_GET["name"], FILTER_SANITIZE_SPECIAL_CHARS);
		$title = filter_var($_GET["title"], FILTER_SANITIZE_SPECIAL_CHARS);
		if (isset($_GET["hcolor"]))
			$hcolor = filter_var($_GET["hcolor"], FILTER_SANITIZE_SPECIAL_CHARS);
	} else {
		$n = filter_get($_GET["n"]);
		$name = filter_get($_GET["name"]);
		$title = filter_get($_GET["title"]);
		if (isset($_GET["hcolor"]))
			$hcolor = filter_get($_GET["hcolor"]);
	}
	$name1 = $n."";
	$name2 = $n."_color";

?>
<html>
<head>
<title>Color Picker</title>
<style type="text/css">
body	{ font-size: 12px; font-family: Verdana, Sans-Serif; text-align:center; background-color:#FFFFFF; color:navy;}
td  { font-size: 12px; font-family: Verdana, Sans-Serif; text-align:center; background-color:#FFFFFF}
.table_black_border {border-style:solid; border-width:1px; border-color:#000000;}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
	
// D�pos� par Frosty sur www.toutjavascript.com
// 27/5/2003 - Ajout compatibilit� IE5 sur MacOS
// 5/6/2003  - Ajout compatibilit� Mozilla
// 5/9/2005  - Correction d'un bug (clic sur la bordure de la palette principale)
// 6/9/2005  - Ajout de la possibilit� de s�lectionner une couleur en d�pla�ant la souris
//             sur les palettes (bouton gauche enfonc�)

/*****************************************************************
* Script Color Picker �crit par Frosty (Maxime Pacary) - Mai 2003
******************************************************************/
	
	// var. globale
	var detail = 50; // nombre de nuances de couleurs dans la barre de droite
	
	// ne pas modifier
	var strhex = "0123456789ABCDEF";
	var i;
	var is_mouse_down = false;
	var is_mouse_over = false;
	
	// conversion decimal (0-255) => hexa
	function dechex(n) {
		return strhex.charAt(Math.floor(n/16)) + strhex.charAt(n%16);
	}
	
	// d�tection d'un clic/mouvement souris sur la "palette" (� gauche)
	function compute_color(e)
	{
		x = e.offsetX ? e.offsetX : (e.target ? e.clientX-e.target.x : 0);
		y = e.offsetY ? e.offsetY : (e.target ? e.clientY-e.target.y : 0);
		
		// calcul de la couleur � partir des coordonn�es du clic
		var part_width = document.all ? document.all.color_picker.width/6 : document.getElementById('color_picker').width/6;
		var im_height = document.all ? document.all.color_picker.height : document.getElementById('color_picker').height;
		
		
		var red = (x >= 0)*(x < part_width)*255
				+ (x >= part_width)*(x < 2*part_width)*(2*255 - x * 255 / part_width)
				+ (x >= 4*part_width)*(x < 5*part_width)*(-4*255 + x * 255 / part_width)
				+ (x >= 5*part_width)*(x < 6*part_width)*255;
		var blue = (x >= 2*part_width)*(x < 3*part_width)*(-2*255 + x * 255 / part_width)
				+ (x >= 3*part_width)*(x < 5*part_width)*255
				+ (x >= 5*part_width)*(x < 6*part_width)*(6*255 - x * 255 / part_width);
		var green = (x >= 0)*(x < part_width)*(x * 255 / part_width)
				+ (x >= part_width)*(x < 3*part_width)*255
				+ (x >= 3*part_width)*(x < 4*part_width)*(4*255 - x * 255 / part_width);
		
		var coef = (im_height-y)/im_height;
		
		// composantes de la couleur choisie sur la "palette"
		red = 128+(red-128)*coef;
		green = 128+(green-128)*coef;
		blue = 128+(blue-128)*coef;
		
		// mise � jour de la couleur finale
		changeFinalColor('#' + dechex(red) + dechex(green) + dechex(blue));
		
		// mise � jour de la barre de droite en fonction de cette couleur
		UpdateGradBarColor(red, green, blue);
		
	}
	
	// pour afficher la couleur finale choisie
	function changeFinalColor(color)
	{
		document.forms['colpick_form'].elements['btn_choose_color'].style.backgroundColor = color;
		document.forms['colpick_form'].elements['btn_choose_color'].style.borderColor = color;
	}
	

	function UpdateGradBarColor(red, green, blue) {
		if (red == null)
			red=00;
		if (green == null)
			green=00;
		if (blue == null)
			blue=00;
		var part_detail = detail/2;
		for(i = 0; i < detail; i++) {
			if ((i >= 0) && (i < part_detail)) {
				var final_coef = i/part_detail ;
				var final_red = dechex(255 - (255 - red) * final_coef);
				var final_green = dechex(255 - (255 - green) * final_coef);
				var final_blue = dechex(255 - (255 - blue) * final_coef);
			} else {
				var final_coef = 2 - i/part_detail ;
				var final_red = dechex(red * final_coef);
				var final_green = dechex(green * final_coef);
				var final_blue = dechex(blue * final_coef);
			}
			color = final_red + final_green + final_blue ;
			document.all ? document.all('gs'+i).style.backgroundColor = '#'+color : document.getElementById('gs'+i).style.backgroundColor = '#'+color;
		}
	}


	// "renvoyer" la couleur en cliquant sur OK
	function send_color()
	{
		if (window.opener)
		{
		   var new_color = document.forms['colpick_form'].elements['btn_choose_color'].style.backgroundColor;
		   exp_rgb = new RegExp("rgb","g");
		   if (exp_rgb.test(new_color))
		   {
		   	exp_extract = new RegExp("[0-9]+","g");
		   	var tab_rgb = new_color.match(exp_extract);
		   	
		      new_color = '#'+dechex(parseInt(tab_rgb[0]))+dechex(parseInt(tab_rgb[1]))+dechex(parseInt(tab_rgb[2]));
		   }
	
	        window.opener.document.forms['Form'].elements['<?php echo $name1; ?>'].value = new_color;
		     window.opener.document.forms['Form'].elements['<?php echo $name2;?>'].style.borderColor = new_color;
		   window.opener.document.forms['Form'].elements['<?php echo $name2; ?>'].style.backgroundColor = new_color;
			window.opener.focus();
			window.close();
		}
	}
	
	window.focus();

</script>
</head>
<body>
   <form name="colpick_form" action="#" method="post">
	<h2><?php echo $title; ?></h2>
	<h3><?php echo $name; ?></h3>
	<table border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td>
				<table border="1" cellspacing="0" cellpadding="0" class="table_black_border">
					<tr>
						<td style="padding:0px; border-width:0px; border-style:none;">
							<img id="color_picker" src="colpick.jpg" onclick="compute_color(event)"
							   onmousedown="is_mouse_down = true; return false;"
							   onmouseup="is_mouse_down = false;"
							   onmousemove="if (is_mouse_down && is_mouse_over) compute_color(event); return false;"
							   onmouseover="is_mouse_over = true;"
							   onmouseout="is_mouse_over = false;"
                        style="cursor:crosshair;" /></td>

						</td>
					</tr>
				</table>
			</td>
			<td style="background-color:#ffffff; width:20px; height:2px; padding:0px;"></td>
			<td>
				<table border="1" cellspacing="0" cellpadding="0" class="table_black_border" style="cursor:crosshair">
					<script type="text/javascript">
						for(i = 0; i < detail; i++)
						{
							document.write('<tr><td id="gs'+i+'" style="background-color:#000000; width:20px; height:3px; border-style:none; border-width:0px;"'
                        + ' onclick="changeFinalColor(this.style.backgroundColor)"'
                        + ' onmousedown="is_mouse_down = true; return false;"'
                        + ' onmouseup="is_mouse_down = false;"'
                        + ' onmousemove="if (is_mouse_down && is_mouse_over) changeFinalColor(this.style.backgroundColor); return false;"'
                        + ' onmouseover="is_mouse_over = true;"'
				   + ' onmouseout="is_mouse_over = false;"'
                        
                        + '></td></tr>');
						}
					</script>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<table align="center">
		<tr valign="center">
			<td><input type="button" name="btn_choose_color" value="&nbsp;" style="background-color:#000000; border-color:#000000; width:100px; height:35px;"></td>
			<td><input type="button" name="btn_ok" value="Ok" style="width:70px" onclick="send_color();"></td>
		</tr>
	</table>
	</form>
</body>
<script type="text/javascript">
	changeFinalColor('#<?php echo $hcolor;?>');
	UpdateGradBarColor('<?php echo hexdec(substr($hcolor,0,2));?>','<?php echo hexdec(substr($hcolor,2,2));?>','<?php echo hexdec(substr($hcolor,4,2));?>');
</script>
</html>
