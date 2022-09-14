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
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Menu.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Acl/Resource.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Contact/Contact.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Contact/Group.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Resource.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Menu.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Action.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Contact/Contact.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Acl/Group/Contact/Group.php";
require_once __DIR__ . "/Repository/AclGroupRepository.php";
require_once __DIR__ . "/Repository/SessionRepository.php";

use CentreonClapi\Repository\SessionRepository;
use CentreonClapi\Repository\AclGroupRepository;
use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

/**
 * Class for managing ACL groups
 * @author sylvestre
 *
 */
class CentreonACLGroup extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_ALIAS = 1;

    public $aDepends = array(
        'CONTACT',
        'CG',
        'ACLMENU',
        'ACLACTION',
        'ACLRESOURCE'
    );

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
        $this->object = new \Centreon_Object_Acl_Group($dependencyInjector);
        $db = $dependencyInjector['configuration_db'];
        $this->aclGroupRepository = new AclGroupRepository($db);
        $this->sessionRepository = new SessionRepository($db);
        $this->params = array(
            'acl_group_changed' => '1',
            'acl_group_activate' => '1'
        );
        $this->nbOfCompulsoryParams = 2;
        $this->activateField = "acl_group_activate";
        $this->action = "ACLGROUP";
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
        $addParams['acl_group_alias'] = $params[self::ORDER_ALIAS];
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
            $params[1] = "acl_group_" . $params[1];
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
        $params = array("acl_group_id", "acl_group_name", "acl_group_alias", "acl_group_activate");
        $paramString = str_replace("acl_group_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList(
            $params,
            -1,
            0,
            null,
            null,
            $filters
        );
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
     * Magic method
     *
     * @param string $name
     * @param array $args
     * @return void
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|set|add|del)([a-zA-Z_]+)/", $name, $matches)) {
            $relclass = "Centreon_Object_Relation_Acl_Group_" . ucwords($matches[2]);
            if (class_exists("Centreon_Object_Acl_" . ucwords($matches[2]))) {
                $class = "Centreon_Object_Acl_" . ucwords($matches[2]);
            } elseif ($matches[2] == "contactgroup") {
                $class = "Centreon_Object_Contact_Group";
                $relclass = "Centreon_Object_Relation_Acl_Group_Contact_Group";
            } else {
                $class = "Centreon_Object_" . ucwords($matches[2]);
            }
            if (class_exists($relclass) && class_exists($class)) {
                $uniqueLabel = $this->object->getUniqueLabelField();
                /* Parse arguments */
                if (!isset($arg[0])) {
                    throw new CentreonClapiException(self::MISSINGPARAMETER);
                }
                $args = explode($this->delim, $arg[0]);
                $groupIds = $this->object->getIdByParameter($uniqueLabel, array($args[0]));
                if (!count($groupIds)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0]);
                }
                $groupId = $groupIds[0];

                $relobj = new $relclass($this->dependencyInjector);
                $obj = new $class($this->dependencyInjector);
                if ($matches[1] == "get") {
                    $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $groupIds);
                    echo "id" . $this->delim . "name" . "\n";
                    foreach ($tab as $value) {
                        $tmp = $obj->getParameters($value, array($obj->getUniqueLabelField()));
                        echo $value . $this->delim . $tmp[$obj->getUniqueLabelField()] . "\n";
                    }
                } else {
                    if (!isset($args[1])) {
                        throw new CentreonClapiException(self::MISSINGPARAMETER);
                    }
                    $relation = $args[1];
                    $relations = explode("|", $relation);
                    $relationTable = array();
                    foreach ($relations as $rel) {
                        $tab = $obj->getIdByParameter($obj->getUniqueLabelField(), array($rel));
                        if (!count($tab)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $rel);
                        }
                        $relationTable[] = $tab[0];
                    }
                    if ($matches[1] == "set") {
                        $relobj->delete($groupId);
                    }
                    $existingRelationIds = $relobj->getTargetIdFromSourceId(
                        $relobj->getSecondKey(),
                        $relobj->getFirstKey(),
                        array($groupId)
                    );
                    foreach ($relationTable as $relationId) {
                        if ($matches[1] == "del") {
                            $relobj->delete($groupId, $relationId);
                        } elseif ($matches[1] == "set" || $matches[1] == "add") {
                            if (!in_array($relationId, $existingRelationIds)) {
                                $relobj->insert($groupId, $relationId);
                            }
                        }
                    }

                    if ($matches[2] === "action") {
                        $this->flagUpdatedAclForAuthentifiedUsers($groupIds);
                    }

                    $updateParams = array('acl_group_changed' => '1');

                    if (
                        isset($updateParams[$uniqueLabel])
                        && $this->objectExists($updateParams[$uniqueLabel], $groupId) == true
                    ) {
                        throw new CentreonClapiException(self::NAMEALREADYINUSE);
                    }

                    $this->object->update($groupId, $updateParams);
                    $p = $this->object->getParameters($groupId, $uniqueLabel);
                    if (isset($p[$uniqueLabel])) {
                        $this->addAuditLog(
                            'c',
                            $groupId,
                            $p[$uniqueLabel],
                            $updateParams
                        );
                    }
                }
            } else {
                throw new CentreonClapiException(self::UNKNOWN_METHOD);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
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
        $aclGroupList = $this->object->getList(
            '*',
            -1,
            0,
            $labelField,
            'ASC',
            $filters
        );

        $exportLine = '';
        foreach ($aclGroupList as $aclGroup) {
            $exportLine .= $this->action . $this->delim . "ADD" . $this->delim
                . $aclGroup['acl_group_name'] . $this->delim
                . $aclGroup['acl_group_alias'] . $this->delim . "\n";

            $exportLine .= $this->action . $this->delim . "SETPARAM" . $this->delim
                . $aclGroup['acl_group_name'] . $this->delim
                . 'activate' . $this->delim
                . $aclGroup['acl_group_activate'] . $this->delim . "\n";

            $exportLine .= $this->exportLinkedObjects($aclGroup['acl_group_id'], $aclGroup['acl_group_name']);

            echo $exportLine;
            $exportLine = '';
        }
    }

    /**
     * @param $aclGroupId
     * @param $aclGroupName
     * @return string
     */
    private function exportLinkedObjects($aclGroupId, $aclGroupName)
    {
        $objectList = array(
            array(
                'object' => 'MENU',
                'relClass' => 'Centreon_Object_Relation_Acl_Group_Menu',
                'objectFieldName' => 'acl_topo_name'
            ),
            array(
                'object' => 'ACTION',
                'relClass' => 'Centreon_Object_Relation_Acl_Group_Action',
                'objectFieldName' => 'acl_action_name'
            ),
            array(
                'object' => 'RESOURCE',
                'relClass' => 'Centreon_Object_Relation_Acl_Group_Resource',
                'objectFieldName' => 'acl_res_name'
            ),
            array(
                'object' => 'CONTACT',
                'relClass' => 'Centreon_Object_Relation_Acl_Group_Contact',
                'objectFieldName' => 'contact_alias'
            ),
            array(
                'object' => 'CONTACTGROUP',
                'relClass' => 'Centreon_Object_Relation_Acl_Group_Contact_Group',
                'objectFieldName' => 'cg_name'
            ),
        );

        $linkedObjectsSetter = $this->action . $this->delim . 'SET%s' . $this->delim .
            $aclGroupName . $this->delim .
            '%s' . $this->delim . "\n";

        $linkedObjectsStr = '';

        foreach ($objectList as $currentObject) {
            $linkedObjects = $this->getLinkedObject(
                $aclGroupId,
                $currentObject['relClass'],
                $currentObject['objectFieldName']
            );
            if (!empty($linkedObjects)) {
                $linkedObjectsStr .= sprintf($linkedObjectsSetter, $currentObject['object'], $linkedObjects);
            }
        }

        return $linkedObjectsStr;
    }

    /**
     * @param $aclGroupId
     * @param $relClass
     * @param $objectFieldName
     * @return string
     * @throws CentreonClapiException
     */
    private function getLinkedObject($aclGroupId, $relClass, $objectFieldName)
    {
        if (!class_exists($relClass)) {
            throw  new CentreonClapiException('Unsupported relation object : ' . $relClass);
        }

        $relObj = new $relClass($this->dependencyInjector);

        $comparisonKey1 = $this->object->getTableName() . '.' . $this->object->getPrimaryKey();

        $links = $relObj->getMergedParameters(
            array(),
            array($objectFieldName),
            -1,
            0,
            $objectFieldName,
            'ASC',
            array($comparisonKey1 => $aclGroupId),
            'AND'
        );

        $linkedObjects = '';

        foreach ($links as $link) {
            $linkedObjects .= $link[$objectFieldName] . '|';
        }

        return trim($linkedObjects, '|');
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
