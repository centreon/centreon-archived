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
	$cct = array();
/*	if (($o == "c" || $o == "w") && $contact_id)	{
		$cct["contact_hostNotifCmds"] = array();
		$cct["contact_svNotifCmds"] = array();
		$cct["contact_cgNotif"] = array();
		$res =& $pearDB->query("SELECT * FROM contact WHERE contact_id = '".$contact_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$cct = array_map("myDecode", $res->fetchRow());
		$cct["contact_passwd"] = NULL;
		# Set Host Notification Options
		$tmp = explode(',', $cct["contact_host_notification_options"]);
		foreach ($tmp as $key => $value)
			$cct["contact_hostNotifOpts"][trim($value)] = 1;
		# Set Service Notification Options
		$tmp = explode(',', $cct["contact_service_notification_options"]);
		foreach ($tmp as $key => $value)
			$cct["contact_svNotifOpts"][trim($value)] = 1;
		$res->free();
		# Set Contact Group Parents
		$res =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$contact_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($notifCg); $i++)
			$cct["contact_cgNotif"][$i] = $notifCg["contactgroup_cg_id"];
		$res->free();
		# Set Host Notification Commands
		$res =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '".$contact_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($notifCmd); $i++)
			$cct["contact_hostNotifCmds"][$i] = $notifCmd["command_command_id"];
		$res->free();
		# Set Service Notification Commands
		$res =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '".$contact_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($notifCmd); $i++)
			$cct["contact_svNotifCmds"][$i] = $notifCmd["command_command_id"];
		$res->free();
		$res =& $pearDB->query("SELECT ldap_auth_enable FROM general_opt LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$res->fetchInto($ldap_auth);
		$res->free();
	}*/

	$res =& $pearDB->query("SELECT ldap_host, ldap_port, ldap_base_dn, ldap_login_attrib, ldap_ssl, ldap_auth_enable, ldap_search_user, ldap_search_user_pwd, ldap_search_filter, ldap_search_timeout, ldap_search_limit FROM general_opt LIMIT 1");
	if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	$ldap_auth = array_map("myDecode", $res->fetchRow());


	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Langs -> $langs Array
/*	$langs = array();
	 $chemintotal = "./lang/";
	if ($handle  = opendir($chemintotal))   {
	    while ($file = readdir($handle))
	    	if (!is_dir("$chemintotal/$file") && strcmp($file, "index.php")) {
				$tab = split('\.', $file);
	      		$langs[$tab[0]] = $tab[0];
	      	}
		closedir($handle);
	}
	# Timeperiods comes from DB -> Store in $notifsTps Array
	$notifTps = array();
	$res =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($notifTp))
		$notifTps[$notifTp["tp_id"]] = $notifTp["tp_name"];
	$res->free();
	# Notification commands comes from DB -> Store in $notifsCmds Array
	$notifCmds = array();
	$res =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '1' ORDER BY command_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($notifCmd))
		$notifCmds[$notifCmd["command_id"]] = $notifCmd["command_name"];
	$res->free();
	# Contact Groups comes from DB -> Store in $notifCcts Array
	$notifCgs = array();
	$res =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($notifCg))
		$notifCgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	$res->free();*/
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	/*$attrsText 		= array("size"=>"30");
	$attrsText2 		= array("size"=>"60");*/

	$attrsText 	= array("size"=>"80");
	$attrsText2	= array("size"=>"5");


	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p );

	$form->addElement('header', 'title',$lang['cct_ldap_search']);

	#
	## Command information
	#
	$form->addElement('header', 'options', $lang['cct_ldap_search_options']);
	$form->addElement('text', 'ldap_search_filter', $lang['cct_ldap_search_filter'], $attrsText );
	$form->addElement('text', 'ldap_base_dn', $lang["genOpt_ldap_base_dn"], $attrsText);
	$form->addElement('text', 'ldap_search_timeout', $lang["genOpt_ldap_search_timeout"], $attrsText2);
	$form->addElement('text', 'ldap_search_limit', $lang["genOpt_ldap_search_limit"], $attrsText2);
	$form->addElement('header', 'result', $lang['cct_ldap_search_result']);
	$form->addElement('header', 'ldap_search_result_output', $lang["cct_ldap_search_result_output"]);

	$link = "LdapSearch()";
	$form->addElement("button", "ldap_search_button", $lang['cct_ldap_search'], array("onClick"=>$link));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'contact_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);


	#
	## Form Rules
	#
/*	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["contact_name"]));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('contact_name', 'myReplace');
	$form->addRule('contact_name', $lang['ErrName'], 'required');
	$form->addRule('contact_alias', $lang['ErrAlias'], 'required');
	$form->addRule('contact_email', $lang['ErrEmail'], 'required');
	$form->addRule('contact_hostNotifOpts', $lang['ErrOpt'], 'required');
	$form->addRule('timeperiod_tp_id', $lang['ErrTp'], 'required');
	$form->addRule('contact_hostNotifCmds', $lang['ErrCmd'], 'required');
	$form->addRule('contact_svNotifOpts', $lang['ErrOpt'], 'required');
	$form->addRule('timeperiod_tp_id2', $lang['ErrTp'], 'required');
	$form->addRule('contact_svNotifCmds', $lang['ErrCmd'], 'required');
	$form->addRule(array('contact_passwd', 'contact_passwd2'), $lang['ErrCctPasswd'], 'compare');
	$form->registerRule('exist', 'callback', 'testContactExistence');
	$form->addRule('contact_name', $lang['ErrAlreadyExist'], 'exist');
	$form->registerRule('existAlias', 'callback', 'testAliasExistence');
	$form->addRule('contact_alias', $lang['ErrAlreadyExist'], 'existAlias');
	$form->registerRule('keepOneContactAtLeast', 'callback', 'keepOneContactAtLeast');
	$form->addRule('contact_alias', $lang['ErrNotEnoughtContact'], 'keepOneContactAtLeast');

	$form->setRequiredNote($lang['requiredFields']);
*/
	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);


	$tpl->assign('ldap_search_filter_help', $lang["cct_ldap_search_filter_help"]);
	$tpl->assign('ldap_search_filter_help_title', $lang["cct_ldap_search_filter_help_title"]);
	$tpl->assign('javascript', '<script type="text/javascript" src="./include/common/javascript/ajaxLdapSearch.js"></script>');


	# Just watch a contact information
	if ($o == "li")	{
		$subA =& $form->addElement('submit', 'submitA', $lang['cct_ldap_import_users']);
		$form->setDefaults($ldap_auth);
	}

	$valid = false;
	if ($form->validate())	{
		if (isset($_POST["contact_select"]["select"]) ) {
			if ($form->getSubmitValue("submitA"))
				insertLdapContactInDB($_POST["contact_select"]);
			}
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listContact.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("ldapImportContact.ihtml");
	}
?>