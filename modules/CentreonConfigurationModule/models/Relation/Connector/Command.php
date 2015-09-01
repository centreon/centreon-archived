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

namespace CentreonConfiguration\Models\Relation\Connector;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;
use CentreonConfiguration\Models\Command as ExternalCommand;

class Command extends CentreonRelationModel
{
    protected static $relationTable = "cfg_hosts_hostparents_relations";
    protected static $firstKey = "host_host_id";
    protected static $secondKey = "host_parent_hp_id";
    public static $firstObject = "\CentreonConfiguration\Models\Connector";
    public static $secondObject = "\CentreonConfiguration\Models\Command";
    
    /**
     * Used for inserting relation into database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function insert($fkey, $skey = null)
    {
        ExternalCommand::update($skey, array('connector_id' => $fkey));
    }
    
    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function delete($skey, $fkey = null)
    {
        $sql = "UPDATE cfg_commands SET connector_id = NULL WHERE connector_id = ?";
        $args = array($skey);
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute($args);
    }
}
