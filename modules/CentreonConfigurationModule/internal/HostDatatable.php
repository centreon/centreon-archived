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
 */

namespace CentreonConfiguration\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Di;
use CentreonRealtime\Repository\HostRepository as RealTimeHostRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\HostTemplateRepository;
use Centreon\Internal\Datatable;
use CentreonAdministration\Repository\TagsRepository;

/**
 * Description of HostDatatable
 *
 * @author lionel
 */
class HostDatatable extends Datatable
{
    /**
     *
     * @var type 
     */
    protected static $objectId = 'host_id';

    /**
     *
     * @var type 
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Host';
    
    /**
     *
     * @var array 
     */
   // protected static $additionnalDatasource = array('\CentreonConfiguration\Models\Relation\Host\Tag');
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'host_id', 'name' => 'host_name');
    
    /**
     *
     * @var array 
     */
    protected static  $aFieldNotAuthorized = array('tagname');

    /**
     *
     * @var array 
     */
    public static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('host_name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(

        array (
            'title' => "",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '20px',
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
            'searchParam' => array(
                'main' => 'true',
                'type' => 'select',
                'additionnalParams' => array(
                    'Enabled' => '1',
                    'Disabled' => '0'
                )
            ),
            'className' => "cell_center",
            'width' => '50px'
        ),
        array (
            'title' => "Id",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '20px',
            'className' => "cell_center"
        ),
        array (
            'title' => 'Host',
            'name' => 'host_name',
            'data' => 'host_name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'host',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::host_name::'
                )
            ),
            'searchParam' => array(
                'main' => 'true',
            )
        ),
        array (
            'title' => 'Description',
            'name' => 'host_alias',
            'data' => 'host_alias',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'IP Address / DNS',
            'name' => 'host_address',
            'data' => 'host_address',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Interval',
            'name' => 'host_check_interval',
            'data' => 'host_check_interval',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => "cell_center"
        ),
        array (
            'title' => 'Retry',
            'name' => 'host_retry_check_interval',
            'data' => 'host_retry_check_interval',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => "cell_center"
        ),
        array (
            'title' => 'Atp',
            'name' => 'host_max_check_attempts',
            'data' => 'host_max_check_attempts',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
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
            'className' => "cell_center",
            'width' => "20px"
        ),

        array (
            'title' => 'Tags',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'tablename' => 'cfg_tags'
        ),
    );
    
    protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'host'
        )
    );

    //protected static $hook= 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'host'
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
        $router = Di::getDefault()->get('router');
        
        foreach ($resultSet as &$myHostSet) {

            /* ------------

            $router->getPathFor('/centreon-configuration/host/snapshot/')
            $router->getPathFor('/centreon-realtime/host/')

            ---------*/

            $myHostSet['host_name'] ='<span class="icoListing">'.HostRepository::getIconImage($myHostSet['host_name']).'</span>'.
                $myHostSet['host_name'];

                /* Host State */
                $myHostSet['host_name'] .= RealTimeHostRepository::getStatusBadge(
                    RealTimeHostRepository::getStatus($myHostSet['host_id'])
                );


            /* Templates */
            $myHostSet['host_template']  = "";
            $templates = HostRepository::getTemplateChain($myHostSet['host_id'], array(), 1);
            $myHostSet['host_template'] .= '<span>'.count($templates).'</span>';

            /*foreach ($templates as $template) {
               /* $myHostSet['host_template'] .= '<span class="badge alert-success" data-overlay-url="'.$router->getPathFor('/centreon-configuration/hosttemplate/viewconf/').
                    $template['id'].'"><a class="overlay" href="'.
                    $router->getPathFor("/centreon-configuration/hosttemplate/[i:id]", array('id' => $template['id'])).
                    '"><i class="fa '.
                    $template['ico'].
                    '"></i></a></span>';
            }*/
            
            /* Tags */
            $myHostSet['tagname']  = "";
            $aTags = TagsRepository::getList('host', $myHostSet['host_id'], 2);
            foreach ($aTags as $oTags) {
                $myHostSet['tagname'] .= TagsRepository::getTag('host', $myHostSet['host_id'], $oTags['id'], $oTags['text'], $oTags['user_id']);
            }
            $myHostSet['tagname'] .= TagsRepository::getAddTag('host', $myHostSet['host_id']);
        }
    }
    
}
