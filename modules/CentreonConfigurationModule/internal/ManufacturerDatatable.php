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

namespace CentreonConfiguration\Internal;

use \Centreon\Internal\Datatable\Datasource\CentreonDb;

/**
 * Description of ManufacturerDatatable
 *
 * @author lionel
 */
class ManufacturerDatatable extends \Centreon\Internal\ExperimentalDatatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Manufacturer';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc'),
            array('id', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    protected static $columns = array(
        array (
            'title' => "<input id='allManufacturerid' class='allManufacturerid' type='checkbox'>",
            'name' => 'id',
            'data' => 'id',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/configuration/trap/[i:id]',
                    'routeParams' => array(
                        'id' => '::id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Description',
            'name' => 'description',
            'data' => 'description',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
    );
    
    /**
     * 
     * @param array $params
     */
    public function __construct($params, $objectModelClass = '')
    {
        parent::__construct($params, $objectModelClass);
    }
}
