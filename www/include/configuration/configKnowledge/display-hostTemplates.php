<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/service.php $
 * SVN : $Id: service.php 8549 2009-07-01 16:20:26Z shotamchay $
 *
 */

if (!isset($oreon)) {
    exit();
}

$modules_path = $centreon_path . "www/include/configuration/configKnowledge/";
require_once $modules_path . 'functions.php';


if (!isset($limit) || !$limit) {
    $limit = $oreon->optGen["maxViewConfiguration"];
}

if (isset($_POST['num']) && $_POST['num'] == 0) {
    $_GET['num'] = 0;
}

if (isset($_POST['searchHostTemplate'])) {
    if (!isset($_POST['searchHasNoProcedure']) && isset($_GET['searchHasNoProcedure'])) {
        unset($_REQUEST['searchHasNoProcedure']);
    }
    if (!isset($_POST['searchTemplatesWithNoProcedure']) && isset($_GET['searchTemplatesWithNoProcedure'])) {
        unset($_REQUEST['searchTemplatesWithNoProcedure']);
    }
}

$order = "ASC";
$orderby = "host_name";
if (isset($_REQUEST['order']) && $_REQUEST['order'] && isset($_REQUEST['orderby']) && $_REQUEST['orderby']) {
    $order = $_REQUEST['order'];
    $orderby = $_REQUEST['orderby'];
}

require_once "./include/common/autoNumLimit.php";

/*
 * Add paths
 */
set_include_path(get_include_path() . PATH_SEPARATOR . $modules_path);

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once "HTML/QuickForm/advmultiselect.php";
require_once "HTML/QuickForm/Renderer/ArraySmarty.php";

require_once $centreon_path . "/www/class/centreon-knowledge/procedures_DB_Connector.class.php";
require_once $centreon_path . "/www/class/centreon-knowledge/procedures.class.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($modules_path, $tpl);

try {
    $conf = getWikiConfig($pearDB);
    $WikiURL = $conf['kb_wiki_url'];

    $currentPage = "hostTemplates";
    require_once $modules_path . 'search.php';

    /*
     * Init Status Template
     */
    $status = array(
        0 => "<font color='orange'> " . _("No wiki page defined") . " </font>",
        1 => "<font color='green'> " . _("Wiki page defined") . " </font>"
    );
    $line = array(0 => "list_one", 1 => "list_two");

    $proc = new procedures(
        3,
        $conf['kb_db_name'],
        $conf['kb_db_user'],
        $conf['kb_db_host'],
        $conf['kb_db_password'],
        $pearDB,
        $conf['kb_db_prefix']
    );
    $proc->setHostInformations();
    $proc->setServiceInformations();

    $query = "SELECT SQL_CALC_FOUND_ROWS host_name, host_id, host_register, ehi_icon_image " .
        "FROM host, extended_host_information ehi " .
        "WHERE host.host_id = ehi.host_host_id " .
        "AND host.host_register = '0' " .
        "AND host.host_locked = '0' ";
    if (isset($_REQUEST['searchHostTemplate']) && $_REQUEST['searchHostTemplate']) {
        $query .= " AND host.host_name LIKE '%" . $_REQUEST['searchHostTemplate'] . "%' ";
    }
    $query .= " ORDER BY $orderby $order LIMIT " . $num * $limit . ", " . $limit;
    $DBRESULT = $pearDB->query($query);

    $res = $pearDB->query("SELECT FOUND_ROWS() as numrows");
    $row = $res->fetchRow();
    $rows = $row['numrows'];

    $selection = array();
    while ($data = $DBRESULT->fetchRow()) {
        if ($data["host_register"] == 0) {
            $selection[$data["host_name"]] = $data["host_id"];
        }
        $proc->hostIconeList[$data["host_name"]] = "./img/media/" . $proc->getImageFilePath($data["ehi_icon_image"]);
    }
    $DBRESULT->free();
    unset($data);

    /*
     * Create Diff
     */

    $tpl->assign("host_name", _("Hosts Templates"));

    $diff = array();
    $templateHostArray = array();
    foreach ($selection as $key => $value) {
        $tplStr = "";
        $tplArr = $proc->getMyHostMultipleTemplateModels($value);
        if ($proc->hostTemplateHasProcedure($key, $tplArr) == true) {
            $diff[$key] = 1;
        } else {
            $diff[$key] = 0;
        }

        if (isset($_REQUEST['searchTemplatesWithNoProcedure'])) {
            if ($diff[$key] == 1 || $proc->hostTemplateHasProcedure($key, $tplArr, PROCEDURE_INHERITANCE_MODE) == true) {
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
                    $tplStr .= " <a href='" . $WikiURL . "/index.php?title=Host-Template:$value1' target = '_blank' > " .
                        $value1 . "</a > ";
                    $firstTpl = 0;
                } else {
                    $tplStr .= "&nbsp;|&nbsp;<a href = '" . $WikiURL .
                        "/index.php?title=Host-Template:$value1' target = '_blank' > " . $value1 . "</a > ";
                }
            }
        }
        $templateHostArray[$key] = $tplStr;
        unset($tplStr);
    }

    include("./include/common/checkPagination.php");

    if (isset($templateHostArray)) {
        $tpl->assign("templateHostArray", $templateHostArray);
    }

    $WikiVersion = getWikiVersion($WikiURL . '/api.php');
    $tpl->assign("WikiVersion", $WikiVersion);
    $tpl->assign("WikiURL", $WikiURL);
    $tpl->assign("content", $diff);
    $tpl->assign("status", $status);
    $tpl->assign("selection", 2);
    $tpl->assign("icone", $proc->getIconeList());

    /*
     * Send template in order to open
     */

    /*
     * translations
     */
    $tpl->assign("status_trans", _("Status"));
    $tpl->assign("actions_trans", _("Actions"));
    $tpl->assign("template_trans", _("Template"));

    /*
     * Template
     */
    $tpl->assign("lineTemplate", $line);
    $tpl->assign('limit', $limit);

    $tpl->assign('order', $order);
    $tpl->assign('orderby', $orderby);
    $tpl->assign('defaultOrderby', 'host_name');

    /*
     * Apply a template definition
     */

    $tpl->display($modules_path . "templates/display.ihtml");
} catch (\Exception $e) {
    $tpl->assign('errorMsg', $e->getMessage());
    $tpl->display($modules_path . "templates/NoWiki.tpl");
}