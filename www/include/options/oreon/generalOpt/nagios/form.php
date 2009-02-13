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
	
	/*
	 * Database retrieve information for differents elements list we need on the page
	 */
	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#

	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	/*
	 * Nagios information
	 */
	$form->addElement('header', 'nagios', _("Nagios information"));
	$form->addElement('text', 'nagios_path', _("Directory"), $attrsText);
	$form->addElement('text', 'nagios_path_bin', _("Directory + Binary"), $attrsText);
	$form->addElement('text', 'nagios_init_script', _("Init Script"), $attrsText);
	$form->addElement('text', 'nagios_path_img', _("Images Directory"), $attrsText);
	$form->addElement('text', 'nagios_path_plugins', _("Plugins Directory"), $attrsText);
	$form->addElement('text', 'mailer_path_bin', _("Directory + Mailer Binary"), $attrsText);
	
	$form->addElement('select', 'nagios_version', _("Nagios Release"), array(2=>"2", 3=>"3"));
	
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, _("Yes"), '1');
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, _("No"), '0');
	
	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Form Rules
	 */

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
	
	//$form->addRule('nagios_path_plugins', ("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path_img', _("The directory isn't valid"), 'is_valid_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');
	//$form->addRule('nagios_path_bin', _("Can't execute binary"), 'is_executable_binary');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."/nagios", $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateNagiosConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("impossible to validate, one or more field is incorrect")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=nagios'"));

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_nagios_properties", _("Nagios Properties"));
	$tpl->assign("genOpt_nagios_version", _("Nagios version"));
	$tpl->assign("genOpt_nagios_init_script", _("Initialization Script "));
	$tpl->assign("genOpt_nagios_direstory", _("Nagios Directories"));
	$tpl->assign("genOpt_mailer_path", _("Mailer path"));
	$tpl->assign("genOpt_ndo_configuration", _("NDO Configuration"));
	
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
