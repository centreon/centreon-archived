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

require_once dirname(__FILE__) . '/object.class.php';

abstract class AbstractHost extends AbstractObject {
    protected $attributes_select = '
        host_id,
        command_command_id as check_command_id,
        command_command_id_arg1 as check_command_arg,
        timeperiod_tp_id as check_period_id,
        timeperiod_tp_id2 as notification_period_id,
        command_command_id2 as event_handler_id,
        command_command_id_arg2 as event_handler_arg,
        host_name,
        host_alias as alias,
        host_address as address,
        display_name,
        host_max_check_attempts as max_check_attempts,
        host_check_interval as check_interval,
        host_retry_check_interval as retry_interval,
        host_active_checks_enabled as active_checks_enabled,
        host_passive_checks_enabled as passive_checks_enabled,
        initial_state,
        host_obsess_over_host as obsess_over_host,
        host_check_freshness as check_freshness,
        host_freshness_threshold as freshness_threshold,
        host_event_handler_enabled as event_handler_enabled,
        host_low_flap_threshold as low_flap_threshold,
        host_high_flap_threshold as high_flap_threshold,
        host_flap_detection_enabled as flap_detection_enabled,
        flap_detection_options,
        host_process_perf_data as process_perf_data,
        host_retain_status_information as retain_status_information,
        host_retain_nonstatus_information as retain_nonstatus_information,
        host_notification_interval as notification_interval,
        host_notification_options as notification_options,
        host_notifications_enabled as notifications_enabled,
        contact_additive_inheritance,
        cg_additive_inheritance,
        host_first_notification_delay as first_notification_delay,
        host_recovery_notification_delay as recovery_notification_delay,
        host_stalking_options as stalking_options,
        host_snmp_community,
        host_snmp_version,
        host_register as register,
        ehi_notes as notes,
        ehi_notes_url as notes_url,
        ehi_action_url as action_url,
        ehi_icon_image as icon_image_id,
        ehi_icon_image_alt as icon_image_alt,
        ehi_vrml_image as vrml_image_id,
        ehi_statusmap_image as statusmap_image_id,
        host_location,
        host_acknowledgement_timeout as acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'host_name',
        'alias',
        'address',
        'display_name',
        'contacts',
        'contact_groups',
        'check_command',
        'check_period',
        'notification_period',
        'event_handler',
        'max_check_attempts',
        'check_interval',
        'retry_interval',
        'initial_state',
        'freshness_threshold',
        'low_flap_threshold',
        'high_flap_threshold',
        'flap_detection_options',
        'notification_interval',
        'notification_options',
        'first_notification_delay',
        'recovery_notification_delay',
        'stalking_options',
        'register',
        'notes',
        'notes_url',
        'action_url',
        'icon_image',
        'icon_image_alt',
        'vrml_image',
        'statusmap_image',
        'timezone',
        'acknowledgement_timeout'
    );
    protected $attributes_default = array(
        'active_checks_enabled',
        'passive_checks_enabled',
        'event_handler_enabled',
        'flap_detection_enabled',
        'notifications_enabled',
        'obsess_over_host',
        'check_freshness',
        'process_perf_data',
        'retain_status_information',
        'retain_nonstatus_information',
    );
    protected $attributes_array = array(
        'use',
        'parents',
    );
    protected $attributes_hash = array(
        'macros'
    );
    protected $loop_htpl = array(); # To be reset
    protected $stmt_macro = null;
    protected $stmt_htpl = null;
    protected $stmt_contact = null;
    protected $stmt_cg = null;
    
    protected function getImages(&$host) {
        $media = Media::getInstance();
        if (!isset($host['icon_image'])) {
            $host['icon_image'] = $media->getMediaPathFromId($host['icon_image_id']);
        }
        if (!isset($host['vrml_image'])) {
            $host['vrml_image'] = $media->getMediaPathFromId($host['vrml_image_id']);
        }
        if (!isset($host['statusmap_image'])) {
            $host['statusmap_image'] = $media->getMediaPathFromId($host['statusmap_image_id']);
        }
    }
    
    protected function getMacros(&$host) {
        if (isset($host['macros'])) {
            return 1;
        }
        
        if (is_null($this->stmt_macro)) {
            $this->stmt_macro = $this->backend_instance->db->prepare("SELECT 
              host_macro_name, host_macro_value
            FROM on_demand_macro_host
            WHERE host_host_id = :host_id
            ");
        }
        $this->stmt_macro->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_macro->execute();
        $macros = $this->stmt_macro->fetchAll(PDO::FETCH_ASSOC);
        
        $host['macros'] = array();
        foreach ($macros as $macro) {
            $host['macros'][preg_replace('/\$_HOST(.*)\$/', '_$1', $macro['host_macro_name'])] = $macro['host_macro_value'];
        }
        if (!is_null($host['host_snmp_community']) && $host['host_snmp_community'] != '') {
            $host['macros']['_SNMPCOMMUNITY'] = $host['host_snmp_community'];
        }
        if (!is_null($host['host_snmp_version']) && $host['host_snmp_version'] != 0) {
            $host['macros']['_SNMPVERSION'] = $host['host_snmp_version'];
        }
        
        return 0;
    }
    
    protected function getHostTemplates(&$host) {
        if (!isset($host['htpl'])) {
            if (is_null($this->stmt_htpl)) {
                $this->stmt_htpl = $this->backend_instance->db->prepare("SELECT 
                    host_tpl_id
                FROM host_template_relation
                WHERE host_host_id = :host_id
                ORDER BY `order` ASC
                ");
            }            
            $this->stmt_htpl->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_htpl->execute();
            $host['htpl'] = $this->stmt_htpl->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $host_template = HostTemplate::getInstance();
        $host['use'] = array();
        foreach ($host['htpl'] as $template_id) {
            $host['use'][] = $host_template->generateFromHostId($template_id);
        }
    }
    
    protected function getContacts(&$host) {
        if (!isset($host['contacts_cache'])) {
            if (is_null($this->stmt_contact)) {
                $this->stmt_contact = $this->backend_instance->db->prepare("SELECT 
                    contact_id
                FROM contact_host_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_contact->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_contact->execute();
            $host['contacts_cache'] = $this->stmt_contact->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $contact = Contact::getInstance();
        $contact_result = '';
        $contact_result_append = '';
        foreach ($host['contacts_cache'] as $contact_id) {
            $tmp = $contact->generateFromContactId($contact_id);
            if (!is_null($tmp)) {
                $contact_result .= $contact_result_append . $tmp;
                $contact_result_append = ',';
            }
        }
        
        if ($contact_result != '') {
            $host['contacts'] = $contact_result;
            if (!is_null($host['contact_additive_inheritance']) && $host['contact_additive_inheritance'] == 1) {
                $host['contacts'] = '+' . $host['contacts'];
            }
        }
    }
    
    protected function getContactGroups(&$host) {
        if (!isset($host['contact_groups_cache'])) {
            if (is_null($this->stmt_cg)) {
                $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    contactgroup_cg_id
                FROM contactgroup_host_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_cg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_cg->execute();
            $host['contact_groups_cache'] = $this->stmt_cg->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $cg = Contactgroup::getInstance();
        $cg_result = '';
        $cg_result_append = '';
        foreach ($host['contact_groups_cache'] as $cg_id) {
            $tmp = $cg->generateFromCgId($cg_id);
            if (!is_null($tmp)) {
                $cg_result .= $cg_result_append . $tmp;
                $cg_result_append = ',';
            }
        }
        
        if ($cg_result != '') {
            $host['contact_groups'] = $cg_result;
            if (!is_null($host['cg_additive_inheritance']) && $host['cg_additive_inheritance'] == 1) {
                $host['contact_groups'] = '+' . $host['contact_groups'];
            }
        }
    }
    
    public function isHostTemplate($host_id, $host_tpl_id) {
        $loop = array();
        $stack = array();
        
        $hosts_tpl = HostTemplate::getInstance()->hosts;
        $stack = $this->hosts[$host_id]['htpl'];
        while (($host_id = array_shift($stack))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if ($host_id == $host_tpl_id) {
                return 1;
            }
            $stack = array_merge($hosts_tpl[$host_id]['htpl'], $stack);
        }
        
        return 0;
    }
    
    protected function findCommandName($host_id, $command_label) {
        $loop = array();
        $stack = array();
        
        $hosts_tpl = HostTemplate::getInstance()->hosts;
        $stack = $this->hosts[$host_id]['htpl'];
        while (($host_id = array_shift($stack))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if (isset($hosts_tpl[$host_id][$command_label]) && !is_null($hosts_tpl[$host_id][$command_label])) {
                return $hosts_tpl[$host_id][$command_label];
            }
            $stack = array_merge($hosts_tpl[$host_id]['htpl'], $stack);
        }
        
        return null;
    }
    
    protected function getHostCommand(&$host, $result_name, $command_id_label, $command_arg_label) {
        $command_name = Command::getInstance()->generateFromCommandId($host[$command_id_label]);
        $command_arg = '';
        
        if (isset($host[$result_name])) {
            return 1;
        }
        $host[$result_name] = $command_name;
        if (isset($host[$command_arg_label]) && !is_null($host[$command_arg_label]) && $host[$command_arg_label] != '') {
            $command_arg = $host[$command_arg_label];
            if (is_null($command_name)) {
                # Find Command Name in templates
                $command_name = $this->findCommandName($host['host_id'], $result_name);
                # Can have 'args after'. We replace
                if (!is_null($command_name)) {
                    $command_name = preg_replace('/!.*/', '', $command_name);
                    $host[$result_name] = $command_name . $command_arg;
                }
            } else {
                $host[$result_name] = $command_name . $command_arg;
            }
        }
        
        return 0;
    }
    
    protected function getHostCommands(&$host) {
        $this->getHostCommand($host, 'check_command', 'check_command_id', 'check_command_arg');        
        $this->getHostCommand($host, 'event_handler', 'event_handler_id', 'event_handler_arg');
    }
    
    protected function getHostPeriods(&$host) {
        $period = Timeperiod::getInstance();
        $host['check_period'] = $period->generateFromTimeperiodId($host['check_period_id']);
        $host['notification_period'] = $period->generateFromTimeperiodId($host['notification_period_id']);
    }
    
    public function getString($host_id, $attr) {
        if (isset($this->hosts[$host_id][$attr])) {
            return $this->hosts[$host_id][$attr];
        }
        return null;
    }
}
