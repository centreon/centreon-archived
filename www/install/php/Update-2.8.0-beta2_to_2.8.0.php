<?php
/*
 * Copyright 2005-2016 Centreon
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
 *
 */

require_once _CENTREON_PATH_ . '/www/class/centreonMeta.class.php';

if (isset($pearDB)) {
    $metaObj = new CentreonMeta($pearDB);
    $hostId = null;
    $virtualServices = array();

    /* Check virtual host */
    $queryHost = 'SELECT host_id '
        . 'FROM host '
        . 'WHERE host_register = "2" '
        . 'AND host_name = "_Module_Meta" ';
    $res = $pearDB->query($queryHost);
    if ($res->numRows()) {
        $row = $res->fetchRow();
        $hostId = $row['host_id'];
    } else {
        $query = 'INSERT INTO host (host_name, host_register) '
            . 'VALUES ("_Module_Meta", "2") ';
        $pearDB->query($query);
        $res = $pearDB->query($queryHost);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            $hostId = $row['host_id'];
        }
    }

    /* Check existing virtual services */
    $query = 'SELECT service_id, service_description '
        . 'FROM service '
        . 'WHERE service_description LIKE "meta_%" '
        . 'AND service_register = "2" ';
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        if (preg_match('/meta_(\d+)/', $row['service_description'], $matches)) {
            $metaId = $matches[1];
            $virtualServices[$matches[1]]['service_id'] = $row['service_id'];
        }
    }

    /* Check existing relations between virtual services and virtual host */
    $query = 'SELECT s.service_id, s.service_description '
        . 'FROM service s, host_service_relation hsr '
        . 'WHERE hsr.host_host_id = "' . $hostId . '" '
        . 'AND s.service_register = "2" '
        . 'AND s.service_description LIKE "meta_%" ';
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        if (preg_match('/meta_(\d+)/', $row['service_description'], $matches)) {
            $metaId = $matches[1];
            $virtualServices[$matches[1]]['relation'] = true;
        }
    }

    $query = 'SELECT meta_id, meta_name '
        . 'FROM meta_service ';
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        if (!isset($virtualServices[$row['meta_id']]) || !isset($virtualServices[$row['meta_id']]['service_id'])) {
            $serviceId = $metaObj->insertVirtualService($row['meta_id'], $row['meta_name']);
        } else {
            $serviceId = $virtualServices[$row['meta_id']]['service_id'];
        }
        if (!isset($virtualServices[$row['meta_id']]) || !isset($virtualServices[$row['meta_id']]['relation'])) {
            $query = 'INSERT INTO host_service_relation (host_host_id, service_service_id) '
                . 'VALUES (' . $hostId . ',' . $serviceId . ') ';
            $pearDB->query($query);
        }
    }
}

?>
