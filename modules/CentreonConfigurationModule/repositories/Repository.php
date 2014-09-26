<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

use \Centreon\Internal\Di;
use \Centreon\Internal\Exception;
use \CentreonConfiguration\Repository\AuditlogRepository;

/**
 * Abstact class for configuration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@merethis.com>
 */
abstract class Repository
{
    const ORGANIZATION_FIELD = 'organization_id';

    /**
     * @var array
     */
    protected static $relationMap;

    /**
     * @var string
     */
    protected static $objectClass;

    /**
     * @var string
     */
    protected static $secondaryObjectClass;

    /**
     * @var string
     */
    protected static $objectName;


    /**
     * Reset all static properties
     */ 
    public static function reset()
    {
        static::$relationMap = null;
        static::$objectClass = null;
        static::$secondaryObjectClass = null;
        static::$objectName = null;
    }

    /**
     * Set relation map property
     *
     * @param array $relationMap
     */
    public static function setRelationMap($relationMap)
    {
        static::$relationMap = $relationMap;
    }

    /**
     * Set object name property
     *
     * @param string $objectName
     */
    public static function setObjectName($objectName)
    {
        static::$objectName = $objectName;
    }

    /**
     * Set object class property
     *
     * @param string $objectClass
     */ 
    public static function setObjectClass($objectClass)
    {
        static::$objectClass = $objectClass;
    }

    /**
     * Set secondary object class property
     *
     * @param string $secondaryObjectClass
     */
    public static function setSecondaryObjectClass($secondaryObjectClass)
    {
        static::$secondaryObjectClass = $secondaryObjectClass;
    }

    /**
     * Get list of objects
     *
     * @param string $searchStr
     * @return array
     */
    public static function getFormList($searchStr = "")
    {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }

        $idField = $class::getPrimaryKey();
        $uniqueField = $class::getUniqueLabelField();
        $filters = array(
            $uniqueField => '%'.$searchStr.'%'
        );

        $columns = $class::getColumns();
        if (in_array(static::ORGANIZATION_FIELD, $columns)) {
           $filters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
        }

