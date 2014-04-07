<?php
/*
 * Copyright 2005-2012 MERETHIS
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

if (isset($pearDB)) {
    $pearDB->query("DELETE FROM `auth_ressource` WHERE `ar_id` NOT IN (SELECT DISTINCT ar_id FROM auth_ressource_info)");
    $res = $pearDB->query("SELECT `value` FROM  `options` WHERE `key` = 'ldap_auth_enable'");
    $row = $res->fetchRow();
    // LDAP is enabled, we can proceed with the migration
    if ($row['value']) {
        $sql = "SELECT `key`, `value` FROM `options` WHERE `key` LIKE 'ldap\_%' AND `key` <> 'ldap_auth_enable'";
        $res = $pearDB->query($sql);
        $data = array();
        while ($row = $res->fetchRow()) {
            $data[$row['key']] = $row['value'];
        }
        $res = $pearDB->query("SELECT ari.ari_name, ari.ari_value, ari.ar_id, ar.ar_enable
                               FROM auth_ressource ar, auth_ressource_info ari 
                               WHERE ar.ar_id = ari.ar_id
                               ORDER BY ar.ar_id");
        $hostData = array();
        $templateData = array();
        while ($row = $res->fetchRow()) {
            if ($row['ar_enable'] == 0) {
                $templateData[$row['ari_name']] = $row['ari_value'];
            } else {
                if (!isset($hostData[$row['ar_id']])) {
                    $hostData[$row['ar_id']] = array();
                }
                $hostData[$row['ar_id']][$row['ari_name']] = $row['ari_value'];
            }
        }
        $i = 0;
        foreach ($hostData as $arId => $hData) {
            if (isset($hData['host'])) {
                $i++;
                $sql = "INSERT INTO auth_ressource_host (auth_ressource_id, host_address, host_port, use_ssl, use_tls, host_order)
                            VALUES (".$arId.", '".$hData['host']."', '".$hData['port']."', '".$hData['use_ssl']."', '".$hData['use_tls']."', $i)";
                $pearDB->query($sql);
                $pearDB->query("DELETE FROM auth_ressource_info WHERE `ari_name` IN ('host', 'port', 'use_ssl', 'use_tls') AND ar_id = $arId");
                foreach ($data as $k => $v) {
                    $pearDB->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) 
                                    VALUES (".$arId.", '".$k."', '".$v."')");
                }
                foreach ($templateData as $k => $v) {
                    $pearDB->query("INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) 
                                    VALUES (".$arId.", '".$k."', '".$v."')");
                }
            }
        }
        $pearDB->query("UPDATE auth_ressource 
                        SET ar_name = 'Default configuration', ar_description = 'Default configuration'
                        WHERE ar_enable = '1'");
        $pearDB->query("DELETE FROM `options` WHERE `key` LIKE 'ldap\_%' AND `key` <> 'ldap_auth_enable' AND `key` <> 'ldap_last_acl_update'");
        $pearDB->query("DELETE FROM `auth_ressource` WHERE `ar_enable` = '0'");
    }
    
    /*
     * Checks if enable_perfdata_sync exists
     */
    $res = $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'enable_perfdata_sync'");
    if (!$res->numRows()) {
        $pearDB->query("INSERT INTO `options` (`key`, `value`) VALUES ('enable_perfdata_sync', '1')");
    }
    unset($res);
    
    /*
     * Checks if enable_logs_sync exists
     */
    $res = $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'enable_logs_sync'");
    if (!$res->numRows()) {
        $pearDB->query("INSERT INTO `options` (`key`, `value`) VALUES ('enable_logs_sync', '1')");
    }
    unset($res);
}
?>