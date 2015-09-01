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

namespace Centreon\Commands\Database;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;

/**
 * Description of DumpCommand
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class DumpCommand extends AbstractCommand
{
    /**
     * Extract data from a db table and prints a json string
     *
     * @param string $dbname | 'db_centreon' or 'db_storage'
     * @param string $tablename
     * @param string $extra
     */
    public function sqlToJsonAction($dbname, $tablename, $extra = '')
    {
        $db = Di::getDefault()->get($dbname);
        $sql = "SELECT * FROM $tablename $extra";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $tab = array();
        $i = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            foreach ($row as $k => $v) {
                if (!is_null($v)) {
                    $tab[$i][$k] = $v;
                }
            }
            $i++;
        }
        echo json_encode($tab);
    }
    
    
     /**
     * Extract json file and prints a sql string 
     *
     * @param string $sFile
     * @param string $tablename
     * @sDestination
     */
    public function jsonToSqlAction($sFile, $tablename, $sDestination = '')
    {
        $sInsert = "INSERT INTO ".$tablename."(";
        if (file_exists($sFile)) {
            try {
                $sColumns = '';
                $aData = json_decode(file_get_contents($sFile), true);
 
                $sSql = '';

                foreach ($aData as $value) {
                    $sColumns = implode(',', array_keys($value));
                    $sSql .= "(".'"'.implode('","', array_values($value)).'"'."), ";
                }
                $sChars = $sInsert.$sColumns.") VALUES ".$sSql;
                $sContent = substr($sChars, 0, strlen($sChars) - 2).";";
                
                if (empty($sDestination)) {
                    echo $sContent;
                } else {
                    if (file_exists($sDestination)) {
                        file_put_contents($sContent, $sDestination);
                    } else {
                        throw new Exception("invalide desination");
                    }
                }
             
            } catch (Exception $ex) {
                 return "invalid content";
            }
        }
    }
}
