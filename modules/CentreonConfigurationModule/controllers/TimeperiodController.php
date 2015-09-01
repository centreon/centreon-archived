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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Timeperiod;
use CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded;
use CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded;
use Centreon\Controllers\FormController;
use CentreonConfiguration\Repository\TimePeriodRepository;

class TimeperiodController extends FormController
{
    protected $objectDisplayName = 'Timeperiod';
    public static $objectName = 'timeperiod';
    protected $objectBaseUrl = '/centreon-configuration/timeperiod';
    protected $datatableObject = '\CentreonConfiguration\Internal\TimeperiodDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Timeperiod';
    protected $repository = '\CentreonConfiguration\Repository\TimePeriodRepository';    
    public static $relationMap = array(
        'tp_include' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded',
        'tp_exclude' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded'
    );

    /**
     * 
     * @method get
     * @route /timeperiod/[i:id]/include
     */
    public function includedTimeperiodAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $includedTimeperiodList = Timeperiodincluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_timeperiods_include_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($includedTimeperiodList as $includedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $includedTimeperiod['tp_id'],
                "text" => $includedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
    
    /**
     * 
     * @method get
     * @route /timeperiod/[i:id]/exclude
     */
    public function excludedTimeperiodAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $excludedTimeperiodList = Timeperiodexcluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_timeperiods_exclude_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($excludedTimeperiodList as $excludedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $excludedTimeperiod['tp_id'],
                "text" => $excludedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
    
    
    /**
     * Update a timeperiod
     *
     * @method post
     * @route /timeperiod/update
     */
    public function updateAction()
    {
        $params = $this->getParams('post')->all();
        $router = Di::getDefault()->get('router');

        /* Save information */
        try {
            TimePeriodRepository::update($params, 'form', $this->getUri());
        } catch (\Exception $e) {
            return $router->response()->json(array('success' => false, 'error' => $e->getMessage()));
        }

        return $router->response()->json(array('success' => true));
    }
    

    /**
     * Create a new timeperiod
     *
     * @method post
     * @route /timeperiod/add
     */
    public function createAction()
    {
        $params = $this->getParams('post');
        $router = Di::getDefault()->get('router');
         
        $params['object'] = static::$objectName;
        try {
            TimePeriodRepository::create($params, 'wizard', $this->getUri());
        } catch (\Exception $e) {
            return $router->response()->json(array('success' => false, 'error' => $e->getMessage()));
        }
        return $router->response()->json(array('success' => true));

    }
    
    
    
}
