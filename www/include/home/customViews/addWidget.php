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

if (!isset($centreon)) {
    exit;
}

require_once $centreon_path . "www/class/centreonCustomView.class.php";
require_once $centreon_path . "www/class/centreonWidget.class.php";

$db = new CentreonDB();
$viewObj = new CentreonCustomView($centreon, $db);
$widgetObj = new CentreonWidget($centreon, $db);
$title = "";
$action = "addWidget";
$title = _("Add a new widget");
$viewId = $_REQUEST['view_id'];

/**
 * Smarty
 */
$path = "./include/home/customViews/";
$template = new Smarty();
$template = initSmartyTpl($path, $template, "./");

/**
 * Field templates
 */
$attrsText 		= array("size"=>"30");
$attrsAdvSelect = array("style" => "width: 200px; height: 150px;");
$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

/**
 * Quickform
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$form = new HTML_QuickForm('Form', 'post', "?p=103");

$form->addElement('header', 'w_title', $title);
$form->addElement('header', 'title', $title);
$form->addElement('header', 'information', _("Widget Information"));

/**
 * Name
 */
$form->addElement('text', 'widget_title', _("Widget Title"), $attrsText);

/**
 * Widgets
 */
$widgetList = $widgetObj->getWidgetModels();
$widgetModels = array();
foreach ($widgetList as $widgetModelId => $widgetModelName) {
    $widgetModels[$widgetModelId] = $widgetObj->getWidgetInfoById($widgetModelId);
}

/**
 * Submit button
 */
$form->addElement('button', 'submit', _("Submit"), array("onClick" => "submitData();"));
$form->addElement('reset', 'reset', _("Reset"));
$form->addElement('hidden', 'action');
$form->addElement('hidden', 'custom_view_id');
$form->setDefaults(array('action'         => $action,
                         'custom_view_id' => $viewId));

/**
 * Renderer
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$template->assign('widgetModels', $widgetModels);
$template->assign('form', $renderer->toArray());
$template->display("addWidget.ihtml");
?>
<script type="text/javascript">
jQuery(function()
{
	jQuery("input[type=text]").keypress(function(e) {
		var code = null;
		code =  (e.keyCode ? e.keyCode : e.which);
		return (code == 13) ? false : true;
	} );
});

function submitData()
{
	jQuery.ajax({
			type	:	"POST",
			dataType:	"xml",
			url 	:	"./include/home/customViews/action.php",
			data	:   jQuery("#Form").serialize(),
			success :	function(response) {
							var view = response.getElementsByTagName('custom_view_id');
							var error = response.getElementsByTagName('error');
							if (view !== undefined) {
								var viewId = view.item(0).firstChild.data;
								window.top.location = './main.php?p=103&currentView='+viewId;
							} else if (err !== undefined) {
								var errorMsg = err.item(0).firstChild.data;
								console.log(errorMsg);
							}
						}
	});
}
</script>