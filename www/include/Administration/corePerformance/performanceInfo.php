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

	if (!isset($oreon)) {
		exit();
	}

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
	$pollerList = array();
	$defaultPoller = null;
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `name`");
	while ($data = $DBRESULT->fetchRow()) {
		if ($data['localhost']) {
		    $defaultPoller = $data['id'];
		}
	    $pollerList[$data["id"]] = $data["name"];
	}
	$DBRESULT->free();


	isset($_POST['pollers']) && $_POST['pollers'] != "" ? $selectedPoller = $_POST['pollers'] : $selectedPoller = $defaultPoller;
	$form->addElement('select', 'pollers', _("Poller :"), $pollerList, array("onChange" =>"this.form.submit();"));

	if (isset($selectedPoller) && $selectedPoller) {
		$form->setDefaults(array('pollers' => $selectedPoller));
		$host_list[$selectedPoller] = $pollerList[$selectedPoller];
		$tab_server[$selectedPoller] = $pollerList[$selectedPoller];
	} else {
		$form->setDefaults(array('pollers' => null));
	}

	/*
	 * Get Poller List
	 */
	$tab_nagios_server = array();
	$DBRESULT = $pearDB->query("SELECT n.id, n.name " .
								"FROM `nagios_server` n " .
								"WHERE n.ns_activate = '1' " .
								"AND n.id = '".$pearDB->escape($selectedPoller)."' " .
								"ORDER BY n.localhost DESC");

	$perfInfo = array();
	$empty_stats = array();
	$emptyPage = 1;
	while ($nagios = $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
		if ($nagios['id']) {
			$emptyPage = 0;
			$empty_stats[$nagios['id']] = 1;
			$DBRESULT3 = $pearDBO->query("SELECT * FROM `nagios_stats` WHERE instance_id = '".$nagios['id']."'");
			while ($row = $DBRESULT3->fetchRow()) {
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
				$empty_stats[$nagios['id']] = 0;
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

	$path = "./include/Administration/corePerformance/";

	/*
	 * Smarty template Init
	 */

	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());

	$yes_no_tab = array("0" => "No", "1" => "Yes");

	/*
	 * Assign values
	 */
	$tpl->assign("perfInfo", $perfInfo);
	$tpl->assign("host_actively_checked", _("Actively Checked"));
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

	$tpl->assign("empty_stats", $empty_stats);
	$tpl->assign("empty_stats_label", _("No stats available for this poller. Is it currently running?"));
	$tpl->assign("emptyPage", $emptyPage);
	$tpl->assign("empty_page_label", _("No poller selected. Please select a poller to see reports."));


	if (isset($host_list) && $host_list) {
		$tpl->assign('host_list', $host_list);
	}
	if (isset($tab_server) && $tab_server) {
		$tpl->assign('tab_server', $tab_server);
	}

	$tpl->assign("performanceInfoLabel", _("Monitoring Engine performance informations"));
	$tpl->display("performanceInfo.ihtml");
?>