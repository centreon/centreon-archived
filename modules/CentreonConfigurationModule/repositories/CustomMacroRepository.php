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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;

/**
 * Description of CustomMacrosRepository
 *
 * @author lionel
 */
class CustomMacroRepository
{
    public static function loadHostCustomMacro($objectId)
    {
        
    }
    
    public static function saveHostCustomMacro($objectId, $submittedValues)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $checkRequest = "SELECT host_macro_id FROM cfg_customvariables_hosts WHERE host_host_id = :host "
            . "AND host_macro_name = :macro_name";
        $stmtCheck = $dbconn->prepare($checkRequest);
        
        $insertRequest = "INSERT INTO cfg_customvariables_hosts(host_macro_name, host_macro_value, is_password, host_host_id)"
            . " VALUES(:macro_name, :macro_value, :is_password, :host)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        
        $updateRequest = "UPDATE cfg_customvariables_hosts SET host_macro_name = :macro_name, "
            . "host_macro_value = :macro_value, "
            . "is_password = :is_password, "
            . "host_host_id = :host";
        $stmtUpdate = $dbconn->prepare($updateRequest);
        
        foreach ($submittedValues as $customMacroName => $customMacro) {
            $stmtCheck->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
            $stmtCheck->bindParam(':host', $objectId, \PDO::PARAM_INT);
            $stmtCheck->execute();
            $rowMacro = $stmtCheck->fetchAll(\PDO::FETCH_ASSOC);
            if (count($rowMacro) == 0) {
                $stmtInsert->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
                $stmtInsert->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
                $stmtInsert->bindParam(':is_password', $customMacro['value'], \PDO::PARAM_STR);
                $stmtInsert->bindParam(':host', $objectId, \PDO::PARAM_INT);
                $stmtInsert->execute();
            } elseif (count($rowMacro) == 0) {
                $stmtUpdate->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':is_password', $customMacro['value'], \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':host', $objectId, \PDO::PARAM_INT);
                $stmtUpdate->execute();
            }
        }
    }
    
    public static function saveServiceCustomMacro($objectId, $submittedValues)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $checkRequest = "SELECT svc_macro_id FROM cfg_customvariables_svcs WHERE svc_svc_id = :svc "
            . "AND svc_macro_name = :macro_name";
        $stmtCheck = $dbconn->prepare($checkRequest);
        
        $insertRequest = "INSERT INTO cfg_customvariables_svcs(svc_macro_name, svc_macro_value, is_password, svc_svc_id)"
            . " VALUES(:macro_name, :macro_value, :is_password, :svc)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        
        $updateRequest = "UPDATE cfg_customvariables_svcs SET svc_macro_name = :macro_name, "
            . "svc_macro_value = :macro_value, "
            . "is_password = :is_password, "
            . "svc_svc_id = :svc";
        $stmtUpdate = $dbconn->prepare($updateRequest);
        
        foreach ($submittedValues as $customMacroName => $customMacro) {
            $stmtCheck->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
            $stmtCheck->bindParam(':svc', $objectId, \PDO::PARAM_INT);
            $stmtCheck->execute();
            $rowMacro = $stmtCheck->fetchAll(\PDO::FETCH_ASSOC);
            if (count($rowMacro) == 0) {
                $stmtInsert->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
                $stmtInsert->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
                $stmtInsert->bindParam(':is_password', $customMacro['value'], \PDO::PARAM_STR);
                $stmtInsert->bindParam(':svc', $objectId, \PDO::PARAM_INT);
                $stmtInsert->execute();
            } elseif (count($rowMacro) == 0) {
                $stmtUpdate->bindParam(':macro_name', $customMacroName, \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':macro_value', $customMacro['value'], \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':is_password', $customMacro['value'], \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':svc', $objectId, \PDO::PARAM_INT);
                $stmtUpdate->execute();
            }
        }
    }
}
