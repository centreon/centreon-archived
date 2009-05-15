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
	require_once "./class/centreonDB.class.php";

	unset($tpl);
	unset($path);
	
	$pearDBO = new CentreonDB("centstorage");
	
	$form = new HTML_QuickForm('form', 'post', "?p=".$p);	

	/*
	 * Get Poller List
	 */
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT ndomod.id, ndomod.instance_name, n.name " .
								"FROM `cfg_ndomod` ndomod, `nagios_server` n " .
								"WHERE ndomod.activate = '1' " .
								"AND ndomod.ns_nagios_server = n.id " .
								"ORDER BY n.localhost DESC");
	$perfInfo = array();					
	while ($nagios =& $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];		
		if ($nagios['id']) {
			$DBRESULT3 =& $pearDBO->query("SELECT * FROM `nagios_stats` WHERE instance_id = '".$nagios['id']."'");
			while ($row =& $DBRESULT3->fetchRow()) {
				switch($row['stat_label']) {
					case "Hosts Status" :
						$perfInfo[$row['instance_id']]["hs"][$row['stat_key']] = $row['stat_value'];						
						break;
					case "Host Actively Checked" :
						$perfInfo[$row['instance_id']]["hac"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Host Check Execution Time" :
						$perfInfo[$row['instance_id']]["hcet"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Host Check Latency" :
						$perfInfo[$row['instance_id']]["hcl"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Services Status" :
						$perfInfo[$row['instance_id']]["ss"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Service Actively Checked" :
						$perfInfo[$row['instance_id']]["sac"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Service Check Execution Time" :
						$perfInfo[$row['instance_id']]["scet"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Service Check Latency" :
						$perfInfo[$row['instance_id']]["scl"][$row['stat_key']] = $row['stat_value'];
						break;
					case "Buffer Usage" :
						$perfInfo[$row['instance_id']]["bu"][$row['stat_key']] = $row['stat_value'];
						break;
				}
				$perfInfo[$row['instance_id']][$row['stat_key']] = $row['stat_value'];
			}
		}		
	}	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value){
		$host_list[$key] = $value;
		$tab_server[$cpt] = $value;
		$cpt++;
	}

	$path = "./include/nagiosStats/";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);		
	$yes_no_tab = array("0" => "No", "1" => "Yes");
	
	/*
	 * Assign values
	 */		
	$tpl->assign("perfInfo", $perfInfo); 
	$tpl->assign("service_actively_checked", _("Services Actively Checked"));
	$tpl->assign("host_actively_checked", _("Hosts Actively Checked"));	
	$tpl->assign("time_frame", _("Time Frame"));
	$tpl->assign("svc_checked", _("Services Checked"));
	$tpl->assign("host_checked", _("Hosts Checked"));
	
	$tpl->assign("exec_time", _("Check Execution Time"));
	$tpl->assign("latency", _("Check Latency"));
	$tpl->assign("status", _("Status"));
	
	$tpl->assign("Hosts", _("Hosts"));
	$tpl->assign("Services", _("Services"));
	
	$tpl->assign("Min", _("Min"));
	$tpl->assign("Max", _("Max"));
	$tpl->assign("Avg", _("Average"));
	$tpl->assign("sec", _("sec"));
	$tpl->assign("loe1", _("<= 1 min"));
	$tpl->assign("loe5", _("<= 5 min"));
	$tpl->assign("loe15", _("<= 15 min"));
	$tpl->assign("loe60", _("<= 60 min"));
	
	$tpl->assign("up", _("Up"));
	$tpl->assign("down", _("Down"));
	$tpl->assign("unreachable", _("Unreachable"));
	$tpl->assign("ok", _("OK"));
	$tpl->assign("warning", _("Warning"));
	$tpl->assign("critical", _("Critical"));
	$tpl->assign("unknown", _("Unknown"));
	
	$tpl->assign("available", _("Total Available"));
	$tpl->assign("in_use", _("In Use"));
	$tpl->assign("max_used", _("Max Used"));
	$tpl->assign("buffer_usage", _("Buffer Usage"));
	
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
		
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);		
		
	$tpl->assign("performanceInfoLabel", _("Nagios Performance Information"));		
	$tpl->display("performanceInfo.ihtml");
?>