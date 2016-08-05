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

if (!isset($centreon)) {
    exit();
}

function deleteDowntimeFromDb($centreon, $select = array())
{
    if (isset($select)) {
        $pearDBO = new CentreonDB("centstorage");

        $dIds =  array();
        foreach ($select as $key => $val) {
            $tmp = explode(";", $key);
            if (isset($tmp[1])) {
                $dIds[] = $tmp[1];
            }
        }

        if (count($dIds)) {
            $request = "DELETE FROM downtimes WHERE internal_id IN (".implode(', ', $dIds).")";
            $pearDBO->query($request);
        }
    }
}

function getDowntimes($internalId)
{
    $pearDBO = new CentreonDB("centstorage");
    $request = "Select host_id,service_id from downtimes WHERE internal_id = ".CentreonDB::escape($internalId)." ORDER BY downtime_id DESC LIMIT 0,1";
    $res = $pearDBO->query($request);
    $row = $res->fetchRow();
    if (!empty($row)) {
        return $row;
    }
    return false;
}

function isDownTimeHost($internalId)
{
    $downtime = getDowntimes($internalId);
    if (!empty($downtime['host_id']) && !empty($downtime['service_id'])) {
        return false;
    }
    return true;
}
