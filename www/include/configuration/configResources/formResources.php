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

    if (!$oreon->user->admin && isset($resource_id)
        && count($allowedResourceConf) && !isset($allowedResourceConf[$resource_id])) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icones/16x16/warning.gif");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this object configuration'));
        return null;
    }

    $initialValues = array();

    $instances = $acl->getPollerAclConf(array('fields' => array('id', 'name'),
                                              'keys'   => array('id'),
                                              'get_row' => 'name',
                                              'order'   => array('name')));

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
            if (!$oreon->user->admin
                && $pollerString != "''"
                && false === strpos($pollerString, "'".$row['instance_id']."'")) {
                $initialValues['instance_id'][] = $row['instance_id'];
            }
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

    $init = $form->addElement('hidden', 'initialValues');
    $init->setValue(serialize($initialValues));

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
	if ($valid && $action["action"])
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
