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

	#
	## Database retrieve information for Nagios
	#
	$pp = array();
	$ppTemp = array();
	if (($o == "c" || $o == "w") && $perfparse_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_perfparse WHERE perfparse_id = '".$perfparse_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$ppTemp = array_map("myDecode", $DBRESULT->fetchRow());
		foreach ($ppTemp as $key=>$value)
			$pp[strtolower($key)] = $value;
		$DBRESULT->free();
	}
	##########################################################
	# Var information to format the element
	#
	$attrsText		= array("size"=>"30");
	$attrsText2 	= array("size"=>"50");
	$attrsText3 	= array("size"=>"10");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["pp_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["pp_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["pp_view"]);

	#
	## Perfparse Configuration basic information
	#
	$form->addElement('header', 'information', $lang['pp_infos']);
	$form->addElement('text', 'perfparse_name', $lang["pp_name"], $attrsText);
	$form->addElement('textarea', 'perfparse_comment', $lang["pp_comment"], $attrsTextarea);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'perfparse_activate', null, $lang["enable"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'perfparse_activate', null, $lang["disable"], '0');
	$form->addGroup($ppTab, 'perfparse_activate', $lang["status"], '&nbsp;');	
	
	## Part 1
	$form->addElement('header', 'sManagement', $lang['pp_sMan']);
	$form->addElement('text', 'server_port', $lang['pp_serPort'], $attrsText3);

	## Part 2
	$form->addElement('header', 'pManagement', $lang['pp_pMan']);
	$form->addElement('header', 'perfDLF', $lang['pp_perfDLF']);
	$form->addElement('text', 'service_log', $lang['pp_serLog'], $attrsText2);	
	$form->addElement('text', 'service_log_position_mark_path', $lang['pp_svLPMP'], $attrsText2);
	
	$form->addElement('header', 'errHandling', $lang['pp_errHandling']);
	$form->addElement('text', 'error_log', $lang['pp_errLog'], $attrsText2);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'error_log_rotate', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'error_log_rotate', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'error_log_rotate', $lang['pp_errLogRot'], '&nbsp;');
	$form->addElement('text', 'error_log_keep_n_days', $lang['pp_errLKND'], $attrsText3);	
	$form->addElement('text', 'drop_file', $lang['pp_dropFile'], $attrsText2);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'drop_file_rotate', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'drop_file_rotate', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'drop_file_rotate', $lang['pp_dropFileRot'], '&nbsp;');
	$form->addElement('text', 'drop_file_keep_n_days', $lang['pp_dropFKND'], $attrsText3);
	
	$form->addElement('header', 'lockFileTxt', $lang['pp_lockFileTxt']);	
	$form->addElement('text', 'lock_file', $lang['pp_lockFile'], $attrsText2);
	
	## Part 3
	$form->addElement('header', 'reportOpt', $lang['pp_reportOpt']);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'show_status_bar', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'show_status_bar', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'show_status_bar', $lang['pp_showSB'], '&nbsp;');
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'do_report', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'do_report', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'do_report', $lang['pp_doReport'], '&nbsp;');
	
	## Part 4
	$form->addElement('header', 'cgiMan', $lang['pp_cgiMan']);	
    $form->addElement('select', 'default_user_permissions_policy', $lang['pp_defUPP'], array(1=>"ro", 2=>"rw", 3=>"hide"));
    $form->addElement('select', 'default_user_permissions_host_groups', $lang['pp_defUPHG'], array(1=>"ro", 2=>"rw", 3=>"hide"));
    $form->addElement('select', 'default_user_permissions_summary', $lang['pp_defUPS'], array(1=>"ro", 2=>"rw", 3=>"hide"));
	
	## Part 5
	$form->addElement('header', 'outLog', $lang['pp_outLog']);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'output_log_file', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'output_log_file', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'output_log_file', $lang['pp_outLogFile'], '&nbsp;');
	$form->addElement('text', 'output_log_filename', $lang['pp_outLogFileName'], $attrsText2);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'output_log_rotate', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'output_log_rotate', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'output_log_rotate', $lang['pp_outLogRot'], '&nbsp;');
	$form->addElement('text', 'output_log_keep_n_days', $lang['pp_outLKND'], $attrsText3);
	
	## Part 6
	$form->addElement('header', 'SockOutMan', $lang['pp_SockOutMan']);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'use_storage_socket_output', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'use_storage_socket_output', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'use_storage_socket_output', $lang['pp_useStoSockOut'], '&nbsp;');
	$form->addElement('text', 'storage_socket_output_host_name', $lang['pp_stoSockOutHName'], $attrsText3);
	$form->addElement('text', 'storage_socket_output_port', $lang['pp_stoSockOutPort'], $attrsText3);
	
	## Part 7
	$form->addElement('header', 'dbMan', $lang['pp_dbMan']);
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'use_storage_mysql', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'use_storage_mysql', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'use_storage_mysql', $lang['pp_useStorMySQL'], '&nbsp;');
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'no_raw_data', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'no_raw_data', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'no_raw_data', $lang['pp_noRawData'], '&nbsp;');
	$ppTab = array();
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'no_bin_data', null, $lang["yes"], '1');
	$ppTab[] = &HTML_QuickForm::createElement('radio', 'no_bin_data', null, $lang["no"], '0');
	$form->addGroup($ppTab, 'no_bin_data', $lang['pp_noBinData'], '&nbsp;');
	$form->addElement('text', 'db_user', $lang['pp_dbUser'], $attrsText3);
	$form->addElement('text', 'db_name', $lang['pp_dbName'], $attrsText3);
	$form->addElement('password', 'db_pass', $lang['pp_dbPass'], $attrsText3);
	$form->addElement('text', 'db_host', $lang['pp_dbHost'], $attrsText3);
	$form->addElement('text', 'dummy_hostname', $lang['pp_dumHN'], $attrsText3);
	$form->addElement('text', 'storage_modules_load', $lang['pp_stoModLoad'], $attrsText3);
		
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	
	$form->setDefaults(array(
	"perfparse_activate"=>'1',
	"service_log"=>"-",
	"error_log_rotate"=>'1',
	"drop_file"=>"/tmp/perfparse.drop",
	"drop_file_rotate"=>'1',
	"lock_file"=>"/var/lock/perfparse.lock",
	"show_status_bar"=>'0',
	"do_report"=>'0',
	"default_user_permissions_policy"=>0,
	"default_user_permissions_host_groups"=>0,
	"default_user_permissions_summary"=>0,
	"output_log_file"=>'1',
	"output_log_rotate"=>'1',
	"use_storage_socket_output"=>'0',
	"use_storage_mysql"=>'1',
	"no_raw_data"=>'1',
	"no_bin_data"=>'0',
	"dummy_hostname"=>"dummy",
	"storage_modules_load"=>"mysql",
	"action"=>'1'
	));
		
	$form->addElement('hidden', 'perfparse_id');
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
	$form->addRule('perfparse_name', $lang['ErrName'], 'required');
	$form->addRule('perfparse_comment', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('perfparse_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a nagios information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&perfparse_id=".$perfparse_id."'"));
	    $form->setDefaults($pp);
		$form->freeze();
	}
	# Modify a nagios information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($pp);
	}
	# Add a nagios information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version()));
	
	$valid = false;
	if ($form->validate())	{
		$ppObj =& $form->getElement('perfparse_id');
		if ($form->getSubmitValue("submitA"))
			$ppObj->setValue(insertPerfparseInDB());
		else if ($form->getSubmitValue("submitC"))
			updatePerfparseInDB($ppObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&perfparse_id=".$ppObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listPerfparse.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formPerfparse.ihtml");
	}
?>