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
        'stateSave' => true,
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
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'servicetemplate',
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
            ),
            'searchParam' => array(
                'main' => 'true'
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
