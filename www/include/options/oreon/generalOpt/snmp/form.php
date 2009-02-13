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
	## SNMP information
	#
	$form->addElement('header', 'snmp', _("SNMP information"));
	$form->addElement('text', 'snmp_community', _("Global Community"), $attrsText);
	$form->addElement('select', 'snmp_version', _("Version"), array("0"=>"1", "1"=>"2", "2"=>"2c", "3"=>"3"), $attrsAdvSelect);
	$form->addElement('text', 'snmp_trapd_path_conf', _("Directory of traps configuration files"), $attrsText);
	$form->addElement('text', 'snmpttconvertmib_path_bin', _("snmpttconvertmib Directory + Binary"), $attrsText);
	$form->addElement('text', 'snmptt_unknowntrap_log_file', _("SNMPTT log file"), $attrsText);
	$form->addElement('text', 'perl_library_path', _("Perl library directory"), $attrsText);
	
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->addRule('snmp_trapd_path_conf', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', _("Required Field"), 'required');
	$form->addRule('snmpttconvertmib_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('perl_library_path', _("Can't read in directory"), 'is_readable_path');
	#
	##End of form definition
	#

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."snmp/", $tpl);

	$form->setDefaults($oreon->optGen);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateSNMPConfigData($form->getSubmitValue("gopt_id"));
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

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=snmp'"));

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
