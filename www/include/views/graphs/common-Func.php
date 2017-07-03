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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

function getServiceGroupCount($search = null)
{
    global $pearDB;

    if ($search != "") {
        $DBRESULT = $pearDB->query(
            "SELECT count(sg_id) FROM `servicegroup` WHERE sg_name LIKE '%$search%'"
        );
    } else {
        $DBRESULT = $pearDB->query("SELECT count(sg_id) FROM `servicegroup`");
    }
    $num_row = $DBRESULT->fetchRow();
    $DBRESULT->closeCursor();
    return $num_row["count(sg_id)"];
}

function getMyHostGraphs($host_id = null)
{
    global $pearDBO;
    if (!isset($host_id)) {
        return null;
    }
    $tab_svc = array();

    $DBRESULT = $pearDBO->query(
        "SELECT `service_id`, `service_description` "
        . "FROM `index_data`, `metrics` "
        . "WHERE metrics.index_id = index_data.id "
        . "AND `host_id` = '".CentreonDB::escape($host_id)."' "
        . "AND index_data.`hidden` = '0' "
        . "AND index_data.`trashed` = '0' "
        . "ORDER BY `service_description`"
    );
    while ($row = $DBRESULT->fetchRow()) {
        $tab_svc[$row["service_id"]] = $row['service_description'];
    }
    return $tab_svc;
}

function getHostGraphedList()
{
    global $pearDBO;

    $tab = array();
    $DBRESULT = $pearDBO->query(
        "SELECT `host_id` FROM `index_data`, `metrics` "
        . "WHERE metrics.index_id = index_data.id "
        . "AND index_data.`hidden` = '0' "
        . "AND index_data.`trashed` = '0' "
        . "ORDER BY `host_name`"
    );
    while ($row = $DBRESULT->fetchRow()) {
        $tab[$row["host_id"]] = 1;
    }
    return $tab;
}

function checkIfServiceSgIsEn($host_id = null, $service_id = null)
{
    global $pearDBO;
    if (!isset($host_id) || !isset($service_id)) {
        return null;
    }
    $tab_svc = array();

    $DBRESULT = $pearDBO->query(
        "SELECT `service_id` FROM `index_data` "
        . "WHERE `host_id` = '".CentreonDB::escape($host_id)."' "
        . "AND `service_id` = '".CentreonDB::escape($service_id)."' "
        . "AND index_data.`hidden` = '0' "
        . "AND `trashed` = '0'"
    );
    $num_row = $DBRESULT->rowCount();
    $DBRESULT->closeCursor();
    return $num_row;
}
