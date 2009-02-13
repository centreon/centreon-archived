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
	## Debug information
	#
	$form->addElement('header', 'debug', _("Debug"));
	$form->addElement('text', 'debug_path', _("Logs Directory"), $attrsText);

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_auth_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_nagios_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_rrdtool_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_ldap_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_inventory_clear', '&nbsp;', '&nbsp;&nbsp;');


	$form->addElement('select', 'debug_auth', _("Authentification Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_nagios_import', _("Nagios Import Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_rrdtool', _("RRDTool Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_ldap_import', _("LDAP Import Users Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_inventory', _("Inventory Debug"), array(0=>_("No"), 1=>_("Yes")));

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
	$form->addRule('debug_path', _("Can't write in directory"), 'is_writable_path');

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'debug/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateDebugConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();

		if (isset($_POST["debug_auth_clear"]))
			@unlink($oreon->optGen["debug_path"]."auth.log");

		if (isset($_POST["debug_nagios_import_clear"]))
			@unlink($oreon->optGen["debug_path"]."cfgimport.log");

		if (isset($_POST["debug_rrdtool_clear"]))
			@unlink($oreon->optGen["debug_path"]."rrdtool.log");

		if (isset($_POST["debug_ldap_import_clear"]))
			@unlink($oreon->optGen["debug_path"]."ldapsearch.log");

		if (isset($_POST["debug_inventory_clear"]))
			@unlink($oreon->optGen["debug_path"]."inventory.log");
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=debug'"));
	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_debug_options", _("Debug Properties"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>