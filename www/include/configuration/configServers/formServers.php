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
	if (($o == "c" || $o == "w") && $server_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `id` = '".$server_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cfg_server = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	# nagios servers comes from DB 
	$nagios_servers = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY name");
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
	$form->addElement('header', 'information', $lang['ns_infos']);
	$form->addElement('text', 'name', $lang["ns_name"], $attrsText);
	$form->addElement('text', 'ns_ip_address', $lang["ns_ip_address"], $attrsText);
	$form->addElement('text', 'user', $lang["user"], $attrsText);
	$form->addElement('text', 'password', $lang["n2db_db_pass"], $attrsText);
	$form->addElement('text', 'ns_key', $lang["ns_key"], $attrsText3);
		
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, $lang["yes"], '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, $lang["no"], '0');
	$form->addGroup($Tab, 'localhost', $lang["ns_localhost"], '&nbsp;');	
		
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, $lang["enable"], '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, $lang["disable"], '0');
	$form->addGroup($Tab, 'ns_activate', $lang["status"], '&nbsp;');	
		
	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array(
		"name"=>'',
		"localhost"=>'0',
		"ns_ip_address"=>"127.0.0.1",
		"ns_http_suffix"=>"/oreon/",
		"ns_http_port"=>"80",
		"ns_key"=>'',
		"ns_activate"=>'1'));
	} else {
		if (isset($cfg_server))
			$form->setDefaults($cfg_server);
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
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$server_id."'"));
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
			insertServerInDB();
		else if ($form->getSubmitValue("submitC"))
			updateServerInDB($nagiosObj->getValue());
		$o = NULL;
		$valid = true;
	}
	if ($valid)
		require_once($path."listServers.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('lang', $lang);
		$tpl->display("formServers.ihtml");
	}
?>