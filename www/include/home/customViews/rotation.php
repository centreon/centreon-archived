<?php
/**
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
 */

if (!isset($centreon) || !isset($_REQUEST['view_id'])) {
    exit;
}

/**
 * Smarty
 */
$path = "./include/home/customViews/";
$template = new Smarty();
$template = initSmartyTpl($path, $template, "./");

/**
 * Quickform
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$rotationTimer = 0;
if (isset($_SESSION['rotation_timer'])) {
    $rotationTimer = $_SESSION['rotation_timer'];
}
$viewId = $_REQUEST['view_id'];

/**
 * Renderer
 */
$template->display("rotation.ihtml");
?>
<script type="text/javascript">
var rotationTimer = <?php echo $rotationTimer;?>;
var viewId = <?php echo $viewId;?>;

jQuery(function()
{
	jQuery("#rotation_timer").slider({
										value	: rotationTimer,
										min		: 0,
										max		: 300,
										step	: 5,
										slide	: function(event, ui) {
													jQuery("#timer_value").html(ui.value + " seconds");
										},
										stop	: function(event, ui) {
													setTimerLabel();
												}
									 });
	jQuery("input[type=button]").button();
	setTimerLabel();
});

function setTimerLabel()
{
	var val = jQuery("#rotation_timer").slider("value");

	jQuery("#timer_value").html(val + " seconds");
}

function submitData()
{
	jQuery.ajax({
			type	:	"POST",
			dataType:	"xml",
			url 	:	"./include/home/customViews/action.php",
			data	:   {
							action:	"setRotate",
							timer:	jQuery("#rotation_timer").slider("value")
						},
			success :	function(response) {
							var view = response.getElementsByTagName('custom_view_id');
							var error = response.getElementsByTagName('error');
							if (typeof(view) != 'undefined') {
								window.top.location = './main.php?p=103&currentView='+viewId;
							} else if (typeof(err) != 'undefined') {
								var errorMsg = err.item(0).firstChild.data;
								console.log(errorMsg);
							}
						}
	});
}
</script>