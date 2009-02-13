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

	
	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($opt =& $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
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
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));
	
	#
	## Various information
	#
	$form->addElement('text', 'rrdtool_path_bin', _("Directory + RRDTOOL Binary"), $attrsText);
	$form->addElement('text', 'rrdtool_version', _("RRDTool Version"), $attrsText2);

	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, _("Graphs Plugins"), '1');
	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, _("Simple Graphs Renderer"), '0');
	
	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('nagios_path', 'slash');
	//$form->applyFilter('nagios_path_bin', 'slash');
	$form->applyFilter('nagios_path_img', 'slash');
	$form->applyFilter('nagios_path_plugins', 'slash');
	$form->applyFilter('oreon_path', 'slash');
	$form->applyFilter('oreon_web_path', 'slash');
	$form->applyFilter('oreon_rrdbase_path', 'slash');
	$form->applyFilter('debug_path', 'slash');
	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
	$form->addRule('nagios_path_plugins', _("Can't write directory"), 'is_writable_path');
	$form->addRule('nagios_path_img', _("Can't write directory"), 'is_writable_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');
	$form->addRule('nagios_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('mailer_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('rrdtool_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('oreon_rrdbase_path', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('debug_path', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', _("Can't write in file"), 'is_writable_file_if_exist');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'rrdtool/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));


    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateRRDToolConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=rrdtool'"));

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_rrdtool_properties", _("RRDTool Properties"));
	$tpl->assign("genOpt_rrdtool_configurations", _("RRDTool Configuration"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
