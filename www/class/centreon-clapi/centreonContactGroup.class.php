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
require_once "Centreon/Object/Contact/Contact.php";
require_once "Centreon/Object/Contact/Group.php";
require_once "Centreon/Object/Relation/Contact/Group/Contact.php";

/**
 * Class for managing contact groups
 *
 * @author sylvestre
 */
class CentreonContactGroup extends CentreonObject
{

    const ORDER_UNIQUENAME = 0;
    const ORDER_ALIAS = 1;

    public static $aDepends = array(
        'CMD',
        'TP',
        'CONTACT'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Contact_Group();
        $this->params = array('cg_activate' => '1');
        $this->insertParams = array('cg_name', 'cg_alias');
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
        $this->action = "CG";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "cg_activate";
    }

    /**
     * List contact groups
     *
     * @param $string $parameters
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array('cg_id', 'cg_name', 'cg_alias');
        $paramString = str_replace("cg_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Add contact group
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $this->checkIllegalChar($params[self::ORDER_UNIQUENAME]);
        $addParams['cg_alias'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Update contact groups
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
            if (!preg_match("/^cg_/", $params[1])) {
                $params[1] = "cg_" . $params[1];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Magic method for get/set/add/del relations
     *
     * @param string $name
     * @param array $arg
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|set|add|del)contact$/", $name, $matches)) {
            $relobj = new \Centreon_Object_Relation_Contact_Group_Contact();
            $obj = new \Centreon_Object_Contact();

            /* Parse arguments */
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $args = explode($this->delim, $arg[0]);
            $cgIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
            if (!count($cgIds)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0]);
            }
            $cgId = $cgIds[0];

            if ($matches[1] == "get") {
                $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $cgIds);
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
                    $relobj->delete($cgId);
                }
                $existingRelationIds = $relobj->getTargetIdFromSourceId(
                    $relobj->getSecondKey(),
                    $relobj->getFirstKey(),
                    array($cgId)
                );
                foreach ($relationTable as $relationId) {
                    if ($matches[1] == "del") {
                        $relobj->delete($cgId, $relationId);
                    } elseif ($matches[1] == "set" || $matches[1] == "add") {
                        if (!in_array($relationId, $existingRelationIds)) {
                            $relobj->insert($cgId, $relationId);
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
    public function export($filter_name)
    {
        if (!parent::export($filter_name)) {
            return false;
        }
        $filters = array($labelField => $filter_name);

        $relObj = new \Centreon_Object_Relation_Contact_Group_Contact();
        $contactObj = new \Centreon_Object_Contact();
        $cgFieldName = $this->object->getUniqueLabelField();
        $cFieldName = $contactObj->getUniqueLabelField();
        $elements = $relObj->getMergedParameters(
            array($cgFieldName),
            array($cFieldName, "contact_id"),
            -1,
            0,
            $cgFieldName,
            'ASC',
            $filters,
            'AND'
        );
        foreach ($elements as $element) {
            CentreonContact::getInstance()->export($element['contact_alias']);
            echo $this->action . $this->delim . "addcontact" .
                $this->delim . $element[$cgFieldName] . $this->delim . $element[$cFieldName] .
                $this->delim . $element['contact_alias'] . "\n";
        }
    }
}
