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
$action = null;
$defaultTab = array();
if ($_REQUEST['action'] == "add") {
    $title = _("Add a new view");
    $action = "add";
} elseif ($_REQUEST['action'] == "edit" && isset($_REQUEST['view_id']) && $_REQUEST['view_id']) {
    $viewId = $_REQUEST['view_id'];
    $title = _("Edit view");
    $action = "edit";
    $defaultTab['custom_view_id'] = $viewId;
    $views = $viewObj->getCustomViews();
    if (isset($views[$viewId])) {
        $defaultTab['name'] = $views[$viewId]['name'];
        $defaultTab['layout']['layout'] = $views[$viewId]['layout'];
        $defWidgets = $widgetObj->getWidgetsFromViewId($viewId);
        $tmp = array();
        foreach ($defWidgets as $widgetId => $tmpTab) {
            $defaultTab['widget_id'][] = $widgetId;
        }
    }
}

if (!isset($action)) {
    echo _("No action");
    exit;
}

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
$form->addElement('header', 'title', $title);
$form->addElement('header', 'information', _("General Information"));
/**
 * Name
 */
$form->addElement('text', 'name', _("View name"), $attrsText);

/**
 * Layout
 */
$layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("1 Column"), 'column_1');
$layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("2 Columns"), 'column_2');
$layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("3 Columns"), 'column_3');
$form->addGroup($layouts, 'layout', _("Layout"), '&nbsp;');
if ($action == "add") {
    $form->setDefaults(array('layout[layout]' => 'column_1'));
}

/**
 * Submit button
 */
$form->addElement('button', 'submit', _("Submit"), array("onClick" => "submitData();"));
$form->addElement('reset', 'reset', _("Reset"));
$form->addElement('hidden', 'action');
$form->setDefaults(array('action' => $action));

if ($action == "edit") {
    $form->addElement('hidden', 'custom_view_id');
    $form->setDefaults($defaultTab);
}

/**
 * Renderer
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$template->assign('form', $renderer->toArray());
$template->display("form.ihtml");
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
							if (typeof(view) != 'undefined') {
								var viewId = view.item(0).firstChild.data;
								window.top.location = './main.php?p=103&currentView='+viewId;
							} else if (typeof(error) != 'undefined') {
								var errorMsg = error.item(0).firstChild.data;
							}
						}
	});
}
</script>