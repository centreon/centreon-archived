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

namespace CentreonConfiguration\Models;

use Centreon\Internal\Di;
use Centreon\Models\CentreonBaseModel;

/**
 * Used for interacting with services
 *
 * @author sylvestre
 */
class Servicetemplate extends CentreonBaseModel
{
    protected static $table = "cfg_services";
    protected static $primaryKey = "service_id";
    protected static $uniqueLabelField = "service_description";
    /*protected static $basicFilters = array(
        'service_register' => '0',
    );*/
    
    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public static function insert($params = array())
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO " . static::$table;
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        $not_null_attributes = array();
        $is_int_attribute = array();
        $params['service_register'] = '0';
        static::setAttributeProps($params, $not_null_attributes, $is_int_attribute);

        foreach ($params as $key => $value) {
            if ($key == static::$primaryKey || is_null($value)) {
                continue;
            }
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            } elseif (!is_numeric($value) && isset($is_int_attribute[$key])) {
                $value = null;
            }
            $type = \PDO::PARAM_STR;
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            }
            $sqlParams[] = array('value' => trim($value), 'type' => $type);
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $stmt = $db->prepare($sql);
            $i = 1;
            foreach ($sqlParams as $v) {
                $stmt->bindValue($i, $v['value'], $v['type']);
                $i++;
            }
            $stmt->execute();
            return $db->lastInsertId(static::$table, static::$primaryKey);
        }
        return null;
    }
    
    /**
     * 
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $aAddFilters = array();
        $aGroup = array();
        $filters['service_register'] = '0';
        if (is_array($filterType)) {
            $filterType['service_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'service_register' => 'AND'
            );
        }
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_services'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_services.tag_id', 
                    'cfg_tags_services.resource_id=cfg_services.service_id ')
            ); 
        }

        if (isset($filters['tagname']) && count($filters['tagname']) > 1) {
            $aGroup = array('sField' => 'cfg_tags_services.resource_id', 'nb' => count($filters['tagname']));
        }
        
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, null, null, $aAddFilters, $aGroup);
    }
    
    /**
     * 
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getListBySearch(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $aAddFilters = array();
        $aGroup = array();
        $filters['service_register'] = '0';
        
        if (is_array($filterType)) {
            $filterType['service_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'service_register' => 'AND'
            );
        }
        
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_services'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_services.tag_id', 
                    'cfg_tags_services.resource_id=cfg_services.service_id ')
            ); 
        }
        
        if (isset($filters['tagname']) && count($filters['tagname']) > 1) {
            $aGroup = array('sField' => 'cfg_tags_services.resource_id', 'nb' => count($filters['tagname']));
        }

        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, null, null, $aAddFilters, $aGroup);
    }

    /**
     * @param string $paramName
     * @param array $paramValues
     * @param array $extraConditions
     * @return array
     */
    public static function getIdByParameter($paramName, $paramValues = array(), $extraConditions = array(), $conditionType = '=')
    {
        $extraConditions['service_register'] = '0';
        return parent::getIdByParameter($paramName, $paramValues, $extraConditions, $conditionType);
    }

    /**
     * Used for duplicate a service
     *
     * @param int $sourceObjectId The source service id
     * @param int $duplicateEntries The number entries
     * @return array List of new service id
     */
    public static function duplicate($sourceObjectId, $duplicateEntries = 1)
    {
        $db = Di::getDefault()->get(static::$databaseName);
        /* Get element to duplicate */
        $sourceParams = static::getParameters($sourceObjectId, '*');
        if (false === $sourceParams) {
            throw new \Exception(static::OBJ_NOT_EXIST);
        }
        unset($sourceParams['service_id']);
        $originalName = $sourceParams['service_description'];
        $explodeOriginalName = explode('_', $originalName);
        $j = 0;
        if (($count = count($explodeOriginalName)) > 1 && is_numeric($explodeOriginalName[$count - 1])) {
            $newName = join('_', array_slice($explodeOriginalName, 0, -1));
            $j = $explodeOriginalName[$count - 1];
        } else {
            $newName = $originalName;
        }

        /* Insert new service */
        $listDuplicateId = array();
        for ($i = 0; $i < $duplicateEntries; $i++) {
            /* Search the unique name for duplicate service if not duplicate from host */
            do {
                $j++;
                $unique = self::isUnique($newName . '_' . $j);
            } while (false === $unique);
            $newName = $originalName . '_' . $j;
            $sourceParams['service_description'] = $newName;
            /* Insert the duplicate service */
            $lastId = static::insert($sourceParams);
            $listDuplicateId[] = $lastId;
            /* Insert relation */
            $db->beginTransaction();
            /*  Duplicate macros for new service */
            $queryDupMacros = "INSERT INTO cfg_customvariables_services (svc_macro_name, svc_macro_value, is_password, svc_svc_id)
                SELECT svc_macro_name, svc_macro_value, is_password, " . $lastId . " FROM cfg_customvariables_services
                    WHERE svc_svc_id = " . $sourceObjectId;
            $db->query($queryDupMacros);
            /* Service global tags */
            $queryDupTag = "INSERT INTO cfg_tags_services (tag_id, resource_id)
                SELECT ts.tag_id, " . $lastId . " FROM cfg_tags_services ts, cfg_tags t
                    WHERE t.user_id IS NULL AND t.tag_id = ts.tag_id AND ts.resource_id = " . $sourceObjectId;
            $db->query($queryDupTag);
            /* Add relation to host template */
            $queryDupHostRelation = "INSERT INTO cfg_hosts_services_relations (host_host_id, service_service_id)
                SELECT host_host_id, " . $lastId . " FROM cfg_hosts_services_relations
                    WHERE service_service_id = " . $sourceObjectId;
            $db->query($queryDupHostRelation);
            $db->commit();
        }
        return $listDuplicateId;
    }

    /**
     * Check if the name is unique
     *
     * @param string $name The name to validate
     * @return bool
     */
    public static function isUnique($name)
    {
        $db = Di::getDefault()->get(static::$databaseName);
        $query = "SELECT COUNT(service_id) as nb FROM cfg_services
            WHERE service_register = '0' AND service_description = :svc_desc";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':svc_desc', $name, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row['nb'] > 0) {
            return false;
        }
        return true;   
    }
}
