<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

class CentreonDuration
{
    public static function toString($duration, $periods = null)
    {
        if (!is_array($duration)) {
            $duration = CentreonDuration::int2array($duration, $periods);
        }
        return CentreonDuration::array2string($duration);
    }
 
    public static function int2array($seconds, $periods = null)
    {
        // Define time periods
        if (!is_array($periods)) {
            $periods = array (
                    'y'     => 31556926,
                    'M' => 2629743,
                    'w' => 604800,
                    'd' => 86400,
                    'h' => 3600,
                    'm' => 60,
                    's' => 1
                    );
        }
 
        // Loop
        $seconds = (int) $seconds;
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);
 
            if ($count == 0) {
                continue;
            }
 
            $values[$period] = $count;
            $seconds = $seconds % $value;
        }
 
        // Return
        if (empty($values)) {
            $values = null;
        }
        return $values;
    }
 
    public static function array2string($duration)
    {
        if (!is_array($duration)) {
            return false;
        }

        $i = 0;
        foreach ($duration as $key => $value) {
            if ($i < 2) {
                $segment = $value . '' . $key;
                $array[] = $segment;
                $i++;
            }
        }
        $str = implode(' ', $array);
        return $str;
    }
}

class DurationHoursMinutes
{
    public static function toString($duration, $periods = null)
    {
        if (!is_array($duration)) {
            $duration = DurationHoursMinutes::int2array($duration, $periods);
        }
        return DurationHoursMinutes::array2string($duration);
    }
 
    public static function int2array($seconds, $periods = null)
    {
        // Define time periods
        if (!is_array($periods)) {
            $periods = array (
                    'h' => 3600,
                    'm' => 60,
                    's' => 1
                    );
        }
 
        // Loop
        $seconds = (int) $seconds;
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);
            if ($count == 0) {
                continue;
            }
 
            $values[$period] = $count;
            $seconds = $seconds % $value;
        }
 
        // Return
        if (empty($values)) {
            $values = null;
        }
 
        return $values;
    }
 
    public static function array2string($duration)
    {
        if (!is_array($duration)) {
            return false;
        }

        foreach ($duration as $key => $value) {
            $array[] = $value."".$key;
        }
        unset($segment);
        $str = implode(' ', $array);
        return $str;
    }
}
