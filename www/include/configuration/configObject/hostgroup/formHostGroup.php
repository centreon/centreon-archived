<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	#
	## Database retrieve information for HostGroup
	#
	$hg = array();
	if (($o == "c" || $o == "w") && $hg_id)	{	
		if ($oreon->user->admin || !HadUserLca($pearDB))
			$rq = "SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1";
		else
			$rq = "SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' AND hg_id IN (".$lcaHostGroupstr.") LIMIT 1";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$hg = array_map("myDecode", $DBRESULT->fetchRow());
		# Set HostGroup Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$hg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($hosts); $i++)
			$hg["hg_hosts"][$i] = $hosts["host_host_id"];
		$DBRESULT->free();
		# Nagios 1 - Set Contact Group Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_hostgroup_relation WHERE hostgroup_hg_id = '".$hg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($cgs); $i++)
			$hg["hg_cgs"][$i] = $cgs["contactgroup_cg_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Hosts comes from DB -> Store in $hosts Array
	$hosts = array();
	if ($oreon->user->admin || !HadUserLca($pearDB))
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	else
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_id IN (".$lcaHoststr.") AND host_register = '1' ORDER BY host_name");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($host))
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	# Contact Groups comes from DB -> Store in $cgs Array
	$cgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($cg))
		$cgs[$cg["cg_id"]] = $cg["cg_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["hg_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["hg_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["hg_view"]);

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', $lang['hg_infos']);
	$form->addElement('text', 'hg_name', $lang["hg_name"], $attrsText);
	$form->addElement('text', 'hg_alias', $lang["hg_alias"], $attrsText);
	$form->addElement('select', 'hg_snmp_version', $lang['h_snmpVer'], array(0=>null, 1=>"1", 2=>"2c", 3=>"3"));
	$form->addElement('text', 'hg_snmp_community', $lang['h_snmpCom'], $attrsText);
	
	##
	## Hosts Selection
	##
	$form->addElement('header', 'relation', $lang['hg_links']);
	
    $ams1 =& $form->addElement('advmultiselect', 'hg_hosts', $lang['hg_HostMembers'], $hosts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	##
	## Contact Groups Selection
	##
	$form->addElement('header', 'notification', $lang['hg_notif']);
	
    $ams1 =& $form->addElement('advmultiselect', 'hg_cgs', $lang['hg_CgMembers'], $cgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, $lang["enable"], '1');
	$hgActivation[] = &HTML_QuickForm::createElement('radio', 'hg_activate', null, $lang["disable"], '0');
	$form->addGroup($hgActivation, 'hg_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('hg_activate' => '1'));
	$form->addElement('textarea', 'hg_comment', $lang["comment"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
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
	$form->addRule('hg_name', $lang['ErrName'], 'required');
	$form->addRule('hg_alias', $lang['ErrAlias'], 'required');
	//$form->addRule('hg_hosts', $lang['ErrCct'], 'required');
	if ($oreon->user->get_version() == 1)
		$form->addRule('hg_cgs', $lang['ErrCg'], 'required');
	$form->registerRule('exist', 'callback', 'testHostGroupExistence');
	$form->addRule('hg_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a HostGroup information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hg_id."'"));
	    $form->setDefaults($hg);
		$form->freeze();
	}
	# Modify a HostGroup information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($hg);
	}
	# Add a HostGroup information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
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
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hgObj->getValue()."'"));
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
		$tpl->display("formHostGroup.ihtml");
	}
?>