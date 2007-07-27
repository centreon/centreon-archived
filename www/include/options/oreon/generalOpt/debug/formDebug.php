<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	if (!isset($oreon))
		exit();

	#
	## Database retrieve information
	#
	$DBRESULT =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
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
	$form->addElement('header', 'title', $lang["genOpt_change"]);

    #
	## Debug information
	#
	$form->addElement('header', 'debug', $lang["genOpt_debug"]);
	$form->addElement('text', 'debug_path', $lang["genOpt_dPath"], $attrsText);

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_auth_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_nagios_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_rrdtool_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_ldap_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_inventory_clear', '&nbsp;', '&nbsp;&nbsp;');


	$form->addElement('select', 'debug_auth', $lang["genOpt_debug_auth"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_nagios_import', $lang["genOpt_debug_nagios_import"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_rrdtool', $lang["genOpt_debug_rrdtool"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_ldap_import', $lang["genOpt_debug_ldap_import"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_inventory', $lang["genOpt_debug_inventory"], array(0=>$lang['no'], 1=>$lang['yes']));

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
	$form->addRule('debug_path', $lang['ErrWrPath'], 'is_writable_path');

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

	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$DBRESULT =& $form->addElement('reset', 'reset', $lang["reset"]);

    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateDebugConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $DBRESULT2->fetchRow();
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
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=debug'"));
	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('lang', $lang);
	$tpl->assign('valid', $valid);
	$tpl->display("formDebug.ihtml");
?>
