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
    
    public static function getKpi($object){
        
        $params = array();
        $db = Di::getDefault()->get('db_centreon');
        $result = null;
        if(isset($object['service_id']) && isset($object['host_id'])){
            $query = ' select * from cfg_bam_kpi i '
                    . ' where i.service_id = :service_id and i.host_id = :host_id and i.id_ba = :id_ba ';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':service_id', $object['service_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':host_id', $object['host_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }else if(isset($object['id_indicator_ba'])){
            $query = ' select * from cfg_bam_kpi i '
                   . ' where id_indicator_ba = :id_indicator_ba and id_ba = :id_ba ';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_indicator_ba', $object['id_indicator_ba'], \PDO::PARAM_INT);
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }else if(isset($object['boolean_id'])){
            $query = ' select * from cfg_bam_kpi i '
                    . ' inner join cfg_bam_boolean cbb on cbb.boolean_id = i.boolean_id '
                    . ' where i.boolean_id = :boolean_id and id_ba = :id_ba ';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':boolean_id', $object['boolean_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':id_ba', $object['id_ba'], \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        
        if(!empty($result)){
            return $result[0];
        }
    }
    
    
}
