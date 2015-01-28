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
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;

/**
 * Handles audit logs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 */
class AuditlogRepository
{
    /**
     * Add a change log
     *
     * @param $actionType string Action type
     * @param $objType string The object type
     * @param $objId int The object id
     * @param $objName string The object name
     * @param $objValues array The list of changed values
     */
    public static function addLog($actionType, $objType, $objId, $objName, $objValues)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $query = 'INSERT INTO log_action
            (action_log_date, object_type, object_id, object_name, action_type, log_contact_id)
            VALUES (:date, :obj_type, :obj_id, :obj_name, :action_type, :contact_id)';
        $stmt = $dbconn->prepare($query);
        $time = time();
        $stmt->bindParam(':date', $time, \PDO::PARAM_INT);
        $stmt->bindParam(':obj_type', $objType, \PDO::PARAM_STR);
        $stmt->bindParam(':obj_id', $objId, \PDO::PARAM_INT);
        $stmt->bindParam(':obj_name', $objName, \PDO::PARAM_STR);
        $stmt->bindParam(':action_type', $actionType, \PDO::PARAM_STR);
        $stmt->bindParam(':contact_id', $_SESSION['user']->getId(), \PDO::PARAM_INT);
        $stmt->execute();

        /* Get new insert log */
        $query = 'SELECT MAX(action_log_id) as action_log_id
            FROM log_action
            WHERE action_log_date = :time';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
        $row = $stmt->fetch();
        if (false === $row) {
            throw new \Exception("Error while inserting action log");
        }
        $actionId = $row['action_log_id'];

        /* Insert changed elements in database */
        $query = 'INSERT INTO log_action_modification
            (field_name, field_value, action_log_id)
            VALUES (:field_name, :field_value, :action_id)';
        $dbconn->beginTransaction();
        $stmt = $dbconn->prepare($query);
        foreach ($objValues as $name => $value) {
            if (!is_null($value)) {
                try {
                    $stmt->bindParam(':field_name', $name, \PDO::PARAM_STR);
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $stmt->bindParam(':field_value', $value, \PDO::PARAM_STR);
                    $stmt->bindParam(':action_id', $actionId, \PDO::PARAM_INT);
                    $stmt->execute();
                } catch (\Exception $e) {
                    $dbconn->rollback();
                    throw $e;
                }
            }
        }
        $dbconn->commit();
    }
}
