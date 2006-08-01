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
	if (($o == "c" || $o == "w") && $contact_id)	{
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
	}
	if ($o == "a") {
      $res =& $pearDB->query("SELECT ldap_auth_enable FROM general_opt LIMIT 1");
      if (PEAR::isError($pearDB)) {
         print "Mysql Error : ".$pearDB->getMessage();
      }
      $res->fetchInto($ldap_auth);
      $res->free();
   }

	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Langs -> $langs Array
	$langs = array();
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
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 		= array("size"=>"60");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["cct_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["cct_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["cct_view"]);

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', $lang['cct_infos']);
	$form->addElement('text', 'contact_name', $lang["cct_name"], $attrsText);
	$form->addElement('text', 'contact_alias', $lang["alias"], $attrsText);
	$form->addElement('text', 'contact_email', $lang["cct_mail"], $attrsText);
	$form->addElement('text', 'contact_pager', $lang["cct_pager"], $attrsText);
    $ams3 =& $form->addElement('advmultiselect', 'contact_cgNotif', $lang["cct_cgNotif"], $notifCgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);


	#
	## Contact Oreon information
	#
	$form->addElement('header', 'oreon', $lang['cct_oreon']);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'contact_oreon', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'contact_oreon', null, $lang["no"], '0');
	$form->addGroup($tab, 'contact_oreon', $lang['cct_oreon_text'], '&nbsp;');
	$form->setDefaults(array('contact_oreon' => '1'));
	$form->addElement('password', 'contact_passwd', $lang['cct_passwd'], $attrsText);
	$form->addElement('password', 'contact_passwd2', $lang['cct_passwd2'], $attrsText);
    $form->addElement('select', 'contact_lang', $lang["cct_lang"], $langs);
    $form->addElement('select', 'contact_type_msg', $lang['cct_mailType'], array("txt"=>"txt", "html"=>"html", "pdf"=>"pdf"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'contact_admin', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'contact_admin', null, $lang["no"], '0');
	$form->addGroup($tab, 'contact_admin', $lang['cct_admin'], '&nbsp;');
	$form->setDefaults(array('contact_admin' => '1'));

   $auth_type = array();
   $auth_type["local"] = "local";
	if (isset($ldap_auth['ldap_auth_enable']) && $ldap_auth['ldap_auth_enable'] == 1) {
		$auth_type["ldap"] = "ldap";
		$form->addElement('text', 'contact_ldap_dn', $lang["cct_ldap_dn"], $attrsText2);
	}

   	$form->addElement('select', 'contact_auth_type', $lang["cct_contact_auth_type"], $auth_type);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', $lang['cct_notif']);

	# Host notif
	$form->addElement('header', 'hostNotification', $lang['h']);
 	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'Down');
	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unreachable');
	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
	$form->addGroup($hostNotifOpt, 'contact_hostNotifOpts', $lang["cct_hostNotifOpt"], '&nbsp;&nbsp;');
    $form->addElement('select', 'timeperiod_tp_id', $lang["cct_hostNotifTp"], $notifTps);
    $ams1 =& $form->addElement('advmultiselect', 'contact_hostNotifCmds', $lang["cct_hostNotifCmd"], $notifCmds, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	# Service notif
	$form->addElement('header', 'serviceNotification', $lang['sv']);
 	$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$svNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
	$form->addGroup($svNotifOpt, 'contact_svNotifOpts', $lang["cct_svNotifOpt"], '&nbsp;&nbsp;');
    $form->addElement('select', 'timeperiod_tp_id2', $lang["cct_svNotifTp"], $notifTps);
    $ams2 =& $form->addElement('advmultiselect', 'contact_svNotifCmds', $lang["cct_svNotifCmd"], $notifCmds, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams2->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$cctActivation[] = &HTML_QuickForm::createElement('radio', 'contact_activate', null, $lang["enable"], '1');
	$cctActivation[] = &HTML_QuickForm::createElement('radio', 'contact_activate', null, $lang["disable"], '0');
	$form->addGroup($cctActivation, 'contact_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('contact_activate' => '1'));
	$form->addElement('textarea', 'contact_comment', $lang["cmt_comment"], $attrsTextarea);

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
	function myReplace()	{
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


	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a contact information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&contact_id=".$contact_id."'"));
	    $form->setDefaults($cct);
		$form->freeze();
	}
	# Modify a contact information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($cct);
	}
	# Add a contact information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$valid = false;
	if ($form->validate())	{
		$cctObj =& $form->getElement('contact_id');
		if ($form->getSubmitValue("submitA"))
			$cctObj->setValue(insertContactInDB());
		else if ($form->getSubmitValue("submitC"))
			updateContactInDB($cctObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&contact_id=".$cctObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listContact.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		if (isset($ldap_auth['ldap_auth_enable']))
			$tpl->assign('ldap', $ldap_auth['ldap_auth_enable'] );
		$tpl->display("formContact.ihtml");
	}
?>