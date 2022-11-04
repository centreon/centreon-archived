<?php

/*
 * Copyright 2005-2020 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/service.php $
 * SVN : $Id: service.php 8549 2009-07-01 16:20:26Z shotamchay $
 *
 */

if (!isset($centreon)) {
    exit();
}

$modules_path = $centreon_path . "www/include/configuration/configKnowledge/";
require_once $modules_path . 'functions.php';
require_once $centreon_path . '/bootstrap.php';
$pearDB = $dependencyInjector['configuration_db'];

if (!isset($limit) || (int) $limit < 0) {
    $limit = $centreon->optGen["maxViewConfiguration"];
}

$order = "ASC";
$orderBy = "host_name";

// Use whitelist as we can't bind ORDER BY values
if (!empty($_POST['order']) && !empty($_POST['orderby'])) {
    if (in_array($_POST['order'], ["ASC", "DESC"])) {
        $order = $_POST['order'];
    }
    if (in_array($_POST['orderby'], ["host_name", "service_description"])) {
        $orderBy = $_POST['orderby'];
    }
}

require_once "./include/common/autoNumLimit.php";

/*
 * Add paths
 */
set_include_path(get_include_path() . PATH_SEPARATOR . $modules_path);

require_once $centreon_path . "/www/class/centreon-knowledge/procedures.class.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($modules_path, $tpl);

