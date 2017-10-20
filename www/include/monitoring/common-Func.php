<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($centreon)) {
    exit();
}

function getMyHostRow($host_id = null, $rowdata)
{
    global $pearDB;

    if (!$host_id) {
        exit();
    }
    while (1) {
        $DBRESULT = $pearDB->query("SELECT host_" . $rowdata .
            ", host_template_model_htm_id FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        if ($row["host_" . $rowdata]) {
            return $row["host_$rowdata"];
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function get_user_param($user_id, $pearDB)
{
    $list_param = array(
        'ack_sticky',
        'ack_notify',
        'ack_persistent',
        'ack_services',
        'force_active',
        'force_check'
    );
    $tab_row = array();
    foreach ($list_param as $param) {
        if (isset($_SESSION[$param])) {
            $tab_row[$param] = $_SESSION[$param];
        }
    }
    return $tab_row;
}

function set_user_param($user_id, $pearDB, $key, $value)
{
    $_SESSION[$key] = $value;
}

function get_notified_infos_for_host($hostId)
{
    global $pearDB;

    $loop = array();    
    $stack = array($hostId);
    $hosts = array();
    $results = array('contacts' => array(), 'contactGroups' => array(), 'enableNotif' => 2);
    $stopReading = array('contacts' => 0, 'contactGroups' => 0, 'notifParam' => 0,
        'contactAdditiveInheritance' => 0, 'cgAdditiveInheritance' => 0);
    
    while (($hostId = array_shift($stack))) {
        if (isset($loop[$hostId])) {
            continue;
        }
        $loop[$hostId] = 1;
        
        $DBRESULT = $pearDB->query("SELECT contact_additive_inheritance, cg_additive_inheritance,
                host_notifications_enabled
                FROM host WHERE host_id = " . $hostId);
        $notifParam = $DBRESULT->fetchRow();

        /*
         * Manage notification activation
         */
        if ($notifParam['host_notifications_enabled'] == 1 && $results['enableNotif'] == 2) {
            $results['enableNotif'] = 1;
            $stopReading['notifParam'] = 1;
        }
        if ($notifParam['host_notifications_enabled'] == 0 && $results['enableNotif'] == 2) {
            $results['enableNotif'] = 0;
            break;
        }
        
        /*
         * Manage contact inheritance
         */
        if ($stopReading['contacts'] == 0) {
            $contacts = getContactsForHost($hostId);
            $results['contacts'] = $results['contacts'] + $contacts;
            if ($notifParam['contact_additive_inheritance'] == 1) {
               $stopReading['contactAdditiveInheritance'] = 1;
            }
            if ($notifParam['contact_additive_inheritance'] == 0 && count($contacts) > 0) {
               $stopReading['contactAdditiveInheritance'] = 0;
            }

        }

        if (count($results['contacts']) > 0 && $stopReading['contactAdditiveInheritance'] == 0) {
            $stopReading['contacts'] = 1;
        }
        if ($stopReading['contactGroups'] == 0) {
            $contactGroups = getContactGroupsForHost($hostId);
            $results['contactGroups'] = $results['contactGroups'] + $contactGroups;
            if ($notifParam['cg_additive_inheritance'] == 1) {
               $stopReading['cgAdditiveInheritance'] = 1;
            }
            if ($notifParam['cg_additive_inheritance'] == 0 && count($contactGroups) > 0) {
               $stopReading['cgAdditiveInheritance'] = 0;
            }
        }

        if (count($results['contactGroups']) > 0 && $stopReading['cgAdditiveInheritance'] == 0) {
            $stopReading['contactGroups'] = 1;
        }
        
        if ($stopReading['contacts'] == 1 && $stopReading['contactGroups'] == 1 && $stopReading['notifParam'] == 1) {
            break;
        }
        
        /*
         * Manage template
         */
        $DBRESULT = $pearDB->query("SELECT host_tpl_id
                FROM host_template_relation
                WHERE host_host_id = " . $hostId . "
                ORDER BY `order` ASC");
        $hostsTpl = array();
        while (($row = $DBRESULT->fetchRow())) {
            $hostsTpl[] = $row['host_tpl_id'];
        }
        
        $stack = array_merge($hostsTpl, $stack);
    }

    if ($results['enableNotif'] == 0) {
        $results = array('contacts' => array(), 'contactGroups' => array());
    }

    if (version_compare(phpversion(), '5.4.0') >= 0){
        asort($results['contacts'], SORT_NATURAL | SORT_FLAG_CASE);
        asort($results['contactGroups'], SORT_NATURAL | SORT_FLAG_CASE);
    } else {
        natcasesort($results['contacts']);
        natcasesort($results['contactGroups']);
    }

    return $results;
}

function getContactgroupsForHost($hostId)
{
    global $pearDB;
    
    $contactGroups = array();
    $DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup cg, contactgroup_host_relation cghr
            WHERE cghr.host_host_id = " . $hostId . " AND cghr.contactgroup_cg_id = cg.cg_id
            AND cg.cg_activate = '1'");
    while (($row = $DBRESULT->fetchRow())) {
        $contactGroups[$row['cg_id']] = $row['cg_name'];
    }
    
    return $contactGroups;
}

function getContactsForHost($hostId)
{
    global $pearDB;    
    
    $contacts = array();
    $DBRESULT = $pearDB->query("SELECT c.contact_id, contact_name FROM contact c, contact_host_relation chr
            WHERE chr.host_host_id = " . $hostId . " AND chr.contact_id = c.contact_id
            AND c.contact_activate = '1' AND NOT c.contact_enable_notifications = '0'");
    while (($row = $DBRESULT->fetchRow())) {
        $contacts[$row['contact_id']] = $row['contact_name'];
    }
    
    return $contacts;
}

function get_notified_infos_for_service($serviceId, $hostId)
{
    global $pearDB;

    $loop = array();
    $results = array('contacts' => array(), 'contactGroups' => array(), 'enableNotif' => 2);
    $stopReading = array('contacts' => 0, 'contactGroups' => 0, 'notifParam' => 0,
        'contactAdditiveInheritance' => 0, 'cgAdditiveInheritance' => 0);
    $useOnlyContactsFromHost = 0;
    
    while (1) {
        if (isset($loop[$serviceId])) {
            break;
        }
        $loop[$serviceId] = 1;
        
        $DBRESULT = $pearDB->query("SELECT contact_additive_inheritance, service_notifications_enabled,
                cg_additive_inheritance, service_use_only_contacts_from_host, service_template_model_stm_id
                FROM service WHERE service_id = " . $serviceId);
        $notifParam = $DBRESULT->fetchRow();

        /*
         * Manage notification activation
         */
        if ($notifParam['service_notifications_enabled'] == 1 && $results['enableNotif'] == 2) {
            $results['enableNotif'] = 1;
            $stopReading['notifParam'] = 1;
        }
        if ($notifParam['service_notifications_enabled'] == 0 && $results['enableNotif'] == 2) {
            $results['enableNotif'] = 0;
            break;
        }
        
        /*
         * Manage contact inheritance
         */
        if (!is_null($notifParam['service_use_only_contacts_from_host']) && 
            $notifParam['service_use_only_contacts_from_host'] == 1) {
            $useOnlyContactsFromHost = 1;
        }

        if ($stopReading['contacts'] == 0 && $useOnlyContactsFromHost == 0) {
            $contacts = getContactsForService($serviceId);
            $results['contacts'] = $results['contacts'] + $contacts;
            if ($notifParam['contact_additive_inheritance'] == 1) {
               $stopReading['contactAdditiveInheritance'] = 1;
            }
            if ($notifParam['contact_additive_inheritance'] == 0 && count($contacts) > 0) {
               $stopReading['contactAdditiveInheritance'] = 0;
            }
        }

        if (count($results['contacts']) > 0 && $stopReading['contactAdditiveInheritance'] == 0 || 
            $useOnlyContactsFromHost == 1) {
            $stopReading['contacts'] = 1;
        }

        if ($stopReading['contactGroups'] == 0 && $useOnlyContactsFromHost == 0) {
            $contactGroups = getContactgroupsForService($serviceId);
            $results['contactGroups'] = $results['contactGroups'] + $contactGroups;
            if ($notifParam['cg_additive_inheritance'] == 1) {
               $stopReading['cgAdditiveInheritance'] = 1;
            }
            if ($notifParam['cg_additive_inheritance'] == 0 && count($contactGroups) > 0) {
               $stopReading['cgAdditiveInheritance'] = 0;
            }
        }

        if (count($results['contactGroups']) > 0 && $stopReading['cgAdditiveInheritance'] == 0 || 
            $useOnlyContactsFromHost == 1) {
            $stopReading['contactGroups'] = 1;
        }
        
        if ($stopReading['contacts'] == 1 && $stopReading['contactGroups'] == 1 && $stopReading['notifParam'] == 1) {
            break;
        }

        /*
         * Manage template
         */
        if (!isset($notifParam['service_template_model_stm_id']) || 
            is_null($notifParam['service_template_model_stm_id'])
            || $notifParam['service_template_model_stm_id'] == '') {
            break;
        }

        $serviceId = $notifParam['service_template_model_stm_id'];
    }

    if ($useOnlyContactsFromHost || 
        (count($results['contacts']) == 0) && (count($results['contactGroups']) == 0) 
            && $results['enableNotif'] != 0) {
        return get_notified_infos_for_host($hostId);
    }
   
    if ($results['enableNotif'] == 0) {
        $results = array('contacts' => array(), 'contactGroups' => array());
    }
         
    if (version_compare(phpversion(), '5.4.0') >= 0){
        asort($results['contacts'], SORT_NATURAL | SORT_FLAG_CASE);
        asort($results['contactGroups'], SORT_NATURAL | SORT_FLAG_CASE);
    } else {
        natcasesort($results['contacts']);
        natcasesort($results['contactGroups']);
    }

    return $results;
}

function getContactgroupsForService($serviceId)
{
    global $pearDB;

    $contactGroups = array();
    $DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup cg, contactgroup_service_relation cgsr
            WHERE cgsr.service_service_id = " . $serviceId . " AND cgsr.contactgroup_cg_id = cg.cg_id
            AND cg.cg_activate = '1'");
    while (($row = $DBRESULT->fetchRow())) {
        $contactGroups[$row['cg_id']] = $row['cg_name'];
    }
    
    return $contactGroups;
}

function getContactsForService($serviceId)
{
    global $pearDB;

    $contacts = array();
    $DBRESULT = $pearDB->query("SELECT c.contact_id , contact_name FROM contact c, contact_service_relation csr
            WHERE csr.service_service_id = " . $serviceId . " AND csr.contact_id = c.contact_id
            AND c.contact_activate = '1' AND NOT c.contact_enable_notifications = '0'");
    while (($row = $DBRESULT->fetchRow())) {
        $contacts[$row['contact_id']] = $row['contact_name'];
    }
    
    return $contacts;
}
