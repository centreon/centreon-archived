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
	 * Database retrieve information for HostGroup
	 */
	$hg = array();
	if (($o == "c" || $o == "w") && $hg_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		/*
		 * Set base value
		 */
		$hg = array_map("myDecode", $DBRESULT->fetchRow());
		
		/*
		 *  Set HostGroup Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$hg_id."'");
		for ($i = 0; $hosts =& $DBRESULT->fetchRow(); $i++)
			$hg["hg_hosts"][$i] = $hosts["host_host_id"];
		$DBRESULT->free();
		unset($hosts);
		
		/*
		 *  Set HostGroup Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hg_child_id FROM hostgroup_hg_relation WHERE hg_parent_id = '".$hg_id."'");
		for ($i = 0; $hgs =& $DBRESULT->fetchRow(); $i++)
			$hg["hg_hg"][$i] = $hgs["hg_child_id"];
		$DBRESULT->free();
		unset($hgs);
	}
	
	/*
	 * Hosts comes from DB -> Store in $hosts Array
	 */
	$hosts = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host =& $DBRESULT->fetchRow())
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	unset($host);
	
	/*
	 * Hostgroups comes from DB -> Store in $hosts Array
	 */
	
	$EDITCOND = "";
	if ($o == "w" || $o == "c")
		$EDITCOND = " WHERE `hg_id` != '".$hg_id."' ";
	
	$hostGroups = array();
	$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup $EDITCOND ORDER BY hg_name");
	while ($hgs =& $DBRESULT->fetchRow())
		$hostGroups[$hgs["hg_id"]] = $hgs["hg_name"];
	$DBRESULT->free();
	unset($hgs);
	
	/*
	 * Contact Groups comes from DB -> Store in $cgs Array
	 */
	$cgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while ($cg =& $DBRESULT->fetchRow())
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
	$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Create formulary
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Host Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Host Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Host Group"));

	/*
	 * Contact basic information
	 */
	$form->addElement('header', 	'information', _("General Information"));
	$form->addElement('text', 		'hg_name', _("Host Group Name"), $attrsText);
	$form->addElement('text', 		'hg_alias', _("Alias"), $attrsText);
	$form->addElement('select', 	'hg_snmp_version', _("Version"), array(0=>null, 1=>"1", "2c"=>"2c", 3=>"3"));
	$form->addElement('text', 		'hg_snmp_community', _("SNMP Community"), $attrsText);
	
	/*
	 * Hosts Selection
	 */
	$form->addElement('header', 'relation', _("Relations"));
	$ams1 =& $form->addElement('advmultiselect', 'hg_hosts', array(_("Linked Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	$ams1 =& $form->addElement('advmultiselect', 'hg_hg', array(_("Linked Host Groups"), _("Available"), _("Selected")), $hostGroups, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	/*
	 * Extended information
	 */
	$form->addElement('header', 	'extended', _("Extended Information"));
	$form->addElement('text', 		'hg_notes', _("Notes"), $attrsText);
	$form->addElement('text', 		'hg_notes_url', _("Notes URL"), $attrsTextLong);
	$form->addElement('text', 		'hg_action_url', _("Action URL"), $attrsTextLong);
	$form->addElement('select', 	'hg_icon_image', _("Icon"), $extImg, array("onChange"=>"showLogo('hg_icon_image_img',this.form.elements['hg_icon_image'].value)"));
	$form->addElement('select', 	'hg_map_icon_image', _("Map Icon"), $extImg, array("onChange"=>"showLogo('hg_map_icon_image_img',this.form.elements['hg_map_icon_image'].value)"));
	
	/*
	 * Further informations
	 */
	
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('textarea', 'hg_comment', _("Comments"), $attrsTextarea);
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Enabled"), '1');
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Disabled"), '0');
	$form->addGroup($hgActivation, 'hg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('hg_activate' => '1'));
	
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'hg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["hg_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('hg_name', 'myReplace');
	$form->addRule('hg_name', _("Compulsory Name"), 'required');
	$form->addRule('hg_alias', _("Compulsory Alias"), 'required');
	
	$form->registerRule('exist', 'callback', 'testHostGroupExistence');
	$form->addRule('hg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	if ($o == "w")	{
		/*
		 * Just watch a HostGroup information
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hg_id."'"));
	    $form->setDefaults($hg);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a HostGroup information
		 */
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($hg);
	} else if ($o == "a")	{
		/*
		 * Add a HostGroup information
		 */
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign('p', $p);
	$tpl->assign('nagios', $oreon->user->get_version());
	$tpl->assign("initJS", "<script type='text/javascript'>
							window.onload = function () {
							initAutoComplete('Form','city_name','sub');
							};</script>");
	$tpl->assign('javascript', "<script type='text/javascript' src='./include/common/javascript/showLogo.js'></script>" );
	$tpl->assign("helpattr", 'TITLE, "Help", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );

	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) { 
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate())	{
		$hgObj =& $form->getElement('hg_id');
		if ($form->getSubmitValue("submitA"))
			$hgObj->setValue(insertHostGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateHostGroupInDB($hgObj->getValue());
		$o = NULL;
		$hgObj =& $form->getElement('hg_id');
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]) {
		require_once($path."listHostGroup.php");
	} else	{
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('topdoc', _("Documentation"));		
		$tpl->display("formHostGroup.ihtml");
	}
?>
