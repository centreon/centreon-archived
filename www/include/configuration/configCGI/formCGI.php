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
	$cgi = array();
	if (($o == "c" || $o == "w") && $cgi_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_cgi WHERE cgi_id = '".$cgi_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cgi = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array();
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$checkCmds = array(NULL=>NULL);
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
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
		$form->addElement('header', 'title', $lang["cgi_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["cgi_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["cgi_view"]);

	#
	## CGI Configuration basic information
	#
	$form->addElement('header', 'information', $lang['cgi_infos']);
	$form->addElement('text', 'cgi_name', $lang["cgi_name"], $attrsText);
	$form->addElement('textarea', 'cgi_comment', $lang["cgi_comment"], $attrsTextarea);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'cgi_activate', null, $lang["enable"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'cgi_activate', null, $lang["disable"], '0');
	$form->addGroup($nagTab, 'cgi_activate', $lang["status"], '&nbsp;');	
	
	## Part 1
	$form->addElement('text', 'main_config_file', $lang["cgi_mainConfFile"], $attrsText2);
	$form->addElement('text', 'physical_html_path', $lang["cgi_phyHtmlPath"], $attrsText2);
	$form->addElement('text', 'url_html_path', $lang["cgi_urlHtmlPath"], $attrsText2);
	
	## Part 2
	$form->addElement('text', 'nagios_check_command', $lang["cgi_nagCheckCmd"], $attrsText2);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_authentication', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_authentication', null, $lang["no"], '0');
	$form->addGroup($nagTab, 'use_authentication', $lang["cgi_authUsage"], '&nbsp;');
	$form->addElement('text', 'default_user_name', $lang["cgi_defUserName"], $attrsText);
	$form->addElement('textarea', 'authorized_for_system_information', $lang["cgi_authFSysInfo"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_system_commands', $lang["cgi_authFSysCmd"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_configuration_information', $lang["cgi_authFConfInf"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_hosts', $lang["cgi_authFAllHosts"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_host_commands', $lang["cgi_authFAllHostCmds"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_services', $lang["cgi_authFAllSv"], $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_service_commands', $lang["cgi_authFAllSvCmds"], $attrsTextarea);
	
	## Part 3
	$form->addElement('text', 'statusmap_background_image', $lang["cgi_smBckImg"], $attrsText2);
	$form->addElement('select', 'default_statusmap_layout', $lang["cgi_defSMLayMet"], array(0=>"User-defined coordinates", 1=>"Depth layers", 2=>"Collapsed tree", 3=>"Balanced tree", 4=>"Circular", 5=>"Circular (Marked Up)", 6=>"Circular (Balloon)"));
	$form->addElement('text', 'statuswrl_include', $lang["cgi_statCGIIncWld"], $attrsText2);
	$form->addElement('select', 'default_statuswrl_layout', $lang["cgi_defStatWRLLay"], array(0=>"User-defined coordinates", 1=>"Depth layers", 2=>"Collapsed tree", 3=>"Balanced tree", 4=>"Circular"));

	## Part 4
	$form->addElement('text', 'refresh_rate', $lang["cgi_cgIRefRate"], $attrsText3);
	$form->addElement('text', 'host_unreachable_sound', $lang["cgi_hus"], $attrsText2);
	$form->addElement('text', 'host_down_sound', $lang["cgi_hdu"], $attrsText2);
	$form->addElement('text', 'service_critical_sound', $lang["cgi_scs"], $attrsText2);
	$form->addElement('text', 'service_warning_sound', $lang["cgi_sws"], $attrsText2);
	$form->addElement('text', 'service_unknown_sound', $lang["cgi_sus"], $attrsText2);

	## Part 5
	$form->addElement('textarea', 'ping_syntax', $lang["cgi_pingSyntax"], $attrsTextarea);
		
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	
	$form->setDefaults(array(
	"cgi_activate"=>'0',
	"main_config_file"=>'/usr/local/nagios/etc/nagios.cfg',
	"physical_html_path"=>'/usr/local/nagios/share',
	"use_authentication"=>array("use_authentication"=>1),
	"default_user_name"=>'guest',
	"authorized_for_system_information"=>'nagiosadmin',
	"authorized_for_system_commands"=>'nagiosadmin',
	"authorized_for_configuration_information"=>'nagiosadmin',
	"authorized_for_all_hosts"=>'nagiosadmin',
	"authorized_for_all_host_commands"=>'nagiosadmin',
	"authorized_for_all_services"=>'nagiosadmin',
	"authorized_for_all_service_commands"=>'nagiosadmin',
	"default_statusmap_layout"=>'4',
	"default_statuswrl_layout"=>'4',
	"refresh_rate"=>'90',
	"ping_syntax"=>'/bin/ping -n -U -c 5 $HOSTADDRESS$',
	'action'=>'1'
	));
		
	$form->addElement('hidden', 'cgi_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/");
	}
	$form->applyFilter('physical_html_path', 'slash');
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('cgi_name', $lang['ErrName'], 'required');
	$form->addRule('cgi_comment', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testCgiExistence');
	$form->addRule('cgi_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a CGI information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cgi_id=".$cgi_id."'"));
	    $form->setDefaults($cgi);
		$form->freeze();
	}
	# Modify a CGI information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($cgi);
	}
	# Add a CGI information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$tpl->assign("nagios", $oreon->user->get_version());
	$valid = false;
	if ($form->validate())	{
		$cgiObj =& $form->getElement('cgi_id');
		if ($form->getSubmitValue("submitA"))
			$cgiObj->setValue(insertCGIInDB());
		else if ($form->getSubmitValue("submitC"))
			updateCGIInDB($cgiObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cgi_id=".$cgiObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listCGI.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formCGI.ihtml");
	}
?>