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

/*********************************************************************************
 * Code base on 'Script Color Picker' wrote by Frosty (Maxime Pacary) - Mai 2003 *
 *********************************************************************************/
	
	function cp_init(l_id, l_color) {
		// mouse variables
		cp_imd = false;
		cp_imo = false;

		// Write Color
		e_cpcolor = document.getElementById('colpick_ncolor');
		e_targettxtcolor = document.forms['Form'].elements[l_id];
		e_targetcolor = document.forms['Form'].elements[l_id+"_color"];
		id_target = l_id;

		// slice number of gradiant
		var l_slice = 100;

		// Update active color and gradiant bar color
		document.getElementById('colpick_acolor').style.backgroundColor = "#"+l_color;
		updateGradBarColor(hexdec(l_color.substr(0,2)),hexdec(l_color.substr(2,2)),hexdec(l_color.substr(4,2)), l_slice);
	}
	
	// conversion decimal (0-255) => hexa
	function dechex(n) {
		strhex = "0123456789ABCDEF";
		return strhex.charAt(Math.floor(n/16)) + strhex.charAt(n%16);
	}

	// Conversion hexa To decimal
	function hexdec(n) {
		return parseInt(n,16);
	}

	function compute_gradiant(l_e) {
		var tgt = l_e.target || l_e.srcElement;

		var exp = new RegExp('cg_[0-9]+','g');
		if (exp.test(tgt.id)) {
			updateColor(tgt.style.backgroundColor);
		}	
	}

	
	// détection 'clic/Mouvement' souris sur la 'palette de couleur'
	function compute_color(l_e, l_slice) {

		var evt = l_e || window.event;
		var tgt = evt.target || evt.srcElement; 

		var curleft = curtop = 0;
		if (tgt.offsetParent) {
			do {
				curleft += tgt.offsetLeft;
				curtop += tgt.offsetTop;
			} while (tgt = tgt.offsetParent);
		}
		// x : [ FF {1,300} | IE {0,299} ]
		var x = evt.clientX-curleft-1;
		var y = evt.clientY-curtop;

		if ( x < 0) {
			x = 0;
		}
	
		// calcul de la couleur � partir des coordonn�es du clic
		var part_width = document.all ? document.all.colpick_color_img.width/6 : document.getElementById('colpick_color_img').width/6;
		var im_height = document.all ? document.all.colpick_color_img.height : document.getElementById('colpick_color_img').height;
		
		
		//
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
		updateColor('#' + dechex(red) + dechex(green) + dechex(blue));
		
		// mise � jour de la barre de droite en fonction de cette couleur
		updateGradBarColor(red, green, blue, l_slice);
		
	}
	
	// pour afficher la couleur finale choisie
	function updateColor(l_color) {
		e_cpcolor.style.backgroundColor = l_color;
	}
	

	function updateGradBarColor(l_red, l_green, l_blue, l_slice) {
		if (l_red == null)
			l_red=00;
		if (l_green == null)
			l_green=00;
		if (l_blue == null)
			l_blue=00;
		var l_mid = l_slice/2;
		for(l_i = 0; l_i < l_slice; l_i++) {
			if ((l_i >= 0) && (l_i < l_mid)) {
				var l_coef = l_i/l_mid ;
				var l_redf = dechex(255 - (255 - l_red) * l_coef);
				var l_greenf = dechex(255 - (255 - l_green) * l_coef);
				var l_bluef = dechex(255 - (255 - l_blue) * l_coef);
			} else {
				var l_coef = 2 - l_i/l_mid ;
				var l_redf = dechex(l_red * l_coef);
				var l_greenf = dechex(l_green * l_coef);
				var l_bluef = dechex(l_blue * l_coef);
			}
			l_color = l_redf + l_greenf + l_bluef ;
			document.all ? document.all('cg_'+l_i).style.backgroundColor = '#'+l_color : document.getElementById('cg_'+l_i).style.backgroundColor = '#'+l_color;
		}
	}


	// "renvoie" la couleur en cliquant sur 'Save'
	function exportColor() {
		var new_color = e_cpcolor.style.backgroundColor;
		exp_rgb = new RegExp("rgb","g");
		if (exp_rgb.test(new_color)) {
			exp_extract = new RegExp("[0-9]+","g");
			var tab_rgb = new_color.match(exp_extract);
			new_color = '#'+dechex(parseInt(tab_rgb[0]))+dechex(parseInt(tab_rgb[1]))+dechex(parseInt(tab_rgb[2]));
		}
		new_color = new_color.toUpperCase();	
                e_targettxtcolor.value = new_color;
                e_targetcolor.style.backgroundColor = new_color;
		e_targetcolor.style.borderColor = new_color;
		Modalbox.hide();
	}
