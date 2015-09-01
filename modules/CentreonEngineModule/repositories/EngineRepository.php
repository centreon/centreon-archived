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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonEngine\Models\Engine;
use CentreonMain\Repository\FormRepository;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class EngineRepository extends FormRepository
{
    /**
     * @var string
     */
    private static $engineModel = '\CentreonEngine\Models\Engine';

    /**
     * Save engine parameters of a poller
     *
     * @param int $pollerId
     * @param array $params
     */
    public static function save($pollerId, $params, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        if ($validate) {
            self::validateForm($params, $origin, $route, $validateMandatory);
        }
        
        $db = Di::getDefault()->get('db_centreon');

        /* Get engine id, if it does not exist, insert it */
        $stmt = $db->prepare("SELECT poller_id FROM cfg_engine WHERE poller_id = ?");
        $stmt->execute(array($pollerId));
        if (!$stmt->rowCount()) {
            $stmt = $db->prepare("INSERT INTO cfg_engine (poller_id) VALUES (?)");
            $stmt->execute(array($pollerId));
        }

        /* Update parameters of engine only if the parameter name matches the column name */
        $sqlParams = array(':poller_id' => $pollerId);
        $updateSql = "";
        $model = static::$engineModel;
        $columns = $model::getColumns();
        foreach ($params as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }
            if (is_string($v)) {
                $v = trim($v);
            }
            if (empty($v)) {
                $v = null;
            }
            
            $newkey = ':' . $k;
            $sqlParams[$newkey] = $v;
            if ($updateSql != "") {
                $updateSql .= ', ';
            }
            $updateSql .= "{$k} = :{$k}";
        }
        if ($updateSql) {
            $sql = "UPDATE cfg_engine SET {$updateSql} WHERE poller_id = :poller_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($sqlParams);
        }
    }

    /**
     * Get directories from poller id
     *
     * @param int $pollerId
     * @return array
     */
    public static function getDirectories($pollerId)
    {
        $sql = "SELECT conf_dir, log_dir, var_lib_dir, module_dir 
            FROM cfg_engine
            WHERE poller_id = :poller_id";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':poller_id' => $pollerId));
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }

    
    /**
     * Send external command for poller
     *
     * @param int $cmdId
     */
    public static function sendCommand($command, $pollerId)
    {
        $externalCommandFile = '/var/lib/centreon-broker/central-broker-extcommands-engine-poller-module-' . $pollerId . '.cmd';
        if (file_exists($externalCommandFile)) {
            file_put_contents($externalCommandFile, $command, FILE_APPEND);
        } else {
            throw new \Exception ("The external command file of broker does not exist");
        }
    }
}
