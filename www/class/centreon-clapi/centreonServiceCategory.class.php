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
require_once "centreonSeverityAbstract.class.php";
require_once "centreonACL.class.php";
require_once "Centreon/Object/Service/Category.php";
require_once "Centreon/Object/Service/Service.php";
require_once "Centreon/Object/Relation/Host/Service.php";
require_once "Centreon/Object/Relation/Service/Category/Service.php";

/**
 * Class for managing service categories
 *
 * @author sylvestre
 */
class CentreonServiceCategory extends CentreonSeverityAbstract
{
    public static $aDepends = array(
        'SERVICE'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Service_Category();
        $this->params = array('sc_activate' => '1');
        $this->insertParams = array('sc_name', 'sc_description');
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array($this->object->getPrimaryKey(), 'level', 'icon_id')
        );
        $this->action = "SC";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "sc_activate";
    }

    /**
     * Display list of service categories
     *
     * @param string $parameters
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array('sc_id', 'sc_name', 'sc_description', 'level');
        $paramString = str_replace("sc_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            if (!$tab['level']) {
                $tab['level'] = 'none';
            }
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Add Service category
     *
     * @param string $parameters
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['sc_description'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Set parameter
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if (!preg_match("/^sc_/", $params[1])) {
                $params[1] = "sc_" . $params[1];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     *
     * @param type $name
     * @param type $arg
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|add|del|set)(service|servicetemplate)\$/", $name, $matches)) {
            /* Parse arguments */
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $args = explode($this->delim, $arg[0]);
            $hcIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
            if (!count($hcIds)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0]);
            }
            $categoryId = $hcIds[0];

            $obj = new \Centreon_Object_Service();
            $relobj = new \Centreon_Object_Relation_Service_Category_Service();
            $hostServiceRel = new \Centreon_Object_Relation_Host_Service();
            if ($matches[1] == "get") {
                $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $hcIds);
                if ($matches[2] == "servicetemplate") {
                    echo "template id" . $this->delim . "service template description\n";
                } elseif ($matches[2] == "service") {
                    echo "host id" . $this->delim
                        . "host name" . $this->delim
                        . "service id" . $this->delim
                        . "service description\n";
                }
                foreach ($tab as $value) {
                    $p = $obj->getParameters($value, array('service_description', 'service_register'));
                    if ($p['service_register'] == 1 && $matches[2] == "service") {
                        $elements = $hostServiceRel->getMergedParameters(
                            array('host_name', 'host_id'),
                            array('service_description'),
                            -1,
                            0,
                            "host_name,service_description",
                            "ASC",
                            array("service_id" => $value),
                            "AND"
                        );
                        if (isset($elements[0])) {
                            echo $elements[0]['host_id'] . $this->delim
                                . $elements[0]['host_name'] . $this->delim
                                . $value . $this->delim
                                . $elements[0]['service_description'] . "\n";
                        }
                    } elseif ($p['service_register'] == 0 && $matches[2] == "servicetemplate") {
                        echo $value . $this->delim . $p['service_description'] . "\n";
                    }
                }
            } elseif ($matches[1] == "set") {
                if ($matches[2] == "servicetemplate") {
                    $this->setServiceTemplate($args, $relobj, $obj, $categoryId);
                } elseif ($matches[2] == "service") {
                    $this->setService($args, $relobj, $categoryId, $hostServiceRel, $obj);
                }
            } else {
                if (!isset($args[1])) {
                    throw new CentreonClapiException(self::MISSINGPARAMETER);
                }
                $relation = $args[1];
                $relations = explode("|", $relation);
                $relationTable = array();
                foreach ($relations as $rel) {
                    if ($matches[2] == "service") {
                        $tmp = explode(",", $rel);
                        if (count($tmp) < 2) {
                            throw new CentreonClapiException(self::MISSINGPARAMETER);
                        }
                        $elements = $hostServiceRel->getMergedParameters(
                            array('host_id'),
                            array('service_id'),
                            -1,
                            0,
                            null,
                            null,
                            array("host_name" => $tmp[0], "service_description" => $tmp[1]),
                            "AND"
                        );
                        if (!count($elements)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $tmp[0] . "/" . $tmp[1]);
                        }
                        $relationTable[] = $elements[0]['service_id'];
                    } elseif ($matches[2] == "servicetemplate") {
                        $tab = $obj->getList(
                            "service_id",
                            -1,
                            0,
                            null,
                            null,
                            array('service_description' => $rel, 'service_register' => 0),
                            "AND"
                        );
                        if (!count($tab)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $rel);
                        }
                        $relationTable[] = $tab[0]['service_id'];
                    }
                }
                $existingRelationIds = $relobj->getTargetIdFromSourceId(
                    $relobj->getSecondKey(),
                    $relobj->getFirstKey(),
                    array($categoryId)
                );
                foreach ($relationTable as $relationId) {
                    if ($matches[1] == "del") {
                        $relobj->delete($categoryId, $relationId);
                    } elseif ($matches[1] == "add") {
                        if (!in_array($relationId, $existingRelationIds)) {
                            $relobj->insert($categoryId, $relationId);
                        }
                    }
                }
                $acl = new CentreonACL();
                $acl->reload(true);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }

    /**
     *
     * @param type $args
     * @param type $relobj
     * @param type $categoryId
     * @param type $hostServiceRel
     * @param type $obj
     * @throws CentreonClapiException
     */
    private function setService($args, $relobj, $categoryId, $hostServiceRel, $obj)
    {
        if (!isset($args[1])) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $relation = $args[1];
        $relations = explode("|", $relation);
        $relationTable = array();
        $excludedList = $obj->getList(
            'service_id',
            -1,
            0,
            null,
            null,
            array('service_register' => '1'),
            'AND'
        );

        foreach ($relations as $rel) {
            $tmp = explode(",", $rel);
            if (count($tmp) < 2) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            } elseif (count($tmp) > 2) {
                throw new CentreonClapiException('One Service by Host Name please!');
            }
            $elements = $hostServiceRel->getMergedParameters(
                array('host_id'),
                array('service_id'),
                -1,
                0,
                null,
                null,
                array('host_name' => $tmp[0], 'service_description' => $tmp[1], 'service_register' => '1'),
                "AND"
            );
            if (!count($elements)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $tmp[0] . "/" . $tmp[1]);
            }
            $relationTable[] = $elements[0]['service_id'];
        }
        $existingRelationIds = $relobj->getTargetIdFromSourceId(
            $relobj->getSecondKey(),
            $relobj->getFirstKey(),
            array($categoryId)
        );

        foreach ($excludedList as $excluded) {
            $relobj->delete($categoryId, $excluded['service_id']);
        }

        foreach ($relationTable as $relationId) {
            $relobj->insert($categoryId, $relationId);
        }
        $acl = new CentreonACL();
        $acl->reload(true);
    }

    /**
     *
     * @param type $args
     * @param type $relobj
     * @param type $obj
     * @param type $categoryId
     * @throws CentreonClapiException
     */
    private function setServiceTemplate($args, $relobj, $obj, $categoryId)
    {
        if (!isset($args[1])) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $relation = $args[1];
        $relations = explode("|", $relation);
        $relationTable = array();
        $excludedList = $obj->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array('service_register' => 0),
            "AND"
        );

        foreach ($relations as $rel) {
            $tab = $obj->getList(
                "service_id",
                -1,
                0,
                null,
                null,
                array('service_description' => $rel, 'service_register' => 0),
                "AND"
            );
            if (!count($tab)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $rel);
            }
            $relationTable[] = $tab[0]['service_id'];
        }

        $existingRelationIds = $relobj->getTargetIdFromSourceId(
            $relobj->getSecondKey(),
            $relobj->getFirstKey(),
            array($categoryId)
        );

        foreach ($excludedList as $excluded) {
            $relobj->delete($categoryId, $excluded['service_id']);
        }

        foreach ($relationTable as $relationId) {
            $relobj->insert($categoryId, $relationId);
        }
        $acl = new CentreonACL();
        $acl->reload(true);
    }

    /**
     * Export
     *
     * @return void
     */
    public function export($filters = null, $exportDependencies = true)
    {
        parent::export($filters, $exportDependencies);
        $scs = $this->object->getList(
            array($this->object->getPrimaryKey(), $this->object->getUniqueLabelField()),
            -1,
            0,
            null,
            null,
            $filters
        );
        $relobj = new \Centreon_Object_Relation_Service_Category_Service();
        $hostServiceRel = new \Centreon_Object_Relation_Host_Service();
        $svcObj = new \Centreon_Object_Service();
        foreach ($scs as $sc) {
            $scId = $sc[$this->object->getPrimaryKey()];
            $scName = $sc[$this->object->getUniqueLabelField()];
            $relations = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $scId);
            foreach ($relations as $serviceId) {
                $svcParam = $svcObj->getParameters($serviceId, array('service_description', 'service_register'));
                if ($svcParam['service_register'] == 1) {
                    $elements = $hostServiceRel->getMergedParameters(
                        array('host_name'),
                        array('service_description'),
                        -1,
                        0,
                        null,
                        null,
                        array("service_id" => $serviceId),
                        "AND"
                    );
                    foreach ($elements as $element) {
                        echo $this->action . $this->delim
                            . "addservice" . $this->delim
                            . $scName . $this->delim
                            . $element['host_name'] . "," . $element['service_description'] . "\n";
                    }
                } else {
                    echo $this->action . $this->delim
                        . "addservicetemplate" . $this->delim
                        . $scName . $this->delim
                        . $svcParam['service_description'] . "\n";
                }
            }
        }
    }
}
