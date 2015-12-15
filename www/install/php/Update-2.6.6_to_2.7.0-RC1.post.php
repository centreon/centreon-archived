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

# Make broker configuration easier
if (isset($pearDB)) {

    # Fill retention path
    $query1 = "SELECT config_id
        FROM cfg_centreonbroker";
    $res1 = $pearDB->query($query1);
    while ($row1 = $res1->fetchRow()) {
        $retention_path = '/var/lib/centreon-broker/';
        $query2 = "SELECT config_value
            FROM cfg_centreonbroker_info
            WHERE config_key = 'path'
            AND config_id = " . $pearDB->escape($row1['config_id']) . "
            ORDER BY config_group DESC";
        $res2 = $pearDB->query($query2);
        while ($row2 = $res2->fetchRow()) {
            if (trim($row2['config_value']) != '') {
                $retention_path = dirname(trim($row2['config_value']));
                break;
            }
        }
        $query3 = "UPDATE cfg_centreonbroker
            SET retention_path = '" . $pearDB->escape($retention_path). "'
            WHERE config_id = " . $pearDB->escape($row1['config_id']);
        $pearDB->query($query3);
    }

    # Delete old failover output
    $query = "SELECT config_id,config_group,config_group_id FROM
                (SELECT cbi2.config_id,cbi2.config_group,cbi2.config_group_id
                FROM cfg_centreonbroker_info cbi1, cfg_centreonbroker_info cbi2, cfg_centreonbroker_info cbi3
                WHERE cbi1.config_id = cbi2.config_id and cbi1.config_group = cbi2.config_group
                AND cbi2.config_id = cbi3.config_id AND cbi2.config_group = cbi3.config_group AND cbi2.config_group_id = cbi3.config_group_id
                AND cbi1.config_group='output'
                AND cbi2.config_group='output'
                AND cbi3.config_group='output'
                AND cbi1.config_key='failover'
                AND cbi2.config_key='name'
                AND cbi1.config_value = cbi2.config_value
                AND cbi3.config_key='type'
                AND cbi3.config_value='file'
                ) as q";
    $result = $pearDB->query($query);
    while ($row = $result->fetchRow()) {
        if (!is_null($row['config_id']) && !is_null($row['config_group']) && !is_null($row['config_group_id'])) {
            $query = "DELETE FROM cfg_centreonbroker_info
                      WHERE config_id = '" . $row['config_id'] . "'
                      AND config_group = '" . $row['config_group'] . "'
                      AND config_group_id = '" . $row['config_group_id'] . "'";
            $pearDB->query($query);
        }
    }

    # Delete failover names which join to non existing failover
    $query ="UPDATE cfg_centreonbroker_info
        SET config_value=''
        WHERE config_key = 'failover'
        AND config_value NOT IN
            (SELECT config_value FROM
                (SELECT config_value
                FROM cfg_centreonbroker_info
                WHERE config_key = 'name'
                ) as q
            )";
    $pearDB->query($query);

    # Enable correlation if it was configured
    $query = "UPDATE cfg_centreonbroker
        SET correlation_activate='1'
        WHERE config_id IN
            (SELECT distinct config_id
            FROM cfg_centreonbroker_info
            WHERE config_group='correlation'
            )";
    $pearDB->query($query);

    # Delete correlation, stats and temporary configuration if it was configured
    $query = "DELETE FROM cfg_centreonbroker_info
        WHERE config_group='correlation'
        OR config_group='stats'
        OR config_group='temporary'";
     $pearDB->query($query);

    # Delete correlation, stats and temporary tabs
    $query = "DELETE FROM cb_tag
        WHERE tagname='correlation'
        OR tagname='stats'
        OR tagname='temporary'";
    $pearDB->query($query);

    # Delete correlation, stats and temporary parameters
    $query = "DELETE FROM cb_module
        WHERE name='correlation'
        OR name='stats'
        OR name='temporary'";
    $pearDB->query($query);
}
?>
