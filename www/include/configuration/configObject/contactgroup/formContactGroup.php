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

 	if (!isset($oreon))
 		exit();

        if (!$oreon->user->admin && $cg_id) {
            $aclOptions = array('fields'     => array('cg_id','cg_name'),
                                'keys'       => array('cg_id'),
                                'get_row'    => 'cg_name',
                                'conditions' => array('cg_id' => $cg_id));
            $cgs = $acl->getContactGroupAclConf($aclOptions);
            if (!count($cgs)) {
                $msg = new CentreonMsg();
                $msg->setImage("./img/icones/16x16/warning.gif");
                $msg->setTextStyle("bold");
                $msg->setText(_('You are not allowed to access this contact group'));
                return null;
            }
        }
        
	/*
	 * Form Rules
	 */
	/*function myReplace() {
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["cg_name"]));
	}*/

        $initialValues = array();
        
	/*
	 * Database retrieve information for Contact
	 */
	$cg = array();
	if (($o == "c" || $o == "w") && $cg_id)	{
		/*
		 * Get host Group information
		 */
		$DBRESULT = $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");

		/*
		 * Set base value
		 */
		$cg = array_map("myDecode", $DBRESULT->fetchRow());

		/*
		 * Set Contact Childs
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT `contact_contact_id` FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'");
		for ($i = 0; $contacts = $DBRESULT->fetchRow(); $i++) {
                    if (!$oreon->user->admin && !isset($allowedContacts[$contacts['contact_contact_id']])) {
                        $initialValues['cg_contacts'][] = $contacts["contact_contact_id"];
                    } else {
                        $cg["cg_contacts"][$i] = $contacts["contact_contact_id"];
                    }
                }
		$DBRESULT->free();

		/*
		 * Get acl group
		 */
		$sql = "SELECT acl_group_id 
			FROM acl_group_contactgroups_relations 
			WHERE cg_cg_id = " .$pearDB->escape($cg_id);
		$res = $pearDB->query($sql);
		for ($i = 0; $aclgroup = $res->fetchRow(); $i++) {
        	if (!$oreon->user->admin && !isset($allowedAclGroups[$aclgroup['acl_group_id']])) {
            	$initialValues['cg_acl_groups'][] = $aclgroup["acl_group_id"];
            } else {
                $cg["cg_acl_groups"][$i] = $aclgroup["acl_group_id"];
            }
		}
	}

	/*
	 * Contacts comes from DB -> Store in $contacts Array
	 */
	$contacts = array();
	$DBRESULT = $pearDB->query("SELECT DISTINCT `contact_id`, `contact_name`, `contact_register` 
                                    FROM `contact` ".
                                   $acl->queryBuilder('WHERE', 'contact_id', $contactstring).
                                   " ORDER BY `contact_name`");
	while ($contact = $DBRESULT->fetchRow()) {
		$contacts[$contact["contact_id"]] = $contact["contact_name"];
		if ($contact['contact_register'] == 0) {
		    $contacts[$contact["contact_id"]] .= "(Template)";
		}
	}
	unset($contact);
	$DBRESULT->free();

	$aclgroups = array();
	$aclCondition = "";
	if (!$oreon->user->admin) {
		$aclCondition = " WHERE acl_group_id IN (".$acl->getAccessGroupsString().") ";
	}
	$sql = "SELECT acl_group_id, acl_group_name
		FROM acl_groups
		{$aclCondition}
		ORDER BY acl_group_name";
	$res = $pearDB->query($sql);
	while ($aclg = $res->fetchRow()) {
		$aclgroups[$aclg['acl_group_id']] = $aclg['acl_group_name'];
	}

	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"60");
	$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Contact Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Contact Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Contact Group"));

	/*
	 * Contact basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'cg_name', _("Contact Group Name"), $attrsText);
	$form->addElement('text', 'cg_alias', _("Alias"), $attrsText);

	/*
	 * Contacts Selection
	 */
	$form->addElement('header', 'notification', _("Relations"));

	$ams1 = $form->addElement('advmultiselect', 'cg_contacts', array(_("Linked Contacts"), _("Available"), _("Selected")), $contacts, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);


	/*
     * Acl group selection
	 */
	$ams1 = $form->addElement('advmultiselect', 'cg_acl_groups', array(_("Linked ACL groups"), _("Available"), _("Selected")), $aclgroups, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);


	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$cgActivation[] = HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Enabled"), '1');
	$cgActivation[] = HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Disabled"), '0');
	$form->addGroup($cgActivation, 'cg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('cg_activate' => '1'));
	$form->addElement('textarea', 'cg_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'cg_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

        $init = $form->addElement('hidden', 'initialValues');
        $init->setValue(serialize($initialValues));
        
	/*
	 * Set rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	//$form->applyFilter('cg_name', 'myReplace');
	$form->addRule('cg_name', _("Compulsory Name"), 'required');
	$form->addRule('cg_alias', _("Compulsory Alias"), 'required');

	if(!$oreon->user->admin) {
		$form->addRule('cg_acl_groups', _('Compulsory field'), 'required');
	}

	$form->registerRule('exist', 'callback', 'testContactGroupExistence');
	$form->addRule('cg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	if ($o == "w")	{
		/*
		 * Just watch a Contact Group information
		 */
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
	    $form->setDefaults($cg);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a Contact Group information
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cg);
	} else if ($o == "a")	{
		/*
		 * Add a Contact Group information
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{

		$cgObj = $form->getElement('cg_id');

		if ($form->getSubmitValue("submitA"))
			$cgObj->setValue(insertContactGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateContactGroupInDB($cgObj->getValue());

		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]) {
		require_once($path."listContactGroup.php");
	} else {
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);

		$tpl->display("formContactGroup.ihtml");
	}
?>
