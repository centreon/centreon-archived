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
 */

if (!isset($centreon)) {
    exit ();
}

require_once ("./include/common/autoNumLimit.php");

function searchUserName($user_name) {
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
    $searchO = NULL;
}

if (isset($_POST["searchU"])) {
  $searchU = $_POST["searchU"];
  $_SESSION['searchU'] = $searchU;
} elseif (isset($_SESSION["searchU"])) {
  $searchU = $_SESSION["searchU"];
} else {
  $searchU = NULL;
}

if (isset($_POST["otype"])) {
  $otype = $_POST["otype"];
  $_SESSION['otype'] = $otype;
} elseif (isset($_SESSION["otype"])) {
  $otype = $_SESSION["otype"];
} else {
  $otype = NULL;
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
                         
$tpl->assign("date", _("Date"));
$tpl->assign("type", _("Type"));
$tpl->assign("object_id", _("Object ID"));
$tpl->assign("object_name", _("Object"));
$tpl->assign("action", _("Action"));
$tpl->assign("contact_name", _("Contact Name"));
$tpl->assign("field_name", _("Field Name"));
$tpl->assign("field_value", _("Field Value"));
$tpl->assign("before", _("Before"));
$tpl->assign("after", _("After"));
$tpl->assign("logs", _("Logs for "));
$tpl->assign("author", _("Author"));
$tpl->assign("modification_type", _("Modification type"));
$tpl->assign("objTypeLabel", _("Object type : "));
$tpl->assign("objNameLabel", _("Object name : "));
$tpl->assign("noModifLabel", _("No modification was made."));

$objects_type_tab = array();
$objects_type_tab = $oreon->CentreonLogAction->listObjecttype();
$options = "";
foreach ($objects_type_tab as $key => $name) {
    $name = _("$name");
    $options .= "<option value='$key' ".(($otype == $key) ? 'selected' : "").">$name</option>";
}

$tpl->assign("obj_type", $options);

/*
 * Init Data table
 */
$tabz_obj_type = array();
$tabz_obj_name = array();
$tabz_obj_id = array();
$tabz_event_type = array();
$tabz_obj_time = array();
$tabz_contact = array();
$tabz_host = array();

$query = "SELECT SQL_CALC_FOUND_ROWS object_id, object_type, object_name, action_log_date, action_type, log_contact_id FROM log_action";

$where_flag = 1;
if ($searchO) {
    if ($where_flag)  {
        $query .= " WHERE ";
        $where_flag = 0;
    } else {
        $query .= " AND ";
    }
    $query .= " object_name LIKE '%".$searchO."%' ";
}
if ($searchU) {
    if ($where_flag)  {
        $query .= " WHERE ";
        $where_flag = 0;
    } else {
        $query .= " AND ";
    }
    $query .= " log_contact_id IN (".searchUserName($searchU).") ";
}
if (!is_null($otype)) {
  if ($otype != 0) {
    if ($where_flag)  {
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

while ($res = $DBRESULT->fetchRow()) {
    if ($res['object_id']) { 
        $res['obj_name'] = str_replace('#S#', "/", $res["object_name"]);
        $res['obj_name'] = str_replace('#BS#', "\\", $res["obj_name"]);
        $tabz_obj_id[] = $res['object_id'];
        $tabz_obj_type[] = $res['object_type'];
        if ($res['object_type'] == "service") {
            $info = getMyServiceHosts($res['object_id']);
            $array = array();
            if (count($info) != 1) {
                foreach ($info as $host_id) {
                    $array[$host_id] = getMyHostName($host_id);
                }
                $tabz_hosts[] = $array; 
            } else {
                foreach ($info as $host_id) {
                    $tabz_host[] = getMyHostName($host_id);
                }
            }
        } else {
            $tabz_host[] = array();
            $tabz_hosts[] = array();
        }
        $tabz_obj_name[] = $res['obj_name'];
        $tabz_obj_time[] = date('d/m/Y H:i:s', $res['action_log_date']);
        $tabz_event_type[] = $tabAction[$res['action_type']];
        $tabz_contact[] = $contactList[$res['log_contact_id']];
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
$tpl->assign('tabz_obj_id', $tabz_obj_id);
$tpl->assign('tabz_obj_type', $tabz_obj_type);
$tpl->assign('tabz_obj_name', $tabz_obj_name);
$tpl->assign('tabz_event_type', $tabz_event_type);
$tpl->assign('tabz_obj_time', $tabz_obj_time);
$tpl->assign('tabz_contact', $tabz_contact);
$tpl->assign('tabz_host', $tabz_host);
$tpl->assign('tabz_hosts', $tabz_hosts);
$tpl->assign('type_id', $otype);

$tpl->assign('event_type', _("Event Type"));
$tpl->assign('time', _("Time"));
$tpl->assign('contact', _("Contact"));

// Set Color
$tpl->assign("color", array(_("Added") => "#0BF30B", _("Changed") => "#F3D30B", _("Massive Change") => '#F3D30B', _("Deleted") => '#F90531', _("Enabled") => '#06A1F8', _("Disabled") => '#BA0EF0'));

// Pagination 
$tpl->assign('limit', $limit);
$tpl->assign('rows', $rows);

$tpl->assign('p', $p);

if (isset($_POST['searchO']) || isset($_POST['searchU']) 
    || isset($_POST['otype']) || !isset($_GET['object_id'])) {
    $tpl->display("viewLogs.ihtml");
} else {
    $listAction = array();
    $listAction = $oreon->CentreonLogAction->listAction($_GET['object_id'], $_GET['object_type']);
    $listModification = array();
    $listModification = $oreon->CentreonLogAction->listModification($_GET['object_id'], $_GET['object_type']);
  
    if (isset($listAction)) {
        $tpl->assign("action", $listAction);
    }
    if (isset($listModification)) {
        $tpl->assign("modification", $listModification);
    }
  
    $tpl->display("viewLogsDetails.ihtml");
}

?>
