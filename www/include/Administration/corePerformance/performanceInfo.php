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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/Administration/SchedulePerformance/performanceInfo.php $
 * SVN : $Id: performanceInfo.php 10025 2010-02-19 14:29:02Z jmathis $
 *
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
	$pollerList = array(null => null);
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `localhost` DESC");
	while ($data = $DBRESULT->fetchRow()) {
		$pollerList[$data["id"]] = $data["name"];
	}
	$DBRESULT->free();
	$form->addElement('select', 'pollers', _("Poller :"), $pollerList, array("onChange" =>"this.form.submit();"));
	if (isset($_POST["pollers"])) {
		$form->setDefaults(array('pollers' => htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8")));
		$host_list[htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8")] = $pollerList[htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8")];
		$tab_server[htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8")] = $pollerList[htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8")];
	} else {
		$form->setDefaults(array('pollers' => null));
	}

	/*
	 * Get Poller List
	 */
	$tab_nagios_server = array();
	$DBRESULT = $pearDB->query("SELECT n.id, ndomod.instance_name, n.name " .
								"FROM `cfg_ndomod` ndomod, `nagios_server` n " .
								"WHERE ndomod.activate = '1' AND n.ns_activate = '1' " .
								"AND ndomod.ns_nagios_server = n.id AND n.id = '".(isset($_POST["pollers"]) && $_POST["pollers"] != "" ? htmlentities($_POST["pollers"], ENT_QUOTES, "UTF-8") : 0)."' " .
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
	$tpl->assign("empty_page_label", _("No poller selected. Please selection a poller to see reports."));


	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);

	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);

	$tpl->assign("performanceInfoLabel", _("Nagios Performance Information"));
	$tpl->display("performanceInfo.ihtml");
?>