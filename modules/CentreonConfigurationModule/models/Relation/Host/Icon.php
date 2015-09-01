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

namespace CentreonConfiguration\Models\Relation\Host;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Icon extends CentreonRelationModel
{
    protected static $relationTable = "host_template_relation";
    protected static $firstKey = "host_host_id";
    protected static $secondKey = "host_tpl_id";
    public static $firstObject = "\CentreonConfiguration\Models\Host";
    public static $secondObject = "\CentreonConfiguration\Models\Host";
    
    /**
     * 
     * @param int $fkey
     * @param int $skey
     */
    public static function insert($fkey, $skey = null)
    {
        if (isset($skey) && is_numeric($skey)) {
            $sql = 'INSERT INTO cfg_hosts_images_relations(host_id, binary_id) VALUES(?, ?)';
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
        $sql = 'DELETE FROM cfg_hosts_images_relations WHERE host_id = ?';
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
    public static function getIconForHost($hostId, $limit = 1)
    {
        $sql = "SELECT b.binary_id, b.filename FROM cfg_binaries b, cfg_hosts_images_relations hir "
            . "WHERE hir.host_id = ? "
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
