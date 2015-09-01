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
use Centreon\Internal\Exception;
use CentreonConfiguration\Repository\Repository;
use CentreonConfiguration\Models\Command;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class CommandRepository extends Repository
{
    const NOTIF_TYPE = 1;
    const CHECK_TYPE = 2;

    /**
     *
     * @var array
     */
    public static $exposedParams = array('type' => 'command_type');
    
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_commands';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Command';
    
    /**
     *
     * @var type 
     */
    protected static $unicityFields = array(
        'fields' => array(
            'command' => 'cfg_commands,command_id,command_name'
        ),
    );
    
    public static $objectClass = '\CentreonConfiguration\Models\Command';
    
    /**
     * 
     * @param int $id
     * @return mixed
     */
    public static function getCommandName($id)
    {
        $res = Command::get($id, "command_name");
        
        if (is_array($res)) {
            $returnedValue = $res['command_name'];
        } else {
            $returnedValue = -1;
        }
        
        return $returnedValue;
    }

    /**
     * 
     * @param int $id
     * @param string $object
     * @return string
     */
    public static function getUseNumber($id, $object)
    {
        $di = Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $result = "";
        
        if ($object == 'host') {
            $objectTable = 'cfg_hosts';
        } elseif ($object == 'service') {
            $objectTable = 'cfg_services';
        }

        /* Get Object Stats */
        for ($i = 1; $i != -1; $i--) {
            $stmt = $dbconn->prepare(
                "SELECT count(*) AS number "
                . "FROM $objectTable "
                . "WHERE (command_command_id = '$id' "
                . "OR command_command_id2 = '$id') "
                . "AND ".$object."_register = '$i'"
            );
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (isset($row["number"])) {
                if ($i) {
                    $result .= $row["number"];
                } else {
                    $result .= " (".$row["number"].")";
                }
            }
        }
        return $result;
    }
}
