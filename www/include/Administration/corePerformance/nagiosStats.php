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
		$pollerName = $pollerList[$selectedPoller];
	} else {
		$form->setDefaults(array('pollers' => null));
	}

	/*
	 * Get Periode
	 */
	$time_period = array("last3hours"	=> _("Last 3 hours"),
						"today" 		=> _("Today"),
						"yesterday" 	=> _("Yesterday"),
						"last4days" 	=> _("Last 4 days"),
						"lastweek" 		=> _("Last week"),
						"lastmonth" 	=> _("Last month"),
						"last6month" 	=> _("Last 6 months"),
						"lastyear" 		=> _("Last year"));

	$selTP = $form->addElement('select', 'start', _("Period :"), $time_period, array("onChange" =>"this.form.submit();"));
	if (isset($_POST["start"])) {
		$form->setDefaults(array('start' => $_POST["start"]));
	} else {
		$form->setDefaults(array('start' => "today"));
	}

	$options = array(	"active_host_check" => "nagios_active_host_execution.rrd",
						"active_service_check" => "nagios_active_service_execution.rrd",
						"active_host_last" => "nagios_active_host_last.rrd",
						"active_service_last" => "nagios_active_service_last.rrd",
						"host_latency" => "nagios_active_host_latency.rrd",
						"service_latency" => "nagios_active_service_latency.rrd",
						"host_states" => "nagios_hosts_states.rrd",
						"service_states" => "nagios_services_states.rrd",
						"cmd_buffer" => "nagios_cmd_buffer.rrd");

	$path = "./include/Administration/corePerformance/";

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

	if (isset($_POST["start"])) {
		$tpl->assign('startPeriod', $_POST["start"]);
	} else {
	    $tpl->assign('startPeriod', 'today');
	}
	if (isset($host_list) && $host_list) {
		$tpl->assign('host_list', $host_list);
	}
	if (isset($tab_server) && $tab_server) {
		$tpl->assign('tab_server', $tab_server);
	}

	$tpl->assign("p", $p);
	if (isset($pollerName)) {
		$tpl->assign("pollerName", $pollerName);
	}
	$tpl->assign("options", $options);
	$tpl->assign("session_id", session_id());
	$tpl->display("nagiosStats.ihtml");
?>