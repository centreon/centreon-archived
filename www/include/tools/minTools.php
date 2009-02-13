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

	if (isset($_GET["host_id"]))
		$host_id = $_GET["host_id"];
	else if (isset($_POST["host_id"]))
		$host_id = $_POST["host_id"];
	else
		$host_id = NULL;

	$msg ='';

	/*
	 * Database retrieve information for differents elements list we need on the page
	 */
	$Host = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_address, host_snmp_community, host_snmp_version FROM host WHERE host_id =". $host_id ."");
	$Host = array(NULL=>NULL);
	$Host =& $DBRESULT->fetchRow();
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

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("minTools.ihtml");
?>