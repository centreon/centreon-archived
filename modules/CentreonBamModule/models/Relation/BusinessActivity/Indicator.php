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
 */

namespace CentreonBam\Models\Relation\BusinessActivity;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;
use CentreonBam\Models\Indicator as IndicatorSimpleModel;

class Indicator extends CentreonRelationModel
{
    protected static $relationTable = "cfg_bam_kpi";
    protected static $firstKey = "kpi_id";
    protected static $secondKey = "id_ba";
    public static $firstObject = "\CentreonBam\Models\Indicator";
    public static $secondObject = "\CentreonBam\Models\BusinessActivity";
    
    /**
     * Used for inserting relation into database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function insert($fkey, $skey = null)
    {
        IndicatorSimpleModel::update($fkey, array('id_ba' => $skey));
    }
    
    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function delete($skey, $fkey = null)
    {
        $sql = "UPDATE cfg_bam_kpi SET id_ba = NULL WHERE id_ba = ?";
        $args = array($fkey);
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute($args);
    }
}
