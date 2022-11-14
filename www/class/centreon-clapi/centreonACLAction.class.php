<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Group.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Action.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Action.php";
require_once __DIR__ . "/Repository/AclGroupRepository.php";
require_once __DIR__ . "/Repository/SessionRepository.php";

use CentreonClapi\Repository\SessionRepository;
use CentreonClapi\Repository\AclGroupRepository;
use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

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
     * @var AclGroupRepository
     */
    private AclGroupRepository $aclGroupRepository;

    /**
     * @var SessionRepository
     */
    private SessionRepository $sessionRepository;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $db = $dependencyInjector["configuration_db"];
        $this->aclGroupRepository = new AclGroupRepository($db);
        $this->sessionRepository = new SessionRepository($db);
        $this->object = new \Centreon_Object_Acl_Action($dependencyInjector);
        $this->aclGroupObj = new \Centreon_Object_Acl_Group($dependencyInjector);
        $this->relObject = new \Centreon_Object_Relation_Acl_Group_Action($dependencyInjector);
        $this->params = array('acl_action_activate' => '1');
        $this->nbOfCompulsoryParams = 2;
        $this->availableActions = array(
            'generate_cfg',
            'create_edit_poller_cfg',
            'delete_poller_cfg',
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
        $this->updateAclActionsForAuthentifiedUsers($aclActionId);
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
        $this->updateAclActionsForAuthentifiedUsers($aclActionId);
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
     * @throws CentreonClapiException
     */
    public function del($objectName): void
    {
        // $ids will always be an array of 1 or 0 elements as we cannot delete multiple action acl at the same time.
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($objectName));

        if (empty($ids)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $objectName);
        }

        $id = (int) $ids[0];
        $this->updateAclActionsForAuthentifiedUsers($id);
        $this->object->delete($id);
        $this->addAuditLog('d', $id, $objectName);
        $aclObj = new CentreonACL($this->dependencyInjector);
        $aclObj->reload(true);
    }

    /**
     * @param array $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters = array()): void
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
                $this->updateAclActionsForAuthentifiedUsers((int) $objectId);
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

    /**
     * Updates ACL actions for an authentified user from ACL Action ID
     *
     * @param integer $aclActionId
     */
    private function updateAclActionsForAuthentifiedUsers(int $aclActionId): void
    {
        $aclGroupIds = $this->aclGroupRepository->getAclGroupIdsByActionId($aclActionId);
        $this->flagUpdatedAclForAuthentifiedUsers($aclGroupIds);
    }

    /**
     * This method flags updated ACL for authentified users.
     *
     * @param int[] $aclGroupIds
     */
    private function flagUpdatedAclForAuthentifiedUsers(array $aclGroupIds): void
    {
        $userIds = $this->aclGroupRepository->getUsersIdsByAclGroupIds($aclGroupIds);
        $readSessionRepository = $this->getReadSessionRepository();
        foreach ($userIds as $userId) {
            $sessionIds = $readSessionRepository->findSessionIdsByUserId($userId);
            $this->sessionRepository->flagUpdateAclBySessionIds($sessionIds);
        }
    }

    /**
     * This method gets SessionRepository from Service container
     *
     * @return ReadSessionRepositoryInterface
     */
    private function getReadSessionRepository(): ReadSessionRepositoryInterface
    {
        $kernel = \App\Kernel::createForWeb();
        $readSessionRepository = $kernel->getContainer()->get(
            ReadSessionRepositoryInterface::class
        );

        return $readSessionRepository;
    }
}
