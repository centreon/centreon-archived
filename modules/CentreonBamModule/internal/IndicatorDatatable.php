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
 */

namespace CentreonBam\Internal;

use \Centreon\Internal\Datatable\Datasource\CentreonDb;

/**
 * Description of BaDatatable
 *
 * @author lionel
 */
class IndicatorDatatable extends \Centreon\Internal\Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonBam\Models\Indicator';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('kpi_id', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    public static $columns = array(
        array (
            'title' => "<input id='allIndicatorid' class='allIndicatorid' type='checkbox'>",
            'name' => 'kpi_id',
            'data' => 'kpi_id',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::ba_group_name::'
                )
            )
        ),
        array (
            'title' => 'Type',
            'name' => 'kpi_type',
            'data' => 'kpi_type',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/bam/business-view/[i:id]',
                    'routeParams' => array(
                        'id' => '::kpi_id::'
                    ),
                    'linkName' => '::kpi_type::'
                )
            )
        ),
        array (
            'title' => 'Warning Impact',
            'name' => 'drop_warning',
            'data' => 'drop_warning',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Critical Impact',
            'name' => 'drop_critical',
            'data' => 'drop_critical',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Unknown Impact',
            'name' => 'drop_unknown',
            'data' => 'drop_unknown',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Status',
            'name' => 'activate',
            'data' => 'activate',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
            'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>',
                )
            )
        ),
    );
}