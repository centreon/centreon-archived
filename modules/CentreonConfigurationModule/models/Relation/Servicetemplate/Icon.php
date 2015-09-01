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

namespace CentreonConfiguration\Models\Relation\Servicetemplate;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Icon extends CentreonRelationModel
{
    protected static $relationTable = "cfg_services_images_relations";
    protected static $firstKey = "service_id";
    protected static $secondKey = "service_tpl_id";
    public static $firstObject = "\CentreonConfiguration\Models\Servicetemplate";
    public static $secondObject = "\CentreonConfiguration\Models\Servicetemplate";
    
    /**
     * 
     * @param int $fkey
     * @param int $skey
     */
    public static function insert($fkey, $skey = null)
    {
        if (isset($skey) && is_numeric($skey)) {
            $sql = 'INSERT INTO cfg_services_images_relations(service_id, binary_id) VALUES(?, ?)';
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare($sql);
            $stmt->execute(array($fkey, $skey));
        }
    }
    
    /**
     * 
     * @param int $fkey
     * @param int $skey
     */
    public static function delete($fkey, $skey = null)
    {
        $sql = 'DELETE FROM cfg_services_images_relations WHERE service_id = ?';
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($fkey));
    }
    
    /**
     * 
     * @param int $hostId
     * @param int $limit
     * @return array
     */
    public static function getIconForService($hostId, $limit = 1)
    {
        $sql = "SELECT b.binary_id, b.filename FROM cfg_binaries b, cfg_services_images_relations hir "
            . "WHERE hir.service_id = ? "
            . "AND filetype = 1 "
            . "AND hir.binary_id = b.binary_id "
            . "LIMIT $limit";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($hostId));
        $rawIconList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $finalIconList = array();
        
        if (($limit == 1) && isset($rawIconList[0])) {
            $finalIconList = $rawIconList[0];
        } elseif (count($rawIconList) > 0) {
            $finalIconList = $rawIconList;
        }
        
        return $finalIconList;
    }
}
