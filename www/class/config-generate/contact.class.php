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

class Contact extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    private $contacts_service_linked_cache = array();
    protected $contacts_cache = array();
    protected $contacts = array();
    protected $generate_filename = 'contacts.cfg';
    protected $object_name = 'contact';
    protected $attributes_select = '
        contact_id,
        contact_template_id,
        timeperiod_tp_id as host_notification_period_id,
        timeperiod_tp_id2 as service_notification_period_id,
        contact_name,
        contact_alias as alias,
        contact_host_notification_options as host_notification_options,
        contact_service_notification_options as service_notification_options,
        contact_email as email,
        contact_pager as pager,
        contact_address1 as address1,
        contact_address2 as address2,
        contact_address3 as address3,
        contact_address4 as address4,
        contact_address5 as address5,
        contact_address6 as address6,
        contact_enable_notifications as enable_notifications,
        contact_register as register,
        contact_location
    ';
    protected $attributes_write = array(
        'name',
        'contact_name',
        'alias',
        'email',
        'pager',
        'address1',
        'address2',
        'address3',
        'address4',
        'address5',
        'address6',
        'host_notification_period',
        'service_notification_period',
        'host_notification_options',
        'service_notification_options',
        'register',
        'timezone'
    );
    protected $attributes_default = array(
        'host_notifications_enabled',
        'service_notifications_enabled',
    );
    protected $attributes_array = array(
        'host_notification_commands',
        'service_notification_commands',
        'use',
    );
    protected $stmt_contact = null;
    protected $stmt_commands = array('host' => null, 'service' => null);
    protected $stmt_contact_service = null;
    
    private function getContactCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contact
                WHERE contact_activate = '1'
        ");
        $stmt->execute();
        $this->contacts_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    private function getContactForServiceCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    contact_id, service_service_id
                FROM contact_service_relation
        ");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->contacts_service_linked_cache[$value['service_service_id']])) {
                $this->contacts_service_linked_cache[$value['service_service_id']][] = $value['contact_id'];
            } else {
                $this->contacts_service_linked_cache[$value['service_service_id']] = array($value['contact_id']);
            }
        }
    }
    
    public function getContactForService($service_id) {
        $this->buildCache();
        
        # Get from the cache
        if (isset($this->contacts_service_linked_cache[$service_id])) {
            return $this->contacts_service_linked_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return array();
        }
        
        if (is_null($this->stmt_contact_service)) {
            $this->stmt_contact_service = $this->backend_instance->db->prepare("SELECT 
                    contact_id
                FROM contact_service_relation
                WHERE service_service_id = :service_id
            ");
        }

        $this->stmt_contact_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_contact_service->execute();
        $this->contacts_service_linked_cache[$service_id] = $this->stmt_contact_service->fetchAll(PDO::FETCH_COLUMN);
        return $this->contacts_service_linked_cache[$service_id];
    }
    
    protected function getContactFromId($contact_id) {
        if (is_null($this->stmt_contact)) {
            $this->stmt_contact = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contact
                WHERE contact_id = :contact_id AND contact_activate = '1'
            ");
        }
        $this->stmt_contact->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $this->stmt_contact->execute();
        $results = $this->stmt_contact->fetchAll(PDO::FETCH_ASSOC);
        $this->contacts[$contact_id] = array_pop($results);
        if (is_null($this->contacts[$contact_id])) {
            return 1;
        }
        $this->contacts[$contact_id]['host_notifications_enabled'] = $this->contacts[$contact_id]['enable_notifications'];
        $this->contacts[$contact_id]['service_notifications_enabled'] = $this->contacts[$contact_id]['enable_notifications'];
    }
    
    protected function getContactNotificationCommands($contact_id, $label) {
        if (!isset($this->contacts[$contact_id][$label . '_commands_cache'])) {
            if (is_null($this->stmt_commands[$label])) {
                $this->stmt_commands[$label] = $this->backend_instance->db->prepare("SELECT 
                        command_command_id
                    FROM contact_" . $label . "commands_relation
                    WHERE contact_contact_id = :contact_id
                ");
            }
            $this->stmt_commands[$label]->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
            $this->stmt_commands[$label]->execute();
            $this->contacts[$contact_id][$label . '_commands_cache'] = $this->stmt_commands[$label]->fetchAll(PDO::FETCH_COLUMN);
        }
                
        $command = Command::getInstance();
        $this->contacts[$contact_id][$label . '_notification_commands'] = array();
        foreach ($this->contacts[$contact_id][$label . '_commands_cache'] as $command_id) {
            $this->contacts[$contact_id][$label . '_notification_commands'][] = $command->generateFromCommandId($command_id);
        }        
    }
    
    protected function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->getContactCache();
        $this->getContactForServiceCache();
        $this->done_cache = 1;
    }
    
    public function generateFromContactId($contact_id) {        
        if (is_null($contact_id)) {
            return null;
        }
        
        $this->buildCache();
        
        if ($this->use_cache == 1) {
            if (!isset($this->contacts_cache[$contact_id])) {
                return null;
            }
            $this->contacts[$contact_id] = &$this->contacts_cache[$contact_id];
        } else if (!isset($this->contacts[$contact_id])) {
            $this->getContactFromId($contact_id);
        }
        
        if (is_null($this->contacts[$contact_id])) {
            return null;
        }
        if ($this->checkGenerate($contact_id)) {
            return $this->contacts[$contact_id]['register'] == 1 ? $this->contacts[$contact_id]['contact_name'] : $this->contacts[$contact_id]['name'];
        }
        
        if ($this->contacts[$contact_id]['register'] == 0 && !isset($this->contacts[$contact_id]['name'])) {
            $this->contacts[$contact_id]['name'] = $this->contacts[$contact_id]['contact_name'];
            unset($this->contacts[$contact_id]['contact_name']);
        }
        
        $this->contacts[$contact_id]['use'] = array();
        $this->contacts[$contact_id]['use'][] = $this->generateFromContactId($this->contacts[$contact_id]['contact_template_id']);
        $this->getContactNotificationCommands($contact_id, 'host');
        $this->getContactNotificationCommands($contact_id, 'service');        
        $period = Timeperiod::getInstance();
        $this->contacts[$contact_id]['host_notification_period'] = $period->generateFromTimeperiodId($this->contacts[$contact_id]['host_notification_period_id']);
        $this->contacts[$contact_id]['service_notification_period'] = $period->generateFromTimeperiodId($this->contacts[$contact_id]['service_notification_period_id']);
        $this->contacts[$contact_id]['host_notifications_enabled'] = $this->contacts[$contact_id]['enable_notifications'];
        $this->contacts[$contact_id]['service_notifications_enabled'] = $this->contacts[$contact_id]['enable_notifications'];
        $oTimezone = Timezone::getInstance();
        $sTimezone = $oTimezone->getTimezoneFromId($this->contacts[$contact_id]['contact_location']);
        if (!is_null($sTimezone)) {
            $this->contacts[$contact_id]['timezone'] = ":". $sTimezone;
        }
        
        $this->generateObjectInFile($this->contacts[$contact_id], $contact_id);
        return $this->contacts[$contact_id]['register'] == 1 ? $this->contacts[$contact_id]['contact_name'] : $this->contacts[$contact_id]['name'];
    }
    
    public function isTemplate($contact_id)  {
        if ($this->contacts[$contact_id]['register'] == 0) {
            return 1;
        }
        return 0;
    }
}
