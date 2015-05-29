<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace CentreonAdministration\Internal;


use Centreon\Internal\Datatable;

/**
 * Description of AuthDatatable
 *
 * @author bsauveton
 */
class AuthDatatable extends Datatable
{
    
    
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'ar_id', 'name' => 'ar_name','description'=>'ar_description','status' =>'ar_enable');
    
    
    /**
     *
     * @var type 
     */
    protected static $objectId = 'ar_id';
    
    /**
     *
     * @var type 
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonAdministration\Models\AuthRessource';
    
    /**
     *
     * @var array 
     */
    public static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('ar_name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true
    );
    
    
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'ar_id',
            'data' => 'ar_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '20px',
            'className' => "cell_center"
        ),
        array (
            'title' => "Name",
            'name' => 'ar_name',
            'data' => 'ar_name',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_name::'
                )
            ),
            'className' => "cell_center"
        ),
        array (
            'title' => "Description",
            'name' => 'ar_description',
            'data' => 'ar_description',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_description::'
                )
            ),
            'className' => "cell_center"
        ),
        array (
            'title' => "Status",
            'name' => 'ar_enable',
            'data' => 'ar_enable',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_enable::'
                )
            ),
            'className' => "cell_center"
        )
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
        foreach ($resultSet as &$myAuthSet) {
            if($myAuthSet['ar_enable'] == 0){
                $myAuthSet['ar_enable'] = 'Disabled';
            }else if($myAuthSet['ar_enable'] == 1){
                $myAuthSet['ar_enable'] = 'Enabled';
            }
        }
    }
    
    //put your code here
}
