<?php
/*
 * Copyright 2005-2011 MERETHIS
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
	if (($o == "c" || $o == "w") && $id)	{	
		$DBRESULT = $pearDB->query("SELECT * FROM cfg_ndomod WHERE id = '".$id."' LIMIT 1");
		# Set base value
		$cfg_ndomod = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	/*
	 * nagios servers comes from DB 
	 */
	$nagios_servers = array();
	$DBRESULT = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
	while ($nagios_server = $DBRESULT->fetchRow())
		$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
	$DBRESULT->free();
	
	/*
	 * Var information to format the element
	 */
	$attrsText		= array("size"=>"30");
	$attrsText2 	= array("size"=>"50");
	$attrsText3 	= array("size"=>"10");
	$attrsText4 	= array("size"=>"5");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	/*
	 *  Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a ndomod Configuration File"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a ndomod Configuration File"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a ndomod Configuration File"));

	/*
	 * Nagios Configuration basic information
	 */
	$form->addElement('header', 'information', _("ndomod configuration"));
	$form->addElement('text', 'description', _("Description"), $attrsText);
	$form->addElement('select', 'ns_nagios_server', _("Instance Name"), $nagios_servers);
	$form->addElement('select', 'output_type', _("Interface Type"), array("file"=>"file","tcpsocket"=>"tcpsocket","unixsocket"=>"unixsocket"));
	$form->addElement('text', 'output', _("Output"), $attrsText);
	$form->addElement('text', 'tcp_port', _("TCP Port"), $attrsText4);
	$form->addElement('text', 'output_buffer_items', _("Buffer size of the interface"), $attrsText4);
	$form->addElement('text', 'buffer_file', _("Buffer File"), $attrsText);
	$form->addElement('text', 'file_rotation_interval', _("Rotation interval"), $attrsText4);
	$form->addElement('text', 'file_rotation_command', _("Rotation command"), $attrsText);
	$form->addElement('text', 'file_rotation_timeout', _("Rotation timeout"), $attrsText4);
	$form->addElement('text', 'reconnect_interval', _("Reconnection interval"), $attrsText4);
	$form->addElement('text', 'reconnect_warning_interval', _("Notification interval in case of disconnection"), $attrsText4);
	$form->addElement('text', 'data_processing_options', _("Data processing options"), $attrsText3);
	$form->addElement('text', 'config_output_options', _("Output options"), $attrsText3);
	
	$Tab = array();
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Enabled"), '1');
	$Tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Disabled"), '0');
	$form->addGroup($Tab, 'activate', _("Status"), '&nbsp;');	
		
	if (isset($_GET["o"]) && $_GET["o"] == 'a'){
		$form->setDefaults(array("description"=>'',
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
	
	$tpl->assign("informations1", _("Description"));
	$tpl->assign("informations2", _("Output"));
	$tpl->assign("informations3", _("Rotations"));
	$tpl->assign("informations4", _("Database Disconnexions"));
	$tpl->assign("informations5", _("Misc"));
	
	if ($o == "w")	{
		/*
		 * Just watch a nagios information
		 */
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$ndomod_id."'"));
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
			insertNdomodInDB();
		else if ($form->getSubmitValue("submitC"))
			updateNdomodInDB($nagiosObj->getValue());
		$o = NULL;
		$valid = true;
	}
	if ($valid)
		require_once($path."listNdomod.php");
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
		$tpl->display("formNdomod.ihtml");
	}
?>