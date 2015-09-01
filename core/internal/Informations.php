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
namespace Centreon\Internal;

/**
 * Description of Informations
 *
 * @author lionel
 */
class Informations
{
    /**
     * 
     * @return type
     * @throws Exception
     */
    public static function getCentreonVersion()
    {
        $di = Di::getDefault();
        $db = $di->get('db_centreon');
        
        try {
            $stmt = $db->query("SELECT `value` FROM cfg_informations WHERE `key` = 'version'");
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (count($res) == 0) {
                throw new \Exception("No values");
            }
            
        } catch (\PDOException $e) {
            if ($e->getCode() == "42S02") {
                throw new \Exception("Table not exist");
            }
        }
        
        return $res[0]['value'];
    }
}
