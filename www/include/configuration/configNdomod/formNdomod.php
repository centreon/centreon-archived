<?php
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
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_ndomod WHERE id = '".$id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cfg_ndomod = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
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
	$form->addElement('header', 'information', $lang['ndomod_infos']);
	$form->addElement('text', 'description', $lang["nmod_decription"], $attrsText);
	$form->addElement('select', 'ns_nagios_server', $lang["nmod_nagios_server"], $nagios_servers);
	$form->addElement('text', 'instance_name', $lang["nmod_instance_name"], $attrsText);
	$form->addElement('select', 'output_type', $lang["nmod_output_type"], array("file"=>"file","tcpsocket"=>"tcpsocket","unixsocket"=>"unixsocket"));
	$form->addElement('text', 'output', $lang["nmod_output"], $attrsText);
	$form->addElement('text', 'tcp_port', $lang["nmod_tcp_port"], $attrsText3);
	$form->addElement('text', 'output_buffer_items', $lang["nmod_output_buffer_items"], $attrsText);

	$form->addElement('text', 'file_rotation_interval', $lang["nmod_file_rotation_interval"], $attrsText);
	$form->addElement('text', 'file_rotation_command', $lang["nmod_file_rotation_command"], $attrsText);
	$form->addElement('text', 'file_rotation_timeout', $lang["nmod_file_rotation_timeout"], $attrsText);

	$form->addElement('text', 'reconnect_interval', $lang["nmod_reconnect_interval"], $attrsText);
	$form->addElement('text', 'reconnect_warning_interval', $lang["nmod_reconnect_warning_interval"], $attrsText);
	
	$form->addElement('text', 'data_processing_options', $lang["nmod_data_processing_options"], $attrsText3);
	$form->addElement('text', 'config_output_options', $lang["nmod_config_output_options"], $attrsText3);
	
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["enable"], '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["disable"], '0');
	$form->addGroup($Tab, 'activate', $lang["status"], '&nbsp;');	
		
	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array(
		"description"=>'',
		"instance_name"=>'',
		"output"=>"127.0.0.1",
		"output_type"=>"tcpsocket",
		"tcp_port"=>"5668",
		"output_buffer_items"=>'5000',
		"file_rotation_interval"=>'14400',
		"file_rotation_command"=>'',
		"file_rotation_timeout"=>'60',
		"reconnect_interval"=>'15',
		"reconnect_warning_interval"=>'900',
		"data_processing_options"=>'-1',
		"config_output_options"=>'3',
		"activate"=>'1'));
	} else {
		$form->setDefaults($cfg_ndomod);
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
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$ndomod_id."'"));
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
			insertNdomodInDB();
		else if ($form->getSubmitValue("submitC"))
			updateNdomodInDB($nagiosObj->getValue());
		$o = NULL;
		$valid = true;
	}
	if ($valid)
		require_once($path."listNdomod.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('lang', $lang);
		$tpl->display("formNdomod.ihtml");
	}
?>