<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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

	if (isset($_GET["host_id"]))
		$host_id = $_GET["host_id"];
	else if (isset($_POST["host_id"]))
		$host_id = $_POST["host_id"];
	else
		$host_id = NULL;

	$msg ='';

	## Database retrieve information for differents elements list we need on the page
	#
	# Host comes from DB
	$Host = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_address, host_snmp_community, host_snmp_version FROM host WHERE host_id =". $host_id ."");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$Host  = array(NULL=>NULL);
	$DBRESULT->fetchInto($Host);
	$DBRESULT->free();
	switch ($o)	{
		case "p" : $tool_cmd_script = "include/tools/ping.php?host=".$Host["host_address"]; $tool = $lang ["m_mon_tools_ping"]; break;
		case "tr" : $tool_cmd_script = "include/tools/traceroute.php?host=".$Host["host_address"]; $tool = $lang ["m_mon_tools_tracert"]; break;
		default :  $tool_cmd_script = "include/tools/ping.php?host=".$Host["host_address"]; $tool = $lang ["m_mon_tools_ping"]; break;
	}

	$attrsText 		= array("size"=>"15");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',$lang["m_mon_tools"]);

	#
	## Command information
	#
	$form->addElement('header', 'host_information', $lang['m_mon_host_info']);
	$form->addElement('text', 'host_name', $lang ["m_mon_host"], $attrsText);
	$form->addElement('text', 'host_ip', $lang ["m_mon_address_ip"], $attrsText);

	#
	## Command information
	#
	$form->addElement('header', 'information', $lang["m_mon_tools_result"]);
	$form->addElement('text', 'command_tool', $lang["m_mon_tools_command"], $attrsText);
	$form->addElement('text', 'command_help', $lang['cmd_output'], $attrsText);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign('host_name', $Host["host_name"]);
	$tpl->assign('host_ip',$Host["host_address"] );
	$tpl->assign('command_tool',$tool );

	$tpl->assign("initJS", "<script type='text/javascript'>
		display('".$lang ["m_mon_waiting"]."<br><br><img src=\'./img/icones/48x48/stopwatch.gif\'>','tools');
//		display('".$lang ["m_mon_waiting"]."','tools');
		loadXMLDoc('".$tool_cmd_script."','tools');
		</script>");

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("minTools.ihtml");
?>