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

namespace CentreonAdministration\Models;

use Centreon\Models\CentreonBaseModel;
use Centreon\Internal\Di;

/**
 * Models for tags
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @package CentreonAdministration
 */
class Tag extends CentreonBaseModel
{
    protected static $table = 'cfg_tags';
    protected static $primaryKey = 'tag_id';
    protected static $uniqueLabelField = 'tagname';
    protected static $relations = array();
    
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
        $aAddFilters  = array(),
        $aGroup = array()
    ) {
        if (is_null($staticFilter)) {
            $staticFilter = "user_id IS NULL";
        }
        return parent::getList(
            $parameterNames,
            $count,
            $offset,
            $order,
            $sort,
            $filters,
            $filterType,
            $tablesString,
            $staticFilter,
            $aAddFilters,
            ""
        );
    }
}
