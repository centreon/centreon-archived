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

#4278
if (isset($pearDB)) {
    $host_meta_id = '0';
    $querySelect = "SELECT host_id FROM host WHERE host_name = '_Module_Meta';";
    $res = $pearDB->query($querySelect);
    while ($row = $res->fetchRow()) {
        $host_meta_id = $row['host_id'];
    }

    $tab_service_meta_id;
    $querySelect = "SELECT service_id FROM service WHERE service_description LIKE '%meta_%' AND service_register = '2';";
    $res = $pearDB->query($querySelect);
    while ($row = $res->fetchRow()) {
        $tab_service_meta_id[$row['service_id']] = 1;
    }

    $tab_host_service_meta_id_relation;
    $querySelect = "SELECT service_service_id "
        . "FROM host_service_relation "
        . "WHERE service_service_id IN ("
        . "SELECT service_id FROM service "
        . "WHERE service_description LIKE '%meta_%' AND service_register = '2'"
        . ");";
    $res = $pearDB->query($querySelect);
    while ($row = $res->fetchRow()) {
        $tab_host_service_meta_id_relation[$row['service_service_id']] = 1;
    }

    $queryInsert = "";
    foreach ($tab_service_meta_id as $key => $value) {
        if (!isset($tab_host_service_meta_id_relation[$key])) {
            $queryInsert .= "('".$host_meta_id."','".$key."'),";
        }
    }
    if ($queryInsert != "") {
        $queryInsert = "INSERT INTO host_service_relation (host_host_id, service_service_id) VALUES " . $queryInsert;
        $queryInsert = preg_replace('/,$/', ';', $queryInsert);
        $pearDB->query($queryInsert);
    }
}

?>
