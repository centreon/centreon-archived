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

namespace CentreonRealtime\Models;

use Centreon\Models\CentreonBaseModel;

/**
 * Used for interacting with services
 *
 * @author sylvestre
 */
class Service extends CentreonBaseModel
{
    protected static $table = "rt_services s";
    protected static $primaryKey = "service_id";
    protected static $uniqueLabelField = "description";
    protected static $simpleRelation = array('host_id' => '\CentreonRealtime\Models\Host');

    protected static $aclResourceType = 2;
    
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
        $filters['s.enabled'] = '1';
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
        $filters['s.enabled'] = '1';
        $aAddFilters = array();
        $tablesString =  null;
        $aGroup = array();
        
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters['tables'][] = 'cfg_tags';
            $aAddFilters['join'][] = 'cfg_tags.tag_id = cfg_tags_services.tag_id';

            $aAddFilters['tables'][] = 'cfg_tags_services';
            $aAddFilters['join'][] = 'cfg_tags_services.resource_id = s.service_id';
        }

        if (isset($filters['tagname']) && count($filters['tagname']) > 1) {
            $aGroup = array('sField' => 'cfg_tags_services.resource_id', 'nb' => count($filters['tagname']));
        }

        if (isset($filters['host_id']) && !empty($filters['host_id'])) {
            $aAddFilters['tables'][] = 'rt_hosts';
            $aAddFilters['join'][] = 'rt_hosts.host_id = s.host_id';
        }

        if (isset($filters['state'])) {
            $filters['s.state'] = $filters['state'];
            unset($filters['state']);
        }

        if (isset($filters['host_id'])) {
            $filters['name'] = $filters['host_id'];
            unset($filters['host_id']);
        }

        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
}
