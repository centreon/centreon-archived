<?php
/*
 * Copyright 2005-2014 CENTREON
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
require_once "centreonHost.class.php";
require_once "centreonService.class.php";
require_once "Centreon/Object/Dependency/Dependency.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Service/Group.php";
require_once "Centreon/Object/Meta/Service.php";
require_once "Centreon/Object/Relation/Dependency/Parent/Host.php";
require_once "Centreon/Object/Relation/Dependency/Parent/Hostgroup.php";
require_once "Centreon/Object/Relation/Dependency/Parent/Servicegroup.php";
require_once "Centreon/Object/Relation/Dependency/Parent/Metaservice.php";

/**
 * Class for managing dependency objects
 *
 * @author sylvestre
 */
class CentreonDependency extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_ALIAS             = 1;
    const ORDER_DEP_TYPE          = 2;
    const ORDER_PARENTS           = 3;
    const DEP_TYPE_HOST           = 'HOST';
    const DEP_TYPE_HOSTGROUP      = 'HG';
    const DEP_TYPE_SERVICE        = 'SERVICE';
    const DEP_TYPE_SERVICEGROUP   = 'SG';
    const DEP_TYPE_META           = 'META';
    protected $serviceObj;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->serviceObj = new CentreonService();
        $this->object = new \Centreon_Object_Dependency();
        $this->action = "DEP";
        $this->insertParams = array(
            'dep_name',
            'dep_description',
            'type',
            'parents'
        );
        $this->nbOfCompulsoryParams = count($this->insertParams);
    }

    /**
     * Display all Host Groups
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
        $params = array(
            'dep_id',
            'dep_name',
            'dep_description',
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria'
        );
        $paramString = str_replace("dep_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Add action
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function add($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['dep_description'] = $params[self::ORDER_ALIAS];
        $addParams['parents'] = $params[self::ORDER_PARENTS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();

        switch (strtoupper($params[self::ORDER_DEP_TYPE])) {
            case self::DEP_TYPE_HOST:
                $this->addHostDependency($addParams);
                break;
            case self::DEP_TYPE_HOSTGROUP:
                $this->addHostGroupDependency($addParams);
                break;
            case self::DEP_TYPE_SERVICE:
                $this->addServiceDependency($addParams);
                break;
            case self::DEP_TYPE_SERVICEGROUP:
                $this->addServiceGroupDependency($addParams);
                break;
            case self::DEP_TYPE_META:
                $this->addMetaDependency($addParams);
                break;
            default:
                throw new CentreonClapiException(
                    sprintf(
                        'Unknown type %s. Please choose one of the following host|hg|service|sg|meta',
                        $params[self::ORDER_DEP_TYPE]
                    )
                );
                break;
        }
    }

    /**
     * Return the type of dependency
     *
     * @param string $dependencyName
     * @return string
     */
    protected function getDependencyType($dependencyName)
    {
        $sql = "SELECT '".self::DEP_TYPE_HOST."' as type
            FROM dependency d, dependency_hostParent_relation rel
            WHERE rel.dependency_dep_id = d.dep_id
            AND d.dep_name = :name
            UNION
            SELECT '".self::DEP_TYPE_SERVICE."'
            FROM dependency d, dependency_serviceParent_relation rel
            WHERE rel.dependency_dep_id = d.dep_id
            AND d.dep_name = :name
            UNION
            SELECT '".self::DEP_TYPE_HOSTGROUP."'
            FROM dependency d, dependency_hostgroupParent_relation rel
            WHERE rel.dependency_dep_id = d.dep_id
            AND d.dep_name = :name
            UNION
            SELECT '".self::DEP_TYPE_SERVICEGROUP."'
            FROM dependency d, dependency_servicegroupParent_relation rel
            WHERE rel.dependency_dep_id = d.dep_id
            AND d.dep_name = :name
            UNION
            SELECT '".self::DEP_TYPE_META."'
            FROM dependency d, dependency_metaserviceParent_relation rel
            WHERE rel.dependency_dep_id = d.dep_id
            AND d.dep_name = :name";
        $res = $this->db->query($sql, array(':name' => $dependencyName));
        $row = $res->fetch();
        if (isset($row['type'])) {
            return $row['type'];
        }
        return "";
    }

    /**
     * Insert new dependency
     *
     * @param string $name
     * @param string $description
     * @param Centreon_Object $parentObj
     * @param string $parentString
     * @param Centreon_Object_Relation
     * @throws CentreonClapiException
     */
    protected function insertDependency($name, $description, $parentObj, $parentString, $relationObj)
    {
        $parents = explode('|', $parentString);
        $parentIds = array();
        foreach ($parents as $parent) {
            $idTab = $parentObj->getIdByParameter(
                $parentObj->getUniqueLabelField(),
                array($parent)
            );
            // make sure that all parents exist
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find %s', $parent));
            }
            $parentIds[] = $idTab[0];
        }

        // insert dependency
        $depId = $this->object->insert(
            array(
                'dep_name' => $name,
                'dep_description' => $description
            )
        );
        if (is_null($depId)) {
            throw new CentreonClapiException(sprintf("Could not insert dependency %s", $name));
        }

        // insert relations
        foreach ($parentIds as $parentId) {
            $relationObj->insert($depId, $parentId);
        }
    }

    /**
     * Add host type dependency
     *
     * @param array $params
     */
    protected function addHostDependency($params)
    {
        $obj = new \Centreon_Object_Host();
        $relObj = new \Centreon_Object_Relation_Dependency_Parent_Host();
        $this->insertDependency(
            $params['dep_name'],
            $params['dep_description'],
            $obj,
            $params['parents'],
            $relObj
        );
    }

    /**
     * Add hostgroup type dependency
     *
     * @param array $params
     */
    protected function addHostGroupDependency($params)
    {
        $obj = new \Centreon_Object_Host_Group();
        $relObj = new \Centreon_Object_Relation_Dependency_Parent_Hostgroup();
        $this->insertDependency(
            $params['dep_name'],
            $params['dep_description'],
            $obj,
            $params['parents'],
            $relObj
        );
    }

    /**
     * Add service type dependency
     *
     * @param array $params
     */
    protected function addServiceDependency($params)
    {
        $parents = explode('|', $params['parents']);
        $parentIds = array();
        foreach ($parents as $parent) {
            $tmp = explode(',', $parent);
            if (count($tmp) != 2) {
                throw new CentreonClapiException('Incorrect service definition');
            }
            // make sure that all parents exist
            $host = $tmp[0];
            $service = $tmp[1];
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $parentIds[] = $idTab;
        }

        // insert dependency
        $depId = $this->object->insert(
            array(
                'dep_name' => $params['dep_name'],
                'dep_description' => $params['dep_description']
            )
        );
        if (is_null($depId)) {
            throw new CentreonClapiException(sprintf("Could not insert dependency %s", $name));
        }

        // insert relations
        $sql = "INSERT INTO dependency_serviceParent_relation
            (dependency_dep_id, host_host_id, service_service_id) VALUES (?, ?, ?)";
        foreach ($parentIds as $parentId) {
            $this->db->query($sql, array($depId, $parentId[0], $parentId[1]));
        }
    }

    /**
     * Add servicegroup type dependency
     *
     * @param array $params
     */
    protected function addServiceGroupDependency($params)
    {
        $obj = new \Centreon_Object_Service_Group();
        $relObj = new \Centreon_Object_Relation_Dependency_Parent_Servicegroup();
        $this->insertDependency(
            $params['dep_name'],
            $params['dep_description'],
            $obj,
            $params['parents'],
            $relObj
        );
    }

    /**
     * Add meta type dependency
     *
     * @param array $params
     */
    protected function addMetaDependency($params)
    {
        $obj = new \Centreon_Object_Meta_Service();
        $relObj = new \Centreon_Object_Relation_Dependency_Parent_Metaservice();
        $this->insertDependency(
            $params['dep_name'],
            $params['dep_description'],
            $obj,
            $params['parents'],
            $relObj
        );
    }

    /**
     * Set params
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function setparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if (in_array($params[1], array('comment', 'name', 'description')) && !preg_match("/^dep_/", $params[1])) {
                $params[1] = "dep_".$params[1];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * List dependencies
     *
     * @param string $parameters | dependency name
     * @throws CentreonClapiException
     */
    public function listdep($parameters)
    {
        $type = $this->getDependencyType($parameters);

        if ($type == '') {
            throw new CentreonClapiException('Could not define type of dependency');
        }

        $depId = $this->getObjectId($parameters);

        /* header */
        echo implode($this->delim, array('parents', 'children')) . "\n";

        switch ($type) {
            case self::DEP_TYPE_HOST:
                $this->listhostdep($depId);
                break;
            case self::DEP_TYPE_HOSTGROUP:
                $this->listhostgroupdep($depId);
                break;
            case self::DEP_TYPE_SERVICE:
                $this->listservicedep($depId);
                break;
            case self::DEP_TYPE_SERVICEGROUP:
                $this->listservicegroupdep($depId);
                break;
            case self::DEP_TYPE_META:
                $this->listmetadep($depId);
                break;
            default:
                break;
        }
    }

    /**
     * List host group dependency
     *
     * @param int $depId
     */
    protected function listhostgroupdep($depId)
    {
        /* Parents */
        $sql = "SELECT hg_name
            FROM hostgroup hg, dependency_hostgroupParent_relation rel
            WHERE hg.hg_id = rel.hostgroup_hg_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $parents = array();
        foreach ($rows as $row) {
            $parents[] = $row['hg_name'];
        }

        /* Children */
        $sql = "SELECT hg_name
            FROM hostgroup hg, dependency_hostgroupChild_relation rel
            WHERE hg.hg_id = rel.hostgroup_hg_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $children = array();
        foreach ($rows as $row) {
            $children[] = $row['hg_name'];
        }

        $str = implode('|', $parents) . $this->delim;
        $str .= implode('|', $children);
        echo str_replace("||", "|", $str) . "\n";
    }

    /**
     * List service dependency
     *
     * @param int $depId
     */
    protected function listservicedep($depId)
    {
        /* Parents */
        $sql = "SELECT host_name, service_description
            FROM host h, service s, dependency_serviceParent_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.service_service_id = s.service_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $parents = array();
        foreach ($rows as $row) {
            $parents[] = $row['host_name'] . ',' . $row['service_description'];
        }

        /* Host children */
        $sql = "SELECT host_name
            FROM host h, dependency_hostChild_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $hostChildren = array();
        foreach ($rows as $row) {
            $hostChildren[] = $row['host_name'];
        }

        /* Service children */
        $sql = "SELECT host_name, service_description
            FROM host h, service s, dependency_serviceChild_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.service_service_id = s.service_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $serviceChildren = array();
        foreach ($rows as $row) {
            $serviceChildren[] = $row['host_name'] . ',' .$row['service_description'];
        }

        $strParents = implode('|', $parents) . $this->delim;
        $strChildren = implode('|', $hostChildren) . "|";
        $strChildren .= implode('|', $serviceChildren);
        echo str_replace("||", "|", $strParents . trim($strChildren, "|")) . "\n";
    }

    /**
     * List service group dependency
     *
     * @param int $depId
     */
    protected function listservicegroupdep($depId)
    {
        /* Parents */
        $sql = "SELECT sg_name
            FROM servicegroup sg, dependency_servicegroupParent_relation rel
            WHERE sg.sg_id = rel.servicegroup_sg_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $parents = array();
        foreach ($rows as $row) {
            $parents[] = $row['sg_name'];
        }

        /* Children */
        $sql = "SELECT sg_name
            FROM servicegroup sg, dependency_servicegroupChild_relation rel
            WHERE sg.sg_id = rel.servicegroup_sg_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $children = array();
        foreach ($rows as $row) {
            $children[] = $row['sg_name'];
        }

        $str = implode('|', $parents) . $this->delim;
        $str .= implode('|', $children);
        echo str_replace("||", "|", trim($str, "|")) . "\n";
    }

    /**
     * List meta service dependency
     *
     * @param int $depId
     */
    protected function listmetadep($depId)
    {
        /* Parents */
        $sql = "SELECT meta_name
            FROM meta_service m, dependency_metaserviceParent_relation rel
            WHERE m.meta_id = rel.meta_service_meta_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $parents = array();
        foreach ($rows as $row) {
            $parents[] = $row['meta_name'];
        }

        /* Children */
        $sql = "SELECT meta_name
            FROM meta_service m, dependency_metaserviceChild_relation rel
            WHERE m.meta_id = rel.meta_service_meta_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $children = array();
        foreach ($rows as $row) {
            $children[] = $row['meta_name'];
        }

        $str = implode('|', $parents) . $this->delim;
        $str .= implode('|', $children);
        echo str_replace("||", "|", trim($str, "|")) . "\n";
    }

    /**
     * List host dependency
     *
     * @param int $depId
     */
    protected function listhostdep($depId)
    {
        /* Parents */
        $sql = "SELECT host_name
            FROM host h, dependency_hostParent_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $parents = array();
        foreach ($rows as $row) {
            $parents[] = $row['host_name'];
        }

        /* Host children */
        $sql = "SELECT host_name
            FROM host h, dependency_hostChild_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $hostChildren = array();
        foreach ($rows as $row) {
            $hostChildren[] = $row['host_name'];
        }

        /* Service children */
        $sql = "SELECT host_name, service_description
            FROM host h, service s, dependency_serviceChild_relation rel
            WHERE h.host_id = rel.host_host_id
            AND rel.service_service_id = s.service_id
            AND rel.dependency_dep_id = ?";
        $res = $this->db->query($sql, array($depId));
        $rows = $res->fetchAll();
        $serviceChildren = array();
        foreach ($rows as $row) {
            $serviceChildren[] = $row['host_name'] . ',' .$row['service_description'];
        }

        $strParents = implode('|', $parents) . $this->delim;
        $strChildren .= implode('|', $hostChildren) . "|";
        $strChildren .= implode('|', $serviceChildren);
        echo str_replace("||", "|", $strParents . trim($strChildren, "|")) . "\n";
    }

    /**
     * Add relations
     *
     * @param string $parameters
     * @param string $relType
     */
    protected function addRelations($parameters, $relType = 'parent')
    {
        $param = explode($this->delim, $parameters);
        if (count($param) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        // get dependency id
        $depId = $this->getObjectId($param[0]);
        if (!$depId) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }

        // get dependency type
        $depType = $this->getDependencyType($param[0]);
        $objectToDelete = $param[1];

        switch ($depType) {
            case self::DEP_TYPE_HOSTGROUP:
                $this->addHostgroupRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_SERVICEGROUP:
                $this->addServicegroupRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_META:
                $this->addMetaRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_HOST:
                $this->addHostRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_SERVICE:
                $this->addServiceRelations($depId, $objectToDelete, $relType);
                break;
            default:
                break;
        }
    }

    /**
     *
     * @param int $depId
     * @param string $objectToInsert
     * @param string $relType | 'parent' or 'child'
     */
    protected function addHostgroupRelations($depId, $objectToInsert, $relType)
    {
        $table = "dependency_hostgroup" . ucfirst($relType) . "_relation";
        $sql = "INSERT INTO {$table} (dependency_dep_id, hostgroup_hg_id) VALUES (?, ?)";
        $obj = new \Centreon_Object_Host_Group();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToInsert));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find host group %s', $objectToInsert));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToInsert
     * @param string $relType | 'parent' or 'child'
     */
    protected function addServicegroupRelations($depId, $objectToInsert, $relType)
    {
        $table = "dependency_servicegroup" . ucfirst($relType) . "_relation";
        $sql = "INSERT INTO {$table} (dependency_dep_id, servicegroup_sg_id) VALUES (?, ?)";
        $obj = new \Centreon_Object_Service_Group();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToInsert));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find service group %s', $objectToInsert));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToInsert
     * @param string $relType | 'parent' or 'child'
     */
    protected function addMetaRelations($depId, $objectToInsert, $relType)
    {
        $table = "dependency_metaservice" . ucfirst($relType) . "_relation";

        $sql = "INSERT INTO {$table} (dependency_dep_id, meta_service_meta_id) VALUES (?, ?)";
        $obj = new \Centreon_Object_Meta_Service();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToInsert));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find meta service %s', $objectToInsert));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToInsert
     * @param string $relType | 'parent' or 'child'
     */
    protected function addHostRelations($depId, $objectToInsert, $relType)
    {
        if ($relType == 'parent') {
            $sql = "INSERT INTO dependency_hostParent_relation (dependency_dep_id, host_host_id) VALUES (?, ?)";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToInsert));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToInsert));
            }
            $params = array($depId, $hostIds[0]);
        } elseif ($relType == 'child' && strstr($objectToInsert, ',')) { // service child
            $sql = "INSERT INTO dependency_serviceChild_relation
                (dependency_dep_id, host_host_id, service_service_id)
                VALUES (?, ?, ?)";
            list($host, $service) = explode(",", $objectToInsert);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child') { // host child
            $sql = "INSERT INTO dependency_hostChild_relation (dependency_dep_id, host_host_id) VALUES (?, ?)";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToInsert));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToInsert));
            }
            $params = array($depId, $hostIds[0]);
        }
        $this->db->query($sql, $params);
    }

    /**
     *
     * @param int $depId
     * @param string $objectToInsert
     * @param string $relType | 'parent' or 'child'
     */
    protected function addServiceRelations($depId, $objectToInsert, $relType)
    {
        if ($relType == 'parent') {
            $sql = "INSERT INTO dependency_serviceParent_relation
                (dependency_dep_id, host_host_id, service_service_id)
                VALUES (?, ?, ?)";
            if (!strstr($objectToInsert, ',')) {
                throw new CentreonClapiException('Invalid service definition');
            }
            list($host, $service) = explode(",", $objectToInsert);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child' && strstr($objectToInsert, ',')) { // service child
            $sql = "INSERT INTO dependency_serviceChild_relation (dependency_dep_id, host_host_id, service_service_id)
                VALUES (?, ?, ?)";
            list($host, $service) = explode(",", $objectToInsert);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child') { // host child
            $sql = "INSERT INTO dependency_hostChild_relation (dependency_dep_id, host_host_id)
                VALUES (?, ?)";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToInsert));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToInsert));
            }
            $params = array($depId, $hostIds[0]);
        }
        $this->db->query($sql, $params);
    }

    /**
     * Delete relations
     *
     * @param string $parameters
     * @param string $relType | 'parent' or 'child'
     */
    protected function deleteRelations($parameters, $relType = 'parent')
    {
        $param = explode($this->delim, $parameters);
        if (count($param) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        // get dependency id
        $depId = $this->getObjectId($param[0]);
        if (!$depId) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }

        // get dependency type
        $depType = $this->getDependencyType($param[0]);
        $objectToDelete = $param[1];

        switch ($depType) {
            case self::DEP_TYPE_HOSTGROUP:
                $this->delHostgroupRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_SERVICEGROUP:
                $this->delServicegroupRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_META:
                $this->delMetaRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_HOST:
                $this->delHostRelations($depId, $objectToDelete, $relType);
                break;
            case self::DEP_TYPE_SERVICE:
                $this->delServiceRelations($depId, $objectToDelete, $relType);
                break;
            default:
                break;
        }
    }

    /**
     *
     * @param int $depId
     * @param string $objectToDelete
     * @param string $relType | 'parent' or 'child'
     */
    protected function delHostgroupRelations($depId, $objectToDelete, $relType)
    {
        $table = "dependency_hostgroup" . ucfirst($relType) . "_relation";
        $sql = "DELETE FROM {$table}
            WHERE dependency_dep_id = ?
            AND hostgroup_hg_id = ?";
        $obj = new \Centreon_Object_Host_Group();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToDelete));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find host group %s', $objectToDelete));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToDelete
     * @param string $relType | 'parent' or 'child'
     */
    protected function delServicegroupRelations($depId, $objectToDelete, $relType)
    {
        $table = "dependency_servicegroup" . ucfirst($relType) . "_relation";
        $sql = "DELETE FROM {$table}
            WHERE dependency_dep_id = ?
            AND servicegroup_sg_id = ?";
        $obj = new \Centreon_Object_Service_Group();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToDelete));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find service group %s', $objectToDelete));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToDelete
     * @param string $relType | 'parent' or 'child'
     */
    protected function delMetaRelations($depId, $objectToDelete, $relType)
    {
        $table = "dependency_metaservice" . ucfirst($relType) . "_relation";

        $sql = "DELETE FROM {$table}
            WHERE dependency_dep_id = ?
            AND meta_service_meta_id = ?";
        $obj = new \Centreon_Object_Meta_Service();
        $ids = $obj->getIdByParameter($obj->getUniqueLabelField(), array($objectToDelete));
        if (!count($ids)) {
            throw new CentreonClapiException(sprintf('Could not find meta service %s', $objectToDelete));
        }
        $this->db->query($sql, array($depId, $ids[0]));
    }

    /**
     *
     * @param int $depId
     * @param string $objectToDelete
     * @param string $relType | 'parent' or 'child'
     */
    protected function delHostRelations($depId, $objectToDelete, $relType)
    {
        if ($relType == 'parent') {
            $sql = "DELETE FROM dependency_hostParent_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToDelete));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToDelete));
            }
            $params = array($depId, $hostIds[0]);
        } elseif ($relType == 'child' && strstr($objectToDelete, ',')) { // service child
            $sql = "DELETE FROM dependency_serviceChild_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?
                AND service_service_id = ?";
            list($host, $service) = explode(",", $objectToDelete);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child') { // host child
            $sql = "DELETE FROM dependency_hostChild_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToDelete));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToDelete));
            }
            $params = array($depId, $hostIds[0]);
        }
        $this->db->query($sql, $params);
    }

    /**
     *
     * @param int $depId
     * @param string $objectToDelete
     * @param string $relType | 'parent' or 'child'
     */
    protected function delServiceRelations($depId, $objectToDelete, $relType)
    {
        if ($relType == 'parent') {
            $sql = "DELETE FROM dependency_serviceParent_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?
                AND service_service_id = ?";
            if (!strstr($objectToDelete, ',')) {
                throw new CentreonClapiException('Invalid service definition');
            }
            list($host, $service) = explode(",", $objectToDelete);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child' && strstr($objectToDelete, ',')) { // service child
            $sql = "DELETE FROM dependency_serviceChild_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?
                AND service_service_id = ?";
            list($host, $service) = explode(",", $objectToDelete);
            $idTab = $this->serviceObj->getHostAndServiceId($host, $service);
            if (!count($idTab)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }
            $params = array($depId, $idTab[0], $idTab[1]);
        } elseif ($relType == 'child') { // host child
            $sql = "DELETE FROM dependency_hostChild_relation
                WHERE dependency_dep_id = ?
                AND host_host_id = ?";
            $hostObj = new \Centreon_Object_Host();
            $hostIds = $hostObj->getIdByParameter($hostObj->getUniqueLabelField(), array($objectToDelete));
            if (!count($hostIds)) {
                throw new CentreonClapiException(sprintf('Could not find host %s', $objectToDelete));
            }
            $params = array($depId, $hostIds[0]);
        }
        $this->db->query($sql, $params);
    }

    /**
     * Delete parent
     *
     * @param string $parameters | dep_name;parents_to_delete
     */
    public function delparent($parameters)
    {
        $this->deleteRelations($parameters, 'parent');
    }

    /**
     * Delete child
     *
     * @param string $parameters | dep_name;children_to_delete
     */
    public function delchild($parameters)
    {
        $this->deleteRelations($parameters, 'child');
    }

    /**
     * Add parent
     *
     * @param string
     */
    public function addparent($parameters)
    {
        $this->addRelations($parameters, 'parent');
    }

    /**
     * Add child
     */
    public function addchild($parameters)
    {
        $this->addRelations($parameters, 'child');
    }

    /**
     * Export
     *
     */
    public function export()
    {
        $this->exportHostDep();
        $this->exportServiceDep();
        $this->exportHostgroupDep();
        $this->exportServicegroupDep();
        $this->exportMetaDep();
    }

    /**
     *
     */
    protected function exportHostDep()
    {
        $sql = "SELECT dep_id, dep_name, dep_description, inherits_parent,
            execution_failure_criteria, notification_failure_criteria, dep_comment, host_name
            FROM dependency d, dependency_hostParent_relation rel, host h
            WHERE d.dep_id = rel.dependency_dep_id
            AND rel.host_host_id = h.host_id
            ORDER BY dep_name";
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $previous = 0;
        $paramArr = array(
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria',
            'dep_comment'
        );
        foreach ($rows as $row) {
            if ($row['dep_id'] != $previous) { // add dependency
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADD',
                        $row['dep_name'],
                        $row['dep_description'],
                        self::DEP_TYPE_HOST,
                        $row['host_name']
                    )
                ) . "\n";
                foreach ($row as $k => $v) {
                    if (!in_array($k, $paramArr)) {
                        continue;
                    }
                    // setparam
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'SETPARAM',
                            $row['dep_name'],
                            $k,
                            $v,
                        )
                    ) . "\n";
                }
                // add host children
                $childSql = "SELECT host_name
                    FROM host h, dependency_hostChild_relation rel
                    WHERE h.host_id = rel.host_host_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['host_name']
                        )
                    ) . "\n";
                }

                // add service children
                $childSql = "SELECT host_name, service_description
                    FROM host h, service s, dependency_serviceChild_relation rel
                    WHERE h.host_id = rel.host_host_id
                    AND rel.service_service_id = s.service_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['host_name'] . ',' . $childRow['service_description']
                        )
                    ) . "\n";
                }
            } else {
                // addparent
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADDPARENT',
                        $row['dep_name'],
                        $row['host_name']
                    )
                ) . "\n";
            }
            $previous = $row['dep_id'];
        }
    }

    /**
     *
     */
    protected function exportServiceDep()
    {
        $sql = "SELECT dep_id, dep_name, dep_description, inherits_parent,
            execution_failure_criteria, notification_failure_criteria, dep_comment, host_name, service_description
            FROM dependency d, dependency_serviceParent_relation rel, host h, service s
            WHERE d.dep_id = rel.dependency_dep_id
            AND h.host_id = rel.host_host_id
            AND rel.service_service_id = s.service_id
            ORDER BY dep_name";
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $previous = 0;
        $paramArr = array(
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria',
            'dep_comment'
        );
        foreach ($rows as $row) {
            if ($row['dep_id'] != $previous) { // add dependency
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADD',
                        $row['dep_name'],
                        $row['dep_description'],
                        self::DEP_TYPE_SERVICE,
                        $row['host_name'] . ',' . $row['service_description']
                    )
                ) . "\n";
                foreach ($row as $k => $v) {
                    if (!in_array($k, $paramArr)) {
                        continue;
                    }
                    // setparam
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'SETPARAM',
                            $row['dep_name'],
                            $k,
                            $v,
                        )
                    ) . "\n";
                }
                // add host children
                $childSql = "SELECT host_name
                    FROM host h, dependency_hostChild_relation rel
                    WHERE h.host_id = rel.host_host_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['host_name']
                        )
                    ) . "\n";
                }

                // add service children
                $childSql = "SELECT host_name, service_description
                    FROM host h, service s, dependency_serviceChild_relation rel
                    WHERE h.host_id = rel.host_host_id
                    AND rel.service_service_id = s.service_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['host_name'] . ',' . $childRow['service_description']
                        )
                    ) . "\n";
                }
            } else {
                // addparent
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADDPARENT',
                        $row['dep_name'],
                        $row['host_name'] . ',' . $row['service_description']
                    )
                ) . "\n";
            }
            $previous = $row['dep_id'];
        }
    }

    /**
     *
     */
    protected function exportHostgroupDep()
    {
        $sql = "SELECT dep_id, dep_name, dep_description, inherits_parent,
            execution_failure_criteria, notification_failure_criteria, dep_comment, hg_name
            FROM dependency d, dependency_hostgroupParent_relation rel, hostgroup hg
            WHERE d.dep_id = rel.dependency_dep_id
            AND rel.hostgroup_hg_id = hg.hg_id
            ORDER BY dep_name";
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $previous = 0;
        $paramArr = array(
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria',
            'dep_comment'
        );
        foreach ($rows as $row) {
            if ($row['dep_id'] != $previous) { // add dependency
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADD',
                        $row['dep_name'],
                        $row['dep_description'],
                        self::DEP_TYPE_HOSTGROUP,
                        $row['hg_name']
                    )
                ) . "\n";
                foreach ($row as $k => $v) {
                    if (!in_array($k, $paramArr)) {
                        continue;
                    }
                    // setparam
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'SETPARAM',
                            $row['dep_name'],
                            $k,
                            $v,
                        )
                    ) . "\n";
                }
                // add children
                $childSql = "SELECT hg_name
                    FROM hostgroup hg, dependency_hostgroupChild_relation rel
                    WHERE hg.hg_id = rel.hostgroup_hg_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['hg_name']
                        )
                    ) . "\n";
                }
            } else {
                // addparent
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADDPARENT',
                        $row['dep_name'],
                        $row['hg_name']
                    )
                ) . "\n";
            }
            $previous = $row['dep_id'];
        }
    }

    /**
     *
     */
    protected function exportServicegroupDep()
    {
        $sql = "SELECT dep_id, dep_name, dep_description, inherits_parent,
            execution_failure_criteria, notification_failure_criteria, dep_comment, sg_name
            FROM dependency d, dependency_servicegroupParent_relation rel, servicegroup sg
            WHERE d.dep_id = rel.dependency_dep_id
            AND rel.servicegroup_sg_id = sg.sg_id
            ORDER BY dep_name";
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $previous = 0;
        $paramArr = array(
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria',
            'dep_comment'
        );
        foreach ($rows as $row) {
            if ($row['dep_id'] != $previous) { // add dependency
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADD',
                        $row['dep_name'],
                        $row['dep_description'],
                        self::DEP_TYPE_SERVICEGROUP,
                        $row['sg_name']
                    )
                ) . "\n";
                foreach ($row as $k => $v) {
                    if (!in_array($k, $paramArr)) {
                        continue;
                    }
                    // setparam
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'SETPARAM',
                            $row['dep_name'],
                            $k,
                            $v,
                        )
                    ) . "\n";
                }
                // add children
                $childSql = "SELECT sg_name
                    FROM servicegroup sg, dependency_servicegroupChild_relation rel
                    WHERE sg.sg_id = rel.servicegroup_sg_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['sg_name']
                        )
                    ) . "\n";
                }
            } else {
                // addparent
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADDPARENT',
                        $row['dep_name'],
                        $row['sg_name']
                    )
                ) . "\n";
            }
            $previous = $row['dep_id'];
        }
    }

    /**
     *
     */
    protected function exportMetaDep()
    {
        $sql = "SELECT dep_id, dep_name, dep_description, inherits_parent,
            execution_failure_criteria, notification_failure_criteria, dep_comment, meta_name
            FROM dependency d, dependency_metaserviceParent_relation rel, meta_service m
            WHERE d.dep_id = rel.dependency_dep_id
            AND rel.meta_service_meta_id = m.meta_id
            ORDER BY dep_name";
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $previous = 0;
        $paramArr = array(
            'inherits_parent',
            'execution_failure_criteria',
            'notification_failure_criteria',
            'dep_comment'
        );
        foreach ($rows as $row) {
            if ($row['dep_id'] != $previous) { // add dependency
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADD',
                        $row['dep_name'],
                        $row['dep_description'],
                        self::DEP_TYPE_META,
                        $row['meta_name']
                    )
                ) . "\n";
                foreach ($row as $k => $v) {
                    if (!in_array($k, $paramArr)) {
                        continue;
                    }
                    // setparam
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'SETPARAM',
                            $row['dep_name'],
                            $k,
                            $v,
                        )
                    ) . "\n";
                }
                // add children
                $childSql = "SELECT meta_name
                    FROM meta_service m, dependency_metaserviceChild_relation rel
                    WHERE m.meta_id = rel.meta_service_meta_id
                    AND rel.dependency_dep_id = ?";
                $res = $this->db->query($childSql, array($row['dep_id']));
                $childRows = $res->fetchAll();
                foreach ($childRows as $childRow) {
                    echo implode(
                        $this->delim,
                        array(
                            $this->action,
                            'ADDCHILD',
                            $row['dep_name'],
                            $childRow['meta_name']
                        )
                    ) . "\n";
                }
            } else {
                // addparent
                echo implode(
                    $this->delim,
                    array(
                        $this->action,
                        'ADDPARENT',
                        $row['dep_name'],
                        $row['meta_name']
                    )
                ) . "\n";
            }
            $previous = $row['dep_id'];
        }
    }
}
