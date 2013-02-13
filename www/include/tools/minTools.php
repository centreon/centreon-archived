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