try {
    $postHost = !empty($_POST['searchHost'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchHost'])
        : '';
    $postService = !empty($_POST['searchService'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchService'])
        : '';
    $postHostgroup = !empty($_POST['searchHostgroup'])
        ? filter_input(INPUT_POST, 'searchHostgroup', FILTER_VALIDATE_INT)
        : false;
    $postServicegroup = !empty($_POST['searchServicegroup'])
        ? filter_input(INPUT_POST, 'searchServicegroup', FILTER_VALIDATE_INT)
        : false;
    $postPoller = !empty($_POST['searchPoller'])
        ? filter_input(INPUT_POST, 'searchPoller', FILTER_VALIDATE_INT)
        : false;
    $searchHasNoProcedure = !empty($_POST['searchHasNoProcedure'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchHasNoProcedure'])
        : '';
    $templatesHasNoProcedure = !empty($_POST['searchTemplatesWithNoProcedure'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchTemplatesWithNoProcedure'])
        : '';

    $conf = getWikiConfig($pearDB);
    $WikiURL = $conf['kb_wiki_url'];

    $currentPage = "services";
    require_once $modules_path . 'search.php';

    /*
     * Init Status Template
     */
    $status = [
        0 => "<font color='orange'> " . _("No wiki page defined") . " </font>",
        1 => "<font color='green'> " . _("Wiki page defined") . " </font>"
    ];
    $line = [0 => "list_one", 1 => "list_two"];

    $proc = new procedures($pearDB);
    $proc->fetchProcedures();

    $queryValues = [];
    $query = "
        SELECT SQL_CALC_FOUND_ROWS t1.* FROM (
            SELECT s.service_id, s.service_description, h.host_name, h.host_id
            FROM service s
                LEFT JOIN host_service_relation hsr ON hsr.service_service_id = s.service_id
                RIGHT JOIN host h ON h.host_id = hsr.host_host_id
            WHERE s.service_register = '1' ";
    if (!empty($postHost)) {
        $query .= "AND h.host_name LIKE :postHost ";
        $queryValues[':postHost'] = [
            \PDO::PARAM_STR => "%" . $postHost . "%"
        ];
    }
    if ($postHostgroup !== false) {
        $query .= "
            AND hsr.host_host_id IN
            (SELECT host_host_id FROM hostgroup_relation hgr
                WHERE hgr.hostgroup_hg_id = :postHostgroup ) ";
        $queryValues[':postHostgroup'] = [
            \PDO::PARAM_INT => $postHostgroup
        ];
    }
    if ($postServicegroup !== false) {
        $query .= "
            AND s.service_id IN
            (SELECT service_service_id FROM servicegroup_relation
                WHERE servicegroup_sg_id = :postServicegroup ) ";
        $queryValues[':postServicegroup'] = [
            \PDO::PARAM_INT => $postServicegroup
        ];
    }
    if ($postPoller !== false) {
        $query .= "
            AND hsr.host_host_id IN
            (SELECT host_host_id FROM ns_host_relation
                WHERE nagios_server_id = :postPoller ) ";
        $queryValues[':postPoller'] = [
            \PDO::PARAM_INT => $postPoller
        ];
    }
    if (!empty($postService)) {
        $query .= "AND s.service_description LIKE :postService ";
        $queryValues[':postService'] = [
            \PDO::PARAM_STR => "%" . $postService . "%"
        ];
    }

    $query .= "
        UNION
        SELECT s2.service_id, s2.service_description, h2.host_name, h2.host_id
            FROM service s2
                LEFT JOIN host_service_relation hsr2 ON hsr2.service_service_id = s2.service_id
                RIGHT JOIN hostgroup_relation hgr ON hgr.hostgroup_hg_id = hsr2.hostgroup_hg_id
                LEFT JOIN host h2 ON h2.host_id = hgr.host_host_id
            WHERE s2.service_register = '1' ";
    if ($postHostgroup !== false) {
        $query .= "
            AND (h2.host_id IN
            (SELECT host_host_id FROM hostgroup_relation hgr
                WHERE hgr.hostgroup_hg_id = :postHostgroup )
            OR hgr.hostgroup_hg_id = :postHostgroup ) ";
    }
    if (!empty($postHost)) {
        $query .= "AND h2.host_name LIKE :postHost ";
    }
    if ($postServicegroup !== false) {
        $query .= "
            AND s2.service_id IN
            (SELECT service_service_id FROM servicegroup_relation
                WHERE servicegroup_sg_id = :postServicegroup) ";
    }
    if ($postPoller !== false) {
        $query .= "
            AND h2.host_id IN
            (SELECT host_host_id FROM ns_host_relation
                WHERE nagios_server_id = :postPoller ) ";
    }
    if (!empty($postService)) {
        $query .= "AND s2.service_description LIKE :postService ";
    }
    $query .= ") as t1 ";
    $query .= "ORDER BY $orderBy " . $order . " LIMIT :offset, :limit";

    $statement = $pearDB->prepare($query);
    foreach ($queryValues as $bindId => $bindData) {
        foreach ($bindData as $bindType => $bindValue) {
            $statement->bindValue($bindId, $bindValue, $bindType);
        }
    }
    $statement->bindValue(':offset', $num * $limit, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    $serviceList = [];
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $row['service_description'] = str_replace("#S#", "/", $row['service_description']);
        $row['service_description'] = str_replace("#BS#", "\\", $row['service_description']);
        if (isset($row['host_id']) && $row['host_id']) {
            $serviceList[$row['host_name'] . '_/_' . $row['service_description']] = [
                "id" => $row['service_id'],
                "svc" => $row['service_description'],
                "h" => $row['host_name']
            ];
        }
    }

    $rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

    /*
     * Create Diff
     */
    $tpl->assign("host_name", _("Hosts"));
    $tpl->assign("p", 61002);
    $tpl->assign("service_description", _("Services"));

    $diff = [];
    $templateHostArray = [];

    foreach ($serviceList as $key => $value) {
        $tplStr = "";
        $tplArr = $proc->getMyServiceTemplateModels($value['id']);
        $key_nospace = str_replace(" ", "_", $key);
        if ($proc->serviceHasProcedure($key_nospace, $tplArr) == true) {
            $diff[$key] = 1;
        } else {
            $diff[$key] = 0;
        }

        if (!empty($templatesHasNoProcedure)) {
            if (
                $diff[$key] == 1
                || $proc->serviceHasProcedure($key_nospace, $tplArr, PROCEDURE_INHERITANCE_MODE) == true
            ) {
                $rows--;
                unset($diff[$key]);
                unset($serviceList[$key]);
                continue;
            }
        } elseif (!empty($searchHasNoProcedure)) {
            if ($diff[$key] == 1) {
                $rows--;
                unset($diff[$key]);
                unset($serviceList[$key]);
                continue;
            }
        }

        if (count($tplArr)) {
            $firstTpl = 1;
            foreach ($tplArr as $key1 => $value1) {
                if ($firstTpl) {
                    $firstTpl = 0;
                } else {
                    $tplStr .= "&nbsp;|&nbsp;";
                }
                $tplStr .= "<a href='" . $WikiURL .
                    "/index.php?title=Service-Template_:_" . $value1 . "' target='_blank'>" . $value1 . "</a>";
            }
        }
        $templateHostArray[$key] = $tplStr;
        unset($tplStr);
    }

    include "./include/common/checkPagination.php";

    if (isset($templateHostArray)) {
        $tpl->assign("templateHostArray", $templateHostArray);
    }

    $WikiVersion = getWikiVersion($WikiURL . '/api.php');
    $tpl->assign("WikiVersion", $WikiVersion);
    $tpl->assign("WikiURL", $WikiURL);
    $tpl->assign("content", $diff);
    $tpl->assign("services", $serviceList);
    $tpl->assign("status", $status);
    $tpl->assign("selection", 1);

    /*
     * Send template in order to open
     */

    // translations
    $tpl->assign("status_trans", _("Status"));
    $tpl->assign("actions_trans", _("Actions"));
    $tpl->assign("template_trans", _("Template"));

    // Template
    $tpl->assign("lineTemplate", $line);
    $tpl->assign('limit', $limit);

    $tpl->assign('order', $order);
    $tpl->assign('orderby', $orderBy);
    $tpl->assign('defaultOrderby', 'host_name');

    // Apply a template definition
    $tpl->display($modules_path . "templates/display.ihtml");
} catch (\Exception $e) {
    $tpl->assign('errorMsg', $e->getMessage());
    $tpl->display($modules_path . "templates/NoWiki.tpl");
}
