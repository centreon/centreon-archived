<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon))
		exit();

	/*
	 * Database retrieve information for Nagios
	 */
	$nagios = array();
	if (($o == "c" || $o == "w") && $server_id)	{
		$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `id` = '".$server_id."' LIMIT 1");
		# Set base value
		$cfg_server = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}

	/*
	 * nagios servers comes from DB
	 */
	$nagios_servers = array();
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` ORDER BY name");
	while($nagios_server = $DBRESULT->fetchRow())
		$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
	$DBRESULT->free();

	$attrsText		= array("size"=>"30");
	$attrsText2 	= array("size"=>"50");
	$attrsText3 	= array("size"=>"5");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a poller"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a poller Configuration"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a poller Configuration"));

	/*
	 * Headers
	 */
	$form->addElement('header', 'Server_Informations', _("Server Information"));
	$form->addElement('header', 'SSH_Informations', _("SSH Information"));
	$form->addElement('header', 'Nagios_Informations', _("Nagios Information"));
	$form->addElement('header', 'Misc', _("Miscelleneous"));
	$form->addElement('select', 'monitoring_engine', _("Engine"), array("ICINGA" => "Icinga", "NAGIOS" => "Nagios", "SHINKEN" => "Shinken"));

	/*
	 * Nagios Configuration basic information
	 */
	$form->addElement('header', 'information', _("Satellite configuration"));
	$form->addElement('text', 'name', _("Sattelite Name"), $attrsText);
	$form->addElement('text', 'ns_ip_address', _("IP Address"), $attrsText);
	$form->addElement('text', 'init_script', _("Nagios Init Script"), $attrsText);

	$form->addElement('text', 'nagios_bin', _("nagios Binary"), $attrsText2);
	$form->addElement('text', 'nagiostats_bin', _("nagiostats Binary"), $attrsText2);
	$form->addElement('text', 'nagios_perfdata', _("Perfdata file"), $attrsText2);

	$form->addElement('text', 'ssh_private_key', _("SSH Private key"), $attrsText2);
	$form->addElement('text', 'ssh_port', _("SSH port"), $attrsText3);

	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, _("Yes"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'localhost', null, _("No"), '0');
	$form->addGroup($Tab, 'localhost', _("Localhost ?"), '&nbsp;');

	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'is_default', null, _("Yes"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'is_default', null, _("No"), '0');
	$form->addGroup($Tab, 'is_default', _("Is default poller ?"), '&nbsp;');

	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Enabled"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Disabled"), '0');
	$form->addGroup($Tab, 'ns_activate', _("Status"), '&nbsp;');

	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array(
		"name" => '',
		"localhost" => '0',
		"ns_ip_address" => "127.0.0.1",
		"nagios_bin" => "/usr/sbin/nagios",
		"nagiostats_bin" => "/usr/sbin/nagiostats",
		"monitoring_engine"  =>  $centreon->optGen["monitoring_engine"],
		"init_script" => "/etc/init.d/nagios",
		"ns_activate" => '1',
		"is_default"  =>  '0',
		"ssh_port"  =>  '22',
		"ssh_private_key"  =>  '~/.ssh/rsa.id',
		"nagios_perfdata"  =>  "/var/log/nagios/service-perfdata"));
	} else {
		if (isset($cfg_server))
			$form->setDefaults($cfg_server);
	}
	$form->addElement('hidden', 'id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	$form->addRule('nagios_name', _("Name is already in use"), 'exist');

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	if ($o == "w")	{
		/*
		 * Just watch a nagios information
		 */
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$server_id."'"));
	    $form->setDefaults($nagios);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a nagios information
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($nagios);
	} else if ($o == "a")	{
		/*
		 * Add a nagios information
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$nagiosObj = $form->getElement('id');
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
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formServers.ihtml");
	}
?>