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

require_once "centreonObject.class.php";
require_once "Centreon/Object/Acl/Group.php";
require_once "Centreon/Object/Acl/Action.php";
require_once "Centreon/Object/Relation/Acl/Group/Action.php";

/**
 * Class for managing ACL Actions
 * @author sylvestre
 *
 */
class CentreonACLAction extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_DESCRIPTION       = 1;
    const UNKNOWN_ACTION = "Unknown action";
    protected $relObject;
    protected $aclGroupObj;
    protected $availableActions;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Acl_Action();
        $this->aclGroupObj = new \Centreon_Object_Acl_Group();
        $this->relObject = new \Centreon_Object_Relation_Acl_Group_Action();
        $this->params = array('acl_action_activate' => '1');
        $this->nbOfCompulsoryParams = 2;
        $this->availableActions = array(
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
     * Add action
     *
     * @param string $parameters
     * @return void
     */
    public function add($parameters)
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
        parent::add();
    }

    /**
     * Set Parameters
     *
     * @param string $parameters
     * @return void
     * @throws Exception
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $params[1] = "acl_action_".$params[1];
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Show
     *
     * @param string $parameters
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
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
     * Split params
     *
     * @param string $parameters
     * @return array
     */
    protected function splitParams($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclActionId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($params[0]));
        if (!count($aclActionId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[0]);
        }
        return array($aclActionId[0], $params[1]);
    }

    /**
     * Get Acl Group
     *
     * @param string $parameters
     * @return void
     */
    public function getaclgroup($aclActionName)
    {
        if (!isset($aclActionName) || !$aclActionName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclActionId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($aclActionName));
        if (!count($aclActionId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$aclActionName);
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
     * Grant action
     *
     * @param string $parameters
     * @return void
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
    }

    /**
     * Revoke action
     *
     * @param string $parameters
     * @return void
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
    }

    public function export($filter_name)
    {
        if (!$this->canBeExported($filter_name)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array($labelField => $filter_name);
        $aclActionRuleList = $this->object->getList('*', -1, 0, null, null, $filters);

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

        $query = 'SELECT * FROM acl_actions_rules WHERE acl_action_rule_id = ?';

        $aclActionList = $this->db->fetchAll($query, array($aclActionRuleId));

        foreach ($aclActionList as $aclAction) {
            $grantActions .= $this->action . $this->delim . 'GRANT' . $this->delim .
                $aclActionName . $this->delim .
                $aclAction['acl_action_name'] . $this->delim . "\n";
        }


        return $grantActions;
    }
}
