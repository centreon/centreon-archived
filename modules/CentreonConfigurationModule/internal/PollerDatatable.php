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

use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Di;
use Centreon\Internal\Datatable;
use CentreonConfiguration\Repository\PollerRepository;

/**
 * Description of PollerDatatable
 *
 * @author lionel
 */
class PollerDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Poller';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'poller_id', 'name' => 'name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => 'Id',
            'name' => 'poller_id',
            'data' => 'poller_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'poller',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/poller/[i:id]',
                    'routeParams' => array(
                        'id' => '::poller_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'IP Address',
            'name' => 'ip_address',
            'data' => 'ip_address',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'dataSource' => '\CentreonConfiguration\Models\Node',
            'innerJoin' => 'cfg_pollers.node_id = cfg_nodes.node_id'
        ),
        array (
            'title' => 'Is Running',
            'name' => 'running',
            'data' => 'running',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'dataSource' => '\CentreonRealtime\Models\Instances',
            'leftJoin' => 'cfg_pollers.name = rt_instances.name',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => '<span class="label label-danger">No</span>',
                    '1' => '<span class="label label-success">Yes</span>'
                )
            )
        ),
        array (
            'title' => 'Has changed',
            'name' => 'hasChanged',
            'data' => 'hasChanged',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
            'customFunc' => 'hasChanged',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => '<span class="label label-success">No</span>',
                    '1' => '<span class="label label-warning">Yes</span>'
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Yes' => '1',
                    'No' => '0'
                )
            )
        ),
        array (
            'title' => 'Version',
            'name' => 'version',
            'data' => 'version',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'dataSource' => '\CentreonRealtime\Models\Instances'
        ),
        array (
            'title' => 'Status',
            'name' => 'enable',
            'data' => 'enable',
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
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Enabled' => '1',
                    'Disabled' => '0'
                )
            )
        ),
        array (
            'title' => 'Last restart',
            'name' => 'start_time',
            'data' => 'start_time',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'dataSource' => '\CentreonRealtime\Models\Instances'
        ),
        array (
            'title' => 'Last update',
            'name' => 'last_alive',
            'data' => 'last_alive',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'dataSource' => '\CentreonRealtime\Models\Instances'
        ),
        array (
            'title' => 'engine',
            'name' => 'engine',
            'data' => 'engine',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'dataSource' => '\CentreonRealtime\Models\Instances'
        )
    );

    /**
     * @var mixed
     */
    protected static $hook = array('displayPollerColumn');

    /**
     * 
     * @param array $params
     */
    public function __construct($params, $objectModelClass = '')
    {
        parent::__construct($params, $objectModelClass);
    }
    
    public static function hasChanged(&$resultSet){
        foreach ($resultSet as &$engineServer) {
            $engineServer['hasChanged'] = PollerRepository::checkChangeState(
                $engineServer['poller_id'],
                $engineServer['start_time']
            );
        }
    }
    
    
    /**
     * 
     * @param type $resultSet
     */
    public static function addAdditionnalDatas(&$resultSet)
    {
        // Get datatabases connections
        //$di = Di::getDefault();
        //$dbconn = $di->get('db_centreon');

        /* Get data from cfg_nodes */
        /*$sqlNode = "SELECT poller_id, ip_address 
            FROM cfg_nodes n, cfg_pollers p
            WHERE p.node_id = n.node_id";
        $stmtNode = $dbconn->prepare($sqlNode);
        $stmtNode->execute();
        $resNode = $stmtNode->fetchAll(\PDO::FETCH_ASSOC);
        $nodeData = array();
        foreach ($resNode as $row) {
            $nodeData[$row['poller_id']] = $row;
        }*/

        /* Get data from rt_instances */
        /*$sqlBroker = "SELECT start_time, running, 
            instance_id, name AS instance_name , last_alive, version,
            engine AS program_name
            FROM rt_instances";
        $stmtBroker = $dbconn->query($sqlBroker);
        $resultBroker = $stmtBroker->fetchAll(\PDO::FETCH_ASSOC);
        */
        // Build up the table row
        //foreach ($resultSet as &$engineServer) {
            /*$engineServer['start_time'] = '';
            $engineServer['running'] = 0;
            $engineServer['last_alive'] = '';
            $engineServer['version'] = '';
            $engineServer['program_name'] = '';
            foreach ($resultBroker as $broker) {
                if ($broker['instance_name'] == $engineServer['name']) {
                    $engineServer = array_merge($engineServer, $broker);
                }
            }
            if (isset($nodeData[$engineServer['poller_id']])) {
                $engineServer['ip_address'] = $nodeData[$engineServer['poller_id']]['ip_address'];
            }*/
            /*$engineServer['hasChanged'] = PollerRepository::checkChangeState(
                $engineServer['poller_id'],
                $engineServer['start_time']
            );*/
            
        //}
    }
    
    /**
     * 
     * @param type $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myPollerSet) {
            if(!isset($myPollerSet['engine'])){
                $myPollerSet['engine'] = "";
            }
            
            if (isset($myPollerSet['version'])) {
                $myPollerSet['version'] = $myPollerSet['engine'] . ' ' . $myPollerSet['version'];
            }

            if (isset($myPollerSet['last_alive']) && !empty($myPollerSet['last_alive'])) {
                $myPollerSet['last_alive'] = Datetime::format($myPollerSet['last_alive']);
            }

            if (isset($myPollerSet['start_time']) && !empty($myPollerSet['start_time'])) {
                $myPollerSet['start_time'] = Datetime::format($myPollerSet['start_time']);
            }
        }
    }
    
    
    /**
     * override of getDatas of Datatable
     * @return array
     */
    public function getDatas()
    {

        $modelClass = $this->objectModelClass;
        $modelTable = $modelClass::getTableName();
        $fields = "";
        $arrayInnerJoin = array();
        $arrayLeftJoin = array();
        $postFunctionsStore = array();
        foreach (static::$columns as $column) {
            if(!isset($column['source'])){
                if(!isset($column['dataSource'])){
                    $fields .= $modelTable . '.' . $column['name'] . ',';
                }else{
                    $modelClassSource = $column['dataSource'];
                    $modelTableSource = $modelClassSource::getTableName();
                    $fields .= $modelTableSource . '.' . $column['name'] . ',';
                    if(isset($column['innerJoin'])){
                        if(!isset($arrayInnerJoin[$modelTableSource])){
                            $arrayInnerJoin[$modelTableSource] =  'INNER JOIN ' . $modelTableSource . ' ON ' . $column['innerJoin'];
                        }else{
                            $arrayInnerJoin[$modelTableSource] .=  ' AND ' . $column['innerJoin'];
                        }
                    }else if(isset($column['leftJoin'])){
                        if(!isset($arrayLeftJoin[$modelTableSource])){
                            $arrayLeftJoin[$modelTableSource] =  'LEFT JOIN ' . $modelTableSource . ' ON ' . $column['leftJoin'];
                        }else{
                            $arrayLeftJoin[$modelTableSource] .=  ' AND ' . $column['leftJoin'];
                        }
                    }
                }
            }else if(isset($column['customFunc'])){
                $postFunctionsStore[] = $column['customFunc'];
            }
        }
        $fields = rtrim($fields, ',');
        
        // get fields for search
        $conditions = array();
        foreach ($this->params['columns'] as $columnSearch) {
            if ($columnSearch['searchable'] === "true" && (!empty($columnSearch['search']['value']) || $columnSearch['search']['value'] == "0")) {
                if ($columnSearch['data'] == 'tagname') {
                    $aSearch = explode(" ", $columnSearch['search']['value']);
                    foreach ($aSearch as $sSearch) {
                        $conditions[$columnSearch['data']][] = $sSearch;
                    }
                } else {
                    foreach (static::$columns as $column) {
                        if ($column['data'] === $columnSearch['data']) {
                            if(!isset($column['dataSource'])){
                                $indexCondition = $modelTable . '.' . $columnSearch['data'];
                            }else{
                                $modelClassSource = $column['dataSource'];
                                $modelTableSource = $modelClassSource::getTableName();
                                $indexCondition = $modelTableSource . '.' . $columnSearch['data'];
                            }
                            if (isset($column['type']) && (strtolower($column['type']) === 'string')) {
                                $conditions[$indexCondition] = '%' . $columnSearch['search']['value'] . '%';
                            } else {
                                $conditions[$indexCondition] = $columnSearch['search']['value'];
                            }
                        }
                    }
                }
            }
        }

        
        $sql = 'SELECT ' . $fields . ' FROM ' .$modelTable.' ';
        $sqlCount = 'SELECT count(*) as nbr_of_result_datatable FROM ' .$modelTable.' ';
        
        
        foreach($arrayInnerJoin as $innerJoin){
            $sql .= $innerJoin.' ';
            $sqlCount .= $innerJoin.' ';
        }
        
        foreach($arrayLeftJoin as $leftJoin){
            $sql .= $leftJoin.' ';
            $sqlCount .= $leftJoin.' ';
        }
                    
        $sql .= ' WHERE 1 = 1 ';
        $sqlCount .= ' WHERE 1 = 1 ';
        foreach($conditions as $fields=>$condition){
            $sql .= ' AND ' . $fields . ' LIKE \'' . $condition.'\' ';
            $sqlCount .= ' AND ' . $fields . ' LIKE \'' . $condition.'\' ';
        }
        
        $fieldsOrderBy = "";
        $orderExternal = false;
        $columnToOrderBy = static::$columns[$this->params['order'][0]['column']];
        $dir = $this->params['order'][0]['dir'];
        if(!isset($columnToOrderBy['dataSource']) && !isset($columnToOrderBy['source'])){
            $fieldsOrderBy = $modelTable . '.' . $columnToOrderBy['name'];
            $sql .= ' ORDER BY '.$fieldsOrderBy.' '.$dir;
        }else if(!isset($columnToOrderBy['source'])){
            $modelClassSource = $columnToOrderBy['dataSource'];
            $modelTableSource = $modelClassSource::getTableName();
            $fieldsOrderBy = $modelTableSource . '.' . $columnToOrderBy['name'];
            $sql .= ' ORDER BY '.$fieldsOrderBy.' '.$dir;
        }else{
            $orderExternal = $columnToOrderBy;
        }

        
        
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($postFunctionsStore as $functionStored){
            self::$functionStored($res);
        }
        
        if($orderExternal){
            $arrayFieldOrderBy = array();
            foreach($res as $r){
                $arrayFieldOrderBy[] = $r[$orderExternal['data']];
            }
            if(strtolower($dir) == 'asc'){
                array_multisort($arrayFieldOrderBy, $res, SORT_ASC);
            }else if(strtolower($dir) == 'desc'){
                array_multisort($arrayFieldOrderBy, $res, SORT_DESC);
            }
            
        }

        $datasFromDb['datas'] = $res;
        
        $stmt = $dbconn->prepare($sqlCount);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $datasFromDb['nbOfTotalDatas'] = $res[0]['nbr_of_result_datatable'];
        
        
        static::addAdditionnalDatas($datasFromDb['datas']);
        static::processHooks($datasFromDb['datas']);

        // Add RowId
        if (count(static::$rowIdColumn) > 0) {
            foreach ($datasFromDb['datas'] as &$datas) {
                $datas['DT_RowData'] = array(
                    'id' => $datas[static::$rowIdColumn['id']],
                    'name' => $datas[static::$rowIdColumn['name']]
                );
                $datas['DT_RowId'] = $datas[static::$rowIdColumn['id']];
            }
        }

        $this->formatDatas($datasFromDb['datas']);

        $sendableDatas = $this->prepareDatasForSending($datasFromDb);
        
        return $sendableDatas;
    }
}
