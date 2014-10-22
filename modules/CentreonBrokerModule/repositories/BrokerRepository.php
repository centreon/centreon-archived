<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 */

namespace CentreonBroker\Repository;

use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class BrokerRepository
{
    /**
     * Save broker parameters of a node
     *
     * @param int $pollerId
     * @param array $params
     */
    public static function save($pollerId, $params)
    {
        $db = Di::getDefault()->get('db_centreon');

        $arr = array();
        foreach ($params as $k => $v) {
            $arr[$k] = $v;
        }

        /* Save paths */
        /* Test if exists in db */
        $query = "SELECT COUNT(poller_id) as poller
            FROM cfg_centreonbroker_paths
            WHERE poller_id = :poller_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row['poller'] > 0) {
            /* Update */
            $query = "UPDATE cfg_centreonbroker_paths SET 
                directory_config = :broker_etc_directory,
                directory_modules = :broker_module_directory,
                directory_logs = :broker_logs_directory,
                directory_data = :broker_data_directory
                WHERE poller_id = :poller_id";
        } else {
            /* Insert */
            $query = "INSERT INTO cfg_centreonbroker_paths
                (poller_id, directory_config, directory_modules, directory_logs, directory_data) VALUES
                (:poller_id, :broker_etc_directory, :broker_module_directory, :broker_logs_directory, :broker_data_directory)";
        }
        $stmt = $db->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId);
        $stmt->bindParam(':broker_etc_directory', $arr['broker_etc_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_module_directory', $arr['broker_module_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_logs_directory', $arr['broker_logs_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_data_directory', $arr['broker_data_directory'], \PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Get params from poller id
     * 
     * @param int $pollerId
     */
    public static function getPathsFromPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "SELECT directory_modules, directory_config, directory_logs, directory_data
            FROM cfg_centreonbroker_paths
            WHERE poller_id = :poller_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':poller_id' => $pollerId
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }
}
