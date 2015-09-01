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
 * Convert a value in human readable
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 */
class HumanReadable
{
    public static $units = array(
        'o' => array(
            'smaller' => false,
            'divider' => 1024,
            'units' => array('o','ko','Mo','Go','To','Po','Eo','Zo','Yo')
        ),
        'B' => array(
            'smaller' => false,
            'divider' => 1024,
            'units' => array('B','kB','MB','GB','TB','PB','EB','ZB','YB')
        ),
        'b' => array(
            'smaller' => false,
            'divider' => 1000,
            'units' => array('b','kb','Mb','Gb','Tb','Pb','Eb','Zb','Yb')
        ),
        'o/s' => array(
            'smaller' => false,
            'divider' => 1024,
            'units' => array('o','ko','Mo','Go','To','Po','Eo','Zo','Yo')
        ),
        'B/s' => array(
            'smaller' => false,
            'divider' => 1024,
            'units' => array('B','kB','MB','GB','TB','PB','EB','ZB','YB')
        ),
        'b/s' => array(
            'smaller' => false,
            'divider' => 1000,
            'units' => array('b','kb','Mb','Gb','Tb','Pb','Eb','Zb','Yb')
        ),
        's' => array(
            'smaller' => true,
            'function' => "seconds"
        )
    );

    /**
     * Convert a value for human readable
     *
     * @param int $values The array of values (float or int)
     * @param string $unit The string of the unit
     * @param string $newUnit The new unit string
     * @param int $decimal The number of decimal for the result
     * @param bool $toString If return string (can be complex)
     * @return int|string
     */
    public static function convert($value, $unit, &$newUnit, $decimal = null, $toString = false)
    {
        $retValues = self::convertArray(array($value), $unit, $newUnit, $decimal, $toString);
        return $retValues[0];
    }

    /**
     * Convert a array for human readable
     *
     * @param array $values The array of values (float or int)
     * @param string $unit The string of the unit
     * @param string $newUnit The new unit string
     * @param int $decimal The number of decimal for the result
     * @param bool $toString If return string (can be complex)
     * @return array
     */
    public static function convertArray($values, $unit, &$newUnit, $decimal = null, $toString = false)
    {
        if (false === in_array($unit, array_keys(self::$units))) {
            return $values;
        }
        /* Getting the factor */
        $factor = self::getFactor($values);
        if (false === $factor) {
            // @todo
        } else {
            if (isset(self::$units[$unit]['units'])) {
                $newUnit = self::$units[$unit]['units'][$factor];
            }
            return self::convertArrayWithFactor($values, $unit, $factor, $decimal, $toString);
        }
    }

    /**
     * Convert a array for human readable with a factor
     *
     * @param array $values The array of values (float or int)
     * @param string $unit The string of the unit
     * @param int $factor The factor for divide
     * @param int $decimal The number of decimal for the result
     * @param bool $toString If return string (can be complex)
     * @return array
     */
    public static function convertArrayWithFactor($values, $unit, $factor, $decimal = null, $toString = false)
    {
        if (false === in_array($unit, array_keys(self::$units)) && is_null($decimal)) {
            return $values;
        } elseif (false === in_array($unit, array_keys(self::$units))) {
            return array_map(
                function ($value) use ($decimal) {
                    if (is_null($value)) {
                        return $value;
                    }
                    return sprintf("%.{$decimal}f", $value);
                },
                $values
            );
        }
        if (isset(self::$units[$unit]['divider'])) {
            $divider = self::$units[$unit]['divider'];
            return array_map(
                function ($value) use ($factor, $decimal, $divider) {
                    if (is_null($value)) {
                        return $value;
                    }
                    if (is_null($decimal)) {
                        return $value / pow($divider, $factor);
                    } else {
                        return sprintf("%.{$decimal}f", $value / pow($divider, $factor));
                    }
                },
                $values
            );
        } elseif (isset(self::$units[$unit]['function'])) {
            $func = self::$units[$unit]['function'];
            try {
                return array_map(
                    function ($value) use ($func, $decimal, $toString) {
                        return HumanReadable::$func($value, $decimal, $toString);
                    },
                    $values
                );
            } catch (\Exception $e) {
                return self::convertArrayWithFactor($values, null, $factor, $decimal);
            }
        }
    }

    /**
     * Return the factor for convert
     *
     * @param array $values The array of values (float or int)
     * @return int|bool The factor or false if the max is < 0
     */
    public static function getFactor($values)
    {
        $max = intval(max($values));
        if ($max > 0) {
            return floor((strlen($max) - 1) / 3);
        }
        return false;
    }

    /**
     * Convert seconds to human readable
     *
     * @param int $value The value to convert
     * @param int $decimal The number of decimal for the result
     * @param bool $toString If return string (can be complex)
     * @return int|string
     */
    public static function seconds($value, $decimal = null, $toString = false)
    {
        $units = array(
            "w" => 7 * 24 * 3600,
            "d" => 24 * 3600,
            "h" => 3600,
            "m" => 60,
            "s" => 1
        );
        $retValue = null;
        $retArray = array();
        foreach ($units as $unitName => $divider) {
            if ($quot = intval($value / $divider)) {
                if (is_null($retValue)) {
                    $retValue = $quot;
                }
                $retArray[] = $quot . $unitName;
                $value -= $quot * $divider;
            }
        }
        if ($toString) {
            return join(" ", $retArray);
        }
        return $retValue;
    }
}
