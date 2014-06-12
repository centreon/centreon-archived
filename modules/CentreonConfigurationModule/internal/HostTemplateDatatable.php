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
 * Description of HostDatatable
 *
 * @author lionel
 */
class HostTemplateDatatable extends \Centreon\Internal\ExperimentalDatatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Hosttemplate';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('host_name', 'asc'),
            array('host_id', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "<input id='allHostid' class='allHostid' type='checkbox'>",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::host_name::'
                )
                            ),
            'className' => "cell_center"
        ),
        array (
            'title' => 'Name',
            'name' => 'host_name',
            'data' => 'host_name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/configuration/hosttemplate/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::host_name::'
                )
            )
        ),
        array (
            'title' => 'Description',
            'name' => 'host_alias',
            'data' => 'host_alias',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Interval',
            'name' => 'host_check_interval',
            'data' => 'host_check_interval',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '50px',
            'className' => "cell_center"
        ),
        array (
            'title' => 'Retry',
            'name' => 'host_retry_check_interval',
            'data' => 'host_retry_check_interval',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40px',
            'className' => "cell_center"
        ),
        array (
            'title' => 'Atp',
            'name' => 'host_max_check_attempts',
            'data' => 'host_max_check_attempts',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40px',
            'className' => "cell_center"
        ),
        array (
            'title' => 'Templates',
            'name' => 'host_id as host_template',
            'data' => 'host_template',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => "cell_center"
        ),
        array (
            'title' => 'Status',
            'name' => 'host_activate',
            'data' => 'host_activate',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                            'type' => 'select',
                            'parameters' => array(
                                                  '0' => '<span class="label label-danger">Disabled</span>',
                                                  '1' => '<span class="label label-success">Enabled</span>',
                                                  '2' => 'Trash',
                                                  )
                            ),
            'className' => "cell_center",
            'width' => '50px'
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
    
    /**
     * 
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');

        foreach ($resultSet as &$myHostSet) {
            $myHostSet['host_name'] = \CentreonConfiguration\Repository\HostRepository::getIconImage($myHostSet['host_name']).
                '&nbsp;'.$myHostSet['host_name'];
            
            /* Templates */
            $myHostSet['host_template']  = "";
            $templates = \CentreonConfiguration\Repository\HosttemplateRepository::getTemplateList($myHostSet['host_id']);
            foreach ($templates as $template) {
                $myHostSet['host_template'] .= "<span class='badge alert-success'><a href='".$router->getPathFor("/configuration/hosttemplate/[i:id]", array('id' => $template['id']))."'><i class='fa ".$template['ico']."'></i></a></span>";
            } 

        }
    }
}
