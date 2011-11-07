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