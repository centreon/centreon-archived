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

namespace CentreonRealtime\Repository;
use CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;
/**
 * Description of PollerRepository
 *
 * @author bsauveton
 */
class PollerRepository extends FormRepository
{
    
    
    public function pollerStatus(){
        $router = Di::getDefault()->get('router');
        $orgId = Di::getDefault()->get('organization');
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = 'SELECT c.name, r.last_alive, r.running, r.instance_id
            FROM cfg_pollers c
            LEFT OUTER JOIN rt_instances r
                ON r.instance_id = c.poller_id
            WHERE c.organization_id = :org_id';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':org_id', $orgId, \PDO::PARAM_INT);
        $stmt->execute();
        $now = time();
        $pollers = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['latency'] = 0;
            if (is_null($row['last_alive']) || $row['last_alive'] - $now > 60) {
                $row['disconnect'] = 1;
            } else {
                $row['disconnect'] = 0;
            }
            $pollers[] = $row;
        }
        return $pollers;
    }
    
    public static function formatDataForHeader($data){
        
                        /* Check data */
        $checkdata = array();

        $checkdata[_('id')] = $data['instance_id'];
        $checkdata[_('name')] = $data['name'];

        return $checkdata;
        
    }
    
    
    //put your code here
}
