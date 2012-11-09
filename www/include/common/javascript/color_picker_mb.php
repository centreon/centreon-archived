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
 * SVN : $URL:  $
 * SVN : $Id:  $
 *
 */

        $name = "";

        function filter_get($str){
                if (preg_match("/([a-zA-Z0-9\_\-\%\ ]*)/", $str, $matches))
                        return $matches[1];
                return NULL;
        }

        if (function_exists("filter_var")){
                $name = filter_var($_GET["name"], FILTER_SANITIZE_SPECIAL_CHARS);
        } else {
                $name = htmlentities($_GET["name"], ENT_QUOTES, "UTF-8");
        }

	$l_slice = 100;
?>
<html>
<head>
<link type="text/css" rel="stylesheet" href="./include/common/javascript/color_picker_mb.css" media="screen"/>
</head>
<body id="colpick_body">
<form name="colpick_form" action="#" method="post">
	<div id="colpick_page">
		<div id="colpick_subtitle">
			<?php echo $name; ?>
		</div>
		<div id="colpick_color">
			<img id="colpick_color_img" src="./include/common/javascript/colpick.jpg" 
				onclick="compute_color(event, <?php echo $l_slice; ?>)"
				onmousedown="cp_imd = true; return false;"
				onmouseup="cp_imd = false;"
				onmousemove="if (cp_imd && cp_imo) compute_color(event, <?php echo $l_slice; ?>); return false;"
				onmouseover="cp_imo = true;"
				onmouseout="cp_imo = false;">
		</div>
		<div id="colpick_gradiant" 
                                onclick="compute_gradiant(event)"
                                onmousedown="cp_imd = true; return false;"
                                onmouseup="cp_imd = false;"
                                onmousemove="if (cp_imd && cp_imo) compute_gradiant(event); return false;"
                                onmouseover="cp_imo = true;"
                                onmouseout="cp_imo = false;">
<?php		$l_mid = $l_slice/2;
		for ($l_i = 0; $l_i < $l_slice; $l_i++) {?>
		<div id="cg_<?php echo $l_i; ?>" class="cg_slice"></div>
<?php		}?>
		</div>
		<div id="colpick_subtitle" style="float: left;width: 49%; margin-top: 4px"><?php _("Active Color") ?></div>
		<div id="colpick_subtitle" style="float: right;width: 49%; margin-top: 4px;"><?php _("New Color") ?></div>
		<div id="colpick_acolor" style="float: left;"></div>
		<div id="colpick_ncolor" style="float: right;"></div>
		<div id="colpick_button" style="float: left;">
			<div id="colpick_cancel" style="float: right;">
				<input type="button" id="colpick_close" value="Close" onclick="Modalbox.hide();">
			</div>
		</div>
		<div id="colpick_button" style="float: right;">
			<div id="colpick_save" style="float: left;">
				<input type="button" id="colpick_submit" value="Save" onclick="exportColor();">
			</div>
		</div>
	</div>
</form>
</body>
</html>
