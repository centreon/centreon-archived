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

	if (!isset($centreon)) {
		exit();
	}

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once "./include/common/autoNumLimit.php";

	if (isset($_POST["hostgroup"]))
	  $hostgroup = $_POST["hostgroup"];
	else if (isset($_GET["hostgroup"]))
	   $hostgroup = $_GET["hostgroup"];
	else if (isset($centreon->hostgroup) && $centreon->hostgroup)
	   $hostgroup = $centreon->hostgroup;
	else
	   $hostgroup = 0;

	if (isset($_POST["search_host"]))
		$host_name = $_POST["search_host"];
	else if (isset($_GET["search_host"]))
		$host_name = $_GET["search_host"];
	else
		$host_name = NULL;

	if (isset($_POST["search_output"]))
		$search_output = $_POST["search_output"];
	else if (isset($_GET["search_output"]))
		$search_output = $_GET["search_output"];
	else
		$search_output = NULL;

	if (isset($_POST["view_all"]))
		$view_all = 1;
	else if (isset($_GET["view_all"]))
		$view_all = 1;
	else
		$view_all = 0;

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

	include_once("./class/centreonDB.class.php");

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDB("ndo");
		$ndo_base_prefix = getNDOPrefix();
	}

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "template/");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$hostStr = $centreon->user->access->getHostsString("NAME", ($oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO));

	/************************************
	 * Hosts Downtimes
	 */

	if ($view_all == 1) {
		$downtimeTable = "downtimehistory";
	} else {
		$downtimeTable = "scheduleddowntime";
	}

	if ($oreon->broker->getBroker() == "ndo") {
		$request =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT dtm.internal_downtime_id, unix_timestamp(dtm.entry_time), " .
				"dtm.duration, dtm.author_name, dtm.comment_data, dtm.is_fixed, unix_timestamp(dtm.scheduled_start_time) AS scheduled_start_time, ".
				"unix_timestamp(dtm.scheduled_end_time) AS scheduled_end_time, obj.name1 host_name, was_started " .
				"FROM ".$ndo_base_prefix.$downtimeTable." dtm, ".$ndo_base_prefix."objects obj " .
				(isset($hostgroup) && $hostgroup != 0 ? ", ".$ndo_base_prefix."hostgroup_members mb " : "") .
				"WHERE obj.name1 IS NOT NULL " .
				"AND obj.name2 IS NULL " .
				(isset($host_name) && $host_name != "" ? " AND obj.name1 LIKE '%$host_name%'" : "") .
				(isset($search_output) && $search_output != "" ? " AND dtm.comment_data LIKE '%$search_output%'" : "") .
				(isset($hostgroup) && $hostgroup != 0 ? " AND dtm.object_id = mb.host_object_id AND mb.hostgroup_id = $hostgroup " : "") .
				"AND obj.object_id = dtm.object_id " .
				$centreon->user->access->queryBuilder("AND", "obj.name1", $hostStr) .
				(isset($view_all) && $view_all == 0 ? "AND dtm.scheduled_end_time > '".date("Y-m-d G:i:s", time())."' " : "") .
				"ORDER BY dtm.scheduled_start_time DESC " .
				"LIMIT ".$num * $limit.", ".$limit;
		$DBRESULT_NDO = $pearDBndo->query($request);
		$tab_downtime_host = array();
		for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++){
			$tab_downtime_host[$i] = $data;
			$tab_downtime_host[$i]["scheduled_start_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_host[$i]["scheduled_start_time"])." ";
			$tab_downtime_host[$i]["scheduled_end_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_host[$i]["scheduled_end_time"])." ";
		}
		$DBRESULT_NDO->free();
		unset($data);
	} else {

	}
	$rows = $pearDBndo->numberRows();
	include("./include/common/checkPagination.php");

	$en = array("0" => _("No"), "1" => _("Yes"));
	foreach ($tab_downtime_host as $key => $value) {
		$tab_downtime_host[$key]["is_fixed"] = $en[$tab_downtime_host[$key]["is_fixed"]];
		$tab_downtime_host[$key]["was_started"] = $en[$tab_downtime_host[$key]["was_started"]];
	}

	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	if ($centreon->user->access->checkAction("host_schedule_downtime")) {
		$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>_("Add a downtime"), "delConfirm"=>_("Do you confirm the deletion ?")));
	}

	$tpl->assign("p", $p);

	$tpl->assign("tab_downtime_host", $tab_downtime_host);
	$tpl->assign("nb_downtime_host", count($tab_downtime_host));

	$tpl->assign("dtm_host_name", _("Host Name"));
	$tpl->assign("dtm_start_time", _("Start Time"));
	$tpl->assign("dtm_end_time", _("End Time"));
	$tpl->assign("dtm_author", _("Author"));
	$tpl->assign("dtm_comment", _("Comments"));
	$tpl->assign("dtm_fixed", _("Fixed"));
	$tpl->assign("dtm_duration", _("Duration"));
	$tpl->assign("dtm_started", _("Started"));
	$tpl->assign("dtm_host_downtime", _("Hosts Downtimes"));

	$tpl->assign("secondes", _("s"));

	$tpl->assign("no_host_dtm", _("No downtime scheduled for hosts"));
	$tpl->assign("view_svc_dtm", _("View downtimes of services"));
	$tpl->assign("svc_dtm_link", "./main.php?p=".$p."&o=vs");
	$tpl->assign("limit", $limit);
	$tpl->assign("delete", _("Delete"));

	$tpl->assign("Host", _("Host Name"));
	$tpl->assign("Output", _("Output"));
	$tpl->assign("user", _("Users"));
	$tpl->assign('Hostgroup', _("Hostgroup"));
	$tpl->assign('Search', _("Search"));
	$tpl->assign("ViewAll", _("Show finished downtime"));
	$tpl->assign("search_output", $search_output);
	$tpl->assign('search_host', $host_name);
	$tpl->assign('view_all', $view_all);

	/**
	 * Get Hostgroups
	 */
	$DBRESULT = $pearDBndo->query("SELECT hostgroup_id, alias FROM ".$ndo_base_prefix."hostgroups ORDER BY alias");
	$options = "<option value='0'></options>";
	while ($data = $DBRESULT->fetchRow()) {
        $options .= "<option value='".$data["hostgroup_id"]."' ".(($hostgroup == $data["hostgroup_id"]) ? 'selected' : "").">".$data["alias"]."</option>";
    }
    $DBRESULT->free();

	$tpl->assign('hostgroup', $options);
	unset($options);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("hostDowntime.ihtml");
?>