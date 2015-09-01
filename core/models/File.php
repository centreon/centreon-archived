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

namespace Centreon\Models;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Models
 */
class File
{
    /**
     * 
     * @param type $file
     */
    public static function insert($file)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Insert the file in DB
        $query = 'INSERT INTO `cfg_binaries` (`filename`, `checksum`, `mimetype`, `filetype`, `binary_content`)
                    VALUES (:filename, :checksum, :mimetype, :filetype, :binary_content)';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':filename', $file['filename'], \PDO::PARAM_STR);
        $stmt->bindParam(':checksum', $file['checksum'], \PDO::PARAM_STR);
        $stmt->bindParam(':mimetype', $file['mimetype'], \PDO::PARAM_STR);
        $stmt->bindParam(':filetype', $file['filetype'], \PDO::PARAM_INT);
        $stmt->bindParam(':binary_content', $file['binary_content'], \PDO::PARAM_LOB);
        $stmt->execute();
    }
    
    /**
     * 
     * @param int $fileId
     * @return array
     */
    public static function getFilename($fileId)
    {
        $sql = "SELECT binary_id, filename FROM cfg_binaries WHERE binary_id  = ?";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($fileId));
        $rawIconList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $finalIconList = array();
        
        if (count($rawIconList) > 0) {
            $finalIconList = $rawIconList[0];
        }
        
        return $finalIconList;
    }
    
    /**
     * 
     * @param string $fields
     * @param array $binaryParam
     * @return int
     * @throws Exception
     */
    public static function getBinary($fields = '*', $binaryParam = array())
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Get the id of the brand new file that has been insert in the DB
        $queryRetrieveBinaryId = "SELECT $fields "
            . "FROM cfg_binary_type "
            . "WHERE `type_name` = :typename "
            . "AND `module_id` = :moduleid";
        $stmt = $dbconn->prepare($queryRetrieveBinaryId);
        $stmt->bindParam(':typename', $binaryParam['typename'], \PDO::PARAM_STR);
        $stmt->bindParam(':moduleid', $binaryParam['moduleid'], \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if (false === $row) {
            throw new Exception("Vaue not found", 404);
        }
        
        return $row['binary_type_id'];
    }
    
    /**
     * 
     * @param array $typeParam
     * @return int
     * @throws Exception
     */
    public static function getBinaryType($typeParam)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Get the id of the brand new file that has been insert in the DB
        $queryRetrieveBinaryId = "SELECT `binary_type_id` "
            . "FROM cfg_binary_type "
            . "WHERE `type_name` = :typename "
            . "AND `module_id` = :moduleid";
        $stmt = $dbconn->prepare($queryRetrieveBinaryId);
        $stmt->bindParam(':typename', $typeParam['typename'], \PDO::PARAM_STR);
        $stmt->bindParam(':moduleid', $typeParam['moduleid'], \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if (false === $row) {
            throw new Exception("Value not found", 404);
        }
        
        return $row['binary_type_id'];
    }
}
