<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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