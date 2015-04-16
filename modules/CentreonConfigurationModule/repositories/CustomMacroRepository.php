<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;

/**
 * Description of CustomMacrosRepository
 *
 * @author lionel
 */
class CustomMacroRepository
{
    /**
     * 
     * @param type $objectId
     */
    public static function loadHostCustomMacro($objectId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $getRequest = "SELECT host_macro_name AS macro_name, host_macro_value AS macro_value, is_password AS macro_hidden "
            . "FROM cfg_customvariables_hosts WHERE host_host_id = :host ";
        $stmtGet = $dbconn->prepare($getRequest);
        $stmtGet->bindParam(':host', $objectId, \PDO::PARAM_INT);
        $stmtGet->execute();
        $rowMacro = $stmtGet->fetchAll(\PDO::FETCH_ASSOC);
        return $rowMacro;
    }
    
    /**
     * 
     * @param type $objectId
     * @param type $submittedValues
     */
    public static function saveHostCustomMacro($objectId, $submittedValues)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $deleteRequest = "DELETE FROM cfg_customvariables_hosts WHERE host_host_id = :host";
        $stmtDelete = $dbconn->prepare($deleteRequest);
        $stmtDelete->bindParam(':host', $objectId, \PDO::PARAM_INT);
        $stmtDelete->execute();
        
        $insertRequest = "INSERT INTO cfg_customvariables_hosts(host_macro_name, host_macro_value, is_password, host_host_id)"
            . " VALUES(:macro_name, :macro_value, :is_password, :host)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        foreach ($submittedValues as $customMacroName => $customMacro) {
            $stmtInsert->bindValue(':macro_name', '$_HOST' . $customMacroName . '$', \PDO::PARAM_STR);
            $stmtInsert->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
            $stmtInsert->bindParam(':is_password', $customMacro['ispassword'], \PDO::PARAM_INT);
            $stmtInsert->bindParam(':host', $objectId, \PDO::PARAM_INT);
            $stmtInsert->execute();
        }
    }
    
    /**
     * 
     * @param type $objectId
     */
    public static function loadServiceCustomMacro($objectId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $getRequest = "SELECT svc_macro_name AS macro_name, svc_macro_value AS macro_value, is_password AS macro_hidden "
            . "FROM cfg_customvariables_services WHERE svc_svc_id = :svc ";
        $stmtGet = $dbconn->prepare($getRequest);
        $stmtGet->bindParam(':svc', $objectId, \PDO::PARAM_INT);
        $stmtGet->execute();
        $rowMacro = $stmtGet->fetchAll(\PDO::FETCH_ASSOC);
        return $rowMacro;
    }
    
    /**
     * 
     * @param type $objectId
     * @param type $submittedValues
     */
    public static function saveServiceCustomMacro($objectId, $submittedValues)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $deleteRequest = "DELETE FROM cfg_customvariables_services WHERE svc_svc_id = :svc";
        $stmtDelete = $dbconn->prepare($deleteRequest);
        $stmtDelete->bindParam(':svc', $objectId, \PDO::PARAM_INT);
        $stmtDelete->execute();
        
        $insertRequest = "INSERT INTO cfg_customvariables_services(svc_macro_name, svc_macro_value, is_password, svc_svc_id)"
            . " VALUES(:macro_name, :macro_value, :is_password, :svc)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        
        foreach ($submittedValues as $customMacroName => $customMacro) {
            $stmtInsert->bindValue(':macro_name', '$_SERVICE' . $customMacroName . '$', \PDO::PARAM_STR);
            $stmtInsert->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
            $stmtInsert->bindParam(':is_password', $customMacro['ispassword'], \PDO::PARAM_INT);
            $stmtInsert->bindParam(':svc', $objectId, \PDO::PARAM_INT);
            $stmtInsert->execute();
        }
    }
}
