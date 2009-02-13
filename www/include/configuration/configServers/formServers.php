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

	#
	## Database retrieve information for Nagios
	#
	$nagios = array();
	if (($o == "c" || $o == "w") && $server_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `id` = '".$server_id."' LIMIT 1");
		# Set base value
		$cfg_server = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	# nagios servers comes from DB 
	$nagios_servers = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY name");
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
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a poller"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a poller Configuration"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a poller Configuration"));

	#
	## Nagios Configuration basic information
	#
	$form->addElement('header', 'information', _("Satellite configuration"));
	$form->addElement('text', 'name', _("Sattelite Name"), $attrsText);
	$form->addElement('text', 'ns_ip_address', _("IP Address"), $attrsText);
	$form->addElement('text', 'init_script', _("Nagios Init Script"), $attrsText);
	$form->addElement('text', 'nagios_bin', _("nagios Binary"), $attrsText);
	$form->addElement('text', 'nagiostats_bin', _("nagiostats Binary"), $attrsText);
		
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, _("Yes"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, _("No"), '0');
	$form->addGroup($Tab, 'localhost', _("Localhost ?"), '&nbsp;');	
		
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Enabled"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Disabled"), '0');
	$form->addGroup($Tab, 'ns_activate', _("Status"), '&nbsp;');	
		
	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array(
		"name"=>'',
		"localhost"=>'0',
		"ns_ip_address"=>"127.0.0.1",
		"nagios_bin"=>"/usr/sbin/nagios2",
		"nagiostats_bin"=>"/usr/sbin/nagiostats",
		"init_script"=>"/etc/init.d/nagios".$oreon->user->get_version(),
		"ns_activate"=>'1'));
	} else {
		if (isset($cfg_server))
			$form->setDefaults($cfg_server);
	}	
	$form->addElement('hidden', 'id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	# Form Rules
	$form->addRule('nagios_name', _("Name is already in use"), 'exist');
	
	#End of form definition
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a nagios information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$server_id."'"));
	    $form->setDefaults($nagios);
		$form->freeze();
	} else if ($o == "c")	{# Modify a nagios information
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($nagios);
	} else if ($o == "a")	{# Add a nagios information
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
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
		$tpl->assign('Servers_Informations', _("Servers Informations"));
		$tpl->display("formServers.ihtml");
	}
?>