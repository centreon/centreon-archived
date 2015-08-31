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
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.duration.php
 * Type:     modifier
 * Name:     duration
 * Purpose:  format a timestamp to the centreon format for duration
 * -------------------------------------------------------------
 */
function smarty_modifier_duration($timestamp) {
    $periods = array (
        'y'	=> 31556926,
        'M' => 2629743,
        'w' => 604800,
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
        's' => 1
    );
    
    // Loop
    $timestamp = (int) $timestamp;
    foreach ($periods as $period => $value) {
        $count = floor($timestamp / $value);

        if ($count == 0) {
            continue;
        }

        $values[$period] = $count;
        $timestamp = $timestamp % $value;
    }
    
    foreach ($values as $key => $value) {
        $segment = $value . '' . $key;
        $array[] = $segment;
    }
    $str = implode(' ', $array);
    return $str;
}
