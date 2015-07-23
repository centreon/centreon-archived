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
