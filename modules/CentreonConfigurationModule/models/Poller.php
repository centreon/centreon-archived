<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Models;

use Centreon\Models\CentreonBaseModel;

/**
 * Used for interacting with pollers
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package Centreon
 * @subpackage Configuration
 * @version 3.0.0
 */
class Poller extends CentreonBaseModel
{
    protected static $table = "cfg_pollers";
    protected static $primaryKey = "poller_id";
    protected static $uniqueLabelField = "name";
    protected static $slugField        = "slug";

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
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType);
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
        $tablesString =  null;
        $aGroup = array();
        
        if ($parameterNames != '*' && $count != -1)
        {
            $aParam = explode(",", $parameterNames);
            $aParam = array_diff( $aParam, array( '' ) );
            $aParam = array_map("self::concatNameTable", $aParam);
            $parameterNames = implode(",", $aParam);
        }
       
        // Add join on node table
        if (isset($filters['ip_address']) && !empty($filters['ip_address'])) {
            $aAddFilters['tables'][] = 'cfg_nodes n';
            $aAddFilters['join'][] = static::$table.'.node_id = n.node_id';
        }

        // Add join on instance table
        if ((isset($filters['running']) && !empty($filters['running']))
            || (isset($filters['version']) && !empty($filters['version']))
        ) {
            $aAddFilters['tables'][] = 'rt_instances i';
            $aAddFilters['join'][] = static::$table.'.name = i.name';
        }

        // Avoid error on ambiguous column
        if (isset($filters['name'])) {
            $sField = static::$table.'.name';
            $filters[$sField] = $filters['name'];
            unset($filters['name']);
        }

        // Avoid error on ambiguous column
        if (isset($filters['enable'])) {
            $sField = static::$table.'.enable';
            $filters[$sField] = $filters['enable'];
            unset($filters['enable']);
        }

        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
    
    /**
     * 
     * @param string $item
     */
    public function concatNameTable($item)
    {
        return static::$table.".".$item; 
    }
}
