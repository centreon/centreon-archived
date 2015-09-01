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

namespace CentreonBam\Models;

use Centreon\Models\CentreonBaseModel;

/**
 * Used for interacting with Business activities
 *
 * @author sylvestre
 */
class BusinessActivityRealtime extends CentreonBaseModel
{
    protected static $table = "cfg_bam";
    protected static $primaryKey = "ba_id";
    protected static $uniqueLabelField = "name";

    protected static $aclResourceType = 6;

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
        
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_bas'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_bas.tag_id', 
                    'cfg_tags_bas.resource_id = cfg_bam.ba_id ')
            ); 
        }
        if (isset($filters['tagname']) && count($filters['tagname']) > 1) {
            $aGroup = array('sField' => 'cfg_tags_bas.resource_id', 'nb' => count($filters['tagname']));
        }
        $filters['activate'] = '1';
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
        $filters['activate'] = '1';
        $aAddFilters = array();
        $tablesString =  null;
        

        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_bas'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_bas.tag_id',
                    'cfg_tags_bas.resource_id = cfg_bam.ba_id ')
            ); 
        }
        
        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters);
    }
}
