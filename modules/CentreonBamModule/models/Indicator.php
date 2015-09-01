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

namespace CentreonBam\Models;

use Centreon\Models\CentreonBaseModel;
use CentreonBam\Repository\IndicatorRepository;
use Centreon\Internal\Di;

/**
 * Used for interacting with host categories
 *
 * @author sylvestre
 */
class Indicator extends CentreonBaseModel
{
    protected static $table = "cfg_bam_kpi";
    protected static $primaryKey = "kpi_id";
    protected static $uniqueLabelField = "kpi_id";

    /**
     *
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getListBySearch(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $aAddFilters = array();
        $tablesString =  null;
        $aGroup = array();

        // Filter by kpi name
        if (isset($filters['object']) && !empty($filters['object'])) {
            $indicatorsName = IndicatorRepository::getIndicatorsName($filters['object']);
            if (count($indicatorsName)) {
                foreach ($indicatorsName as $indicatorName) {
                    $filters['kpi_id'][] = $indicatorName['id'];
                }
            } else {
                $count = 0;
            }
            unset($filters['object']);
        }

        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
    
    public static function getKpi($object)
    {
        $organizationId = Di::getDefault()->get('organization');

        $params = array();
        $db = Di::getDefault()->get('db_centreon');
        $result = null;
        
        if (isset($object['service_id'])) {
            $query = ' select * from cfg_bam_kpi i '
                    . ' where i.service_id = :service_id and i.id_ba = :id_ba '
                    . ' and organization_id = :organization_id';
            
            if (isset($object['host_id'])) {
                $query .= " and i.host_id = :host_id ";
            }
            $stmt = $db->prepare($query);
            $stmt->bindParam(':service_id', $object['service_id'], \PDO::PARAM_INT);
            if (isset($object['host_id'])) {
                $stmt->bindParam(':host_id', $object['host_id'], \PDO::PARAM_INT);
            }
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->bindParam(':organization_id', $organizationId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else if(isset($object['id_indicator_ba'])) {
            $query = ' select * from cfg_bam_kpi i '
                   . ' where id_indicator_ba = :id_indicator_ba and id_ba = :id_ba '
                   . ' and organization_id = :organization_id';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_indicator_ba', $object['id_indicator_ba'], \PDO::PARAM_INT);
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->bindParam(':organization_id', $organizationId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else if(isset($object['boolean_id'])) {
            $query = ' select * from cfg_bam_kpi i '
                    . ' inner join cfg_bam_boolean cbb on cbb.boolean_id = i.boolean_id '
                    . ' where i.boolean_id = :boolean_id and id_ba = :id_ba '
                    . ' and organization_id = :organization_id';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':boolean_id', $object['boolean_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->bindParam(':organization_id', $organizationId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        if(!empty($result)){
            return $result[0];
        }
    }
    
    
}
