<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
	/*
	 * Database retrieve information for HostGroup
	 */
	$hg = array();
	if (($o == "c" || $o == "w") && $hg_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		# Set base value
		$hg = array_map("myDecode", $DBRESULT->fetchRow());
		
		/*
		 *  Set HostGroup Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$hg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		for($i = 0; $hosts =& $DBRESULT->fetchRow(); $i++)
			$hg["hg_hosts"][$i] = $hosts["host_host_id"];
		$DBRESULT->free();
	}
	
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Hosts comes from DB -> Store in $hosts Array
	$hosts = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($host =& $DBRESULT->fetchRow())
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	
	/*
	 * Contact Groups comes from DB -> Store in $cgs Array
	 */
	$cgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($cg =& $DBRESULT->fetchRow())
		$cgs[$cg["cg_id"]] = $cg["cg_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 220px; height: 320px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a HostGroup"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a HostGroup"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a HostGroup"));

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'hg_name', _("HostGroup Name"), $attrsText);
	$form->addElement('text', 'hg_alias', _("Alias"), $attrsText);
	$form->addElement('select', 'hg_snmp_version', _("Version"), array(0=>null, 1=>"1", 2=>"2c", 3=>"3"));
	$form->addElement('text', 'hg_snmp_community', _("SNMP Community"), $attrsText);
	
	/*
	 * Hosts Selection
	 */
	$form->addElement('header', 'relation', _("Relations"));
	
    $ams1 =& $form->addElement('advmultiselect', 'hg_hosts', _("Linked Hosts"), $hosts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	##
	## Contact Groups Selection
	##
	$form->addElement('header', 'notification', _("Notification"));
	
    $ams1 =& $form->addElement('advmultiselect', 'hg_cgs', _("Linked ContactGroups"), $cgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Enabled"), '1');
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Disabled"), '0');
	$form->addGroup($hgActivation, 'hg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('hg_activate' => '1'));
	$form->addElement('textarea', 'hg_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'hg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["hg_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('hg_name', 'myReplace');
	$form->addRule('hg_name', _("Compulsory Name"), 'required');
	$form->addRule('hg_alias', _("Compulsory Alias"), 'required');
	if ($oreon->user->get_version() == 1)
		$form->addRule('hg_cgs', _("Compulsory Contact Group"), 'required');
	$form->registerRule('exist', 'callback', 'testHostGroupExistence');
	$form->addRule('hg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a HostGroup information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hg_id."'"));
	    $form->setDefaults($hg);
		$form->freeze();
	}
	# Modify a HostGroup information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($hg);
	}
	# Add a HostGroup information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign('p', $p);
	$tpl->assign('nagios', $oreon->user->get_version());
	$tpl->assign("initJS", "<script type='text/javascript'>
							window.onload = function () {
							initAutoComplete('Form','city_name','sub');
							};</script>");
		
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
	if ($valid && $action["action"]["action"])
		require_once($path."listHostGroup.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('topdoc', _("Documentation"));		
		$tpl->display("formHostGroup.ihtml");
	}
?>