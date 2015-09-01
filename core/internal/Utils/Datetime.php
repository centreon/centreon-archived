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

namespace Centreon\Internal\Utils;

/**
 * Class for funtions to manipulate date and time
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 */
class Datetime
{
    const PRECISION_FULL = 1;
    const PRECISION_FORMAT = 2;
    const PRECISION_CHAR = 3;

    /**
     * Convert a time in seconds to human readable string
     *
     * @param int $diff The time in seconds
     * @param int $precisionType The precision type for the string
     * @param int $precision The precision
     *                       The number of element if type is by format
     *                       The number of character if type is by characters
     * @return string
     */
    public static function humanReadable($diff, $precisionType = 1, $precision = 1)
    {
        /* List of format in Date interval */
        $listFormat = array(
            'y' => array('y', 'y'),
            'm' => array('mo', 'mo'),
            'd' => array('d', 'd'),
            'h' => array('h', 'h'),
            'i' => array('m', 'm'),
            's' => array('s', 's')
        );
        $dateInterval = new DateInterval($diff);
        $formatedStr = '';
        $newFormatedStr = '';
        $count = 0;
        /* Prepare string */
        foreach ($listFormat as $format => $words) {
            if ($dateInterval->$format > 0) {
                if (strlen($newFormatedStr) > 0) {
                    $newFormatedStr .= ' ';
                }
                $newFormatedStr .= $dateInterval->$format
                    . ' '
                    . ngettext($words[0], $words[1], $dateInterval->$format);
                $count++;
            }
            /* Test for precision type format */
            if ($precisionType == self::PRECISION_FORMAT && $count >= $precision) {
                return $newFormatedStr;
            }
            /* Test for precision type character */
            if ($precisionType == self::PRECISION_CHAR && strlen($newFormatedStr) >= $precision) {
                if ($formatedStr === '') {
                    return $newFormatedStr;
                }
                return $formatedStr;
            }
            $formatedStr = $newFormatedStr;
        }
        return $formatedStr;
    }

    /**
     * Convert timestamp into human readable date time
     *
     * @param int $timestamp
     * @return string
     * @todo handle locales
     */
    public static function format($timestamp)
    {
        $format = 'Y-m-d H:i:s';
        $datetime = date($format, $timestamp);
        return $datetime;
    }
}
