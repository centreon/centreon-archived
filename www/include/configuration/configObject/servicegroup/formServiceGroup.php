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
 
	if (!isset($oreon))
		exit();
		
	#
	## Database retrieve information for ServiceGroup
	#
	
	$sg = array();
	if (($o == "c" || $o == "w") && $sg_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$sg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		# Set base value
		$sg = array_map("myDecode", $DBRESULT->fetchRow());
		
		# Set ServiceGroup Childs		
		$DBRESULT =& $pearDB->query("SELECT host_host_id, service_service_id FROM servicegroup_relation WHERE servicegroup_sg_id = '".$sg_id."' AND host_host_id IS NOT NULL ORDER BY service_service_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		for ($i = 0; $host =& $DBRESULT->fetchRow(); $i++)
			$sg["sg_hServices"][$i] = $host["host_host_id"]."-".$host["service_service_id"];
		
		$DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id, service_service_id FROM servicegroup_relation WHERE servicegroup_sg_id = '".$sg_id."' AND hostgroup_hg_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		for ($i = 0; $services =& $DBRESULT->fetchRow(); $i++)
			$sg["sg_hgServices"][$i] = $services["hostgroup_hg_id"]."-".$services["service_service_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Services comes from DB -> Store in $hServices Array and $hgServices
	$hServices = array();
	$hgServices = array();
	$initName = NULL;
	
	$DBRESULT =& $pearDB->query("SELECT host_name, host_id FROM host WHERE host_register = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($host =& $DBRESULT->fetchRow())	{
		$services = getMyHostServices($host["host_id"]);
		foreach ($services as $key => $s)
			$hServices[$host["host_id"]."-".$key] = $host["host_name"]."&nbsp;-&nbsp;".$s;
		unset($services);
	}
	$DBRESULT->free();

	$DBRESULT =& $pearDB->query(	"SELECT DISTINCT hg.hg_name, hg.hg_id, sv.service_description, sv.service_template_model_stm_id, sv.service_id " .
									"FROM host_service_relation hsr, service sv, hostgroup hg " .
									"WHERE sv.service_register = '1' " .
									"AND hsr.service_service_id = sv.service_id " .
									"AND hg.hg_id = hsr.hostgroup_hg_id " .
									"ORDER BY hg.hg_name, sv.service_description");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($elem =& $DBRESULT->fetchRow())	{
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$elem["service_description"])
			$elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);
		
		$elem["service_description"] = str_replace("#S#", "/", $elem["service_description"]);
		$elem["service_description"] = str_replace("#BS#", "\\", $elem["service_description"]);
		
		$hgServices[$elem["hg_id"] . '-'.$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
	}
	$DBRESULT->free();
	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 250px; height: 250px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a ServiceGroup"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a ServiceGroup"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a ServiceGroup"));

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'sg_name', _("ServiceGroup Name"), $attrsText);
	$form->addElement('text', 'sg_alias', _("Alias"), $attrsText);
	
	##
	## Services Selection
	##
	$form->addElement('header', 'relation', _("Relations"));
    $ams1 =& $form->addElement('advmultiselect', 'sg_hServices', _("Host Services linked"), $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	$form->addElement('header', 'relation', _("Relations"));
    $ams1 =& $form->addElement('advmultiselect', 'sg_hgServices', _("Linked Host Group Services"), $hgServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
		
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Enabled"), '1');
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Disabled"), '0');
	$form->addGroup($sgActivation, 'sg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('sg_activate' => '1'));
	$form->addElement('textarea', 'sg_comment', _("Comments"), $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'sg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["sg_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('sg_name', 'myReplace');
	$form->addRule('sg_name', _("Compulsory Name"), 'required');
	$form->addRule('sg_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testServiceGroupExistence');
	$form->addRule('sg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Service Group information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sg_id."'"));
	    $form->setDefaults($sg);
		$form->freeze();
	}
	# Modify a Service Group information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($sg);
	}
	# Add a Service Group information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$tpl->assign('nagios', $oreon->user->get_version());

	$valid = false;
	if ($form->validate())	{
		$sgObj =& $form->getElement('sg_id');
		if ($form->getSubmitValue("submitA"))
			$sgObj->setValue(insertServiceGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceGroupInDB($sgObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	
	if ($valid && $action["action"]["action"])
		require_once($path."listServiceGroup.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formServiceGroup.ihtml");
	}
?>