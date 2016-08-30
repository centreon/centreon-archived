<?php
/**
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
require_once "Centreon/Object/Acl/Resource.php";
require_once "Centreon/Object/Relation/Acl/Group/Resource.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Host/Category.php";
require_once "Centreon/Object/Service/Group.php";
require_once "Centreon/Object/Service/Category.php";
require_once "Centreon/Object/Meta/Service.php";
require_once "Centreon/Object/Instance/Instance.php";
require_once "Centreon/Object/Relation/Acl/Resource/Host/Host.php";
require_once "Centreon/Object/Relation/Acl/Resource/Host/Group.php";
require_once "Centreon/Object/Relation/Acl/Resource/Host/Category.php";
require_once "Centreon/Object/Relation/Acl/Resource/Host/Exclude.php";
require_once "Centreon/Object/Relation/Acl/Resource/Service/Group.php";
require_once "Centreon/Object/Relation/Acl/Resource/Service/Category.php";
require_once "Centreon/Object/Relation/Acl/Resource/Meta/Service.php";
require_once "Centreon/Object/Relation/Acl/Resource/Instance.php";

/**
 * Class for managing ACL groups
 * @author sylvestre
 *
 */
class CentreonACLResource extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_ALIAS             = 1;

    const UNSUPPORTED_WILDCARD    = "Action does not support the '*' wildcard";

    /**
     *
     * @var Centreon_Object_Acl_Group
     */
    protected $aclGroupObj;

    /**
     *
     * @var Centreon_Object_Relation_Acl_Group_Resource
     */
    protected $relObject;

    /**
     * Depends
     *
     * @var unknown_type
     */
    protected $resourceTypeObject;

    /**
     * Depends
     *
     * @var unknown_type
     */
    protected $resourceTypeObjectRelation;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Acl_Resource();
        $this->aclGroupObj = new \Centreon_Object_Acl_Group();
        $this->relObject = new \Centreon_Object_Relation_Acl_Group_Resource();

        $this->params = array(  'all_hosts'           => '0',
                                'all_hostgroups'      => '0',
                                'all_servicegroups'   => '0',
                                'acl_res_activate'      => '1',
                                'changed'              => '1'
                             );
        $this->nbOfCompulsoryParams = 2;
        $this->activateField = "acl_res_activate";
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
        $addParams['acl_res_alias'] = $params[self::ORDER_ALIAS];
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
            $params[1] = "acl_res_".$params[1];
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
        $params = array("acl_res_id", "acl_res_name", "acl_res_alias", "acl_res_comment", "acl_res_activate");
        $paramString = str_replace("acl_res_", "", implode($this->delim, $params));
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
     * Get Acl Group
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getaclgroup($aclResName)
    {
        if (!isset($aclResName) || !$aclResName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclResId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($aclResName));
        if (!count($aclResId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$aclResName);
        }
        $groupIds = $this->relObject->getacl_group_idFromacl_res_id($aclResId[0]);
        echo "id;name" . "\n";
        if (count($groupIds)) {
            foreach ($groupIds as $groupId) {
                $result = $this->aclGroupObj->getParameters($groupId, $this->aclGroupObj->getUniqueLabelField());
                echo $groupId . $this->delim . $result[$this->aclGroupObj->getUniqueLabelField()] . "\n";
            }
        }
    }

    /**
     * Slit parameters
     *
     * @param string $type
     * @param string $parameters
     * @return array
     * @throws CentreonClapiException
     */
    protected function splitParams($type, $parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $aclResId = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($params[0]));
        if (!count($aclResId)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[0]);
        }
        $resources = explode("|", $params[1]);
        $resourceIds = array();

        switch ($type) {
            case "host":
                $this->resourceTypeObject = new \Centreon_Object_Host();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Host();
                break;
            case "hostgroup":
                $this->resourceTypeObject = new \Centreon_Object_Host_Group();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Host_Group();
                break;
            case "hostcategory":
                $this->resourceTypeObject = new \Centreon_Object_Host_Category();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Host_Category();
                break;
            case "servicegroup":
                $this->resourceTypeObject = new \Centreon_Object_Service_Group();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Service_Group();
                break;
            case "servicecategory":
                $this->resourceTypeObject = new \Centreon_Object_Service_Category();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Service_Category();
                break;
            case "metaservice":
                $this->resourceTypeObject = new \Centreon_Object_Meta_Service();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Meta_Service();
                break;
            case "instance":
                $this->resourceTypeObject = new \Centreon_Object_Instance();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Instance();
                break;
            case "excludehost":
                $this->resourceTypeObject = new \Centreon_Object_Host();
                $this->resourceTypeObjectRelation = new \Centreon_Object_Relation_Acl_Resource_Host_Exclude();
                break;
            default:
                throw new CentreonClapiException(self::UNKNOWN_METHOD);
                break;
        }

        foreach ($resources as $resource) {
            if ($resource != "*") {
                $ids = $this->resourceTypeObject->getIdByParameter(
                    $this->resourceTypeObject->getUniqueLabelField(),
                    array($resource)
                );
                if (!count($ids)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$resource);
                }
                $resourceIds[] = $ids[0];
            } else {
                $resourceIds[] = $resource;
            }
        }
        return array($aclResId[0], $resourceIds);
    }

    /**
     * Grant
     *
     * @param string $type
     * @param string $arg
     * @return void
     */
    protected function grant($type, $arg)
    {
        list($aclResourceId, $resourceIds) = $this->splitParams($type, $arg);

        if (isset($this->resourceTypeObjectRelation)) {
            $results = $this->resourceTypeObjectRelation->getTargetIdFromSourceId(
                $this->resourceTypeObjectRelation->getSecondKey(),
                $this->resourceTypeObjectRelation->getFirstKey(),
                $aclResourceId
            );
            foreach ($resourceIds as $resourceId) {
                if ($resourceId != "*" && !in_array($resourceId, $results)) {
                    $this->resourceTypeObjectRelation->insert($aclResourceId, $resourceId);
                } elseif ($resourceId == "*") {
                    if ($type != "host" && $type != "hostgroup" && $type != "servicegroup") {
                        throw new CentreonClapiException(self::UNSUPPORTED_WILDCARD);
                    }
                    $field = "all_".$type."s";
                    $this->object->update($aclResourceId, array($field => '1', 'changed' => '1'));
                }
            }
        }
    }

    /**
     * Revoke
     *
     * @param string $type
     * @param string $arg
     * @return void
     */
    protected function revoke($type, $arg)
    {
        list($aclResourceId, $resourceIds) = $this->splitParams($type, $arg);

        if (isset($this->resourceTypeObjectRelation)) {
            foreach ($resourceIds as $resourceId) {
                if ($resourceId != "*") {
                    $this->resourceTypeObjectRelation->delete($aclResourceId, $resourceId);
                } elseif ($resourceId == "*") {
                    $this->resourceTypeObjectRelation->delete($aclResourceId);
                }
            }
            if ($type == "host" || $type == "hostgroup" || $type == "servicegroup") {
                $field = "all_".$type."s";
                $this->object->update($aclResourceId, array($field => '0', 'changed' => '1'));
            }
        }
    }

    /**
     * Add filter
     *
     * @param string $type
     * @param string $arg
     * @return void
     */
    protected function addfilter($type, $arg)
    {
        $this->grant($type, $arg);
    }

    /**
     * Delete filter
     *
     * @param string $type
     * @param string $arg
     * @return void
     */
    protected function delfilter($type, $arg)
    {
        $this->revoke($type, $arg);
    }

    /**
     * Add host exclusion
     *
     * @param string $parameters
     * @return void
     */
    public function addhostexclusion($parameters)
    {
        $this->grant("excludehost", $parameters);
    }

    /**
     * Delete host exclusion
     *
     * @param string $parameters
     * @return void
     */
    public function delhostexclusion($parameters)
    {
        $this->revoke("excludehost", $parameters);
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
        $name = strtolower($name);
        if (preg_match("/^(grant|revoke|addfilter|delfilter)_([a-zA-Z_]+)/", $name, $matches)) {
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $action = $matches[1];
            $this->$action($matches[2], $arg[0]);
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }
}
