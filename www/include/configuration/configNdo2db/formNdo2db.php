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
	## Database retrieve information for Nagios
	#
	$nagios = array();
	if (($o == "c" || $o == "w") && $id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_ndo2db WHERE id = '".$id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cfg_ndo2db = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	# Database retrieve information for differents elements list we need on the page
	
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array();
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$checkCmds = array(NULL=>NULL);
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	
	# nagios servers comes from DB 
	$nagios_servers = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($nagios_server = $DBRESULT->fetchRow())
		$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
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
		$form->addElement('header', 'title', $lang["nagios_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["nagios_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["nagios_view"]);

	#
	## Nagios Configuration basic information
	#
	$form->addElement('header', 'information', $lang['ndo2db_infos']);
	$form->addElement('text', 'description', $lang["n2db_decription"], $attrsText);
	$form->addElement('select', 'ns_nagios_server', $lang["n2db_nagios_server"], $nagios_servers);
	$form->addElement('select', 'socket_type', $lang["n2db_socket_type"], array("unix"=>"unix","tcp"=>"tcp"));
	$form->addElement('text', 'socket_name', $lang["n2db_socket_name"], $attrsText2);
	$form->addElement('text', 'tcp_port', $lang["n2db_tcp_port"], $attrsText3);
	
	# DB configuration
	$form->addElement('select', 'db_type', $lang["n2db_db_type"], array("mysql"=>"MySQL","pgsql"=>"PostgreSQL"));
	$form->addElement('text', 'db_host', $lang["n2db_db_host"], $attrsText);
	$form->addElement('text', 'db_name', $lang["n2db_db_name"], $attrsText);
	$form->addElement('text', 'db_port', $lang["n2db_db_port"], $attrsText);
	$form->addElement('text', 'db_prefix', $lang["n2db_db_prefix"], $attrsText);
	$form->addElement('text', 'db_user', $lang["n2db_db_user"], $attrsText);
	$form->addElement('text', 'db_pass', $lang["n2db_db_pass"], $attrsText);
	
	# DB retention
	$form->addElement('text', 'max_timedevents_age', $lang["n2db_timedevents_age"], $attrsText3);
	$form->addElement('text', 'max_systemcommands_age', $lang["n2db_systemcommands_age"], $attrsText3);
	$form->addElement('text', 'max_servicechecks_age', $lang["n2db_servicechecks_age"], $attrsText3);
	$form->addElement('text', 'max_hostchecks_age', $lang["n2db_hostchecks_age"], $attrsText3);
	$form->addElement('text', 'max_eventhandlers_age', $lang["n2db_eventhandlers_age"], $attrsText3);
	
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["enable"], '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["disable"], '0');
	$form->addGroup($Tab, 'activate', $lang["status"], '&nbsp;');	
		
	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array(
		"description"=>'',
		"socket_type"=>'unix',
		"socket_name"=>"/usr/local/nagios/var/ndo.sock",
		"tcp_port"=>"5668",
		"db_type"=>'mysql',
		"db_host"=>'',
		"db_port"=>'3306',
		"db_name"=>'nagios',
		"db_prefix"=>'ndo_',
		"db_user"=>'ndo',
		"db_pass"=>'ndo',
		"max_timedevents_age"=>'1440',
		"max_systemcommands_age"=>'10080',
		"max_servicechecks_age"=>'10080',
		"max_hostchecks_age"=>'10080',
		"max_eventhandlers_age"=>'44640',
		"activate"=>'1'));
	} else {
		$form->setDefaults($cfg_ndo2db);
	}	
	$form->addElement('hidden', 'id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	# Form Rules
	$form->addRule('nagios_name', $lang['ErrAlreadyExist'], 'exist');
	
	#End of form definition
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a nagios information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$ndo2db_id."'"));
	    $form->setDefaults($nagios);
		$form->freeze();
	} else if ($o == "c")	{# Modify a nagios information
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($nagios);
	} else if ($o == "a")	{# Add a nagios information
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$nagiosObj =& $form->getElement('id');
		if ($form->getSubmitValue("submitA"))
			insertNdo2dbInDB();
		else if ($form->getSubmitValue("submitC"))
			updateNdo2dbInDB($nagiosObj->getValue());
		$o = NULL;
		$valid = true;
	}
	if ($valid)
		require_once($path."listNdo2db.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('lang', $lang);	
		$tpl->assign('sort1', $lang['n2db_general']);		
		$tpl->assign('sort2', $lang['n2db_database']);		
		$tpl->assign('sort3', $lang['n2db_retention']);
		$tpl->display("formNdo2db.ihtml");
	}
?>