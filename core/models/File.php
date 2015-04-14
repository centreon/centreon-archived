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
