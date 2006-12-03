<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	## Database retrieve information for Contact
	#
	$ppol = array();
	if (($o == "c" || $o == "w") && $purge_policy_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM purge_policy WHERE purge_policy_id = '".$purge_policy_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
		# Set base value
		$ppol = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#

	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["mod_purgePolicy_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["mod_purgePolicy_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["mod_purgePolicy_view"]);

	#
	## Purge Policy basic information
	#
	$form->addElement('header', 'information', $lang["mod_purgePolicy_infos"]);
	$form->addElement('text', 'purge_policy_name', $lang["mod_purgePolicy_name"], $attrsText);
	$form->addElement('text', 'purge_policy_alias', $lang["mod_purgePolicy_alias"], $attrsText);
	$form->addElement('text', 'purge_policy_alias', $lang["mod_purgePolicy_alias"], $attrsText);
	$periods = array(	"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],	
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);	
	$sel =& $form->addElement('select', 'purge_policy_retention', $lang["mod_purgePolicy_retain"], $periods);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_host', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_host', null, $lang["no"], '0');
	$form->addGroup($tab, 'purge_policy_host', $lang["mod_purgePolicy_host"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_service', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_service', null, $lang["no"], '0');
	$form->addGroup($tab, 'purge_policy_service', $lang["mod_purgePolicy_service"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_metric', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_metric', null, $lang["no"], '0');
	$form->addGroup($tab, 'purge_policy_metric', $lang["mod_purgePolicy_metric"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_bin', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_bin', null, $lang["no"], '0');
	$form->addGroup($tab, 'purge_policy_bin', $lang["mod_purgePolicy_bin"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_raw', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_raw', null, $lang["no"], '0');
	$form->addGroup($tab, 'purge_policy_raw', $lang["mod_purgePolicy_raw"], '&nbsp;');
	
	$form->setDefaults(array('purge_policy_bin'=>'1', 'purge_policy_raw'=>'1', 'purge_policy_metric'=>'0', 'purge_policy_service'=>'0', 'purge_policy_host'=>'0', ));
	
	$form->addElement('textarea', 'purge_policy_comment', $lang["mod_purgePolicy_comment"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'purge_policy_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('purge_policy_name', $lang['ErrName'], 'required');
	$form->addRule('purge_policy_alias', $lang['ErrAlias'], 'required');
	$form->addRule('purge_policy_host', $lang['ErrRequired'], 'required');
	$form->addRule('purge_policy_service', $lang['ErrRequired'], 'required');
	$form->addRule('purge_policy_metric', $lang['ErrRequired'], 'required');
	$form->addRule('purge_policy_raw', $lang['ErrRequired'], 'required');
	$form->addRule('purge_policy_bin', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testPurgePolicyExistence');
	$form->addRule('purge_policy_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("help", array("h1"=>$lang["mod_purgePolicy_raw2"], "h2"=>$lang["mod_purgePolicy_bin2"], "h3"=>$lang["mod_purgePolicy_metric2"], "h4"=>$lang["mod_purgePolicy_service2"], "h5"=>$lang["mod_purgePolicy_host2"]));

	# Just watch a contact information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&purge_policy_id=".$purge_policy_id."'"));
	    $form->setDefaults($ppol);
		$form->freeze();
	}
	# Modify a contact information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($ppol);
	}
	# Add a contact information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$valid = false;
	if ($form->validate())	{
		$ppolObj =& $form->getElement('purge_policy_id');
		if ($form->getSubmitValue("submitA"))
			$ppolObj->setValue(insertPurgePolicyInDB());
		else if ($form->getSubmitValue("submitC"))
			updatePurgePolicyInDB($ppolObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&purge_policy_id=".$ppolObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listPurgePolicy.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formPurgePolicy.ihtml");
	}
?>