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
require_once $centreon_path . "www/class/centreonContactgroup.class.php";

$db = new CentreonDB();
$viewObj = new CentreonCustomView($centreon, $db);
$widgetObj = new CentreonWidget($centreon, $db);
$cgObj = new CentreonContactgroup($db);
$title = "";
$defaultTab = array();
if (isset($_REQUEST['view_id']) && $_REQUEST['view_id']) {
    $viewId = $_REQUEST['view_id'];
    $title = _("Share view");
    $action = "share";
    $defaultTab['custom_view_id'] = $viewId;
} else {
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
 * Locked
 */
$layouts[] = HTML_QuickForm::createElement('radio', 'locked', null, _("Yes"), '1');
$layouts[] = HTML_QuickForm::createElement('radio', 'locked', null, _("No"), '0');
$form->addGroup($layouts, 'locked', _("Locked?"), '&nbsp;');
$form->setDefaults(array('locked' => '1'));

/**
 * Get viewers
 */
$viewers = $viewObj->getUsersFromViewId($viewId);
$viewerGroups = $viewObj->getUsergroupsFromViewId($viewId);

/**
 * Users
 */
$userList = array_diff_key($centreon->user->getUserList(), $viewers);
$ams1 = $form->addElement('advmultiselect', 'user_id', array(_("User List"), _("Available"), _("Selected")), $userList, $attrsAdvSelect);
$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);

/**
 * User groups
 */
$userGroupList = array_diff_key($cgObj->getListContactgroup(true), $viewerGroups);
$ams1 = $form->addElement('advmultiselect', 'usergroup_id', array(_("User Group List"), _("Available"), _("Selected")), $userGroupList, $attrsAdvSelect);
$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);


/**
 * Submit button
 */
$form->addElement('button', 'submit', _("Share"), array("onClick" => "submitData();"));
$form->addElement('reset', 'reset', _("Reset"));
$form->addElement('hidden', 'action');
$form->setDefaults(array('action' => $action));
$form->addElement('hidden', 'custom_view_id');
$form->setDefaults($defaultTab);

/**
 * Assign
 */
if (isset($viewers[$centreon->user->user_id])) {
    unset($viewers[$centreon->user->user_id]);
}
$template->assign('viewerLabel', _('Viewers'));
$template->assign('viewergroupLabel', _('Viewer Groups'));
$template->assign('viewers', $viewers);
$template->assign('viewerGroups', $viewerGroups);

/**
 * Renderer
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$template->assign('form', $renderer->toArray());
$template->display("shareView.ihtml");
?>
<script type="text/javascript">
jQuery(function()
{
	jQuery(".removeUser").click(function(event) {
		var substr = event.target.id.split("rmUser_");
		if (typeof(substr[1]) != 'undefined') {
			if (confirm('Remove user from viewer list?')) {
				removeUserFromView(substr[1]);
			}
		}
	});
	jQuery(".removeUsergroup").click(function(event) {
		var substr = event.target.id.split("rmUsergroup_");
		if (typeof(substr[1]) != 'undefined') {
			if (confirm('Remove user group from viewer list?')) {
				removeUsergroupFromView(substr[1]);
			}
		}
	});
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
								parent.jQuery.colorbox.close();
							} else if (typeof(error) != 'undefined') {
								var errorMsg = error.item(0).firstChild.data;
							}
						}
	});
}

function removeUserFromView(user_id)
{
	jQuery.ajax({
			type	:	"POST",
			dataType:	"xml",
			url		: 	"./include/home/customViews/action.php",
			data	:	{
							action				:	"remove",
							custom_view_id		:	jQuery('[name=custom_view_id]').val(),
							user_id				:	user_id
						},
			success	:	function(response) {
							var view = response.getElementsByTagName('custom_view_id');
							var contact_name = response.getElementsByTagName('contact_name');
							var error = response.getElementsByTagName('error');
							if (typeof(view) != 'undefined') {
								jQuery('#viewer_' + user_id).hide();
								var mselbox = document.getElementById("user_id-f");
								var newElem = new Option(contact_name.item(0).firstChild.data, user_id);
								mselbox.options[(mselbox.options.length)] = newElem;
								mselbox.removeAttribute('disabled');
							} else if (typeof(error) != 'undefined') {
								var errorMsg = error.item(0).firstChild.data;
							}
						}
	});
}

function removeUsergroupFromView(usergroup_id)
{
	jQuery.ajax({
			type	:	"POST",
			dataType:	"xml",
			url		: 	"./include/home/customViews/action.php",
			data	:	{
							action				:	"removegroup",
							custom_view_id		:	jQuery('[name=custom_view_id]').val(),
							usergroup_id		:	usergroup_id
						},
			success	:	function(response) {
							var view = response.getElementsByTagName('custom_view_id');
							var contact_name = response.getElementsByTagName('contact_name');
							var error = response.getElementsByTagName('error');
							if (typeof(view) != 'undefined') {
								jQuery('#viewergroup_' + usergroup_id).hide();
								var mselbox = document.getElementById("usergroup_id-f");
								var newElem = new Option(contact_name.item(0).firstChild.data, usergroup_id);
								mselbox.options[(mselbox.options.length)] = newElem;
								mselbox.removeAttribute('disabled');
							} else if (typeof(error) != 'undefined') {
								var errorMsg = error.item(0).firstChild.data;
							}
						}
	});
}
</script>