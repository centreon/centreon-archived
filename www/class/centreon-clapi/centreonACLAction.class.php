<?php
/*
 * Copyright 2005-2015 CENTREON
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
 */

namespace CentreonClapi;

use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

require_once "centreonObject.class.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Group.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Action.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Action.php";

/**
 * Class for managing ACL Actions
 * @author sylvestre
 *
 */
class CentreonACLAction extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_DESCRIPTION = 1;
    const UNKNOWN_ACTION = "Unknown action";
    protected $relObject;
    protected $aclGroupObj;
    protected $availableActions;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->object = new \Centreon_Object_Acl_Action($dependencyInjector);
        $this->aclGroupObj = new \Centreon_Object_Acl_Group($dependencyInjector);
        $this->relObject = new \Centreon_Object_Relation_Acl_Group_Action($dependencyInjector);
        $this->params = array('acl_action_activate' => '1');
        $this->nbOfCompulsoryParams = 2;
        $this->availableActions = array(
            'generate_cfg',
            'generate_trap',
            'global_event_handler',
            'global_flap_detection',
            'global_host_checks',
            'global_host_obsess',
            'global_host_passive_checks',
            'global_notifications',
            'global_perf_data',
            'global_restart',
            'global_service_checks',
            'global_service_obsess',
            'global_service_passive_checks',
            'global_shutdown',
            'host_acknowledgement',
            'host_checks',
            'host_checks_for_services',
            'host_comment',
            'host_disacknowledgement',
            'host_event_handler',
            'host_flap_detection',
            'host_notifications',
            'host_notifications_for_services',
            'host_schedule_check',
            'host_schedule_downtime',
            'host_schedule_forced_check',
            'host_submit_result',
            'poller_listing',
            'poller_stats',
            'service_acknowledgement',
            'service_checks',
            'service_comment',
            'service_disacknowledgement',
            'service_display_command',
            'service_event_handler',
            'service_flap_detection',
            'service_notifications',
            'service_passive_checks',
            'service_schedule_check',
            'service_schedule_downtime',
            'service_schedule_forced_check',
            'service_submit_result',
            'top_counter'
        );
        $this->activateField = "acl_action_activate";
        $this->action = "ACLACTION";
    }

    /**
     * @param $parameters
     * @return mixed|void
     * @throws CentreonClapiException
     */
    public function initInsertParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['acl_action_description'] = $params[self::ORDER_DESCRIPTION];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME]);
        if ($objectId != 0) {
            $params[1] = "acl_action_" . $params[1];
            $updateParams = array($params[1] => $params[2]);
            $updateParams['objectId'] = $objectId;
            return $updateParams;
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * @param null $parameters
     * @param array $filters
     */
    public function show($parameters = null, $filters = array())
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array("acl_action_id", "acl_action_name", "acl_action_description", "acl_action_activate");
        $paramString = str_replace("acl_action_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    protected function splitParams($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclActionId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($params[0]));
        if (!count($aclActionId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[0]);
        }
        return array($aclActionId[0], $params[1]);
    }

    /**
     * @param $aclActionName
     * @throws CentreonClapiException
     */
    public function getaclgroup($aclActionName)
    {
        if (!isset($aclActionName) || !$aclActionName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclActionId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($aclActionName));
        if (!count($aclActionId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $aclActionName);
        }
        $groupIds = $this->relObject->getacl_group_idFromacl_action_id($aclActionId[0]);
        echo "id;name" . "\n";
        if (count($groupIds)) {
            foreach ($groupIds as $groupId) {
                $result = $this->aclGroupObj->getParameters($groupId, $this->aclGroupObj->getUniqueLabelField());
                echo $groupId . $this->delim . $result[$this->aclGroupObj->getUniqueLabelField()] . "\n";
            }
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function grant($parameters)
    {
        list($aclActionId, $action) = $this->splitParams($parameters);
        if ($action == "*") {
            $actions = $this->availableActions;
        } else {
            $actions = explode("|", $action);
            foreach ($actions as $act) {
                if (!in_array($act, $this->availableActions)) {
                    throw new CentreonClapiException(self::UNKNOWN_ACTION . ":" . $act);
                }
            }
        }
        foreach ($actions as $act) {
            $res = $this->db->query(
                "SELECT COUNT(*) as nb FROM acl_actions_rules WHERE acl_action_rule_id = ? AND acl_action_name = ?",
                array($aclActionId, $act)
            );
            $row = $res->fetchAll();
            if (!$row[0]['nb']) {
                $this->db->query(
                    "INSERT INTO acl_actions_rules (acl_action_rule_id, acl_action_name) VALUES (?, ?)",
                    array($aclActionId, $act)
                );
            }
            unset($res);
        }
        $aclGroupIds = $this->getAclGroupIdsByActionId($aclActionId);
        $this->flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function revoke($parameters)
    {
        list($aclActionId, $action) = $this->splitParams($parameters);
        if ($action == "*") {
            $this->db->query(
                "DELETE FROM acl_actions_rules WHERE acl_action_rule_id = ?",
                array($aclActionId)
            );
        } else {
            $actions = explode("|", $action);
            foreach ($actions as $act) {
                if (!in_array($act, $this->availableActions)) {
                    throw new CentreonClapiException(self::UNKNOWN_ACTION . ":" . $act);
                }
            }
            foreach ($actions as $act) {
                $this->db->query(
                    "DELETE FROM acl_actions_rules WHERE acl_action_rule_id = ? AND acl_action_name = ?",
                    array($aclActionId, $act)
                );
            }
        }
        $aclGroupIds = $this->getAclGroupIdsByActionId($aclActionId);
        $this->flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
    }

    /**
     * @param null $filterName
     * @return bool|void
     */
    public function export($filterName = null)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array();
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }
        $aclActionRuleList = $this->object->getList(
            '*',
            -1,
            0,
            $labelField,
            'ASC',
            $filters
        );

        $exportLine = '';
        foreach ($aclActionRuleList as $aclActionRule) {
            $exportLine .= $this->action . $this->delim . 'ADD' . $this->delim
                . $aclActionRule['acl_action_name'] . $this->delim
                . $aclActionRule['acl_action_description'] . $this->delim . "\n";

            $exportLine .= $this->action . $this->delim . 'SETPARAM' . $this->delim
                . $aclActionRule['acl_action_name'] . $this->delim;

            $exportLine .= 'activate' . $this->delim . $aclActionRule['acl_action_activate'] . $this->delim . "\n";

            $exportLine .= $this->exportGrantActions(
                $aclActionRule['acl_action_id'],
                $aclActionRule['acl_action_name']
            );

            echo $exportLine;
            $exportLine = '';
        }
    }

    /**
     * @param $aclActionRuleId
     * @param $aclActionName
     * @return string
     */
    private function exportGrantActions($aclActionRuleId, $aclActionName)
    {
        $grantActions = '';
        $query = 'SELECT * FROM acl_actions_rules WHERE acl_action_rule_id = :ruleId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ruleId', $aclActionRuleId);
        $stmt->execute();
        $aclActionList = $stmt->fetchAll();

        foreach ($aclActionList as $aclAction) {
            $grantActions .= $this->action . $this->delim . 'GRANT' . $this->delim .
                $aclActionName . $this->delim .
                $aclAction['acl_action_name'] . $this->delim . "\n";
        }

        return $grantActions;
    }

    /**
     * Del Action
     *
     * @param string $objectName
     * @return void
     * @throws CentreonClapiException
     */
    public function del($objectName)
    {
        // $ids will always be an array of 1 or 0 elements as we cannot delete multiple action acl at the same time.
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($objectName));
        if (! empty($ids)) {
            $id = (int) $ids[0];
            //Get all access groups linked to this action group
            $aclGroupIds = $this->getAclGroupIdsByActionId($id);
            $this->object->delete($id);
            $this->flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
            $this->addAuditLog('d', $id, $objectName);
            $aclObj = new CentreonACL($this->dependencyInjector);
            $aclObj->reload(true);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $objectName);
        }
    }

    /**
     * This method flags updated ACL for authentified users.
     *
     * @param int[] $aclGroupIds
     */
    function flagUpdatedAclForAuthentifiedUsers(array $aclGroupIds): void
    {
        $userIds = $this->getUsersIdsByAclGroup($aclGroupIds);
        $readSessionRepository = $this->getReadSessionRepository();
        foreach ($userIds as $userId) {
            try {
                $sessionIds = $readSessionRepository->findSessionIdsByUserId($userId);
                $statement = $this->db->prepare("UPDATE session SET update_acl = '1' WHERE session_id = :sessionId");
                foreach ($sessionIds as $sessionId) {
                    $statement->bindValue(':sessionId', $sessionId, \PDO::PARAM_STR);
                    $statement->execute();
                }
            } catch (\Throwable $ex) {
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
        $queryValues = [];
        foreach ($aclGroupIds as $index => $aclGroupId) {
            $sanitizedAclGroupId = filter_var($aclGroupId, FILTER_VALIDATE_INT);
            if ($sanitizedAclGroupId !== false) {
                $queryValues[":acl_group_id_" . $index] = $sanitizedAclGroupId;
            }
        }

        $aclGroupIdQueryString = "(" . implode(", ", array_keys($queryValues)) . ")";
        $statement = $this->db->prepare(
            "SELECT DISTINCT `contact_contact_id` FROM `acl_group_contacts_relations`
                WHERE `acl_group_id`
                IN $aclGroupIdQueryString"
        );
        foreach ($queryValues as $bindParameter => $bindValue) {
            $statement->bindValue($bindParameter, $bindValue, \PDO::PARAM_INT);
        }
        $statement->execute();
        $userIds = [];
        while($result = $statement->fetch()) {
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
     * Get Acl group ids linked to an action access.
     *
     * @param int $actionId
     * @return int[]
     */
    function getAclGroupIdsByActionId(int $actionId): array
    {
        $aclGroupIds = [];
        $statement = $this->db->prepare(
            "SELECT DISTINCT acl_group_id FROM acl_group_actions_relations
                WHERE acl_action_id = :aclActionId"
        );
        $statement->bindValue(":aclActionId", $actionId, \PDO::PARAM_INT);
        $statement->execute();
        while($result = $statement->fetch()) {
            $aclGroupIds[] = (int) $result["acl_group_id"];
        };

        return $aclGroupIds;
    }

    /**
     * @param array $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters = array())
    {
        if (method_exists($this, "initUpdateParameters")) {
            $params = $this->initUpdateParameters($parameters);
        } else {
            $params = $parameters;
        }

        if (!empty($params)) {
            $uniqueLabel = $this->object->getUniqueLabelField();
            $objectId = $params['objectId'];
            unset($params['objectId']);
            if (
                isset($params[$uniqueLabel])
                && $this->objectExists($params[$uniqueLabel], $objectId) == true
            ) {
                throw new CentreonClapiException(self::NAMEALREADYINUSE);
            }

            $this->object->update($objectId, $params);
            if (array_key_exists("acl_action_activate", $params)) {
                $aclGroupIds = $this->getAclGroupIdsByActionId((int) $objectId);
                $this->flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
            }
            $p = $this->object->getParameters($objectId, $uniqueLabel);

            if (isset($p[$uniqueLabel])) {
                $this->addAuditLog(
                    'c',
                    $objectId,
                    $p[$uniqueLabel],
                    $params
                );
            }
        }
    }
}
