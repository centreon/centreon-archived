<?php
/*
 * Copyright 2005-2011 MERETHIS
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
    $query = "SELECT `key`, `value` FROM `options` WHERE `key` LIKE 'ldap_%'";
    $res = $pearDB->query($query);
    if (PEAR::isError($res)) {
        return false;
    }
    $queries = array();
    $insertLdap = false;
    while ($row = $res->fetchRow()) {
        switch ($row['key']) {
            case 'ldap_host':
                if ($row['value'] != null && $row['value'] != '') {
                    $insertLdap = true;
                    array_unshift($queries, "INSERT INTO auth_ressource (ar_id, ar_type, ar_enable, ar_order) VALUES (2, 'ldap_tmpl', '0', 0)");
                    array_unshift($queries, "INSERT INTO auth_ressource (ar_id, ar_type, ar_enable, ar_order) VALUES (1, 'ldap', '1', 1)");
                    $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (1, 'host', '" . $row['value'] . "')";
                }
                break;
            case 'ldap_port':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (1, 'port', '" . $row['value'] . "')";
                break;
            case 'ldap_base_dn':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_base_search', '" . $row['value'] . "')";
                break;
            case 'ldap_login_attrib':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'alias', '" . $row['value'] . "')";
                break;
            case 'ldap_ssl':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (1, 'use_ssl', '" . $row['value'] . "')";
                break;
            case 'ldap_search_user':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'bind_dn', '" . $row['value'] . "')";
                break;
            case 'ldap_search_user_pwd':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'bind_pass', '" . $row['value'] . "')";
                break;
            case 'ldap_search_filter':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_base_search', '" . $row['value'] . "')";
                break;
            case 'ldap_protocol_version':
                $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (1, 'protocol_version', '" . $row['value'] . "')";
                break;
        }
    }
    if ($insertLdap) {
        $errors = false;
        /* New values */
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (1, 'use_tls', '0')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_group', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_name', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_email', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_pager', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_firstname', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'user_lastname', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'group_filter', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'group_base_search', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'group_name', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'group_member', '')";
        $queries[] = "INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) VALUES (2, 'tmpl', '0')";
        
        
        
        foreach ($queries as $query) {
            if (PEAR::isError($pearDB->query($query))) {
                $errors = true;
            }
        }
        
    }
    /* Delete old values */
    $query = "DELETE FROM `options` WHERE `key` IN ('ldap_host', 'ldap_port', 'ldap_base_dn', 'ldap_login_attrib', 'ldap_ssl', 'ldap_search_user', 'ldap_search_user_pwd', 'ldap_search_filter', 'ldap_protocol_version')";
    if (PEAR::isError($pearDB->query($query))) {
        return false;
    }
    if ($errors) {
        return false;
    }
}
?>