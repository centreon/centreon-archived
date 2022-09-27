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

use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

if (!isset($centreon)) {
    exit();
}

/**
 * @param null $name
 * @return bool
 */
function testActionExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('acl_action_id');
    }
    $query = "SELECT acl_action_id, acl_action_name FROM acl_actions " .
        "WHERE acl_action_name = '" . htmlentities($name, ENT_QUOTES, "UTF-8") . "'";
    $dbResult = $pearDB->query($query);
    $action = $dbResult->fetch();
    #Modif case
    if ($dbResult->rowCount() >= 1 && $action["acl_action_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($dbResult->rowCount() >= 1 && $action["acl_action_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $aclActionId
 * @param array $actions
 */
function enableActionInDB($aclActionId = null, $actions = array())
{
    if ($aclActionId === null && empty($actions)) {
        return;
    }

    global $pearDB, $centreon;

    if ($aclActionId) {
        $actions = array($aclActionId => "1");
    }

    $queryValues = [];

    foreach ($actions as $key => $value) {
        $sanitizedAclActionId = filter_var($key, FILTER_VALIDATE_INT);
        if ($sanitizedAclActionId === false) {
            throw new \InvalidArgumentException("Invalid id");
        }
        $queryValues[":acl_action_id_" . $sanitizedAclActionId] = $sanitizedAclActionId;
        $statement = $pearDB->prepare(
            "UPDATE acl_actions SET acl_action_activate = '1' WHERE acl_action_id = :acl_action_id_"
                . $sanitizedAclActionId
        );
        $statement->bindValue(
            ":acl_action_id_" . $sanitizedAclActionId,
            $queryValues[":acl_action_id_" . $sanitizedAclActionId],
            \PDO::PARAM_INT
        );
        $statement->execute();

        $statementSelect = $pearDB->prepare(
            "SELECT acl_action_name FROM `acl_actions` WHERE acl_action_id = :acl_action_id_"
                . $sanitizedAclActionId . " LIMIT 1"
        );
        $statementSelect->bindValue(
            ":acl_action_id_" . $sanitizedAclActionId,
            $queryValues[":acl_action_id_" . $sanitizedAclActionId],
            \PDO::PARAM_INT
        );
        $statementSelect->execute();
        $row = $statementSelect->fetch();
        $centreon->CentreonLogAction->insertLog(
            "action access",
            $sanitizedAclActionId,
            $row['acl_action_name'],
            "enable"
        );
    }

    updateACLActionsForAuthentifiedUsers($queryValues);
}

/**
 * @param null $aclActionId
 * @param array $actions
 */
function disableActionInDB($aclActionId = null, $actions = array())
{
    if ($aclActionId === null && empty($actions)) {
        return;
    }

    global $pearDB, $centreon;

    if ($aclActionId) {
        $actions = array($aclActionId => "1");
    }

    $queryValues = [];

    foreach ($actions as $key => $value) {
        $sanitizedAclActionId = filter_var($key, FILTER_VALIDATE_INT);
        if ($sanitizedAclActionId === false) {
            throw new \InvalidArgumentException("Invalid id");
        }
        $queryValues[":acl_action_id_" . $sanitizedAclActionId] = $sanitizedAclActionId;
        $statement = $pearDB->prepare(
            "UPDATE acl_actions SET acl_action_activate = '0' WHERE acl_action_id = :acl_action_id_"
                . $sanitizedAclActionId
        );
        $statement->bindValue(
            ":acl_action_id_" . $sanitizedAclActionId,
            $queryValues[":acl_action_id_" . $sanitizedAclActionId],
            \PDO::PARAM_INT
        );
        $statement->execute();

        $statementSelect = $pearDB->prepare(
            "SELECT acl_action_name FROM `acl_actions` WHERE acl_action_id = :acl_action_id_"
                . $sanitizedAclActionId . " LIMIT 1"
        );
        $statementSelect->bindValue(
            ":acl_action_id_" . $sanitizedAclActionId,
            $queryValues[":acl_action_id_" . $sanitizedAclActionId],
            \PDO::PARAM_INT
        );
        $statementSelect->execute();
        $row = $statementSelect->fetch();
        $centreon->CentreonLogAction->insertLog(
            "action access",
            $sanitizedAclActionId,
            $row['acl_action_name'],
            "disable"
        );
    }

    updateACLActionsForAuthentifiedUsers($queryValues);
}

/**
 *
 * delete an action rules
 * @param $actions
 */
function deleteActionInDB($actions = array())
{
    global $pearDB, $centreon;

    $aclGroupIds = [];
    foreach ($actions as $key => $value) {
        $sanitizedAclActionId = filter_var($key, FILTER_VALIDATE_INT);
        if ($sanitizedAclActionId === false) {
            throw new \InvalidArgumentException("Invalid id");
        }
        $queryValues[":acl_action_id_" . $sanitizedAclActionId] = $sanitizedAclActionId;
        $statement = $pearDB->prepare(
            "SELECT acl_action_name FROM `acl_actions`
                WHERE acl_action_id = :acl_action_id_" . $sanitizedAclActionId . " LIMIT 1"
        );
        $statement->bindValue(
            ":acl_action_id_" . $sanitizedAclActionId,
            $queryValues[":acl_action_id_" . $sanitizedAclActionId],
            \PDO::PARAM_INT
        );
        $statement->execute();
        $row = $statement->fetch();
        $aclActionIdQueryString = "(" . implode(", ", array_keys($queryValues)) . ")";
        $statement = $pearDB->prepare(
            "SELECT DISTINCT acl_group_id FROM acl_group_actions_relations
                WHERE acl_action_id IN $aclActionIdQueryString"
        );
        foreach ($queryValues as $bindParameter => $bindValue) {
            $statement->bindValue($bindParameter, $bindValue, \PDO::PARAM_INT);
        }
        $statement->execute();
        while ($result = $statement->fetch()) {
            $aclGroupIds[] = (int) $result["acl_group_id"];
        }
        $pearDB->query("DELETE FROM acl_actions WHERE acl_action_id = '" . $key . "'");
        $pearDB->query("DELETE FROM acl_actions_rules WHERE acl_action_rule_id = '" . $key . "'");
        $pearDB->query("DELETE FROM acl_group_actions_relations WHERE acl_action_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("action access", $key, $row['acl_action_name'], "d");
    }
    flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
}

/**
 *
 * Duplicate an action rules
 * @param $actions
 * @param $nbrDup
 */
function multipleActionInDB($actions = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    foreach ($actions as $key => $value) {
        $dbResult = $pearDB->query("SELECT * FROM acl_actions WHERE acl_action_id = '" . $key . "' LIMIT 1");
        $row = $dbResult->fetch();
        $row["acl_action_id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $value2 = is_int($value2) ? (string) $value2 : $value2;
                $key2 == "acl_action_name" ? ($acl_action_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "acl_action_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($acl_action_name)) {
                    $fields["acl_action_name"] = $acl_action_name;
                }
            }
            if (testActionExistence($acl_action_name)) {
                $val ? $rq = "INSERT INTO acl_actions VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
                $maxId = $dbResult->fetch();
                $dbResult->closeCursor();
                if (isset($maxId["MAX(acl_action_id)"])) {
                    $query = "SELECT DISTINCT acl_group_id,acl_action_id FROM acl_group_actions_relations " .
                        " WHERE acl_action_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $query = "INSERT INTO acl_group_actions_relations VALUES (:acl_action_id, :acl_group_id)";
                    $statement =  $pearDB->prepare($query);
                    while ($cct = $dbResult->fetch()) {
                        $statement->bindValue(':acl_action_id', (int) $maxId["MAX(acl_action_id)"], \PDO::PARAM_INT);
                        $statement->bindValue(':acl_group_id', (int) $cct["acl_group_id"], \PDO::PARAM_INT);
                        $statement->execute();
                    }

                    # Duplicate Actions
                    $query = "SELECT acl_action_rule_id,acl_action_name FROM acl_actions_rules " .
                        "WHERE acl_action_rule_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $query = "INSERT INTO acl_actions_rules VALUES (NULL, :acl_action_id, :acl_action_name)";
                    $statement = $pearDB->prepare($query);
                    while ($acl = $dbResult->fetch()) {
                        $statement->bindValue(':acl_action_id', (int) $maxId["MAX(acl_action_id)"], \PDO::PARAM_INT);
                        $statement->bindValue(':acl_action_name', $acl["acl_action_name"], \PDO::PARAM_STR);
                        $statement->execute();
                    }

                    $dbResult->closeCursor();
                    $centreon->CentreonLogAction->insertLog(
                        "action access",
                        $maxId["MAX(acl_action_id)"],
                        $acl_action_name,
                        "a",
                        $fields
                    );
                }
            }
        }
    }
}

/**
 *
 * Insert all information in DB
 * @param $ret
 */
function insertActionInDB($ret = array())
{
    global $form, $centreon;

    $aclActionId = insertAction($ret);
    updateGroupActions($aclActionId, $ret);
    updateRulesActions($aclActionId, $ret);
    $ret = $form->getSubmitValues();
    flagUpdatedAclForAuthentifiedUsers($ret['acl_groups']);
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("action access", $aclActionId, $ret['acl_action_name'], "a", $fields);

    return $aclActionId;
}

/**
 *
 * Insert actions
 * @param $ret
 */
function insertAction($ret)
{
    global $form, $pearDB;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $statement = $pearDB->prepare(
        "INSERT INTO acl_actions (acl_action_name, acl_action_description, acl_action_activate) "
            . "VALUES (:aclActionName, :aclActionDescription, :aclActionActivate)"
    );
    $statement->bindValue(
        ':aclActionName',
        htmlentities($ret["acl_action_name"], ENT_QUOTES, "UTF-8"),
        \PDO::PARAM_STR
    );
    $statement->bindValue(
        ':aclActionDescription',
        htmlentities($ret["acl_action_description"], ENT_QUOTES, "UTF-8"),
        \PDO::PARAM_STR
    );
    $statement->bindValue(
        ':aclActionActivate',
        htmlentities(
            (isset($ret["acl_action_activate"])
                ? $ret["acl_action_activate"]["acl_action_activate"]
                : ''
            ),
            ENT_QUOTES,
            "UTF-8"
        ),
        \PDO::PARAM_STR
    );
    $statement->execute();
    $dbResult = $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
    $cg_id = $dbResult->fetch();
    return ($cg_id["MAX(acl_action_id)"]);
}

/**
 *
 * Summary function
 * @param $aclActionId
 */
function updateActionInDB($aclActionId = null)
{
    global $form, $centreon;

    if (!$aclActionId) {
        return;
    }
    updateAction($aclActionId);
    updateGroupActions($aclActionId);
    $ret = $form->getSubmitValues();
    flagUpdatedAclForAuthentifiedUsers($ret['acl_groups']);
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("action access", $aclActionId, $ret['acl_action_name'], "c", $fields);
}

/**
 *
 * Update all Actions
 * @param $aclActionId
 */
function updateAction($aclActionId = null)
{
    if (!$aclActionId) {
        return;
    }
    global $form, $pearDB;

    $ret = $form->getSubmitValues();
    $rq = "UPDATE acl_actions ";
    $rq .= "SET acl_action_name = '" . htmlentities($ret["acl_action_name"], ENT_QUOTES, "UTF-8") . "', " .
        "acl_action_description = '" . htmlentities($ret["acl_action_description"], ENT_QUOTES, "UTF-8") . "', " .
        "acl_action_activate = '" .
        htmlentities($ret["acl_action_activate"]["acl_action_activate"], ENT_QUOTES, "UTF-8") . "' " .
        "WHERE acl_action_id = '" . $aclActionId . "'";
    $pearDB->query($rq);
}

/**
 *
 * Update group action information in DB
 * @param $aclActionId
 * @param $ret
 */
function updateGroupActions($aclActionId, $ret = array())
{
    if (!$aclActionId) {
        return;
    }
    global $form, $pearDB;

    $rq = "DELETE FROM acl_group_actions_relations WHERE acl_action_id = :acl_action_id";
    $statement = $pearDB->prepare($rq);
    $statement->bindValue(':acl_action_id', (int) $aclActionId, \PDO::PARAM_INT);
    $statement->execute();
    if (isset($_POST["acl_groups"])) {
        foreach ($_POST["acl_groups"] as $id) {
            $rq = "INSERT INTO acl_group_actions_relations ";
            $rq .= "(acl_group_id, acl_action_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $aclActionId . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * update all Rules in DB
 * @param $aclActionId
 * @param $ret
 */
function updateRulesActions($aclActionId, $ret = array())
{
    global $form, $pearDB;

    if (!$aclActionId) {
        return;
    }

    $rq = "DELETE FROM acl_actions_rules WHERE acl_action_rule_id = :acl_action_rule_id";
    $statement = $pearDB->prepare($rq);
    $statement->bindValue(':acl_action_rule_id', (int) $aclActionId, \PDO::PARAM_INT);
    $statement->execute();

    $actions = array();
    $actions = listActions();

    foreach ($actions as $action) {
        if (isset($_POST[$action])) {
            $rq = "INSERT INTO acl_actions_rules ";
            $rq .= "(acl_action_rule_id, acl_action_name) ";
            $rq .= "VALUES ";
            $rq .= "('" . $aclActionId . "', '" . $action . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * list all actions
 */
function listActions()
{
    global $dependencyInjector;

    $actions = array();
    $informationsService = $dependencyInjector['centreon_remote.informations_service'];
    $serverIsMaster = $informationsService->serverIsMaster();

    # Global Functionnality access
    $actions[] = "poller_listing";
    $actions[] = "poller_stats";
    $actions[] = "top_counter";

    # Services Actions
    if ($serverIsMaster) {
        $actions[] = "service_checks";
        $actions[] = "service_notifications";
    }
    $actions[] = "service_acknowledgement";
    $actions[] = "service_disacknowledgement";
    $actions[] = "service_schedule_check";
    $actions[] = "service_schedule_forced_check";
    $actions[] = "service_schedule_downtime";
    $actions[] = "service_comment";
    if ($serverIsMaster) {
        $actions[] = "service_event_handler";
        $actions[] = "service_flap_detection";
        $actions[] = "service_passive_checks";
    }
    $actions[] = "service_submit_result";
    $actions[] = "service_display_command";

    # Hosts Actions
    if ($serverIsMaster) {
        $actions[] = "host_checks";
        $actions[] = "host_notifications";
    }
    $actions[] = "host_acknowledgement";
    $actions[] = "host_disacknowledgement";
    $actions[] = "host_schedule_check";
    $actions[] = "host_schedule_forced_check";
    $actions[] = "host_schedule_downtime";
    $actions[] = "host_comment";
    if ($serverIsMaster) {
        $actions[] = "host_event_handler";
        $actions[] = "host_flap_detection";
        $actions[] = "host_checks_for_services";
        $actions[] = "host_notifications_for_services";
    }
    $actions[] = "host_submit_result";

    # Global Nagios External Commands
    $actions[] = "global_shutdown";
    $actions[] = "global_restart";
    $actions[] = "global_notifications";
    $actions[] = "global_service_checks";
    $actions[] = "global_service_passive_checks";
    $actions[] = "global_host_checks";
    $actions[] = "global_host_passive_checks";
    $actions[] = "global_event_handler";
    $actions[] = "global_flap_detection";
    $actions[] = "global_service_obsess";
    $actions[] = "global_host_obsess";
    $actions[] = "global_perf_data";

    $actions[] = "create_edit_poller_cfg";
    $actions[] = "delete_poller_cfg";
    $actions[] = "generate_cfg";
    $actions[] = "generate_trap";

    return $actions;
}

/**
 * Updates ACL actions for an authentified user from ACL Action ID.
 * Ex: $queryValue = [':acl_action_id_1' => 1, ..., ':acl_action_id_3' => 3]
 *
 * @param array<string,string> $queryValues
 */
function updateAclActionsForAuthentifiedUsers(array $queryValues): void
{
    $aclGroupIds = getAclGroupIdsByActionIds($queryValues);
    flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
}

/**
 * This method flags updated ACL for authentified users.
 *
 * @param int[] $aclGroupIds
 */
function flagUpdatedAclForAuthentifiedUsers(array $aclGroupIds): void
{
    global $pearDB;
    $userIds = getUsersIdsByAclGroup($aclGroupIds);
    $readSessionRepository = getReadSessionRepository();
    foreach ($userIds as $userId) {
        $sessionIds = $readSessionRepository->findSessionIdsByUserId($userId);
        $statement = $pearDB->prepare("UPDATE session SET update_acl = '1' WHERE session_id = :sessionId");
        foreach ($sessionIds as $sessionId) {
            $statement->bindValue(':sessionId', $sessionId, \PDO::PARAM_STR);
            $statement->execute();
        }
    }
}

/**
 * This function returns user ids from ACL Group Ids
 *
 * @param int[] $aclGroupIds
 * @return int[]
 */
function getUsersIdsByAclGroup(array $aclGroupIds): array
{
    global $pearDB;

    $queryValues = [];
    foreach ($aclGroupIds as $index => $aclGroupId) {
        $sanitizedAclGroupId = filter_var($aclGroupId, FILTER_VALIDATE_INT);
        if ($sanitizedAclGroupId !== false) {
            $queryValues[":acl_group_id_" . $index] = $sanitizedAclGroupId;
        }
    }

    $aclGroupIdQueryString = "(" . implode(", ", array_keys($queryValues)) . ")";
    $statement = $pearDB->prepare(
        "SELECT DISTINCT `contact_contact_id` FROM `acl_group_contacts_relations`
            WHERE `acl_group_id`
            IN $aclGroupIdQueryString"
    );
    foreach ($queryValues as $bindParameter => $bindValue) {
        $statement->bindValue($bindParameter, $bindValue, \PDO::PARAM_INT);
    }
    $statement->execute();
    $userIds = [];
    while ($result = $statement->fetch()) {
        $userIds[] = (int) $result["contact_contact_id"];
    }

    return $userIds;
}

/**
 * This method gets SessionRepository from Service container
 *
 * @return ReadSessionRepositoryInterface
 */
function getReadSessionRepository(): ReadSessionRepositoryInterface
{
    $kernel = \App\Kernel::createForWeb();
    $readSessionRepository = $kernel->getContainer()->get(
        ReadSessionRepositoryInterface::class
    );

    return $readSessionRepository;
}

/**
 * Returns ACL Group IDs
 * Ex: $queryValue = [':acl_action_id_1' => 1, ..., ':acl_action_id_3' => 3]
 *
 * @param array<string,string> $queryValues
 * @return int[]
 */
function getAclGroupIdsByActionIds(array $queryValues): array
{
    global $pearDB;
    $aclActionIdQueryString = "(" . implode(", ", array_keys($queryValues)) . ")";
    $statement = $pearDB->prepare(
        "SELECT DISTINCT acl_group_id FROM acl_group_actions_relations
            WHERE acl_action_id IN $aclActionIdQueryString"
    );
    foreach ($queryValues as $bindParameter => $bindValue) {
        $statement->bindValue($bindParameter, $bindValue, \PDO::PARAM_INT);
    }
    $statement->execute();
    $aclGroupIds = [];
    while ($result = $statement->fetch()) {
        $aclGroupIds[] = (int) $result["acl_group_id"];
    }

    return $aclGroupIds;
}
