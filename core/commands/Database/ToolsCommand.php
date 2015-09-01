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

class ToolsCommand extends AbstractCommand
{
    
     /**
     * Extract json file and prints a sql string 
     *
     * @param string $file
     * @param string $tablename
     * @param string destination
     */
    public function jsonToSqlAction($file, $tablename, $destination = '')
    {
        $sInsert = "INSERT INTO ".$tablename."(";
        if (file_exists($file)) {
            try {
                $sColumns = '';
                $aData = json_decode(file_get_contents($file), true);
 
                $sSql = '';

                foreach ($aData as $value) {
                    $sColumns = implode(',', array_keys($value));
                    $sSql .= "(".'"'.implode('","', array_values($value)).'"'."), ";
                }
                $sChars = $sInsert.$sColumns.") VALUES ".$sSql;
                $sContent = substr($sChars, 0, strlen($sChars) - 2).";";
                
                if (empty($destination)) {
                    echo $sContent;
                } else {
                    if (file_exists($destination)) {
                        file_put_contents($sContent, $destination);
                    } else {
                        throw new Exception("invalide destination");
                    }
                }
            } catch (Exception $ex) {
                throw new Exception("invalid content");
            }
        }
    }
}
