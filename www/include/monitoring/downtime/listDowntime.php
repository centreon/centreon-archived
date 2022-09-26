<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit();
}

include_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";

include("./include/common/autoNumLimit.php");

$search_service = null;
$host_name = null;
$search_output = null;
$view_all = 0;
$view_downtime_cycle = 0;

if (isset($_POST['SearchB'])) {
    $centreon->historySearch[$url] = array();
    $search_service = isset($_POST['search_service'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_service'])
        : null;
    $centreon->historySearch[$url]["search_service"] = $search_service;
    $host_name = isset($_POST['search_host'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_host'])
        : null;
    $centreon->historySearch[$url]["search_host"] = $host_name;
    $search_output = isset($_POST['search_output'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_output'])
        : null;
    $centreon->historySearch[$url]["search_output"] = $search_output;
    $search_author = isset($_POST['search_author'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_author'])
        : null;
    $centreon->historySearch[$url]["search_author"] = $search_author;
    isset($_POST["view_all"]) ? $view_all = 1 : $view_all = 0;
    $centreon->historySearch[$url]["view_all"] = $view_all;
    isset($_POST["view_downtime_cycle"]) ? $view_downtime_cycle = 1 : $view_downtime_cycle = 0;
    $centreon->historySearch[$url]["view_downtime_cycle"] = $view_downtime_cycle;
} elseif (isset($_GET['SearchB'])) {
    $centreon->historySearch[$url] = array();
    $search_service = isset($_GET['search_service'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_service'])
        : null;
    $centreon->historySearch[$url]['search_service'] = $search_service;
    $host_name = isset($_GET['search_host'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_host'])
        : null;
    $centreon->historySearch[$url]["search_host"] = $host_name;
    $search_output = isset($_GET['search_output'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_output'])
        : null;
    $centreon->historySearch[$url]["search_output"] = $search_output;
    $search_author = isset($_GET['search_author'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_author'])
        : null;
    $centreon->historySearch[$url]["search_author"] = $search_author;
    isset($_GET["view_all"]) ? $view_all = 1 : $view_all = 0;
    $centreon->historySearch[$url]["view_all"] = $view_all;
    isset($_GET["view_downtime_cycle"]) ? $view_downtime_cycle = 1 : $view_downtime_cycle = 0;
    $centreon->historySearch[$url]["view_downtime_cycle"] = $view_downtime_cycle;
} else {
    $search_service = $centreon->historySearch[$url]['search_service'] ?? null;
    $host_name = $centreon->historySearch[$url]["search_host"] ?? null;
    $search_output = $centreon->historySearch[$url]["search_output"] ?? null;
    $search_author = $centreon->historySearch[$url]["search_author"] ?? null;
    $view_all = $centreon->historySearch[$url]["view_all"] ?? 0;
    $view_downtime_cycle = $centreon->historySearch[$url]["view_downtime_cycle"] ?? 0;
}

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

/**
 * true: URIs will correspond to deprecated pages
 * false: URIs will correspond to new page (Resource Status)
 */
$useDeprecatedPages = $centreon->user->doesShowDeprecatedPages();

include_once "./class/centreonDB.class.php";

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl('./include/monitoring/downtime/', $tpl, "template/");

$form = new HTML_QuickFormCustom('select_form', 'GET', "?p=" . $p);

$tab_downtime_svc = array();


$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'SearchB', _("Search"), $attrBtnSuccess);

//Service Downtimes
if ($view_all == 1) {
    $downtimeTable = "downtimehistory";
    $extrafields = ", actual_end_time, cancelled as was_cancelled ";
} else {
    $downtimeTable = "scheduleddowntime";
    $extrafields = "";
}
/*------------------ BAM ------------------*/
$tab_service_bam = array();
$request = "SELECT id FROM modules_informations WHERE name = 'centreon-bam-server';";
$DBRESULT = $pearDB->query($request);
if ($DBRESULT->rowCount()) {
    $request = "SELECT CONCAT('ba_',ba_id) AS id, ba_id, name FROM mod_bam";
    $DBRESULT = $pearDB->query($request);

    while ($elem = $DBRESULT->fetchRow()) {
        $tab_service_bam[$elem['id']] = array(
            'name' => $elem['name'],
            'id' => $elem['ba_id']
        );
    }
}

/* --------------- Services ---------------*/
$request = "(SELECT SQL_CALC_FOUND_ROWS DISTINCT d.internal_id as internal_downtime_id, d.entry_time, duration,
        d.author as author_name, d.comment_data, d.fixed as is_fixed, d.start_time as scheduled_start_time,
        d.end_time as scheduled_end_time, d.started as was_started, d.host_id, d.service_id, h.name as host_name,
        s.description as service_description " . $extrafields . " " .
    "FROM downtimes d, services s, hosts h " . ($is_admin ? "" : ", centreon_acl acl ") .
    "WHERE d.host_id = s.host_id " .
    "AND d.service_id = s.service_id " .
    "AND s.host_id = h.host_id " .
    "AND d.type = 1 ";
if (!$view_all) {
    $request .= " AND d.cancelled = 0 ";
}
if (!$is_admin) {
    $request .= " AND s.host_id = acl.host_id AND s.service_id = acl.service_id AND group_id IN (" .
        $centreon->user->access->getAccessGroupsString() . ") ";
}
$request .= "AND s.description LIKE :service " .
    "AND h.name LIKE :host " .
    "AND d.comment_data LIKE :output " .
    (isset($view_all) && $view_all == 0 ? "AND d.end_time > '" . time() . "' " : "") .
    (
        isset($view_downtime_cycle) && $view_downtime_cycle == 0
            ? " AND d.comment_data NOT LIKE '%Downtime cycle%' "
            : ""
    ) .
    " AND d.author LIKE :author";

/* --------------- Hosts --------------- */
$request .= ") UNION (SELECT DISTINCT d.internal_id as internal_downtime_id, d.entry_time, duration,
  d.author as author_name, d.comment_data, d.fixed as is_fixed, d.start_time as scheduled_start_time,
  d.end_time as scheduled_end_time, d.started as was_started, d.host_id, d.service_id, h.name as host_name,
   '' as service_description " . $extrafields .
    "FROM downtimes d, hosts h " . ($is_admin ? "" : ", centreon_acl acl ") . " " .
    "WHERE d.host_id = h.host_id AND d.type = 2 ";
if (!$view_all) {
    $request .= " AND d.cancelled = 0 ";
}
if (!$is_admin) {
    $request .= " AND h.host_id = acl.host_id AND acl.service_id IS NULL AND group_id IN (" .
        $centreon->user->access->getAccessGroupsString() . ") ";
}
$request .= (isset($search_service) && $search_service != "" ? "AND 1 = 0 " : "") .
    "AND h.name LIKE :host " .
    "AND d.comment_data LIKE :output " .
    (isset($view_all) && $view_all == 0 ? "AND d.end_time > '" . time() . "' " : "") .
    (isset($view_downtime_cycle) && $view_downtime_cycle == 0 ?
        " AND d.comment_data NOT LIKE '%Downtime cycle%' " : "") .
    " AND d.author LIKE :author" .
    ") ORDER BY scheduled_start_time DESC " .
    "LIMIT :offset, :limit";
$downtimesStatement = $pearDBO->prepare($request);
$downtimesStatement->bindValue(':service', '%' . $search_service . '%', \PDO::PARAM_STR);
$downtimesStatement->bindValue(':host', '%' . $host_name . '%', \PDO::PARAM_STR);
$downtimesStatement->bindValue(':output', '%' . $search_output . '%', \PDO::PARAM_STR);
$downtimesStatement->bindValue(':author', '%' . $search_author . '%', \PDO::PARAM_STR);
$downtimesStatement->bindValue(':offset', $num * $limit, \PDO::PARAM_INT);
$downtimesStatement->bindValue(':limit', $limit, \PDO::PARAM_INT);
$downtimesStatement->execute();

$rows = $pearDBO->query("SELECT FOUND_ROWS()")->fetchColumn();

for ($i = 0; $data = $downtimesStatement->fetchRow(); $i++) {
    $tab_downtime_svc[$i] = $data;

    $tab_downtime_svc[$i]['comment_data'] =
        CentreonUtils::escapeAllExceptSelectedTags($data['comment_data']);

    $tab_downtime_svc[$i]['scheduled_start_time'] = $tab_downtime_svc[$i]["scheduled_start_time"] . " ";
    $tab_downtime_svc[$i]['scheduled_end_time'] = $tab_downtime_svc[$i]["scheduled_end_time"] . " ";

    if (preg_match('/_Module_BAM_\d+/', $data['host_name'])) {
        $tab_downtime_svc[$i]['host_name'] = 'Module BAM';
        $tab_downtime_svc[$i]['h_details_uri'] = "./main.php?p=207&o=d&ba_id="
            . $tab_service_bam[$data['service_description']]['id'];
        $tab_downtime_svc[$i]['s_details_uri'] = "./main.php?p=207&o=d&ba_id="
            . $tab_service_bam[$data['service_description']]['id'];
        $tab_downtime_svc[$i]['service_description'] = $tab_service_bam[$data['service_description']]['name'];
        $tab_downtime_svc[$i]['downtime_type'] = 'SVC';
        if ($tab_downtime_svc[$i]['author_name'] == 'Centreon Broker BAM Module') {
            $tab_downtime_svc[$i]['scheduled_end_time'] = "Automatic";
            $tab_downtime_svc[$i]['duration'] = 'Automatic';
        }
    } else {
        $tab_downtime_svc[$i]['host_name'] = $data['host_name'];
        $tab_downtime_svc[$i]['h_details_uri'] = $useDeprecatedPages
            ? './main.php?p=20202&o=hd&host_name=' . $data['host_name']
            : $resourceController->buildHostDetailsUri($data['host_id']);
        if ($data['service_description'] !== '') {
            $tab_downtime_svc[$i]['s_details_uri'] = $useDeprecatedPages
            ? './main.php?p=202&o=svcd&host_name='
                . $data['host_name']
                . '&service_description='
                . $data['service_description']
            : $resourceController->buildServiceDetailsUri(
                $data['host_id'],
                $data['service_id']
            );
            $tab_downtime_svc[$i]['service_description'] = $data['service_description'];
            $tab_downtime_svc[$i]['downtime_type'] = 'SVC';
        } else {
            $tab_downtime_svc[$i]['service_description'] = '-';
            $tab_downtime_svc[$i]['downtime_type'] = 'HOST';
        }
    }
}
unset($data);

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
            $tab_downtime_svc[$key]["actual_end_time"] = $tab_downtime_svc[$key]["actual_end_time"] . " ";
        }
        $tab_downtime_svc[$key]["was_cancelled"] = $en[$tab_downtime_svc[$key]["was_cancelled"]];
    }
}
/*
 * Element we need when we reload the page
 */
$form->addElement('hidden', 'p');
$tab = array("p" => $p);
$form->setDefaults($tab);

if ($oreon->user->access->checkAction("host_schedule_downtime")) {
    $tpl->assign('msgs2', array(
        "addL2" => "?p=" . $p . "&o=a",
        "addT2" => _("Add a downtime"),
        "delConfirm" => addslashes(_("Do you confirm the cancellation ?"))
    ));
}


$tpl->assign("p", $p);
$tpl->assign("o", $o);

$tpl->assign("tab_downtime_svc", $tab_downtime_svc);
$tpl->assign("nb_downtime_svc", count($tab_downtime_svc));
$tpl->assign("dtm_service_downtime", _("Services Downtimes"));
$tpl->assign("secondes", _("s"));
$tpl->assign("view_host_dtm", _("View downtimes of hosts"));
$tpl->assign("host_dtm_link", "./main.php?p=" . $p . "&o=vh");
$tpl->assign("cancel", _("Cancel"));
$tpl->assign("limit", $limit);

$tpl->assign("Host", _("Host Name"));
$tpl->assign("Service", _("Service"));
$tpl->assign("Output", _("Output"));
$tpl->assign("user", _("Users"));
$tpl->assign('Hostgroup', _("Hostgroup"));
$tpl->assign('Search', _("Search"));
$tpl->assign("ViewAll", _("Display Finished Downtimes"));
$tpl->assign("ViewDowntimeCycle", _("Display Recurring Downtimes"));
$tpl->assign("Author", _("Author"));
$tpl->assign("search_output", $search_output);
$tpl->assign('search_host', $host_name);
$tpl->assign("search_service", $search_service);
$tpl->assign('view_all', $view_all);
$tpl->assign('view_downtime_cycle', $view_downtime_cycle);
$tpl->assign('search_author', $search_author ?? '');

/* Send Form */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

/* Display Page */
$tpl->display("listDowntime.ihtml");
