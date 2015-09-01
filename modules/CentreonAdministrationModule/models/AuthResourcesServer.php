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

namespace CentreonAdministration\Models;

use Centreon\Models\CentreonBaseModel;
use Centreon\Internal\Di;
/**
 * Description of AuthResourcesServer
 *
 * @author bsauveton
 */
class AuthResourcesServer extends CentreonBaseModel
{
    //put your code here
    
    protected static $table = "cfg_auth_resources_servers";
    protected static $primaryKey = "ldap_server_id";
    protected static $uniqueLabelField = "server_address";
    protected static $relations = array(
     
    );
    
    
    
    public static function deleteAllForArId($ar_id){
        
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "DELETE FROM cfg_auth_resources_servers WHERE auth_resource_id = ?";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $ar_id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    //put your code here
    
    
    
}
