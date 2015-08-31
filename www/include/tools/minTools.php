<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	if (isset($_GET["host_id"]))
		$host_id = htmlentities($_GET["host_id"], ENT_QUOTES, "UTF-8");
	else if (isset($_POST["host_id"]))
		$host_id = htmlentities($_POST["host_id"], ENT_QUOTES, "UTF-8");
	else
		$host_id = NULL;

	if (!preg_match("/^[0-9]*$/", $host_id))
		exit();

	$msg ='';

	/*
	 * Database retrieve information for differents elements list we need on the page
	 */
	$Host = array(NULL => NULL);
	$DBRESULT = $pearDB->query("SELECT host_id, host_name, host_address, host_snmp_community, host_snmp_version FROM host WHERE host_id =". $host_id ."");
	$Host = $DBRESULT->fetchRow();
	$DBRESULT->free();
	switch ($o)	{
		case "p" : $tool_cmd_script = "include/tools/ping.php?host=".$Host["host_address"]; $tool = _("Ping"); break;
		case "tr" : $tool_cmd_script = "include/tools/traceroute.php?host=".$Host["host_address"]; $tool = _("Traceroute"); break;
		default :  $tool_cmd_script = "include/tools/ping.php?host=".$Host["host_address"]; $tool = _("Ping"); break;
	}

	$attrsText 		= array("size"=>"15");
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Tools"));

	/*
	 * Command information
	 */
	$form->addElement('header', 'host_information', _("Host Information"));
	$form->addElement('text', 'host_name', _("Host"), $attrsText);
	$form->addElement('text', 'host_ip', _("IP Address"), $attrsText);

	/*
	 * Command information
	 */
	$form->addElement('header', 'information', _("Result"));
	$form->addElement('text', 'command_tool', _("Command"), $attrsText);
	$form->addElement('text', 'command_help', _("Output"), $attrsText);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign('host_name', $Host["host_name"]);
	$tpl->assign('host_ip',$Host["host_address"] );
	$tpl->assign('command_tool',$tool );

	$tpl->assign("initJS", "<script type='text/javascript'>
		display('"._("Please wait...")."<br /><br /><img src=\'./img/icones/48x48/stopwatch.gif\'>','tools');
		loadXMLDoc('".$tool_cmd_script."','tools');
		</script>");

	/*
	 * Apply a template definition
	 */

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("minTools.ihtml");
?>