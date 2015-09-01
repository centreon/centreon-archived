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
 * Used for interacting with commands
 *
 * @author sylvestre
 */
class Validators extends CentreonBaseModel
{
    protected static $table = "cfg_forms_validators";
    protected static $primaryKey = "validator_id";
    protected static $uniqueLabelField = "name";
    
    
    /**
     * Used for deleteing object from database
     *
     * @param string $sName
     */
    public static function delete($sName = "", $notFoundError = true)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "DELETE FROM  " . static::$table ;
        if (!empty($sName)) {
            $sql .= " WHERE name ='".$sName."'";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
}
