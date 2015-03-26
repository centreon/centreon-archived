<?php
/*
 * Copyright 2005-2014 CENTREON
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
        's' => array(
            'smaller' => true,
            'function' => "self::seconds"
        )
    );

    /**
     * Convert a array for human readable
     *
     * @param array $values The array of values (float or int)
     * @param string $unit The string of the unit
     * @param string $newUnit The new unit string
     * @param int $decimal The number of decimal for the result
     * @return array
     */
    public static function convertArray($values, $unit, &$newUnit, $decimal = null)
    {
        if (false === in_array($unit, array_keys(self::$units))) {
            return $values;
        }
        /* Getting the factor */
        $factor = self::getFactor($values, $decimal);
        if (false === $factor) {
            // @todo
        } else {
            $newUnit = self::$units[$unit]['units'][$factor];
            return self::convertArrayWithFactor($values, $unit, $factor, $decimal);
        }
    }

    /**
     * Convert a array for human readable with a factor
     *
     * @param array $values The array of values (float or int)
     * @param string $unit The string of the unit
     * @param int $factor The factor for divide
     * @param int $decimal The number of decimal for the result
     * @return array
     */
    public static function convertArrayWithFactor($values, $unit, $factor, $decimal = null)
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
}
