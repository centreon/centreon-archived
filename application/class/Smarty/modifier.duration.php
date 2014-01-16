<?php
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
