<?php
/*
 * Copyright 2005-2015 Centreon
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

require_once("./include/common/autoNumLimit.php");

function searchUserName($user_name)
{
    global $pearDB;
    $str = "";
  
    $DBRES = $pearDB->query("SELECT contact_id FROM contact WHERE contact_name LIKE '%".$user_name."%' OR contact_alias LIKE '%".$user_name."%'");
    while ($row = $DBRES->fetchRow()) {
        if ($str != "") {
            $str .= ", ";
        }
        $str .= "'" . $row['contact_id'] . "'";
    }
    if ($str == "") {
        $str = "''";
    }
    return $str;
}

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the configuration dir
 */
$path = "./include/Administration/configChangelog/";

/*
 * PHP functions
 */
require_once "./include/common/common-Func.php";
require_once("./class/centreonDB.class.php");

/*
 * Connect to Centstorage Database
 */
$pearDBO = new CentreonDB("centstorage");

$contactList = array();
$DBRES = $pearDB->query("SELECT contact_id, contact_name, contact_alias FROM contact");
while ($row = $DBRES->fetchRow()) {
    $contactList[$row["contact_id"]] = $row["contact_name"] . " (".$row["contact_alias"].")";
}

if (isset($_POST["searchO"])) {
    $searchO = $_POST["searchO"];
    $_SESSION['searchO'] = $searchO;
} elseif (isset($_SESSION["searchO"])) {
    $searchO = $_SESSION["searchO"];
} else {
    $searchO = null;
}

if (isset($_POST["searchU"])) {
    $searchU = $_POST["searchU"];
    $_SESSION['searchU'] = $searchU;
} elseif (isset($_SESSION["searchU"])) {
    $searchU = $_SESSION["searchU"];
} else {
    $searchU = null;
}

if (isset($_POST["otype"])) {
    $otype = $_POST["otype"];
    $_SESSION['otype'] = $otype;
} elseif (isset($_SESSION["otype"])) {
    $otype = $_SESSION["otype"];
} else {
    $otype = null;
}

/*
 * Init QuickForm
 */
$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/*
 * Init Smarty
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tabAction = array();
$tabAction["a"] = _("Added");
$tabAction["c"] = _("Changed");
$tabAction["mc"] = _("Massive Change");
$tabAction["enable"] = _("Enabled");
$tabAction["disable"] = _("Disabled");
$tabAction["d"] = _("Deleted");

$badge = array(_("Added") => "ok", _("Changed") => "warning", _("Massive Change") => 'warning', _("Deleted") => 'critical', _("Enabled") => 'ok', _("Disabled") => 'critical');

$tpl->assign("object_id", _("Object ID"));
$tpl->assign("action", _("Action"));
$tpl->assign("contact_name", _("Contact Name"));
$tpl->assign("field_name", _("Field Name"));
$tpl->assign("field_value", _("Field Value"));
$tpl->assign("before", _("Before"));
$tpl->assign("after", _("After"));
$tpl->assign("logs", _("Logs for "));
$tpl->assign("objTypeLabel", _("Object type : "));
$tpl->assign("objNameLabel", _("Object name : "));
$tpl->assign("noModifLabel", _("No modification was made."));

$objects_type_tab = array();
$objects_type_tab = $centreon->CentreonLogAction->listObjecttype();
$options = "";
foreach ($objects_type_tab as $key => $name) {
    $name = _("$name");
    $options .= "<option value='$key' ".(($otype == $key) ? 'selected' : "").">$name</option>";
}

$tpl->assign("obj_type", $options);

$query = "SELECT SQL_CALC_FOUND_ROWS object_id, object_type, object_name, action_log_date, action_type, log_contact_id, action_log_id FROM log_action";

$where_flag = 1;
if ($searchO) {
    if ($where_flag) {
        $query .= " WHERE ";
        $where_flag = 0;
    } else {
        $query .= " AND ";
    }
    $query .= " object_name LIKE '%".$searchO."%' ";
}
if ($searchU) {
    if ($where_flag) {
        $query .= " WHERE ";
        $where_flag = 0;
    } else {
        $query .= " AND ";
    }
    $query .= " log_contact_id IN (".searchUserName($searchU).") ";
}
if (!is_null($otype)) {
    if ($otype != 0) {
        if ($where_flag) {
            $query .= " WHERE ";
            $where_flag = 0;
        } else {
            $query .= " AND ";
        }
        $query .= " object_type = '".$objects_type_tab[$otype]."' ";
    }
}
$query .= " ORDER BY action_log_date DESC LIMIT ".$num * $limit.", ".$limit;
$DBRESULT = $pearDBO->query($query);

/* Get rows number */
$rows = $pearDB->numberRows();
include("./include/common/checkPagination.php");

