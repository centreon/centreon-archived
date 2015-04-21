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
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Relation\Host\Service as HostServiceRelation;
use CentreonConfiguration\Models\Relation\Host\Hosttemplate as HostHosttemplateRelation;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\ServiceRepository;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Host extends CentreonBaseModel
{
    protected static $table = "cfg_hosts";
    protected static $primaryKey = "host_id";
    protected static $uniqueLabelField = "host_name";
    protected static $relations = array(
        "\CentreonConfiguration\Models\Relation\Host\Service",
        "\CentreonConfiguration\Models\Relation\Host\Hostparents",
        "\CentreonConfiguration\Models\Relation\Host\Hostchildren"
    );
    
    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public static function insert($params = array())
    {
        $params['host_register'] = '1';
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO " . static::$table;
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        $not_null_attributes = array();
        $is_int_attribute = array();
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
            
            
            // Custom macros
            
            
            
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
        $filterType = "OR",
        $tablesString = null,
        $staticFilter = null,
        $aAddFilters  = array()
    ) {
        $filters['host_register'] = '1';
        if (is_array($filterType)) {
            $filterType['host_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'host_register' => 'AND'
            );
        }
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, null, null, $aAddFilters);
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
        $tablesString =  '';
        
        $filters['host_register'] = '1';
        if (is_array($filterType)) {
            $filterType['host_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'host_register' => 'AND'
            );
        }
                
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_hosts'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_hosts.tag_id', 'cfg_tags.tag_id = cfg_tags_hosts.tag_id',
                    'cfg_tags_hosts.resource_id=cfg_hosts.host_id ')
            ); 
        }
        
        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters);
    }

    /**
     * Used for duplicate a host
     *
     * @param int $sourceObjectId The source host id
     * @param int $duplicateEntries The number entries
     * @return array List of new host id
     */
    public static function duplicate($sourceObjectId, $duplicateEntries = 1)
    {
        $db = Di::getDefault()->get(static::$databaseName);
        $sourceParams = static::getParameters($sourceObjectId, '*');
        if (false === $sourceParams) {
            throw new \Exception(static::OBJ_NOT_EXIST);
        }
        unset($sourceParams['host_id']);
        $originalName = $sourceParams['host_name'];
        $explodeOriginalName = explode('_', $originalName);
        $j = 0;
        if (($count = count($explodeOriginalName)) > 1 && is_numeric($explodeOriginalName[$count - 1])) {
            $originalName = join('_', array_slice($explodeOriginalName, 0, -1));
            $j = $explodeOriginalName[$count - 1];
        }

        $listDuplicateId = array();
        for ($i = 0; $i < $duplicateEntries; $i++) {
            /* Search the unique name for duplicate host */
            do {
                $j++;
                $unique = self::isUnique($originalName . '_' . $j);
            } while (false === $unique);
            $sourceParams['host_name'] = $originalName . '_' . $j;
            /* Insert the duplicate host */
            $lastId = static::insert($sourceParams);
            $listDuplicateId[] = $lastId;
            /* Insert relation */
            /* Duplicate service */
            /*   Get service for the source host */
            $listSvc = HostServiceRelation::getTargetIdFromSourceId('service_service_id', 'host_host_id', $sourceObjectId);
            foreach ($listSvc as $svcId) {
                /* Duplicate service */
                $newSvcId = Service::duplicate($svcId, 1, true);
                if (count($newSvcId) > 0) {
                    /* Attach the new service to the new host */
                    HostServiceRelation::insert($lastId, $newSvcId[0]);
                }
            }
            $db->beginTransaction();
            /* Duplicate macros */
            $queryDupMacros = "INSERT INTO cfg_customvariables_hosts (host_macro_name, host_macro_value, is_password, host_host_id)
                SELECT host_macro_name, host_macro_value, is_password, " . $lastId . " FROM cfg_customvariables_hosts
                    WHERE host_host_id = " . $sourceObjectId;
            $db->query($queryDupMacros);
            /* Host template */
            $queryDupTemplate = "INSERT INTO cfg_hosts_templates_relations (host_host_id, host_tpl_id, `order`)
                SELECT " . $lastId . ", host_tpl_id, `order` FROM cfg_hosts_templates_relations
                    WHERE host_host_id = " . $sourceObjectId;
            $db->query($queryDupTemplate);
            /* Host global tags */
            $queryDupTag = "INSERT INTO cfg_tags_hosts (tag_id, resource_id)
                SELECT th.tag_id, " . $lastId . " FROM cfg_tags_hosts th, cfg_tags t
                    WHERE t.user_id IS NULL AND t.tag_id = th.tag_id AND th.resource_id = " . $sourceObjectId;
            $db->query($queryDupTag);
            $db->commit();
        }
    }
}
