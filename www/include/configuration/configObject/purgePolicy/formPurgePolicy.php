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
 

	#
	## Database retrieve information for Contact
	#
	$ppol = array();
	if (($o == "c" || $o == "w") && $purge_policy_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM purge_policy WHERE purge_policy_id = '".$purge_policy_id."' LIMIT 1");
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
		$form->addElement('header', 'title', _("Add a Template Deletion Policy"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Template Deletion Policy"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Template Deletion Policy"));

	#
	## Purge Policy basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'purge_policy_name', _("Policy Name"), $attrsText);
	$form->addElement('text', 'purge_policy_alias', _("Alias"), $attrsText);
	$form->addElement('text', 'purge_policy_alias', _("Alias"), $attrsText);
	$periods = array(	"86400"=>_("Last 24 Hours"),
						"172800"=>_("Last 2 Days"),
						"302400"=>_("Last 4 Days"),	
						"604800"=>_("Last 7 Days"),
						"1209600"=>_("Last 14 Days"),
						"2419200"=>_("Last 28 Days"),
						"2592000"=>_("Last 30 Days"),
						"2678400"=>_("Last 31 Days"),
						"5184000"=>_("Last 2 Months"),
						"10368000"=>_("Last 4 Months"),
						"15552000"=>_("Last 6 Months"),
						"31104000"=>_("Last Year"));	
	$sel =& $form->addElement('select', 'purge_policy_retention', _("Retention Period"), $periods);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_host', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_host', null, _("No"), '0');
	$form->addGroup($tab, 'purge_policy_host', _("Host Definition Deletion"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_service', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_service', null, _("No"), '0');
	$form->addGroup($tab, 'purge_policy_service', _("Service Definition Deletion"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_metric', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_metric', null, _("No"), '0');
	$form->addGroup($tab, 'purge_policy_metric', _("Metric Definition Deletion"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_bin', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_bin', null, _("No"), '0');
	$form->addGroup($tab, 'purge_policy_bin', _("Bin Deletion"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_raw', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'purge_policy_raw', null, _("No"), '0');
	$form->addGroup($tab, 'purge_policy_raw', _("Raw Deletion"), '&nbsp;');
	
	$form->setDefaults(array('purge_policy_bin'=>'1', 'purge_policy_raw'=>'1', 'purge_policy_metric'=>'0', 'purge_policy_service'=>'0', 'purge_policy_host'=>'0', ));
	
	$form->addElement('textarea', 'purge_policy_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'purge_policy_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('purge_policy_name', _("Compulsory Name"), 'required');
	$form->addRule('purge_policy_alias', _("Compulsory Alias"), 'required');
	$form->addRule('purge_policy_host', _("Required Field"), 'required');
	$form->addRule('purge_policy_service', _("Required Field"), 'required');
	$form->addRule('purge_policy_metric', _("Required Field"), 'required');
	$form->addRule('purge_policy_raw', _("Required Field"), 'required');
	$form->addRule('purge_policy_bin', _("Required Field"), 'required');
	$form->registerRule('exist', 'callback', 'testPurgePolicyExistence');
	$form->addRule('purge_policy_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("help", array("h1"=>_("Only raw rows according to the retention period"), "h2"=>_("Only bin rows according to the retention period"), "h3"=>_("Not link with period, ALL metric + bin"), "h4"=>_("Not link with period, ALL Service + Metric + bin + raw"), "h5"=>_("Not link with period, ALL Host + Service + Metric + bin + raw")));

	# Just watch a contact information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&purge_policy_id=".$purge_policy_id."'"));
	    $form->setDefaults($ppol);
		$form->freeze();
	}
	# Modify a contact information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($ppol);
	}
	# Add a contact information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$ppolObj =& $form->getElement('purge_policy_id');
		if ($form->getSubmitValue("submitA"))
			$ppolObj->setValue(insertPurgePolicyInDB());
		else if ($form->getSubmitValue("submitC"))
			updatePurgePolicyInDB($ppolObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&purge_policy_id=".$ppolObj->getValue()."'"));
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