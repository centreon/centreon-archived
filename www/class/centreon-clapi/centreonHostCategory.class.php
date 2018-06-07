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
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonSeverityAbstract.class.php";
require_once "centreonACL.class.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Category.php";
require_once "Centreon/Object/Relation/Host/Category/Host.php";


/**
 * Class for managing host categories
 *
 * @author sylvestre
 */
class CentreonHostCategory extends CentreonSeverityAbstract
{
    public static $aDepends = array(
        'HOST'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Host_Category();
        $this->params = array('hc_activate' => '1');
        $this->insertParams = array('hc_name', 'hc_alias');
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array($this->object->getPrimaryKey(), 'level', 'icon_id')
        );
        $this->action = "HC";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "hc_activate";
    }

    /**
     * List host categories
     *
     * @param $string $parameters
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array('hc_id', 'hc_name', 'hc_alias', 'level');
        $paramString = str_replace("hc_", "", implode($this->delim, $params));
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
     * Add host category
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
        $addParams['hc_alias'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Update host category
     *
     * @param string $parameters
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if (!preg_match("/^hc_/", $params[1])) {
                $params[1] = "hc_".$params[1];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Set severity
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setseverity($parameters)
    {
        parent::setseverity($parameters);
    }

    /**
     * Unset severity
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function unsetseverity($parameters)
    {
        parent::unsetseverity($parameters);
    }

    /**
     * Magic method for get/set/add/del relations
     *
     * @param string $name
     * @param array $arg
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|set|add|del)member$/", $name, $matches)) {
            $relobj = new \Centreon_Object_Relation_Host_Category_Host();
            $obj = new \Centreon_Object_Host();

            /* Parse arguments */
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $args = explode($this->delim, $arg[0]);
            $hcIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
            if (!count($hcIds)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND .":".$args[0]);
            }
            $categoryId = $hcIds[0];

            if ($matches[1] == "get") {
                $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $hcIds);
                echo "id".$this->delim."name"."\n";
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
                        throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":".$rel);
                    }
                    $relationTable[] = $tab[0];
                }
                if ($matches[1] == "set") {
                    $relobj->delete($categoryId);
                }
                $existingRelationIds = $relobj->getTargetIdFromSourceId(
                    $relobj->getSecondKey(),
                    $relobj->getFirstKey(),
                    array($categoryId)
                );
                foreach ($relationTable as $relationId) {
                    if ($matches[1] == "del") {
                        $relobj->delete($categoryId, $relationId);
                    } elseif ($matches[1] == "set" || $matches[1] == "add") {
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
     * Export
     *
     * @return void
     */
    public function export($filterName)
    {
        if (!parent::export($filterName)) {
            return false;
        }

        $relobj = new \Centreon_Object_Relation_Host_Category_Host();
        $hcFieldName = $this->object->getUniqueLabelField();
        $filters = array();
        if (!is_null($filterName)) {
            $filters[$hcFieldName] = $filterName;
        }
        $elements = $relobj->getMergedParameters(
            array($hcFieldName),
            array("host_name"),
            -1,
            0,
            $hcFieldName,
            'ASC',
            $filters,
            'AND'
        );
        foreach ($elements as $element) {
            echo $this->action . $this->delim
                . "addmember" . $this->delim
                . $element[$this->object->getUniqueLabelField()] . $this->delim
                . $element['host_name'] . "\n";
        }
    }
}
