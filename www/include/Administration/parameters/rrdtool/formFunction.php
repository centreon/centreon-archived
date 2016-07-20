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


/**
 * Get the version of rrdtool
 *
 * @param string $rrdtoolBin The full path of rrdtool
 * @return string
 */
function getRrdtoolVersion($rrdtoolBin = null)
{
    if (is_null($rrdtoolBin) || !is_executable($rrdtoolBin)) {
        return '';
    }
    $output = array();
    $retval = 0;
    @exec($rrdtoolBin, $output, $retval);
    if ($retval != 0) {
        return '';
    }
    $ret = preg_match('/^RRDtool ((\d\.?)+).*$/', $output[0], $matches);
    if ($ret === false || $ret === 0) {
        return '';
    }
    return $matches[1];
}

/**
 * Validate if only one rrdcached options is set
 *
 * @param array $values rrdcached_port and rrdcached_unix_path
 * @return bool
 */
function rrdcached_valid($values)
{
    if (trim($values[0]) != '' && trim($values[1]) != '') {
        return false;
    }
    return true;
}

function rrdcached_has_option($values)
{
    if (isset($values[0]['rrdcached_enable']) && $values[0]['rrdcached_enable'] == 1) {
        if (trim($values[1]) == '' && trim($values[2]) == '') {
            return false;
        }
    }
    return true;
}
