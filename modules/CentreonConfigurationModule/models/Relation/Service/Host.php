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

namespace CentreonConfiguration\Models\Relation\Service;

use Centreon\Models\CentreonRelationModel;

class Host extends CentreonRelationModel
{
    protected static $relationTable = "cfg_hosts_services_relations";
    protected static $firstKey = "service_service_id";
    protected static $secondKey = "host_host_id";
    public static $firstObject = "\CentreonConfiguration\Models\Service";
    public static $secondObject = "\CentreonConfiguration\Models\Host";
    
    /**
     * 
     * @param type $firstTableParams
     * @param type $secondTableParams
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param type $filters
     * @param type $filterType
     * @param type $relationTableParams
     * @return type
     */
    public static function getMergedParametersBySearch(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $relationTableParams = array()
    ) {
        $filters['service_register'] = '1';
        $aAddFilters = array();
        $tablesString =  '';
        
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_services'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_services.tag_id', 'cfg_tags.tag_id = cfg_tags_services.tag_id',
                    'cfg_tags_services.resource_id = cfg_services.service_id ')
            ); 
        }
        
        return parent::getMergedParametersBySearch(
            $firstTableParams,
            $secondTableParams,
            $count,
            $offset,
            $order,
            $sort,
            $filters,
            $filterType,
            $relationTableParams,
            $aAddFilters
        );
    }
}
