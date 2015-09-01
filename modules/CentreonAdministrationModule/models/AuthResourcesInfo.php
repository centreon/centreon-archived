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
 * Description of Ldap
 *
 * @author bsauveton
 */
class AuthResourcesInfo extends CentreonBaseModel
{
    protected static $table = "cfg_auth_resources_info";
    protected static $primaryKey = "ar_id";
    protected static $uniqueLabelField = "ari_name";
    protected static $relations = array(
     
    );
    
    
    /**
     * 
     * @param int $id
     */
    public static function deleteAllForArId($id){
        
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "DELETE FROM cfg_auth_resources_info WHERE ar_id = ?";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    
    /**
     * 
     * @param array $givenParameters
     */
    public static function create($givenParameters){
        //throw new \Exception('nononono');
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "INSERT INTO cfg_auth_resources_info (ar_id,ari_name,ari_value) VALUES ( ? , ? , ? )";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $givenParameters['ar_id'], \PDO::PARAM_INT);
        $stmt->bindValue(2, $givenParameters['ari_name'], \PDO::PARAM_STR);
        $stmt->bindValue(3, self::transformation($givenParameters['ari_name'],$givenParameters['ari_value']), \PDO::PARAM_STR);
        $stmt->execute();
    }
    
    
    /**
     * 
     * @param string $name
     * @param int $id
     */
    public static function getInfosFromName($name,$id){
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "Select * from cfg_auth_resources_info where ar_id = ? and ari_name = ? ";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $name, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }
    
    
    private static function transformation($name,$value){
        
        
        switch($name){
            
            case 'protocol_version' :
                return substr($value,1);
            default :
                return $value;
        }
        
        return $value;
    }
    
    
    //put your code here
}
