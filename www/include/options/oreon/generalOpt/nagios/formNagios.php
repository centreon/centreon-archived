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
	## Nagios information
	#
	$form->addElement('header', 'nagios', $lang['genOpt_nagios']);
	$form->addElement('text', 'nagios_path', $lang["genOpt_nagPath"], $attrsText);
	$form->addElement('text', 'nagios_path_bin', $lang["genOpt_nagBin"], $attrsText);
	$form->addElement('text', 'nagios_init_script', $lang["genOpt_nagScript"], $attrsText);
	$form->addElement('text', 'nagios_path_img', $lang["genOpt_nagImg"], $attrsText);
	$form->addElement('text', 'nagios_path_plugins', $lang["genOpt_nagPlug"], $attrsText);
	$form->addElement('text', 'mailer_path_bin', $lang["genOpt_mailer"], $attrsText);
	
	$form->addElement('select', 'nagios_version', $lang["genOpt_nagVersion"], array(1=>"1", 2=>"2"));
	
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, $lang["yes"], '1');
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, $lang["no"], '0');

	#
	## ndo
	#
	$ndo_activate[] = &HTML_QuickForm::createElement('radio', 'ndo_activate', null, $lang["yes"], '1');
	$ndo_activate[] = &HTML_QuickForm::createElement('radio', 'ndo_activate', null, $lang["no"], '0');
	$form->addGroup($ndo_activate, 'ndo_activate', $lang["ndo_activate"], '&nbsp;');

	$form->addElement('text', 'ndo_base_name', $lang["ndo_base_name"], $attrsText2);
	$form->addElement('text', 'ndo_base_prefixe', $lang["ndo_base_prefixe"], $attrsText2);
	
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
	$form->applyFilter('nagios_path_img', 'slash');
	$form->applyFilter('nagios_path_plugins', 'slash');
	
	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	
	$form->addRule('nagios_path_plugins', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path_img', $lang['ErrValidPath'], 'is_valid_path');
	$form->addRule('nagios_path', $lang['ErrValidPath'], 'is_valid_path');
	$form->addRule('nagios_path_bin', $lang['ErrExeBin'], 'is_executable_binary');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."/nagios", $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$DBRESULT =& $form->addElement('reset', 'reset', $lang["reset"]);

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateNagiosConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");
	}

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=nagios'"));

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
	$tpl->display("formNagios.ihtml");
?>
