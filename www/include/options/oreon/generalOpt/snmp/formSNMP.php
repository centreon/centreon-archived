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

	if (!isset($oreon))
		exit();

	#
	## Database retrieve information for LCA
	#
	$res =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $res->fetchRow());
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
	## SNMP information
	#
	$form->addElement('header', 'snmp', $lang["genOpt_snmp"]);
	$form->addElement('text', 'snmp_community', $lang["genOpt_snmpCom"], $attrsText);
	$form->addElement('select', 'snmp_version', $lang["genOpt_snmpVer"], array("0"=>"1", "1"=>"2", "2"=>"2c"), $attrsAdvSelect);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, $lang["no"], '0');
	$form->addGroup($tab, 'snmp_trapd_used', $lang["genOpt_snmp_trapd_used"], '&nbsp;');
	$form->setDefaults(array('snmp_trapd_used' => '0'));
	$form->addElement('text', 'snmp_trapd_path_conf', $lang["genOpt_snmp_trapd_pathConf"], $attrsText);
	$form->addElement('text', 'snmp_trapd_path_daemon', $lang["genOpt_snmp_trapd_pathBin"], $attrsText);
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('_ALL_', 'trim');
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
	$form->addRule('oreon_path', $lang['ErrWrPath'], 'is_valid_path');
	$form->addRule('nagios_path_plugins', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path_img', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path', $lang['ErrValidPath'], 'is_valid_path');
	$form->addRule('nagios_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('mailer_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('rrdtool_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('oreon_rrdbase_path', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('debug_path', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', $lang['ErrWrFile'], 'is_writable_file_if_exist');

	#
	##End of form definition
	#

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."snmp/", $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);

    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateSNMPConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$res2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $res2->fetchRow();
		$o = "w";
   		$valid = true;
		$form->freeze();

	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");
	}

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=snmp'"));


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
	$tpl->display("formSNMP.ihtml");
?>
