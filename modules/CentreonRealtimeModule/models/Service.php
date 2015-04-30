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
        $filters['s.enabled'] = '1';
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

        if (isset($filters['name']) && !empty($filters['name'])) {
            $aAddFilters['tables'][] = 'rt_hosts';
            $aAddFilters['join'][] = 'rt_hosts.host_id = s.host_id';
        }

        if (isset($filters['state'])) {
            $filters['s.state'] = $filters['state'];
            unset($filters['state']);
        }

        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
}