        $list = $class::getList(array($idField, $uniqueField), -1, 0, null, "ASC", $filters, "AND");
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$idField],
                "text" => $obj[$uniqueField]
            );
        }
        return $finalList;
    }

    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters)
    {
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $db = Di::getDefault()->get('db_centreon');
        $columns = $class::getColumns();
        $insertParams = array();
        $givenParameters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
        foreach ($givenParameters as $key => $value) {
            if (in_array($key, $columns)) {
                if (!is_array($value) && !empty($value)) {
                    $insertParams[$key] = trim($value);
                }
            }
        }
        $id = $class::insert($insertParams);
        if (is_null($id)) {
            throw new Exception('Could not create object');
        }
        foreach (static::$relationMap as $k => $rel) {
            if (!isset($givenParameters[$k])) {
                continue;
            }
            if ($rel::$firstObject == static::$objectClass) {
                $rel::delete($id);
            } else {
                $rel::delete(null, $id);
            }
            $arr = explode(',', ltrim($givenParameters[$k], ','));
            $db->beginTransaction();

            foreach ($arr as $relId) {
                $relId = trim($relId);
                if (is_numeric($relId)) {
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::insert($id, $relId);
                    } else {
                        $rel::insert($relId, $id);
                    }
                } elseif (!empty($relId)) {
                    $complexeRelId = explode('_', $relId);
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::insert($id, $complexeRelId[1], $complexeRelId[0]);
                    }
                }
            }
            $db->commit();
            unset($givenParameters[$k]);
        }
        static::postSave($id, 'add', $givenParameters);
    }

    /**
     * Generic update function
     *
     * @param array $givenParameters
     * @throws \Centreon\Internal\Exception
     */
    public static function update($givenParameters)
    {
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $givenParameters[$pk] = $givenParameters['object_id'];
        if (!isset($givenParameters[$pk])) {
            throw Exception('Primary key of object is not defined');
        }
        $db = Di::getDefault()->get('db_centreon');
        $id = $givenParameters[$pk];
        unset($givenParameters[$pk]);
        foreach (static::$relationMap as $k => $rel) {
            try {
                if (!isset($givenParameters[$k])) {
                    continue;
                }
                try {
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::delete($id);
                    } else {
                        $rel::delete(null, $id);
                    }
                } catch (Exception $e) {
                    ; // it's okay if nothing got deleted
                }
                $arr = explode(',', ltrim($givenParameters[$k], ','));
                $db->beginTransaction();

                foreach ($arr as $relId) {
                    $relId = trim($relId);
                    if (is_numeric($relId)) {
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $relId);
                        } else {
                            $rel::insert($relId, $id);
                        }
                    } elseif (!empty($relId)) {
                        $complexeRelId = explode('_', $relId);
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $complexeRelId[1], $complexeRelId[0]);
                        }
                    }
                }
                $db->commit();
                unset($givenParameters[$k]);
            } catch (Exception $e) {
                throw new Exception('Error while updating', 0, $e);
            }
        }
        $columns = $class::getColumns();
        foreach ($givenParameters as $key => $value) {
            if (is_string($value)) {
                $givenParameters[$key] = trim($value);
            }
            if (!in_array($key, $columns)) {
                unset($givenParameters[$key]);
            }
        }
        $class::update($id, $givenParameters);
        static::postSave($id, 'update', $givenParameters);
    }

    /**
     * Delete a object
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        $objClass = static::$objectClass;
        foreach ($ids as $id) {
            static::preSave($id, 'delete');
            $objClass::delete($id);
            static::postSave($id, 'delete');
        }
    }

    /**
     * Duplicate a object
     *
     * @param array $listDuplicate
     */
    public static function duplicate($listDuplicate)
    {
        $objClass = static::$objectClass;
        foreach ($listDuplicate as $id => $nb) {
            $objClass::duplicate($id, $nb);
        }
    }

    /**
     * Get relations 
     *
     * @param string $relClass
     * @param int $id
     * @return array 
     */
    public static function getRelations($relClass, $id)
    {
        $curObj = static::$objectClass;
        if ($relClass::$firstObject == $curObj) {
            $tmp = $relClass::$secondObject;
            $fArr = array();
            $sArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
        } else {
            $tmp = $relClass::$firstObject;
            $fArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
            $sArr = array();
        }
        $cmp = $curObj::getTableName() . '.' . $curObj::getPrimaryKey();
        $list = $relClass::getMergedParameters(
            $fArr,
            $sArr,
            -1,
            0,
            null,
            "ASC",
            array($cmp => $id),
            "AND"
        );
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$tmp::getPrimaryKey()],
                "text" => $obj[$tmp::getUniqueLabelField()]
            );
        }
        return $finalList;
    }

    /**
     * Get simple relation (1-N)
     *
     * @param string $fieldName
     * @param string $targetObj
     * @param int $id
     * @param bool $reverse
     */
    public static function getSimpleRelation($fieldName, $targetObj, $id, $reverse = false)
    {
        if ($reverse === false) {
            $obj = static::$objectClass;
            $pk = $obj::getPrimaryKey();
            $fields = $fieldName;
        } else {
            $obj = $targetObj;
            $pk = $fieldName;
            $fields = $targetObj::getPrimaryKey().','.$targetObj::getUniqueLabelField();
        }
        $filters = array(
            $obj::getTableName().'.'.$pk => $id
        );
        $list = $obj::getList($fields, -1, 0, null, "ASC", $filters, "AND");

        if (count($list) == 0) {
            return array('id' => null, 'text' => null);
        } elseif ($reverse === true) {
            $finalList = array();
            foreach ($list as $obj) {
                $finalList[] = array(
                    "id" => $obj[$targetObj::getPrimaryKey()],
                    "text" => $obj[$targetObj::getUniqueLabelField()]
                );
            }
            return $finalList;
        }

        $filters = array($targetObj::getPrimaryKey() => $list[0][$fieldName]);
        $targetPrimaryKey = $targetObj::getPrimaryKey();
        $targetName = $targetObj::getUniqueLabelField();
        $targetList = $targetObj::getList(
            $targetPrimaryKey.','.$targetName,
            -1,
            0,
            null,
            "ASC",
            $filters,
            "AND"
        );

        $finalList = array();
        if (count($targetList) > 0) {
            $finalList["id"] = $targetList[0][$targetPrimaryKey];
            $finalList["text"] = $targetList[0][$targetName];
        }
        return $finalList;
    }

    /**
     * Action before save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     */
    protected static function preSave($id, $action = 'add')
    {
        $actionList = array(
            'delete' => 'd'
        );
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = static::$objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        /* Add change log */
        if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                array()
            );
        }
    }

    /**
     * Action after save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     * @param array $params
     */
    protected static function postSave($id, $action = 'add', $params = array())
    {
        $actionList = array(
            'add' => 'a',
            'update' => 'c'
        );
        $di = Di::getDefault();
        $event = $di->get('events');
        $eventParams = array(
            'id' => $id,
            'params' => $params
        );
        $event->emit(static::$objectName . '.' . $action, $eventParams);
        /* Add change log */
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = static::$objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                $params
            );
        }
    }
}
