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

namespace CentreonRealtime\Repository;

use \CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;
use CentreonRealtime\Models\NotificationRules;

/**
 * Description of NotificationRuleRepository
 *
 * @author bsauveton
 */
class NotificationRulesRepository extends FormRepository
{
    
    /**
     *
     * @var string
     */
    public static $tableName = 'rt_notifications';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'NotificationRules';
    
    
    public static function setRealTimeNotifications($poller_id){
        
        $db = Di::getDefault()->get('db_centreon');    
        $hostSql =  "select * from  cfg_notification_rules r "
            . "inner join cfg_notification_rules_hosts_relations hr on hr.rule_id = r.rule_id "
            . "inner join cfg_hosts h on hr.host_id = h.host_id and h.poller_id = :poller_id "
            . "inner join cfg_notification_rules_contacts_relations cr on cr.rule_id = r.rule_id "
            . "inner join cfg_contacts c on cr.contact_id = c.contact_id";

        $serviceSql = "select * from  cfg_notification_rules r "
            . "inner join cfg_notification_rules_services_relations sr on sr.rule_id = r.rule_id "
            . "inner join cfg_services s on sr.service_id = s.service_id "
            . "inner join cfg_hosts_services_relations hsr on hsr.service_service_id = sr.service_id "
            . "inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id "
            . "inner join cfg_notification_rules_contacts_relations cr on cr.rule_id = r.rule_id "
            . "inner join cfg_contacts c on cr.contact_id = c.contact_id ";
        $stmt = $db->prepare($hostSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        $hostNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        
        $deleteHostSql = "delete r from rt_notification_rules r "
                   . "inner join cfg_notification_rules_hosts_relations hr on hr.rule_id = r.rule_id "
                   . "inner join cfg_hosts h on hr.host_id = h.host_id and h.poller_id = :poller_id ";
        $stmt = $db->prepare($deleteHostSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        
        foreach($hostNotifications as $notification){
            NotificationRules::insert(array(
                                    'timeperiod_id' => $notification['timeperiod_id'],
                                    'rule_id' => $notification['rule_id'],
                                    'method_id' => $notification['method_id'],
                                    'host_id' => $notification['host_id'],
                                    'contact_id' => $notification['contact_id']
                                    )
                                );
        }

        $stmt = $db->prepare($serviceSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        $serviceNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        
        $deleteHostSql = "delete r from rt_notification_rules r "
                        . "inner join cfg_notification_rules_services_relations sr on sr.rule_id = r.rule_id "
                        . "inner join cfg_services s on sr.service_id = s.service_id "
                        . "inner join cfg_hosts_services_relations hsr on hsr.service_service_id = sr.service_id "
                        . "inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id ";
        $stmt = $db->prepare($deleteHostSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        
        
        foreach($serviceNotifications as $notification){
            NotificationRules::insert(array(
                                    'timeperiod_id' => $notification['timeperiod_id'],
                                    'rule_id' => $notification['rule_id'],
                                    'method_id' => $notification['method_id'],
                                    'service_id' => $notification['service_id'],
                                    'contact_id' => $notification['contact_id']
                                    )
                                );
        }
        
    
    }
    
    
    
    //put your code here
}
