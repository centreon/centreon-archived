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
 * SVN : $URL$
 * SVN : $Id$
 */
namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "Centreon/Object/Acl/Group.php";
require_once "Centreon/Object/Acl/Action.php";
require_once "Centreon/Object/Acl/Menu.php";
require_once "Centreon/Object/Acl/Resource.php";
require_once "Centreon/Object/Contact/Contact.php";
require_once "Centreon/Object/Contact/Group.php";
require_once "Centreon/Object/Relation/Acl/Group/Resource.php";
require_once "Centreon/Object/Relation/Acl/Group/Menu.php";
require_once "Centreon/Object/Relation/Acl/Group/Action.php";
require_once "Centreon/Object/Relation/Acl/Group/Contact/Contact.php";
require_once "Centreon/Object/Relation/Acl/Group/Contact/Group.php";

/**
 * Class for managing ACL groups
 * @author sylvestre
 *
 */
class CentreonACLGroup extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_ALIAS             = 1;

 	/**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Acl_Group();
        $this->params = array(  'acl_group_changed'                         => '1',
                                'acl_group_activate'	                    => '1'
                            );
        $this->nbOfCompulsoryParams = 2;
        $this->activateField = "acl_group_activate";
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
        $addParams['acl_group_alias'] = $params[self::ORDER_ALIAS];
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
            $params[1] = "acl_group_".$params[1];
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
        $params = array("acl_group_id", "acl_group_name", "acl_group_alias", "acl_group_activate");
        $paramString = str_replace("acl_group_", "", implode($this->delim, $params));
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
        if (!isset($arg[0])) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $args = explode($this->delim, $arg[0]);
        $groupIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
        if (!count($groupIds)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND .":".$args[0]);
        }
        $groupId = $groupIds[0];
        if (preg_match("/^(get|set|add|del)([a-zA-Z_]+)/", $name, $matches)) {
            $relclass = "Centreon_Object_Relation_Acl_Group_".ucwords($matches[2]);
            if (class_exists("Centreon_Object_Acl_".ucwords($matches[2]))) {
                $class = "Centreon_Object_Acl_".ucwords($matches[2]);
            } elseif ($matches[2] == "contactgroup") {
                $class = "Centreon_Object_Contact_Group";
                $relclass = "Centreon_Object_Relation_Acl_Group_Contact_Group";
            } else {
                $class = "Centreon_Object_".ucwords($matches[2]);
            }
            if (class_exists($relclass) && class_exists($class)) {
                $relobj = new $relclass();
                $obj = new $class();
                if ($matches[1] == "get") {
                    $tab = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), $groupIds);
                    echo "id".$this->delim."name"."\n";
                    foreach($tab as $value) {
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
                    foreach($relations as $rel) {
                        $tab = $obj->getIdByParameter($obj->getUniqueLabelField(), array($rel));
                        if (!count($tab)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":".$rel);
                        }
                        $relationTable[] = $tab[0];
                    }
                    if ($matches[1] == "set") {
                        $relobj->delete($groupId);
                    }
                    $existingRelationIds = $relobj->getTargetIdFromSourceId($relobj->getSecondKey(), $relobj->getFirstKey(), array($groupId));
                    foreach($relationTable as $relationId) {
                        if ($matches[1] == "del") {
                            $relobj->delete($groupId, $relationId);
                        } elseif ($matches[1] == "set" || $matches[1] == "add") {
                            if (!in_array($relationId, $existingRelationIds)) {
                                $relobj->insert($groupId, $relationId);
                            }
                        }
                    }
                    parent::setparam($groupId, array('acl_group_changed' => '1'));
                }
            } else {
                throw new CentreonClapiException(self::UNKNOWN_METHOD);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }
}