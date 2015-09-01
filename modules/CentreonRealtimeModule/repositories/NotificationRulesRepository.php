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
    
    public static function setRulesHost($poller_id){
        $db = Di::getDefault()->get('db_centreon'); 
        $deleteSql = "delete r from rt_notification_rules r , ("
                    . " select r.rule_id from rt_notification_rules r "
                    . " inner join cfg_notification_rules_hosts_relations hr on hr.rule_id = r.rule_id "
                    . " inner join cfg_hosts h on hr.host_id = h.host_id and h.poller_id = :poller_id "
                    . " UNION "
                    . " select r.rule_id from rt_notification_rules r "
                    . " inner join cfg_notification_rules_tags_relations rtr on rtr.rule_id = r.rule_id and rtr.resource_type = '1' "
                    . " inner join cfg_tags_hosts th on rtr.tag_id = th.tag_id "
                    . " inner join cfg_hosts h on h.host_id = th.resource_id and h.poller_id = :poller_id "
                    . " ) r2 "
                    . "where r.rule_id = r2.rule_id";
        $stmt = $db->prepare($deleteSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sqlFindHost =  "select r.*, h.*  "
                    . "from  cfg_notification_rules r "
                    . "inner join cfg_notification_rules_hosts_relations hr on hr.rule_id = r.rule_id "
                    . "inner join cfg_hosts h on hr.host_id = h.host_id and h.poller_id = :poller_id "
                    . " UNION "
                    . " select r.*, h.* "
                    . " from  cfg_notification_rules r "
                    . " inner join cfg_notification_rules_tags_relations rtr on rtr.rule_id = r.rule_id and rtr.resource_type = '1' "
                    . " inner join cfg_tags_hosts th on rtr.tag_id = th.tag_id "
                    . " inner join cfg_hosts h on h.host_id = th.resource_id and h.poller_id = :poller_id ";
        
        $stmt = $db->prepare($sqlFindHost);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        $hostNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $rulesFinal = array();
        $rulesId = array();
        $rulesId[] = -1;
        foreach($hostNotifications as $rules){
            $rulesId[] = $rules['rule_id'];
            $rulesFinal[$rules['rule_id']][$rules['host_id']] = array();
        }
        
        $sqlFindContact = " select c.*, r.* "
                        . " from cfg_contacts c "
                        . " inner join cfg_tags_contacts tc on tc.resource_id = c.contact_id "
                        . " inner join cfg_notification_rules_tags_relations rtr on rtr.tag_id = tc.tag_id and rtr.resource_type = '3' "
                        . " inner join cfg_notification_rules r on rtr.rule_id = r.rule_id "
                        . " where r.rule_id in (".implode(',',$rulesId).") "
                        . " UNION "
                        . " select c.*, r.* "
                        . " from cfg_contacts c "
                        . " inner join cfg_notification_rules_contacts_relations cr on c.contact_id = cr.contact_id "
                        . " inner join cfg_notification_rules r on r.rule_id = cr.rule_id "
                        . " where r.rule_id in (".implode(',',$rulesId).") ";
        $stmt = $db->prepare($sqlFindContact);
        $stmt->execute();
        $contactNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($contactNotifications as $contactNotification){
            foreach($rulesFinal[$contactNotification['rule_id']] as $host_id=>$hostToLink){
                $rulesFinal[$contactNotification['rule_id']][$host_id][$contactNotification['contact_id']] = $contactNotification;
            }
        }

        foreach($rulesFinal as $rule_id=>$rule){
            foreach($rule as $host_id=>$host){
                foreach($host as $contact_id=>$notification){
                    NotificationRules::insert(array(
                        'timeperiod_id' => $notification['timeperiod_id'],
                        'rule_id' => $rule_id,
                        'method_id' => $notification['method_id'],
                        'host_id' => $host_id,
                        'contact_id' => $contact_id
                        )
                    );
                }
            }
        }
    }
    
    public static function setRulesService($poller_id){
        $db = Di::getDefault()->get('db_centreon'); 
        
        $deleteSql = "delete r from rt_notification_rules r , ("
            . " select r.rule_id from rt_notification_rules r "
            . " inner join cfg_notification_rules_services_relations sr on sr.rule_id = r.rule_id "
            . " inner join cfg_services s on sr.service_id = s.service_id "
            . " inner join cfg_hosts_services_relations hsr on hsr.service_service_id = sr.service_id "
            . " inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id "
            . " UNION "
            . " select r.rule_id from rt_notification_rules r "
            . " inner join cfg_notification_rules_tags_relations rtr on rtr.rule_id = r.rule_id and rtr.resource_type = '2' "
            . " inner join cfg_tags_services ts on rtr.tag_id = ts.tag_id "
            . " inner join cfg_services s on ts.resource_id = s.service_id "
            . " inner join cfg_hosts_services_relations hsr on hsr.service_service_id = s.service_id "
            . " inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id "
            . " ) r2 "
            . " where r.rule_id = r2.rule_id";
        $stmt = $db->prepare($deleteSql);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sqlFindService =  "select r.*, s.*  "
            . " from  cfg_notification_rules r "
            . " inner join cfg_notification_rules_services_relations sr on sr.rule_id = r.rule_id "
            . " inner join cfg_services s on sr.service_id = s.service_id "
            . " inner join cfg_hosts_services_relations hsr on hsr.service_service_id = sr.service_id "
            . " inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id "
            . " UNION "
            . " select r.*, s.* "
            . " from  cfg_notification_rules r "
            . " inner join cfg_notification_rules_tags_relations rtr on rtr.rule_id = r.rule_id and rtr.resource_type = '2' "
            . " inner join cfg_tags_services ts on rtr.tag_id = ts.tag_id "
            . " inner join cfg_services s on ts.resource_id = s.service_id "
            . " inner join cfg_hosts_services_relations hsr on hsr.service_service_id = s.service_id "
            . " inner join cfg_hosts h on hsr.host_host_id = h.host_id and h.poller_id = :poller_id ";
        
        $stmt = $db->prepare($sqlFindService);    
        $stmt->bindParam("poller_id", $poller_id, \PDO::PARAM_INT);
        $stmt->execute();
        $serviceNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $rulesFinal = array();
        $rulesId = array();
        $rulesId[] = -1;
        foreach($serviceNotifications as $rules){
            $rulesId[] = $rules['rule_id'];
            $rulesFinal[$rules['rule_id']][$rules['service_id']] = array();
        }
        
        $sqlFindContact = " select c.*, r.* "
                . " from cfg_contacts c "
                . " inner join cfg_tags_contacts tc on tc.resource_id = c.contact_id "
                . " inner join cfg_notification_rules_tags_relations rtr on rtr.tag_id = tc.tag_id and rtr.resource_type = '3' "
                . " inner join cfg_notification_rules r on rtr.rule_id = r.rule_id "
                . " where r.rule_id in (".implode(',',$rulesId).") "
                . " UNION "
                . " select c.*, r.* "
                . " from cfg_contacts c "
                . " inner join cfg_notification_rules_contacts_relations cr on c.contact_id = cr.contact_id "
                . " inner join cfg_notification_rules r on r.rule_id = cr.rule_id "
                . " where r.rule_id in (".implode(',',$rulesId).") ";
        $stmt = $db->prepare($sqlFindContact);
        $stmt->execute();
        $contactNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($contactNotifications as $contactNotification){
            foreach($rulesFinal[$contactNotification['rule_id']] as $service_id=>$serviceToLink){
                $rulesFinal[$contactNotification['rule_id']][$service_id][$contactNotification['contact_id']] = $contactNotification;
            }
        }
        
        foreach($rulesFinal as $rule_id=>$rule){
            foreach($rule as $service_id=>$service){
                foreach($service as $contact_id=>$notification){
                    NotificationRules::insert(array(
                        'timeperiod_id' => $notification['timeperiod_id'],
                        'rule_id' => $rule_id,
                        'method_id' => $notification['method_id'],
                        'service_id' => $service_id,
                        'contact_id' => $contact_id
                        )
                    );
                }
            }
        }
    }
    
    
    public static function setRealTimeNotifications($poller_id){
        
        self::setRulesHost($poller_id);
        self::setRulesService($poller_id);
    }

}
