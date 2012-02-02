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

	/**
	 * Database retrieve information for Resources CFG
	 */
	if (($o == "c" || $o == "w") && $resource_id)	{
		$DBRESULT = $pearDB->query("SELECT * FROM cfg_resource WHERE resource_id = '".$pearDB->escape($resource_id)."' LIMIT 1");
		// Set base value
		$rs = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();

		$rs['instance_id'] = array();
		$res = $pearDB->query("SELECT instance_id
							   FROM cfg_resource_instance_relations
							   WHERE resource_id = " . $pearDB->escape($resource_id));
		while ($row = $res->fetchRow()) {
		    $rs['instance_id'][] = $row['instance_id'];
		}
	}


	/**
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$attrsAdvSelect = array("style" => "width: 220px; height: 220px;");
    $eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	require_once $centreon_path . "www/class/centreonInstance.class.php";

	/**
	 * Form
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Resource"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Resource"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View Resource"));


	/**
	 * Resources CFG basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'resource_name', _("Resource Name"), $attrsText);
	$form->addElement('text', 'resource_line', _("MACRO Expression"), $attrsText);

	$instanceObj = new CentreonInstance($pearDB);
	$instances = $instanceObj->getInstances();
	$ams1 = $form->addElement('advmultiselect', 'instance_id', array(_("Linked Instances"), _("Available"), _("Selected")), $instances, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	/**
	 * Further information
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$rsActivation[] = HTML_QuickForm::createElement('radio', 'resource_activate', null, _("Enabled"), '1');
	$rsActivation[] = HTML_QuickForm::createElement('radio', 'resource_activate', null, _("Disabled"), '0');
	$form->addGroup($rsActivation, 'resource_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('resource_activate' => '1'));
	$form->addElement('textarea', 'resource_comment', _("Comment"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'resource_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/**
	 * Form definition
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["resource_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('resource_name', 'myReplace');
	$form->addRule('resource_name', _("Compulsory Name"), 'required');
	$form->addRule('resource_line', _("Compulsory Alias"), 'required');
	$form->addRule('instance_id', _("Compulsory Instance"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('resource_name', _("Resource used by one of the instances"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

	// Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	// Just watch a Resources CFG information
	if ($o == "w")	{
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$resource_id."'"));
	    $form->setDefaults($rs);
		$form->freeze();
	}
	// Modify a Resources CFG information
	else if ($o == "c")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($rs);
	}
	// Add a Resources CFG information
	else if ($o == "a")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate()) {
		$rsObj = $form->getElement('resource_id');
		if ($form->getSubmitValue("submitA"))
			$rsObj->setValue(insertResourceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateResourceInDB($rsObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$rsObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listResources.php");
	else	{
		// Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formResources.ihtml");
	}
?>