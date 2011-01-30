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

	#
	## Database retrieve information for Nagios
	#
	$cgi = array();
	if (($o == "c" || $o == "w") && $cgi_id)	{	
		$DBRESULT = $pearDB->query("SELECT * FROM cfg_cgi WHERE cgi_id = '".$cgi_id."' LIMIT 1");
		# Set base value
		$cgi = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array();
	$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command ORDER BY command_name");
	$checkCmds = array(NULL=>NULL);
	while ($checkCmd = $DBRESULT->fetchRow())
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
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a CGI Configuration File"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a CGI Configuration File"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a CGI Configuration File"));

	#
	## CGI Configuration basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'cgi_name', _("CGI File Name"), $attrsText);
	$form->addElement('textarea', 'cgi_comment', _("Comments"), $attrsTextarea);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'cgi_activate', null, _("Enabled"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'cgi_activate', null, _("Disabled"), '0');
	$form->addGroup($nagTab, 'cgi_activate', _("Status"), '&nbsp;');	
	
	## Part 1
	$form->addElement('text', 'main_config_file', _("Main Configuration File Location"), $attrsText2);
	$form->addElement('text', 'physical_html_path', _("Physical HTML Path"), $attrsText2);
	$form->addElement('text', 'url_html_path', _("URL HTML Path"), $attrsText2);
	
	## Part 2
	$form->addElement('text', 'nagios_check_command', _("Nagios Process Check Command"), $attrsText2);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_authentication', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_authentication', null, _("No"), '0');
	$form->addGroup($nagTab, 'use_authentication', _("Authentication Usage"), '&nbsp;');
	$form->addElement('text', 'default_user_name', _("Default User Name"), $attrsText);
	$form->addElement('textarea', 'authorized_for_system_information', _("System/Process Information Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_system_commands', _("System/Process Command Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_configuration_information', _("Configuration Information Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_hosts', _("Global Host Information Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_host_commands', _("Global Host Command Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_services', _("Global Service Information Access"), $attrsTextarea);
	$form->addElement('textarea', 'authorized_for_all_service_commands', _("Global Service Command Access"), $attrsTextarea);
	
	## Part 3
	$form->addElement('text', 'statusmap_background_image', _("Statusmap CGI Background Image"), $attrsText2);
	$form->addElement('select', 'default_statusmap_layout', _("Default Statusmap Layout Method"), array(0=>"User-defined coordinates", 1=>"Depth layers", 2=>"Collapsed tree", 3=>"Balanced tree", 4=>"Circular", 5=>"Circular (Marked Up)", 6=>"Circular (Balloon)"));
	$form->addElement('text', 'statuswrl_include', _("Statuswrl CGI Include World"), $attrsText2);
	$form->addElement('select', 'default_statuswrl_layout', _("Default Statuswrl Layout Method"), array(0=>"User-defined coordinates", 1=>"Depth layers", 2=>"Collapsed tree", 3=>"Balanced tree", 4=>"Circular"));

	## Part 4
	$form->addElement('text', 'refresh_rate', _("CGI Refresh Rate"), $attrsText3);
	$form->addElement('text', 'host_unreachable_sound', _("Host Unreachable Sound"), $attrsText2);
	$form->addElement('text', 'host_down_sound', _("Host Down Sound"), $attrsText2);
	$form->addElement('text', 'service_critical_sound', _("Service Critical Sound"), $attrsText2);
	$form->addElement('text', 'service_warning_sound', _("Service Warning Sound"), $attrsText2);
	$form->addElement('text', 'service_unknown_sound', _("Service Unknown Sound"), $attrsText2);

	## Part 5
	$form->addElement('textarea', 'ping_syntax', _("Ping Syntax"), $attrsTextarea);
		
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	
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
	$redirect = $form->addElement('hidden', 'o');
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
	$form->addRule('cgi_name', _("Compulsory Name"), 'required');
	$form->addRule('cgi_comment', _("Required Field"), 'required');
	$form->registerRule('exist', 'callback', 'testCgiExistence');
	$form->addRule('cgi_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a CGI information
	if ($o == "w")	{
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cgi_id=".$cgi_id."'"));
	    $form->setDefaults($cgi);
		$form->freeze();
	}
	# Modify a CGI information
	else if ($o == "c")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cgi);
	}
	# Add a CGI information
	else if ($o == "a")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign("nagios", $oreon->user->get_version());
	$valid = false;
	if ($form->validate())	{
		$cgiObj = $form->getElement('cgi_id');
		if ($form->getSubmitValue("submitA"))
			$cgiObj->setValue(insertCGIInDB());
		else if ($form->getSubmitValue("submitC"))
			updateCGIInDB($cgiObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cgi_id=".$cgiObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listCGI.php");
	else	{
		#Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formCGI.ihtml");
	}
?>