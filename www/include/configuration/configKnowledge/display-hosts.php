<?php
/*
 * Copyright 2005-2019 CENTREON
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

if (!isset($limit) || !$limit) {
    $limit = $centreon->optGen["maxViewConfiguration"];
}

if (isset($_POST['searchHost'])) {
    if (!isset($_POST['searchHasNoProcedure']) && isset($_GET['searchHasNoProcedure'])) {
        unset($_REQUEST['searchHasNoProcedure']);
    }
    if (!isset($_POST['searchTemplatesWithNoProcedure']) && isset($_GET['searchTemplatesWithNoProcedure'])) {
        unset($_REQUEST['searchTemplatesWithNoProcedure']);
    }
}

$order = "ASC";
$orderby = "host_name";
if (isset($_REQUEST['order'])
    && $_REQUEST['order']
    && isset($_REQUEST['orderby'])
    && $_REQUEST['orderby']
) {
    $order = $_REQUEST['order'];
    $orderby = $_REQUEST['orderby'];
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
    $conf = getWikiConfig($pearDB);
    $WikiURL = $conf['kb_wiki_url'];

    $currentPage = "hosts";
    require_once $modules_path . 'search.php';


    // Init Status Template
    $status = array(
        0 => "<font color='orange'> " . _("No wiki page defined") . " </font>",
        1 => "<font color='green'> " . _("Wiki page defined") . " </font>"
    );
    $line = array(0 => "list_one", 1 => "list_two");
    $proc = new procedures(
        $pearDB
    );
    $proc->setHostInformations();
    $proc->setServiceInformations();

    $query = "SELECT SQL_CALC_FOUND_ROWS host_name, host_id, host_register, ehi_icon_image " .
        "FROM extended_host_information ehi, host ";
    if (isset($_REQUEST['searchPoller']) && $_REQUEST['searchPoller']) {
        $query .= " JOIN ns_host_relation nhr ON nhr.host_host_id = host.host_id ";
    }
    if (isset($_REQUEST['searchHostgroup']) && $_REQUEST['searchHostgroup']) {
        $query .= " JOIN hostgroup_relation hgr ON hgr.host_host_id = host.host_id ";
    }
    $query .= " WHERE host.host_id = ehi.host_host_id ";
    if (isset($_REQUEST['searchPoller']) && $_REQUEST['searchPoller']) {
        $query .= " AND nhr.nagios_server_id = " . $pearDB->escape($_REQUEST['searchPoller']);
    }
    $query .= " AND host.host_register = '1' ";
    if (isset($_REQUEST['searchHostgroup']) && $_REQUEST['searchHostgroup']) {
        $query .= " AND hgr.hostgroup_hg_id = " . $pearDB->escape($_REQUEST['searchHostgroup']);
    }
    if (isset($_REQUEST['searchHost']) && $_REQUEST['searchHost']) {
        $query .= " AND host_name LIKE '%" . $pearDB->escape($_REQUEST['searchHost']) . "%'";
    }
    $query .= " ORDER BY " . $orderby . " " . $order . " LIMIT " . $num * $limit . ", " . $limit;
    $dbResult = $pearDB->query($query);

    $rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

    $selection = array();
    while ($data = $dbResult->fetch()) {
        if ($data["host_register"] == 1) {
            $selection[$data["host_name"]] = $data["host_id"];
        }
        $proc->hostIconeList[$data["host_name"]] = "./img/media/" . $proc->getImageFilePath($data["ehi_icon_image"]);
    }
    $dbResult->closeCursor();
    unset($data);

    /*
     * Create Diff
     */
    $tpl->assign("host_name", _("Hosts"));

    $diff = array();
    $templateHostArray = array();

    foreach ($selection as $key => $value) {
        $tplStr = "";
        $tplArr = $proc->getMyHostMultipleTemplateModels($value);
        if ($proc->hostHasProcedure($key, $tplArr) == true) {
            $diff[$key] = 1;
        } else {
            $diff[$key] = 0;
        }

        if (isset($_REQUEST['searchTemplatesWithNoProcedure'])) {
            if ($diff[$key] == 1 || $proc->hostHasProcedure($key, $tplArr, PROCEDURE_INHERITANCE_MODE) == true) {
                $rows--;
                unset($diff[$key]);
                continue;
            }
        } elseif (isset($_REQUEST['searchHasNoProcedure'])) {
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
                        "/index.php?title=Host:" . $value1 . "' target='_blank'>" . $value1 . "</a>";
                    $firstTpl = 0;
                } else {
                    $tplStr .= "&nbsp;|&nbsp;<a href='" . $WikiURL .
                        "/index.php?title=Host:" . $value1 . "' target='_blank'>" . $value1 . "</a>";
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
    $tpl->assign("selection", 0);
    $tpl->assign("icone", $proc->getIconeList());

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
    $tpl->assign('orderby', $orderby);
    $tpl->assign('defaultOrderby', 'host_name');

    // Apply a template definition
    $tpl->display($modules_path . "templates/display.ihtml");
} catch (\Exception $e) {
    $tpl->assign('errorMsg', $e->getMessage());
    $tpl->display($modules_path . "templates/NoWiki.tpl");
}