$elemArray = array();
while ($res = $DBRESULT->fetchRow()) {
    if ($res['object_id']) {
        $objectName = str_replace(array('#S#', '#BS#'), array("/", "\\"), $res["object_name"]);

        if ($res['object_type'] == "service") {
            $tmp = $centreon->CentreonLogAction->getHostId($res['object_id']);
            if ($tmp != -1) {
                if (isset($tmp['h'])) {
                    $tmp2 = $centreon->CentreonLogAction->getHostId($res['object_id']);
                    $tabHost = split(',', $tmp2["h"]);
                    if (count($tabHost) == 1) {
                        $host_name = $centreon->CentreonLogAction->getHostName($tmp2["h"]);
                    } elseif (count($tabHost) > 1) {
                        $hosts = array();
                        foreach ($tabHost as $key => $value) {
                            $hosts[] = $centreon->CentreonLogAction->getHostName($value);
                        }
                    }
                } elseif (isset($tmp['hg'])) {
                    $tmp2 = $centreon->CentreonLogAction->getHostId($res['object_id']);
                    $tabHost = split(',', $tmp2["hg"]);
                    if (count($tabHost) == 1) {
                        $hg_name = $centreon->CentreonLogAction->getHostGroupName($tmp2["hg"]);
                    } elseif (count($tabHost) > 1) {
                        $hostgroups = array();
                        foreach ($tabHost as $key => $value) {
                            $hostgroups[] = $centreon->CentreonLogAction->getHostGroupName($value);
                        }
                    }
                }
            }
        }

        if ($res['object_type'] == "service") {
            if (isset($host_name) && $host_name != '') {
                $elemArray[] = array(
                    "date" => date('Y/m/d H:i:s', $res['action_log_date']),
                    "type" => $res['object_type'],
                    "object_name" => $objectName,
                    "action_log_id" => $res['action_log_id'],
                    "object_id" => $res['object_id'],
                    "modification_type" => $tabAction[$res['action_type']],
                    "author" => $contactList[$res['log_contact_id']],
                    "change" => $tabAction[$res['action_type']],
                    "host" => $host_name,
                    "badge" => $badge[$tabAction[$res['action_type']]]
                );
            } elseif (isset($hosts) && count($hosts) != 1) {
                $elemArray[] = array(
                    "date" => date('Y/m/d H:i:s', $res['action_log_date']),
                    "type" => $res['object_type'],
                    "object_name" => $objectName,
                    "action_log_id" => $res['action_log_id'],
                    "object_id" => $res['object_id'],
                    "modification_type" => $tabAction[$res['action_type']],
                    "author" => $contactList[$res['log_contact_id']],
                    "change" => $tabAction[$res['action_type']],
                    "hosts" => $hosts,
                    "badge" => $badge[$tabAction[$res['action_type']]]
                );
            } elseif (isset($hg_name) && $hg_name != '') {
                $elemArray[] = array(
                    "date" => date('Y/m/d H:i:s', $res['action_log_date']),
                    "type" => $res['object_type'],
                    "object_name" => $objectName,
                    "action_log_id" => $res['action_log_id'],
                    "object_id" => $res['object_id'],
                    "modification_type" => $tabAction[$res['action_type']],
                    "author" => $contactList[$res['log_contact_id']],
                    "change" => $tabAction[$res['action_type']],
                    "hostgroup" => $hg_name,
                    "badge" => $badge[$tabAction[$res['action_type']]]
                );
            } elseif (isset($hostgroups) && count($hostgroups) != 1) {
                $elemArray[] = array(
                    "date" => date('Y/m/d H:i:s', $res['action_log_date']),
                    "type" => $res['object_type'],
                    "object_name" => $objectName,
                    "action_log_id" => $res['action_log_id'],
                    "object_id" => $res['object_id'],
                    "modification_type" => $tabAction[$res['action_type']],
                    "author" => $contactList[$res['log_contact_id']],
                    "change" => $tabAction[$res['action_type']],
                    "hostgroups" => $hostgroups,
                    "badge" => $badge[$tabAction[$res['action_type']]]
                );
            }
            unset($host_name);
            unset($hg_name);
            unset($hosts);
            unset($hostgroups);
        } else {
            $elemArray[] = array(
                "date" => date('Y/m/d H:i:s', $res['action_log_date']),
                "type" => $res['object_type'],
                "object_name" => $objectName,
                "action_log_id" => $res['action_log_id'],
                "object_id" => $res['object_id'],
                "modification_type" => $tabAction[$res['action_type']],
                "author" => $contactList[$res['log_contact_id']],
                "change" => $tabAction[$res['action_type']],
                "badge" => $badge[$tabAction[$res['action_type']]]
            );
        }
    }
}

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('search_object_str', _("Object"));
$tpl->assign('search_user_str', _("User"));
$tpl->assign('Search', _('Search'));
$tpl->assign('searchO', $searchO);
$tpl->assign('searchU', $searchU);
$tpl->assign('obj_str', _("Object Type"));
$tpl->assign('type_id', $otype);

$tpl->assign('event_type', _("Event Type"));
$tpl->assign('time', _("Time"));
$tpl->assign('contact', _("Contact"));

/* 
 * Pagination 
 */
$tpl->assign('limit', $limit);
$tpl->assign('rows', $rows);

$tpl->assign('p', $p);
$tpl->assign('elemArray', $elemArray);


if (isset($_POST['searchO']) || isset($_POST['searchU']) || isset($_POST['otype']) || !isset($_GET['object_id'])) {
    $tpl->display("viewLogs.ihtml");
} else {
    $listAction = array();
    $listAction = $centreon->CentreonLogAction->listAction($_GET['object_id'], $_GET['object_type']);
    $listModification = array();
    $listModification = $centreon->CentreonLogAction->listModification($_GET['object_id'], $_GET['object_type']);
  
    if (isset($listAction)) {
        $tpl->assign("action", $listAction);
    }
    if (isset($listModification)) {
        $tpl->assign("modification", $listModification);
    }
  
    $tpl->display("viewLogsDetails.ihtml");
}
