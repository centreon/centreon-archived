<?php

/*
 * Copyright 2005-2018 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

/*
 * Generate random key for application key
 */
$uniqueKey = md5(uniqid(rand(), true));
$query = "INSERT INTO `informations` (`key`,`value`) VALUES ('appKey', '$uniqueKey')";
$pearDB->query($query);
$query = "INSERT INTO `informations` (`key`,`value`) VALUES ('isRemote', 'no')";
$pearDB->query($query);
$query = "INSERT INTO `informations` (`key`,`value`) VALUES ('isCentral', 'no')";
$pearDB->query($query);

// Retrieve current Nagios plugins path.
$query = "SELECT value FROM options WHERE `key`='nagios_path_plugins'";
$result = $pearDB->query($query);
$row = $result->fetchRow();

// Update to new path if necessary.
if ($row
    && preg_match('#/usr/lib/nagios/plugins/?#', $row['value'])
    && is_dir('/usr/lib64/nagios/plugins')
) {
    // options table.
    $query = "UPDATE options SET value='/usr/lib64/nagios/plugins/' WHERE `key`='nagios_path_plugins'";
    $pearDB->query($query);

    // USER1 resource.
    $pearDB->query(
        "UPDATE cfg_resource SET resource_line='/usr/lib64/nagios/plugins'
        WHERE resource_line='/usr/lib/nagios/plugins'"
    );
}

/*
 * fix menu acl when child is checked but its parent is not checked
 */

// get all acl menu configurations
$aclTopologies = $pearDB->query('SELECT acl_topo_id FROM acl_topology');
while ($aclTopology = $aclTopologies->fetch()) {
    $aclTopologyId = $aclTopology['acl_topo_id'];

    // get parents of topologies which are at least read only
    $statement = $pearDB->prepare(
        'SELECT t.topology_page, t.topology_id, t.topology_parent ' .
        'FROM acl_topology_relations atr, topology t ' .
        'WHERE acl_topo_id = :topologyId ' .
        'AND atr.topology_topology_id = t.topology_id ' .
        'AND atr.access_right IN (1,2) ' // read/write and read only
    );
    $statement->bindParam(':topologyId', $aclTopologyId, \PDO::PARAM_INT);
    $statement->execute();
    $topologies = $statement->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

    // get missing parent topology relations
    $aclToInsert = [];
    foreach ($topologies as $topologyPage => $topologyParameters) {
        if (isset($topologyParameters['topology_parent']) &&
            !isset($topologies[$topologyParameters['topology_parent']]) &&
            !in_array($topologyParameters['topology_parent'], $aclToInsert)
        ) {
            if (strlen($topologyPage) === 5) { // level 3
                $levelOne = substr($topologyPage, 0, 1); // get level 1 from beginning of topology_page
                if (!isset($aclToInsert[$levelOne])) {
                    $aclToInsert[] = $levelOne;
                }
                $levelTwo = substr($topologyPage, 0, 3); // get level 2 from beginning of topology_page
                if (!isset($aclToInsert[$levelTwo])) {
                    $aclToInsert[] = $levelTwo;
                }
            } elseif (strlen($topologyPage) === 3) { // level 2
                $levelOne = substr($topologyPage, 0, 1); // get level 1 from beginning of topology_page
                if (!isset($aclToInsert[$levelOne])) {
                    $aclToInsert[] = $levelOne;
                }
            }
        }
    }

    // insert missing parent topology relations
    if (count($aclToInsert)) {
        $bindedValues = [];
        foreach ($aclToInsert as $aclIndex => $aclValue) {
            $bindedValues[':acl_' . $aclIndex] = (int) $aclValue;
        }
        $bindedQueries = implode(', ', array_keys($bindedValues));
        $statement = $pearDB->prepare(
            'INSERT INTO acl_topology_relations(acl_topo_id, topology_topology_id) ' .
            'SELECT :acl_topology_id, t.topology_id ' .
            'FROM topology t ' .
            "WHERE t.topology_page IN ($bindedQueries)"
        );
        $statement->bindValue(":acl_topology_id", (int) $aclTopologyId, \PDO::PARAM_INT);
        foreach ($bindedValues as $bindedIndex => $bindedValue) {
            $statement->bindValue($bindedIndex, $bindedValue, \PDO::PARAM_INT);
        }
        $statement->execute();
    }
}
