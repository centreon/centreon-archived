<?php
/*
 * Copyright 2005-2013 Centreon
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

if (isset($pearDB)) {
    /*
     * Host severity
     */
    $sql = "SELECT c.name, c.level, c.comments, c.icon_id, crr.host_id
        FROM criticality c, criticality_resource_relations crr
        WHERE c.criticality_id = crr.criticality_id
        AND crr.service_id IS NULL";
    $res = $pearDB->query($sql);
    $hc = array();
    while ($row = $res->fetchRow()) {
        if (!isset($hc[$row['name']])) {
            $pearDB->query("INSERT INTO hostcategories (hc_name, hc_alias, level, icon_id, hc_comment) VALUES (
                    '".$pearDB->escape($row['name'])."',
                    '".$pearDB->escape($row['name'])."',
                    '".$pearDB->escape($row['level'])."',
                    '".$pearDB->escape($row['icon_id'])."',
                    '".$pearDB->escape($row['comments'])."'
                )");
            $res2 = $pearDB->query("SELECT MAX(hc_id) as last_id FROM hostcategories WHERE hc_name = '".$pearDB->escape($row['name'])."'");
            $row2 = $res2->fetchRow();
            $hc[$row['name']] = $row2['last_id'];
        }
        $pearDB->query("INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES (
            {$hc[$row['name']]}, {$row['host_id']}
            )");
    }
    
    /*
     * Service severity
     */
    $sql = "SELECT c.name, c.level, c.comments, c.icon_id, crr.service_id
        FROM criticality c, criticality_resource_relations crr
        WHERE c.criticality_id = crr.criticality_id
        AND crr.service_id IS NOT NULL";
    $res = $pearDB->query($sql);
    $sc = array();
    while ($row = $res->fetchRow()) {
        if (!isset($sc[$row['name']])) {
            $pearDB->query("INSERT INTO service_categories (sc_name, sc_description, level, icon_id, sc_activate) VALUES (
                    '".$pearDB->escape($row['name'])."',
                    '".$pearDB->escape($row['name'])."',
                    '".$pearDB->escape($row['level'])."',
                    '".$pearDB->escape($row['icon_id'])."',
                    '1'
                )");
            $res2 = $pearDB->query("SELECT MAX(sc_id) as last_id FROM service_categories WHERE sc_name = '".$pearDB->escape($row['name'])."'");
            $row2 = $res2->fetchRow();
            $sc[$row['name']] = $row2['last_id'];
        }
        $pearDB->query("INSERT INTO service_categories_relation (sc_id, service_service_id) VALUES (
            {$sc[$row['name']]}, {$row['service_id']}
            )");
    }
    
    $pearDB->query("DROP TABLE criticality_resource_relations");
    $pearDB->query("DROP TABLE criticality");
    
    /**
     * conf.pm
     */
    
    $patterns = array('/--ADDRESS--/',
                  '/--DBUSER--/',
                  '/--DBPASS--/',
                  '/--CONFDB--/',
                  '/--STORAGEDB--/',
                  '/--CENTREONDIR--/',
                  '/--DBPORT--/', 
                  '/--INSTANCEMODE--/', 
                  '/--CENTREON_VARLIB--/');

    $replacements = array($conf_centreon['hostCentreon'],
                      $conf_centreon['user'],
                      $conf_centreon['password'],
                      $conf_centreon['db'],
                      $conf_centreon['dbcstg'],
                      $centreon_path,
                      isset($conf_centreon['port']) ? $conf_centreon['port'] : 3306, 
                      "central", 
                      "@CENTREON_VARLIB@");
    
    $centreonConfPmFile = '@CENTREON_ETC@/conf.pm';
    $contents = file_get_contents('../../var/configFilePmTemplate');
    $contents = preg_replace($patterns, $replacements, $contents);
    file_put_contents($centreonConfPmFile, $contents);
    //@unlink('@CENTREON_ETC@/conf.pm');
}
?>