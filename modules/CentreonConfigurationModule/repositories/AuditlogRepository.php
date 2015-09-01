<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
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
