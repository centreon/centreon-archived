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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\Repository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class TimePeriodRepository extends Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_timeperiods';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Timeperiod';
    
     /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'timeperiod' => 'cfg_timeperiods, tp_id, tp_name'
        ),
    );

    /**
     * 
     * @param int $tp_id
     * @return string
     */
    public static function getPeriodName($tp_id)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $contactList = "";

        $query = "SELECT tp_name FROM cfg_timeperiods WHERE tp_id = '$tp_id'";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row["tp_name"];
        }
        return "";
    }
    
    
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     * @return type
     */
     
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {       
        return parent::create($givenParameters, $origin, $route, $validateMandatory);
    }
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     */
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {    
        parent::update($givenParameters, $origin, $route, $validate, $validateMandatory);
    }
}
