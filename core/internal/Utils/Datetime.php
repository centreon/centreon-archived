<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
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
    public static function humanReadable($diff, $precisionType = 1, $precision=1)
    {
        /* List of format in Date interval */
        $listFormat = array(
            'y' => array('year', 'years'),
            'm' => array('month', 'months'),
            'd' => array('day', 'days'),
            'h' => array('hour', 'hours'),
            'i' => array('minute', 'minutes'),
            's' => array('second', 'seconds')
        );
        if (version_compare(phpversion(), '5.4.0', '<')) {
            $dateInterval = new DateInterval($diff);
        } else {
            $dateInterval = new \DateInterval('PT' . $diff . 'S');
        }
        $formatedStr = '';
        $newFormatedStr = '';
        $count = 0;
        /* Prepare string */
        foreach ($listFormat as $format => $words) {
            if ($dateInterval->$format > 0) {
                if (strlen($newFormatedStr) > 0) {
                    $newFormatedStr .= ' ';
                }
                $newFormatedStr .= $dateInterval->$format . ' ' . ngettext($words[0], $words[1], $dateInterval->$format);
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

     * @todo handle locales
     */
    public static function format($timestamp)
    {
        $format = 'Y-m-d H:i:s';
        $datetime = date($format, $timestamp);
        return $datetime;
    }
}
