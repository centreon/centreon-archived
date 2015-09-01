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

namespace CentreonPerformance\Internal;

use CentreonConfiguration\Repository\ServiceRepository;
use CentreonPerformance\Repository\GraphTemplate;

/**
 * Listing for template graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphTemplateDatatable extends \Centreon\Internal\Datatable
{
    /**
     * @var string The provider for centreon database
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';

    /**
     * @var string The models
     */
    protected static $datasource = '\CentreonPerformance\Models\GraphTemplate';

    /**
     *
     * @var array
     */
    protected static $rowIdColumn = array('id' => 'graph_template_id', 'name' => 'svc_tmpl_id');

    /**
     * @var array Configuration for display listing
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('svc_tmpl_id', 'asc')
        ),
        'stateSave' => false,
        'paging' => true
    );

    /**
     * @var array Configuration displayed columns
     */
    public static $columns = array(
        array(
            'title' => "Id",
            'name' => 'graph_template_id',
            'data' => 'graph_template_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '20px',
            'className' => 'cell_center'
        ),
        array(
            'title' => 'Service Template',
            'name' => 'svc_tmpl_id',
            'data' => 'svc_tmpl_id',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-performance/configuration/graphtemplate/[i:id]',
                    'routeParams' => array(
                        'id' => '::graph_template_id::'
                    ),
                    'linkName' => '::svc_tmpl_id::'
                )
            )
        ),
        array(
            'title' => 'Metrics',
            'name' => 'NULL AS metrics',
            'data' => 'metrics',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string'
        )
    );

    /**
     *
     */
    protected function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$currentResultSet) {
            $tplStr = null;
            $tplArr = ServiceRepository::getMyServiceTemplateModels($currentResultSet['svc_tmpl_id']);
            if (false === is_null($tplArr)) {
                $currentResultSet['svc_tmpl_id'] = $tplArr['description'];
                $currentResultSet['DT_RowData']['name'] = $tplArr['description'];
            }

            $metrics = GraphTemplate::getMetrics($currentResultSet['graph_template_id']);
            $tplStr = '';
            foreach ($metrics as $metric) {
                $tplStr .= '<span style="color: ' . $metric['color'] . '">' . $metric['metric_name'] . '</span>&nbsp;';
            }
            $currentResultSet['metrics'] = $tplStr;
        }
    }
}
