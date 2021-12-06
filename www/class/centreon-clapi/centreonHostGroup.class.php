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
require_once "centreonACL.class.php";
require_once "centreonHost.class.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Service/Group.php";
require_once "Centreon/Object/Service/Service.php";
require_once "Centreon/Object/Relation/Host/Service.php";
require_once "Centreon/Object/Relation/Host/Group/Host.php";
require_once "Centreon/Object/Relation/Host/Group/Service/Service.php";
require_once "Centreon/Object/Relation/Host/Group/Service/Group.php";
require_once "Centreon/Object/Dependency/DependencyHostgroupParent.php";

/**
 * Class for managing host groups
 *
 * @author sylvestre
 */
class CentreonHostGroup extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_ALIAS = 1;
    public const INVALID_GEO_COORDS = "Invalid geo coords";

    public static $aDepends = array(
        'HOST'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->object = new \Centreon_Object_Host_Group($dependencyInjector);
        $this->params = array('hg_activate' => '1');
        $this->insertParams = array('hg_name', 'hg_alias');
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
        $this->action = "HG";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "hg_activate";
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
        $params = array('hg_id', 'hg_name', 'hg_alias');
        $paramString = str_replace("hg_", "", implode($this->delim, $params));
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
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * @param null $parameters
     * @return mixed|void
     * @throws CentreonClapiException
     */
    public function initInsertParameters($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $this->checkIllegalChar($params[self::ORDER_UNIQUENAME]);
        $addParams['hg_alias'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
    }

    /**
     * Del Action
     * Must delete services as well
     *
     * @param string $objectName
     * @return void
     * @throws CentreonClapiException
     */
    public function del($objectName)
    {
        $hostgroupId = $this->getObjectId($objectName);
        $parentDependency = new \Centreon_Object_DependencyHostgroupParent($this->dependencyInjector);
        $parentDependency->removeRelationLastHostgroupDependency($hostgroupId);
        parent::del($objectName);
        $this->db->query(
            "DELETE FROM service WHERE service_register = '1' "
            . "AND service_id NOT IN (SELECT service_service_id FROM host_service_relation)"
        );
    }

    /**
     * Get a parameter
     *
     * @param null $parameters
     * @throws CentreonClapiException
     */
    public function getparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $authorizeParam = array(
            'alias',
            'comment',
            'name',
            'activate',
            'notes',
            'notes_url',
            'action_url',
            'icon_image',
            'map_icon_image',
            'geo_coords'
        );
        $unknownParam = array();

        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $listParam = explode('|', $params[1]);
            $exportedFields = [];
            $resultString = "";
            foreach ($listParam as $paramSearch) {
                if (!$paramString) {
                    $paramString = $paramSearch;
                } else {
                    $paramString = $paramString . $this->delim . $paramSearch;
                }
                $field = $paramSearch;
                if (!in_array($field, $authorizeParam)) {
                    $unknownParam[] = $field;
                } else {
                    switch ($paramSearch) {
                        case "geo_coords":
                            break;
                        default:
                            if (!preg_match("/^hg_/", $paramSearch)) {
                                $field = "hg_" . $paramSearch;
                            }
                            break;
                    }

                    
                    $ret = $this->object->getParameters($objectId, $field);
                    $ret = $ret[$field];
                    
                    if (!isset($exportedFields[$paramSearch])) {
                        $resultString .= $ret . $this->delim;
                        $exportedFields[$paramSearch] = 1;
                    }
                }
            }
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }

        if (!empty($unknownParam)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . implode('|', $unknownParam));
        }
        echo implode(';', array_unique(explode(';', $paramString))) . "\n";
        echo substr($resultString, 0, -1) . "\n";
    }


    /**
     * @param null $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters = null)
    {
        $params = explode($this->delim, $parameters);

        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME]);
        if ($objectId != 0) {
            if (($params[1] == "icon_image" || $params[1] == "map_icon_image")) {
                $params[2] = $this->getIdIcon($params[2]);
            }
            if (!preg_match("/^hg_/", $params[1]) && $params[1] != "geo_coords") {
                $params[1] = "hg_" . $params[1];
            } elseif ($params[1] === "geo_coords") {
                if (!CentreonUtils::validateGeoCoords($params[2])) {
                    throw new CentreonClapiException(self::INVALID_GEO_COORDS);
                }
            }

            $updateParams = array($params[1] => $params[2]);
            $updateParams['objectId'] = $objectId;
            return $updateParams;
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getIdIcon($path)
    {
        $iconData = explode('/', $path);
        $query = 'SELECT dir_id FROM view_img_dir WHERE dir_name = "' . $iconData[0] . '"';
        $res = $this->db->query($query);
        $row = $res->fetch();
        $dirId = $row['dir_id'];

        $query = 'SELECT img_id FROM view_img WHERE img_path = "' . $iconData[1] . '"';
        $res = $this->db->query($query);
        $row = $res->fetch();
        $iconId = $row['img_id'];

        $query = 'SELECT vidr_id FROM view_img_dir_relation ' .
            'WHERE dir_dir_parent_id = ' . $dirId . ' AND img_img_id = ' . $iconId;
        $res = $this->db->query($query);
        $row = $res->fetch();
        return $row['vidr_id'];
    }


    /**
     * Magic method
     *
     * @param $name
     * @param $arg
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|set|add|del)(member|host|servicegroup)$/", $name, $matches)) {
            /* Parse arguments */
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $args = explode($this->delim, $arg[0]);
            $hgIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
            if (!count($hgIds)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0]);
            }
            $groupId = $hgIds[0];

            if ($matches[2] == "host" || $matches[2] == "member") {
                $relobj = new \Centreon_Object_Relation_Host_Group_Host($this->dependencyInjector);
                $obj = new \Centreon_Object_Host($this->dependencyInjector);
            } elseif ($matches[2] == "servicegroup") {
                $relobj = new \Centreon_Object_Relation_Host_Group_Service_Group($this->dependencyInjector);
                $obj = new \Centreon_Object_Service_Group($this->dependencyInjector);
            }
            if ($matches[1] == "get") {
                $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $hgIds);
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
                    if (isset($tab[0]) && $tab[0] != '') {
                        $relationTable[] = $tab[0];
                    } else {
                        if ($rel != '') {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $rel);
                        }
                    }
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
                $acl = new CentreonACL($this->dependencyInjector);
                $acl->reload(true);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }

    /**
     * Export
     *
     * @return void
     */
    public function export($filterName = null)
    {
        if (!parent::export($filterName)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array();
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }
        $relObj = new \Centreon_Object_Relation_Host_Group_Host($this->dependencyInjector);
        $hostObj = new \Centreon_Object_Host($this->dependencyInjector);
        $hFieldName = $hostObj->getUniqueLabelField();
        $elements = $relObj->getMergedParameters(
            array($labelField),
            array($hFieldName, 'host_id'),
            -1,
            0,
            $labelField,
            'ASC',
            $filters,
            'AND'
        );
        foreach ($elements as $element) {
            echo $this->action . $this->delim
                . "addhost" . $this->delim
                . $element[$labelField] . $this->delim
                . $element[$hFieldName] . "\n";
        }
    }
}
