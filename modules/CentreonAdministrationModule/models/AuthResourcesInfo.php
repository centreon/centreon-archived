<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
    
    
    public static function deleteAllForArId($ar_id){
        
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "DELETE FROM cfg_auth_resources_info WHERE ar_id = ?";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $ar_id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public static function create($givenParameters){
        //throw new \Exception('nononono');
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $sql = "INSERT INTO cfg_auth_resources_info (ar_id,ari_name,ari_value) VALUES ( ? , ? , ? )";
        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(1, $givenParameters['ar_id'], \PDO::PARAM_INT);
        $stmt->bindValue(2, $givenParameters['ari_name'], \PDO::PARAM_STR);
        $stmt->bindValue(3, $givenParameters['ari_value'], \PDO::PARAM_STR);
        $stmt->execute();
        
    }
    
    
    //put your code here
}
