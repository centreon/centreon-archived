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

namespace Centreon\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Abstact class for configuration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@centreon.com>
 */
abstract class FormRepository
{
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
                if (!is_array($value)) {
                    $value = trim($value);
                    if (!empty($value)) {
                        $insertParams[$key] = trim($value);
                    }
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
        
        if (method_exists(get_called_class(), 'postSave')) {
            static::postSave($id, 'add', $givenParameters);
        }
        
        return $id;
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
            throw new \Exception('Primary key of object is not defined');
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
        
        if (method_exists(get_called_class(), 'postSave')) {
            static::postSave($id, 'update', $givenParameters);
        }
    }

    /**
     * Delete an object
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        $objClass = static::$objectClass;
        foreach ($ids as $id) {
            if (method_exists(get_called_class(), 'preSave')) {
                static::postSave($id, 'delete', array());
            }
            
            $objClass::delete($id);
            
            if (method_exists(get_called_class(), 'preSave')) {
                static::postSave($id, 'delete', array());
            }
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
}
