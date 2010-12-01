<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 *
 *
 */

/**
 * Replaces characters such as #S# #BS# etc...
 */
function upgradeReplaceSpecialChars($str)
{
    $newStr = str_replace("#S#", "/", $str);
    $newStr = str_replace("#BS#", "\\", $newStr);

    return CentreonDB::escape($newStr);
}

/**
 * Centreon database
 */
if (isset($pearDB)) {
    /**
     * Decodes Host names and host alias
     */
    $query = "SELECT host_id, host_alias, host_name FROM host";
    $res = $pearDB->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE host SET host_name = '".upgradeReplaceSpecialChars(html_entity_decode($rows['host_name']))."',
        		   host_alias = '" . upgradeReplaceSpecialChars(html_entity_decode($rows['host_alias'])) . "' WHERE host_id = '".$rows['host_id']."'";
        $pearDB->query($query2);
    }

    /**
     * Decodes Service description and service alias
     */
    $query = "SELECT service_id, service_description, service_alias FROM service";
    $res = $pearDB->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE service SET service_description = '".upgradeReplaceSpecialChars(html_entity_decode($rows['service_description']))."',
        		   service_alias = '" . upgradeReplaceSpecialChars(html_entity_decode($rows['service_alias'])) . "' WHERE service_id = '".$rows['service_id']."'";
        $pearDB->query($query2);
    }

    /**
     * Decodes Hostgroup names and hostgroup alias
     */
    $query = "SELECT hg_id, hg_alias, hg_name FROM hostgroup";
    $res = $pearDB->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE hostgroup SET hg_name = '".upgradeReplaceSpecialChars(html_entity_decode($rows['hg_name']))."',
        		   hg_alias = '" . upgradeReplaceSpecialChars(html_entity_decode($rows['hg_alias'])) . "' WHERE hg_id = '".$rows['hg_id']."'";
        $pearDB->query($query2);
    }

    /**
     * Decodes Host custom macros
     */
    $query = "SELECT host_macro_id, host_macro_value FROM on_demand_macro_host";
    $res = $pearDB->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE on_demand_macro_host
        		   SET host_macro_value = '".upgradeReplaceSpecialChars(html_entity_decode($rows['host_macro_value']))."'
        		   WHERE host_macro_id = '".$rows['host_macro_id']."'";
        $pearDB->query($query2);
    }

    /**
     * Decodes Service custom macros
     */
    $query = "SELECT svc_macro_id, svc_macro_value FROM on_demand_macro_service";
    $res = $pearDB->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE on_demand_macro_service
        		   SET svc_macro_value = '".upgradeReplaceSpecialChars(html_entity_decode($rows['svc_macro_value']))."'
        		   WHERE svc_macro_id = '".$rows['svc_macro_id']."'";
        $pearDB->query($query2);
    }
}

/**
 * Centstorage database
 */
if (isset($pearDBO)) {
    /**
     * Decodes index data table entries
     */
    $query = "SELECT id, host_name, service_description FROM index_data";
    $res = $pearDBO->query($query);
    while ($rows = $res->fetchRow()) {
        $query2 = "UPDATE index_data SET host_name = '" . upgradeReplaceSpecialChars(html_entity_decode($rows['host_name'])) .  "',
        		   service_description = '". upgradeReplaceSpecialChars(html_entity_decode($rows['service_description'])) ."' WHERE id = '".$rows['id']."'";
        $pearDBO->query($query2);
    }
}
?>