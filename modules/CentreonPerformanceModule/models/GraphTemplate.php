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

namespace CentreonPerformance\Models;

use CentreonConfiguration\Models\Servicetemplate;

/**
 * Listing for template graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphTemplate extends \Centreon\Models\CentreonBaseModel
{
    /**
     * @var string Table name
     */
    protected static $table = 'cfg_graph_template';

    /**
     * @var string Primary key
     */
    protected static $primaryKey = 'graph_template_id';

    /**
     * @var string Unique field
     */
    protected static $uniqueLabelField = 'svc_tmpl_id';

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

        // Filter by service template
        if (isset($filters['svc_tmpl_id']) && !empty($filters['svc_tmpl_id'])) {
            $serviceTemplateId = Servicetemplate::getIdByParameter('service_description', $filters['svc_tmpl_id'], array(), 'LIKE');
            unset($filters['svc_tmpl_id']);

            if (count($serviceTemplateId)) {
                foreach ($serviceTemplateId as $id) {
                    $filters['svc_tmpl_id'][] = $id;
                }
            } else {
                $count = 0;
            }
        }

        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
}
