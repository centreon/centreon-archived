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

$orderBy = "service_description";
$order = "ASC";

// Use whitelist as we can't bind ORDER BY values
if (!empty($_POST['order'])) {
    if (in_array($_POST['order'], ["ASC", "DESC"])) {
        $order = $_POST['order'];
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
    $postServiceTemplate = !empty($_POST['searchServiceTemplate'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchServiceTemplate'])
        : '';
    $searchHasNoProcedure = !empty($_POST['searchHasNoProcedure'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchHasNoProcedure'])
        : '';
    $templatesHasNoProcedure = !empty($_POST['searchTemplatesWithNoProcedure'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['searchTemplatesWithNoProcedure'])
        : '';

    $conf = getWikiConfig($pearDB);
    $WikiURL = $conf['kb_wiki_url'];

    $currentPage = "serviceTemplates";
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

    // Get Services Template Informations
    $query = "
        SELECT SQL_CALC_FOUND_ROWS service_description, service_id
            FROM service
            WHERE service_register = '0'
            AND service_locked = '0' ";
    if (!empty($postServiceTemplate)) {
        $query .= " AND service_description LIKE :postServiceTemplate ";
    }
    $query .= "ORDER BY " . $orderBy . " " . $order . " LIMIT :offset, :limit";

    $statement = $pearDB->prepare($query);
    if (!empty($postServiceTemplate)) {
        $statement->bindValue(':postServiceTemplate', '%' . $postServiceTemplate . '%', PDO::PARAM_STR);
    }
    $statement->bindValue(':offset', $num * $limit, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    $rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

    $selection = [];
    while ($data = $statement->fetch(PDO::FETCH_ASSOC)) {
        $data["service_description"] = str_replace("#S#", "/", $data["service_description"]);
        $data["service_description"] = str_replace("#BS#", "\\", $data["service_description"]);
        $selection[$data["service_description"]] = $data["service_id"];
    }
    $statement->closeCursor();
    unset($data);

    /*
     * Create Diff
     */
    $tpl->assign("host_name", _("Services Templates"));

    $diff = [];
    $templateHostArray = [];
    foreach ($selection as $key => $value) {
        $tplStr = "";
        $tplArr = $proc->getMyServiceTemplateModels($value);
        if ($proc->serviceTemplateHasProcedure($key, $tplArr) == true) {
            $diff[$key] = 1;
        } else {
            $diff[$key] = 0;
        }

        if (!empty($templatesHasNoProcedure)) {
            if (
                $diff[$key] == 1
                || $proc->serviceTemplateHasProcedure($key, $tplArr, PROCEDURE_INHERITANCE_MODE) == true
            ) {
                $rows--;
                unset($diff[$key]);
                continue;
            }
        } elseif (!empty($searchHasNoProcedure)) {
            if ($diff[$key] == 1) {
                $rows--;
                unset($diff[$key]);
                continue;
            }
        }

        if (count($tplArr)) {
            $firstTpl = 1;
            foreach ($tplArr as $key1 => $value1) {
                if ($firstTpl) {
                    $tplStr .= "<a href='" . $WikiURL .
                        "/index.php?title=Service-Template_:_" . $value1 . "' target='_blank'>" . $value1 . "</a>";
                    $firstTpl = 0;
                } else {
                    $tplStr .= "&nbsp;|&nbsp;<a href='" . $WikiURL .
                        "/index.php?title=Service-Template_:_" . $value1 . "' target='_blank'>" . $value1 . "</a>";
                }
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
    $tpl->assign("status", $status);
    $tpl->assign("selection", 3);

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
    $tpl->assign('defaultOrderby', 'service_description');

    // Apply a template definition

    $tpl->display($modules_path . "templates/display.ihtml");
} catch (\Exception $e) {
    $tpl->assign('errorMsg', $e->getMessage());
    $tpl->display($modules_path . "templates/NoWiki.tpl");
}
