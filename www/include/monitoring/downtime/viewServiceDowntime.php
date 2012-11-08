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

	if (!isset($oreon)) {
		exit();
	}

	include_once $centreon_path."www/class/centreonGMT.class.php";

	include("./include/common/autoNumLimit.php");

	if (isset($_POST["search_service"]))
	  	$search_service = $_POST["search_service"];
	else if (isset($_GET["search_service"]))
	  	$search_service = $_GET["search_service"];
	else
	  	$search_service = NULL;

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
	} else {
	    $pearDBndo = new CentreonDB("centstorage");
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

	$tab_downtime_svc = array();

	/*
	 * Service Downtimes
	 */
	if ($view_all == 1) {
		$downtimeTable = "downtimehistory";
		if ($oreon->broker->getBroker() == "ndo") {
		    $extrafields = ", UNIX_TIMESTAMP(dtm.actual_end_time) as actual_end_time, was_cancelled ";
		} else {
		    $extrafields = ", end_time as actual_end_time, cancelled as was_cancelled ";
		}
	} else {
		$downtimeTable = "scheduleddowntime";
		$extrafields = "";
	}
	if ($oreon->broker->getBroker() == "ndo") {
		if ($is_admin) {
			$request =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT dtm.internal_downtime_id, unix_timestamp(dtm.entry_time), dtm.duration, dtm.author_name, dtm.comment_data, dtm.is_fixed, unix_timestamp(dtm.scheduled_start_time) AS scheduled_start_time, unix_timestamp(dtm.scheduled_end_time) AS scheduled_end_time, obj.name1 host_name, obj.name2 service_description, was_started " . $extrafields .
					"FROM ".$ndo_base_prefix.$downtimeTable." dtm, ".$ndo_base_prefix."objects obj " .
					"WHERE obj.name1 IS NOT NULL " .
					"AND obj.name2 IS NOT NULL " .
					"AND obj.object_id = dtm.object_id ";
			$request .= (isset($search_service) && $search_service != "" ? "AND obj.name2 LIKE '%$search_service%' " : "") .
					(isset($host_name) && $host_name != "" ? "AND obj.name1 LIKE '%$host_name%' " : "") .
					(isset($search_output) && $search_output != "" ? "AND dtm.comment_data LIKE '%$search_output%' " : "") .
					(isset($view_all) && $view_all == 0 ? "AND dtm.scheduled_end_time > '".date("Y-m-d G:i:s", time())."' " : "") .
					"ORDER BY dtm.actual_start_time DESC " .
					"LIMIT ".$num * $limit.", ".$limit;
		} else {
			$request =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT dtm.internal_downtime_id, unix_timestamp(dtm.entry_time), dtm.duration, dtm.author_name, dtm.comment_data, dtm.is_fixed, unix_timestamp(dtm.scheduled_start_time) AS scheduled_start_time, unix_timestamp(dtm.scheduled_end_time) AS scheduled_end_time, obj.name1 host_name, obj.name2 service_description, was_started " . $extrafields .
					"FROM ".$ndo_base_prefix.$downtimeTable." dtm, ".$ndo_base_prefix."objects obj, centreon_acl " .
					"WHERE obj.name1 IS NOT NULL " .
					"AND obj.name2 IS NOT NULL " .
					"AND obj.object_id = dtm.object_id " .
					"AND obj.name1 = centreon_acl.host_name ";
			$request .= (isset($search_service) && $search_service != "" ? "AND obj.name2 LIKE '%$search_service%' " : "") .
					(isset($host_name) && $host_name != "" ? "AND obj.name1 LIKE '%$host_name%' " : "") .
					(isset($search_output) && $search_output != "" ? "AND dtm.comment_data LIKE '%$search_output%' " : "") .
					"AND obj.name2 = centreon_acl.service_description " .
					"AND centreon_acl.group_id IN (".$oreon->user->access->getAccessGroupsString().") " .
					(isset($view_all) && $view_all == 0 ? "AND dtm.scheduled_end_time > '".date("Y-m-d G:i:s", time())."' " : "") .
					"ORDER BY dtm.actual_start_time DESC " .
					"LIMIT ".$num * $limit.", ".$limit;
		}
		$DBRESULT_NDO = $pearDBndo->query($request);
		$rows = $pearDBndo->numberRows();
		for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++) {
			$tab_downtime_svc[$i] = $data;
			$tab_downtime_svc[$i]["scheduled_start_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_svc[$i]["scheduled_start_time"])." ";
			$tab_downtime_svc[$i]["scheduled_end_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_svc[$i]["scheduled_end_time"])." ";
			$tab_downtime_svc[$i]["host_name_link"] = urlencode($tab_downtime_svc[$i]["host_name"]);
		}
		unset($data);
	} else {
        if ($is_admin) {
			$request =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT d.internal_id as internal_downtime_id,
						d.entry_time, duration, d.author as author_name, d.comment_data,
						d.fixed as is_fixed, d.start_time as scheduled_start_time, d.end_time as scheduled_end_time,
						d.started as was_started, h.name as host_name, s.description as service_description " . $extrafields .
					"FROM downtimes d, services s, hosts h " .
					"WHERE d.service_id  = s.service_id " .
					"AND s.host_id = h.host_id " . 
					"AND d.host_id = h.host_id ";
            if (!$view_all) {
                $request .= "AND d.cancelled = 0 ";
            }
            $request .= (isset($search_service) && $search_service != "" ? "AND s.description LIKE '%$search_service%' " : "") .
					(isset($host_name) && $host_name != "" ? "AND h.name LIKE '%$host_name%' " : "") .
					(isset($search_output) && $search_output != "" ? "AND d.comment_data LIKE '%$search_output%' " : "") .
					(isset($view_all) && $view_all == 0 ? "AND d.end_time > '".time()."' " : "") .
					"ORDER BY d.start_time DESC " .
					"LIMIT ".$num * $limit.", ".$limit;
		} else {
			$request =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT d.internal_id as internal_downtime_id,
						d.entry_time, duration, d.author as author_name, d.comment_data,
						d.fixed as is_fixed, d.start_time as scheduled_start_time, d.end_time as scheduled_end_time,
						d.started as was_started, h.name as host_name, s.description as service_description " . $extrafields .
					"FROM downtimes d, services s, hosts h, centreon_acl a " .
					"WHERE d.service_id  = s.service_id " .
					"AND s.host_id = h.host_id " .
			        "AND h.host_id = a.host_id " .
			        "AND a.service_id = s.service_id " . 
					"AND d.host_id = h.host_id ";
		    if (!$view_all) {
                $request .= "AND d.cancelled = 0 ";
            }
            $request .= (isset($search_service) && $search_service != "" ? "AND s.description LIKE '%$search_service%' " : "") .
					(isset($host_name) && $host_name != "" ? "AND h.name LIKE '%$host_name%' " : "") .
					(isset($search_output) && $search_output != "" ? "AND d.comment_data LIKE '%$search_output%' " : "") .
					"AND a.group_id IN (".$oreon->user->access->getAccessGroupsString().") " .
					(isset($view_all) && $view_all == 0 ? "AND d.end_time > '".time()."' " : "") .
					"ORDER BY d.start_time DESC " .
					"LIMIT ".$num * $limit.", ".$limit;
		}
		$DBRESULT_NDO = $pearDBndo->query($request);
		$rows = $pearDBndo->numberRows();
		for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++) {
			$tab_downtime_svc[$i] = $data;
			$tab_downtime_svc[$i]["scheduled_start_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_svc[$i]["scheduled_start_time"])." ";
			$tab_downtime_svc[$i]["scheduled_end_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_svc[$i]["scheduled_end_time"])." ";
			$tab_downtime_svc[$i]["host_name_link"] = urlencode($tab_downtime_svc[$i]["host_name"]);
		}
		unset($data);
	}

	/*
	 * Number Rows
	 */
	include("./include/common/checkPagination.php");


	$en = array("0" => _("No"), "1" => _("Yes"));
	foreach ($tab_downtime_svc as $key => $value) {
		$tab_downtime_svc[$key]["is_fixed"] = $en[$tab_downtime_svc[$key]["is_fixed"]];
		$tab_downtime_svc[$key]["was_started"] = $en[$tab_downtime_svc[$key]["was_started"]];
		if ($view_all == 1) {
		    if (!isset($tab_downtime_svc[$key]["actual_end_time"]) || !$tab_downtime_svc[$key]["actual_end_time"]) {
		        if ($tab_downtime_svc[$key]["was_cancelled"] == 0) {
		            $tab_downtime_svc[$key]["actual_end_time"] = _("N/A");
		        } else {
		            $tab_downtime_svc[$key]["actual_end_time"] = _("Never Started");
		        }
		    } else {
		        $tab_downtime_svc[$key]["actual_end_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_downtime_svc[$key]["actual_end_time"])." ";
		    }
		    $tab_downtime_svc[$key]["was_cancelled"] = $en[$tab_downtime_svc[$key]["was_cancelled"]];
		}
	}
	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	if ($oreon->user->access->checkAction("service_schedule_downtime")) {
		$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add a downtime"), "delConfirm"=>_("Do you confirm the deletion ?")));
	}

	$tpl->assign("p", $p);
	$tpl->assign("o", $o);

	$tpl->assign("tab_downtime_svc", $tab_downtime_svc);
	$tpl->assign("nb_downtime_svc", count($tab_downtime_svc));

	$tpl->assign("dtm_host_name", _("Host Name"));
	$tpl->assign("dtm_service_descr", _("Services"));
	$tpl->assign("dtm_start_time", _("Start Time"));
	$tpl->assign("dtm_end_time", _("End Time"));
	$tpl->assign("dtm_author", _("Author"));
	$tpl->assign("dtm_comment", _("Comments"));
	$tpl->assign("dtm_fixed", _("Fixed"));
	$tpl->assign("dtm_duration", _("Duration"));
	$tpl->assign("dtm_started", _("Started"));
	$tpl->assign("dtm_service_downtime", _("Services Downtimes"));
	$tpl->assign("dtm_service_cancelled", _("Cancelled"));
	$tpl->assign("dtm_service_actual_end", _("Actual End"));

	$tpl->assign("secondes", _("s"));

	$tpl->assign("no_svc_dtm", _("No downtime scheduled for services"));
	$tpl->assign("view_host_dtm", _("View downtimes of hosts"));
	$tpl->assign("host_dtm_link", "./main.php?p=".$p."&o=vh");
	$tpl->assign("cancel", _("Cancel"));
	$tpl->assign("delete", _("Delete"));
	$tpl->assign("limit", $limit);

	$tpl->assign("Host", _("Host Name"));
	$tpl->assign("Service", _("Service"));
	$tpl->assign("Output", _("Output"));
	$tpl->assign("user", _("Users"));
	$tpl->assign('Hostgroup', _("Hostgroup"));
	$tpl->assign('Search', _("Search"));
	$tpl->assign("ViewAll", _("Show finished downtime"));
	$tpl->assign("search_output", $search_output);
	$tpl->assign('search_host', $host_name);
	$tpl->assign("search_service", $search_service);
	$tpl->assign('view_all', $view_all);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("serviceDowntime.ihtml");
?>
<script type='text/javascript'>
var msgArr = new Array();
msgArr['cs'] = '<?php echo addslashes(_("Do you confirm the cancellation ?")); ?>';
msgArr['ds'] = '<?php echo addslashes(_("Do you confirm the deletion ?")); ?>';

function doAction(slt, act) {
	if (confirm(msgArr[act])) {
		document.form.submit();
	} else {
		slt.value = 0;
	}
}
</script>