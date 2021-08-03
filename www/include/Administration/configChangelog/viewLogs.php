<?php
/*
 * Copyright 2005-2019 Centreon
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

require_once "./include/common/autoNumLimit.php";

/**
 * Search a contact by username or alias
 *
 * @global CentreonDB $pearDB
 * @param string $username Username to search
 * @return int[] Returns a contact ids list
 */
function searchUserName($username)
{
    global $pearDB;
    
    $contactIds = [];
    $prepareContact = $pearDB->prepare(
        "SELECT contact_id FROM contact " .
        "WHERE contact_name LIKE :contact_name " .
        "OR contact_alias LIKE :contact_alias"
    );
    $prepareContact->bindValue(':contact_name', "%" . $username . "%", \PDO::PARAM_STR);
    $prepareContact->bindValue(':contact_alias', "%" . $username . "%", \PDO::PARAM_STR);
    if ($prepareContact->execute()) {
        while ($contact = $prepareContact->fetch(\PDO::FETCH_ASSOC)) {
            $contactIds[] = (int) $contact['contact_id'];
        }
    }
    return $contactIds;
}

/*
 * Path to the configuration dir
 */
$path = "./include/Administration/configChangelog/";

/*
 * PHP functions
 */
require_once "./include/common/common-Func.php";
require_once "./class/centreonDB.class.php";

/*
 * Connect to Centstorage Database
 */
$pearDBO = new CentreonDB("centstorage");

$contactList = array();
$dbResult = $pearDB->query(
    "SELECT contact_id, contact_name, contact_alias FROM contact"
);
while ($row = $dbResult->fetch()) {
    $contactList[$row["contact_id"]] = $row["contact_name"] . " (" . $row["contact_alias"] . ")";
}

$searchO = null;
$searchU = null;
$searchP = null;

if (isset($_POST['SearchB'])) {
    $centreon->historySearch[$url] = array();
    $searchO = $_POST["searchO"];
    $centreon->historySearch[$url]["searchO"] = $searchO;
    $searchU = $_POST["searchU"];
    $centreon->historySearch[$url]["searchU"] = $searchU;
    $otype = $_POST["otype"];
    $centreon->historySearch[$url]["otype"] = $otype;
} elseif (isset($_GET['SearchB'])) {
    $centreon->historySearch[$url] = array();
    $searchO = $_GET['searchO'];
    $centreon->historySearch[$url]["searchO"] = $searchO;
    $searchU = $_GET['searchU'];
    $centreon->historySearch[$url]["searchU"] = $searchU;
    $otype = $_GET['otype'];
    $centreon->historySearch[$url]["otype"] = $otype;
} else {
    if (isset($centreon->historySearch[$url]['searchO'])) {
        $searchO = $centreon->historySearch[$url]['searchO'];
    }
    if (isset($centreon->historySearch[$url]['searchU'])) {
        $searchU = $centreon->historySearch[$url]['searchU'];
    }
    if (isset($centreon->historySearch[$url]['otype'])) {
        $otype = $centreon->historySearch[$url]['otype'];
    }
}

/**
 * XSS secure
 */
$otype = isset($otype) ? (int) $otype : null;

//Init QuickForm
$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'SearchB', _("Search"), $attrBtnSuccess);

//Init Smarty
$tpl = initSmartyTpl($path, new Smarty());

$tabAction = array();
$tabAction["a"] = _("Added");
$tabAction["c"] = _("Changed");
$tabAction["mc"] = _("Massive Change");
$tabAction["enable"] = _("Enabled");
$tabAction["disable"] = _("Disabled");
$tabAction["d"] = _("Deleted");

$badge = array(
    _("Added") => "ok",
    _("Changed") => "warning",
    _("Massive Change") => 'warning',
    _("Deleted") => 'critical',
    _("Enabled") => 'ok',
    _("Disabled") => 'critical'
);

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

$objects_type_tab = $centreon->CentreonLogAction->listObjecttype();
sort($objects_type_tab);
$options = "";
foreach ($objects_type_tab as $key => $name) {
    $name = _("$name");
    $options .= "<option value='$key' "
        . (($otype == $key) ? 'selected' : "")
        . ">$name</option>";
}

$tpl->assign("obj_type", $options);

$logQuery = "SELECT SQL_CALC_FOUND_ROWS object_id, object_type, object_name, "
    . "action_log_date, action_type, log_contact_id, action_log_id "
    . "FROM log_action";

