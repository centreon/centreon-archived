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
 */

namespace CentreonBam\Listeners\CentreonMain;

use CentreonMain\Events\Status as StatusEvent;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Datetime;


/**
 * Event to top counter for host and service
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonMain
 */
class Status
{
    /**
     * Execute the event
     *
     * @param \CentreonMain\Events\Status $event The event object
     */
    public static function execute(StatusEvent $event)
    {
        /*$bas = \CentreonBam\Repository\BusinessActivityRepository::getList(        
            '*',
            -1,
            0,
            null,
            'asc',
            array('current_status' => '1', 'current_status' => '2'),
            "OR"
            );
        $baList = array();
        $baList['disrupted']['nb_ba'] = 0;
        $baList['unavailable']['nb_ba'] = 0;
        foreach($bas as $ba){
            if($ba['current_status'] === '1'){
                $baList['disrupted']['nb_ba']++;
                $baTemp = $ba;
                $baList['disrupted']['objects'][] = $baTemp;
            }else if($ba['current_status'] === '2'){
                $baList['unavailable']['nb_ba']++;
                $baTemp = $ba;
                $baList['unavailable']['objects'][] = $baTemp;
            }
        }
        
        $states = $event->getStatus('states');
        if(empty($states)){
            $states = array();
        }
        $states['bam-objects'] = $baList;
        $event->addStatus('states', $states);*/
    }
}
