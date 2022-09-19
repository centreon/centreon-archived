<?php

/*
 * Copyright 2005-2020 Centreon
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


//initializing filters values
$searchService = \HtmlAnalyzer::sanitizeAndRemoveTags(
    $_POST["searchService"] ?? $_GET["searchService"] ?? ''
);

$searchHost = \HtmlAnalyzer::sanitizeAndRemoveTags(
    $_POST["searchHost"] ?? $_GET["searchHost"] ?? ''
);
$searchOutput = \HtmlAnalyzer::sanitizeAndRemoveTags(
    $_POST["searchOutput"] ?? $_GET["searchOutput"] ?? ''
);

if (isset($_POST['search']) || isset($_GET['search'])) {
    //saving chosen filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]["searchService"] = $searchService;
    $centreon->historySearch[$url]["searchHost"] = $searchHost;
    $centreon->historySearch[$url]["searchOutput"] = $searchOutput;
} else {
    //restoring saved values
    $searchService = $centreon->historySearch[$url]['searchService'] ?? '';
    $searchHost = $centreon->historySearch[$url]["searchHost"] ?? '';
    $searchOutput = $centreon->historySearch[$url]["searchOutput"] ?? '';
}

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

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

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl, "template/");

include_once("./class/centreonDB.class.php");

$form = new HTML_QuickFormCustom('select_form', 'GET', "?p=" . $p);

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

$tab_comments_svc = array();

$en = array("0" => _("No"), "1" => _("Yes"));

/*
 * Service Comments
 */
$rq2 = "SELECT SQL_CALC_FOUND_ROWS c.internal_id, c.entry_time, c.author, c.data, c.persistent, c.host_id,
 c.service_id, h.name AS host_name, s.description AS service_description " .
    "FROM comments c, hosts h, services s ";
if (!$is_admin) {
    $rq2 .= ", centreon_acl acl ";
}
$rq2 .= "WHERE c.type = 2 AND c.host_id = h.host_id AND c.service_id = s.service_id AND h.host_id = s.host_id ";
$rq2 .= " AND c.expires = '0' AND h.enabled = 1 AND s.enabled = 1 ";
$rq2 .= " AND (c.deletion_time IS NULL OR c.deletion_time = 0) ";
if (!$is_admin) {
    $rq2 .= " AND s.host_id = acl.host_id AND s.service_id = acl.service_id AND group_id IN (" .
        $centreon->user->access->getAccessGroupsString() . ") ";
}
$rq2 .= (isset($searchService) && $searchService != "" ? " AND s.description LIKE '%$searchService%'" : "");
$rq2 .= (isset($searchHost) && $searchHost != "" ? " AND h.name LIKE '%$searchHost%'" : "");
$rq2 .= (isset($searchOutput) && $searchOutput != "" ? " AND c.data LIKE '%$searchOutput%'" : "");

$rq2 .= ' UNION ';

/*
 * Host Comments
 */
$rq2 .= "SELECT c.internal_id, c.entry_time, c.author, c.data, c.persistent, c.host_id,
 '' as service_id, h.name AS host_name, '' AS service_description " .
    "FROM comments c, hosts h ";
if (!$is_admin) {
    $rq2 .= ", centreon_acl acl ";
}
$rq2 .= "WHERE c.host_id = h.host_id AND c.type = 1";
$rq2 .= " AND c.expires = '0' AND h.enabled = 1 ";
$rq2 .= " AND (c.deletion_time IS NULL OR c.deletion_time = 0) ";
if (!$is_admin) {
    $rq2 .= " AND h.host_id = acl.host_id AND acl.service_id IS NULL AND group_id IN (" .
        $centreon->user->access->getAccessGroupsString() . ") ";
}
$rq2 .= (isset($searchService) && $searchService != "" ? " AND 1 = 0" : "");
$rq2 .= (isset($searchHost) && $searchHost != "" ? " AND h.name LIKE '%$searchHost%'" : "");
$rq2 .= (isset($searchOutput) && $searchOutput != "" ? " AND c.data LIKE '%$searchOutput%'" : "");

$rq2 .= " ORDER BY entry_time DESC LIMIT " . $num * $limit . ", " . $limit;

$DBRESULT = $pearDBO->query($rq2);
$rows = $pearDBO->query("SELECT FOUND_ROWS()")->fetchColumn();

for ($i = 0; $data = $DBRESULT->fetchRow(); $i++) {
    $tab_comments_svc[$i] = $data;
    $tab_comments_svc[$i]["persistent"] = $en[$tab_comments_svc[$i]["persistent"]];
    $tab_comments_svc[$i]['data'] = CentreonUtils::escapeAllExceptSelectedTags(
        $tab_comments_svc[$i]['data'],
        ['a', 'br', 'hr']
    );
    $tab_comments_svc[$i]['h_details_uri'] = $useDeprecatedPages
        ? 'main.php?p=20202&o=hd&host_name=' . $data['host_name']
        : $resourceController->buildHostDetailsUri($data['host_id']);

    if ($data['service_description'] != '') {
        $tab_comments_svc[$i]['s_details_uri'] = $useDeprecatedPages
            ? 'main.php?p=202&o=svcd&host_name='
                . $data['host_name']
                . '&service_description='
                . $data['service_description']
            : $resourceController->buildServiceDetailsUri(
                $data['host_id'],
                $data['service_id']
            );
        $tab_comments_svc[$i]['service_description'] = htmlentities($data['service_description'], ENT_QUOTES, 'UTF-8');
        $tab_comments_svc[$i]['comment_type'] = 'SVC';
    } else {
        $tab_comments_svc[$i]['service_description'] = '-';
        $tab_comments_svc[$i]['comment_type'] = 'HOST';
    }
}
unset($data);
$DBRESULT->closeCursor();

include("./include/common/checkPagination.php");

/*
 * Element we need when we reload the page
 */
$form->addElement('hidden', 'p');
$tab = array("p" => $p);
$form->setDefaults($tab);

if ($oreon->user->access->checkAction("service_comment")) {
    $tpl->assign('msgs', array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add a comment"),
        "delConfirm" => _("Do you confirm the deletion ?")
    ));
}

$tpl->assign("p", $p);
$tpl->assign("o", $o);
$tpl->assign("tab_comments_svc", $tab_comments_svc);
$tpl->assign("nb_comments_svc", count($tab_comments_svc));
$tpl->assign("no_svc_comments", _("No Comment for services."));
$tpl->assign("cmt_service_comment", _("Services Comments"));
$tpl->assign("host_comment_link", "./main.php?p=" . $p . "&o=vh");
$tpl->assign("view_host_comments", _("View comments of hosts"));
$tpl->assign("delete", _("Delete"));
$tpl->assign("Host", _("Host Name"));
$tpl->assign("Service", _("Service"));
$tpl->assign("Output", _("Output"));
$tpl->assign("user", _("Users"));
$tpl->assign('Hostgroup', _("Hostgroup"));
$tpl->assign("searchOutput", $searchOutput);
$tpl->assign('searchHost', $searchHost);
$tpl->assign('searchService', $searchService);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('limit', $limit);
$tpl->assign('form', $renderer->toArray());

$tpl->display("comments.ihtml");
