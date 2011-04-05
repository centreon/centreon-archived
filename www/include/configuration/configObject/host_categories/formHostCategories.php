<?php
/*
 * Copyright 2005-2009 MERETHIS
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

 	if (!isset($oreon))
 		exit();

	/*
	 * Hosts comes from DB -> Store in $hosts Array
	 */
	$hosts = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow())
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	unset($host);

	/*
	 * Hosts comes from DB -> Store in $hosts Array
	 */
	$hostTpl = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow())
		$hostTpl[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	unset($host);

	/*
	 * Database retrieve information for HostCategories
	 */
	$hc = array();
	if (($o == "c" || $o == "w") && $hc_id)	{
		$DBRESULT = $pearDB->query("SELECT * FROM hostcategories WHERE hc_id = '".$hc_id."' LIMIT 1");
		/*
		 * Set base value
		 */
		$hc = array_map("myDecode", $DBRESULT->fetchRow());

		/*
		 *  Set hostcategories Childs => Hosts
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT host_host_id FROM hostcategories_relation WHERE hostcategories_hc_id = '".$hc_id."'");
		for ($i = 0, $i2 = 0; $host = $DBRESULT->fetchRow();) {
			if (isset($hosts[$host["host_host_id"]])) {
				$hc["hc_hosts"][$i] = $host["host_host_id"];
				$i++;
			}
			if (isset($hostTpl[$host["host_host_id"]])) {
				$hc["hc_hostsTemplate"][$i2] = $host["host_host_id"];
				$i2++;
			}
		}
		$DBRESULT->free();
		unset($host);
	}

	/*
	 * hostcategories comes from DB -> Store in $hosts Array
	 */
	$EDITCOND = "";
	if ($o == "w" || $o == "c")
		$EDITCOND = " WHERE `hc_id` != '".$hc_id."' ";

	$hostCategories = array();
	$DBRESULT = $pearDB->query("SELECT hc_id, hc_name FROM hostcategories $EDITCOND ORDER BY hc_name");
	while ($hcs = $DBRESULT->fetchRow())
		$hostGroups[$hcs["hc_id"]] = $hcs["hc_name"];
	$DBRESULT->free();
	unset($hcs);

	/*
	 * Contact Groups comes from DB -> Store in $cgs Array
	 */
	$cgs = array();
	$DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while ($cg = $DBRESULT->fetchRow())
		$cgs[$cg["cg_id"]] = $cg["cg_name"];
	$DBRESULT->free();
	unset($cg);

	/*
	 * IMG comes from DB -> Store in $extImg Array
	 */
	$extImg = array();
	$extImg = return_image_list(1);
	$extImgStatusmap = array();
	$extImgStatusmap = return_image_list(2);

	/*
	 * Define Templatse
	 */
	$attrsText 		= array("size"=>"30");
	$attrsTextLong 	= array("size"=>"50");
	$attrsAdvSelect = array("style" => "width: 220px; height: 220px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Create formulary
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a host category"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a  host category"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a  host category"));

	/*
	 * Catrgorie basic information
	 */
	$form->addElement('header', 	'information', _("General Information"));
	$form->addElement('text', 		'hc_name', _("Host Category Name"), $attrsText);
	$form->addElement('text', 		'hc_alias', _("Alias"), $attrsText);

	/*
	 * Hosts Selection
	 */
	$form->addElement('header', 'relation', _("Relations"));
    $ams1 = $form->addElement('advmultiselect', 'hc_hosts', array(_("Linked Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'hc_hostsTemplate', array(_("Linked Host Template"), _("Available"), _("Selected")) , $hostTpl, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('textarea', 'hc_comment', _("Comments"), $attrsTextarea);
	$hcActivation[] = HTML_QuickForm::createElement('radio', 'hc_activate', null, _("Enabled"), '1');
	$hcActivation[] = HTML_QuickForm::createElement('radio', 'hc_activate', null, _("Disabled"), '0');
	$form->addGroup($hcActivation, 'hc_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('hc_activate' => '1'));


	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'hc_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["hc_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('hc_name', 'myReplace');
	$form->addRule('hc_name', _("Compulsory Name"), 'required');
	$form->addRule('hc_alias', _("Compulsory Alias"), 'required');

	$form->registerRule('exist', 'callback', 'testHostCategorieExistence');
	$form->addRule('hc_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	if ($o == "w")	{
		/*
		 * Just watch a HostCategorie information
		 */
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hc_id=".$hc_id."'"));
	    $form->setDefaults($hc);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a HostCategorie information
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($hc);
	} else if ($o == "a")	{
		/*
		 * Add a HostCategorie information
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$tpl->assign('p', $p);

	$valid = false;
	if ($form->validate())	{
		$hcObj = $form->getElement('hc_id');
		if ($form->getSubmitValue("submitA"))
			$hcObj->setValue(insertHostCategoriesInDB());
		else if ($form->getSubmitValue("submitC"))
			updateHostCategoriesInDB($hcObj->getValue());
		$o = NULL;
		$hcObj = $form->getElement('hc_id');
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hc_id=".$hcObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}

	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]) {
		require_once($path."listHostCategories.php");
	} else	{
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('topdoc', _("Documentation"));
		$tpl->display("formHostCategories.ihtml");
	}
?>