$valuesToBind = [];
if (!empty($searchO) || !empty($searchU) || $otype != 0) {
    $logQuery .= ' WHERE ';
    $hasMultipleSubRequest = false;
    
    if (!empty($searchO)) {
        $logQuery .= "object_name LIKE :object_name ";
        $valuesToBind[':object_name'] = "%" . $searchO . "%";
        $hasMultipleSubRequest = true;
    }
    if (!empty($searchU)) {
        $contactIds = searchUserName($searchU);
        if (empty($contactIds)) {
            $contactIds[] = -1;
        }
        if ($hasMultipleSubRequest) {
            $logQuery .= ' AND ';
        }
        $logQuery .= " log_contact_id IN (" . implode(',', $contactIds) . ") ";
        $hasMultipleSubRequest = true;
    }
    if (!is_null($otype) && $otype != 0) {
        if ($hasMultipleSubRequest) {
            $logQuery .= ' AND ';
        }
        $logQuery .= " object_type = :object_type";
        $valuesToBind[':object_type'] = $objects_type_tab[$otype];
    }
}
$logQuery .= " ORDER BY action_log_date DESC LIMIT :from, :nbrElement";
$prepareSelect = $pearDBO->prepare($logQuery);
foreach ($valuesToBind as $label => $value) {
    $prepareSelect->bindValue($label, $value, \PDO::PARAM_STR);
}
$prepareSelect->bindValue(':from', $num * $limit, \PDO::PARAM_INT);
$prepareSelect->bindValue(':nbrElement', $limit, \PDO::PARAM_INT);

$elemArray = array();
$rows = 0;
if ($prepareSelect->execute()) {
    $rows = $pearDBO->query("SELECT FOUND_ROWS()")->fetchColumn();
    while ($res = $prepareSelect->fetch(\PDO::FETCH_ASSOC)) {
        if ($res['object_id']) {
            $objectName = myDecode($res["object_name"]);
            $objectName = stripslashes($objectName);
            $objectName = str_replace(
                array('#S#', '#BS#'),
                array("/", "\\"),
                $objectName
            );
            $objectName = CentreonUtils::escapeSecure(
                $objectName,
                CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
            );

            if ($res['object_type'] == "service") {
                $tmp = $centreon->CentreonLogAction->getHostId($res['object_id']);
                if ($tmp != -1) {
                    if (isset($tmp['h'])) {
                        $tmp2 = $centreon->CentreonLogAction->getHostId($res['object_id']);
                        $tabHost = explode(',', $tmp2["h"]);
                        if (count($tabHost) == 1) {
                            $host_name = CentreonUtils::escapeSecure(
                                $centreon->CentreonLogAction->getHostName($tmp2["h"]),
                                CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
                            );
                        } elseif (count($tabHost) > 1) {
                            $hosts = array();
                            foreach ($tabHost as $key => $value) {
                                $hosts[] = $centreon->CentreonLogAction->getHostName($value);
                            }
                        }
                    } elseif (isset($tmp['hg'])) {
                        $tmp2 = $centreon->CentreonLogAction->getHostId($res['object_id']);
                        $tabHost = explode(',', $tmp2["hg"]);
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
                        "date" => $res['action_log_date'],
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
                        "date" => $res['action_log_date'],
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
                        "date" => $res['action_log_date'],
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
                        "date" => $res['action_log_date'],
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
                } else {
                    if (empty($contactList[$res['log_contact_id']])) {
                        $author = _("unknown");
                    } else {
                        $author = $contactList[$res['log_contact_id']];
                    }

                    // as the relation may have been deleted since the event,
                    // some relations can't be found for this service, while events have been saved for it in the DB
                    $elemArray[] = array(
                        "date" => $res['action_log_date'],
                        "type" => $res['object_type'],
                        "object_name" => $objectName,
                        "action_log_id" => $res['action_log_id'],
                        "object_id" => $res['object_id'],
                        "modification_type" => $tabAction[$res['action_type']],
                        "author" => $author,
                        "change" => $tabAction[$res['action_type']],
                        "host" => "<i>Linked resource has changed</i>",
                        "badge" => $badge[$tabAction[$res['action_type']]]
                    );
                }
                unset($host_name);
                unset($hg_name);
                unset($hosts);
                unset($hostgroups);
            } else {
                $elemArray[] = array(
                    "date" => $res['action_log_date'],
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
}
include "./include/common/checkPagination.php";


/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('search_object_str', _("Object"));
$tpl->assign('search_user_str', _("User"));
$tpl->assign('Search', _('Search'));
$tpl->assign('searchO', htmlentities($searchO));
$tpl->assign('searchU', htmlentities($searchU));
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


if (isset($_POST['searchO'])
    || isset($_POST['searchU'])
    || isset($_POST['otype'])
    || !isset($_GET['object_id'])
) {
    $tpl->display("viewLogs.ihtml");
} else {
    $listAction = $centreon->CentreonLogAction->listAction(
        (int) $_GET['object_id'],
        $_GET['object_type']
    );
    $listModification = $centreon->CentreonLogAction->listModification(
        (int) $_GET['object_id'],
        $_GET['object_type']
    );

    if (isset($listAction)) {
        $tpl->assign("action", $listAction);
    }
    if (isset($listModification)) {
        $tpl->assign("modification", $listModification);
    }

    $tpl->display("viewLogsDetails.ihtml");
}
