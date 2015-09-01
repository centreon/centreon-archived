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

namespace CentreonBam\Models\Relation\Indicator;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Models\CentreonRelationModel;

class Service extends CentreonRelationModel
{
    protected static $firstKey = "kpi_id";

    /**
     * Get Host id service id from kpi id
     *
     * @param int $kpiId
     * @return array multidimentional array with host_id and service_id indexes
     */
    public static function getHostIdServiceIdFromKpiId($kpiId)
    {
        $sql = "SELECT host_id, service_id "
            . "FROM cfg_bam_kpi "
            . "WHERE ".static::$firstKey." = ?";
        $result = self::getResult($sql, array($kpiId));
        $tab = array();
        $i = 0;
        foreach ($result as $rez) {
            $tab[$i]['host_id'] = $rez['host_id'];
            $tab[$i]['service_id'] = $rez['service_id'];
            $i++;
        }
        return $tab;
    }

    /**
     * This call will directly throw an exception
     *
     * @param string $name
     * @param array $arg
     * @throws \Centreon\Internal\Exception
     */
    public function __call($name, $arg)
    {
        throw new Exception('Unknown method');
    }
}
