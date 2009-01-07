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
	
	require_once "./include/monitoring/common-Func.php";

	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	unset($tpl);
	unset($path);

	/*
	 * Time period select
	 */	
	$form = new HTML_QuickForm('form', 'post', "?p=".$p);	
	$time_period = array("last3hours"	=> _("Last 3 hours"),
						"today" 		=> _("Today"),
						"yesterday" 	=> _("Yesterday"),
						"last4days" 	=> _("Last 4 days"),
						"lastweek" 		=> _("Last week"),
						"lastmonth" 	=> _("Last month"),
						"last6month" 	=> _("Last 6 months"),
						"lastyear" 		=> _("Last year"));
	
	$selTP =& $form->addElement('select', 'start', _("Select time period :"), $time_period, array("onChange" =>"this.form.submit();"));	
	if (isset($_POST["start"])) {		
		$form->setDefaults(array('start' => $_POST["start"]));
	} else {
		$form->setDefaults(array('start' => "Today"));
	}

	/*
	 * Get Poller List
	 */
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `localhost` DESC");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value){
		$host_list[$key] = $value;
		$tab_server[$cpt] = $value;
		$cpt++;
	}

	$options = array(	"active_host_check" => "nagios_active_host_execution.rrd", 
						"active_host_last" => "nagios_active_host_last.rrd",
						"host_latency" => "nagios_active_host_latency.rrd",
						"active_host_check" => "nagios_active_service_execution.rrd", 
						"active_service_last" => "nagios_active_service_last.rrd", 
						"service_latency" => "nagios_active_service_latency.rrd", 
						"cmd_buffer" => "nagios_cmd_buffer.rrd", 
						"host_states" => "nagios_hosts_states.rrd", 
						"service_states" => "nagios_services_states.rrd");
		
	$path = "./include/nagiosStats/";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	/*
	 * Assign values
	 */
	 
	$tpl->assign('form', $renderer->toArray());
	
	if (isset($_POST["start"]))
		$tpl->assign('startPeriod', $_POST["start"]);		
	
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
		
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);	
		
	$tpl->assign("p", $p);
	$tpl->assign("options", $options);
	$tpl->assign("session_id", session_id());
	$tpl->display("nagiosStats.ihtml");
?>