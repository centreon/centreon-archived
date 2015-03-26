<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonBam\Models\Relation\Indicator;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Models\CentreonRelationModel;

class Service extends CentreonRelationModel
{
    protected static $relationTable = "cfg_servicegroups_relations";
    protected static $firstKey = "kpi_id";
    //protected static $secondKey = "service_id";
    //public static $firstObject = "\CentreonConfiguration\Models\Servicegroup";
    //public static $secondObject = "\CentreonConfiguration\Models\Service";

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
