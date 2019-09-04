<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace ConfigGenerateRemote;

use \PDO;

class Contact extends AbstractObject
{
    protected $use_cache = 1;
    private $done_cache = 0;
    private $contacts_service_linked_cache = [];
    protected $contacts_cache = [];
    protected $contacts = [];
    protected $table = 'contact';
    protected $generate_filename = 'contacts.infile';
    protected $object_name = 'contact';
    protected $attributes_select = '
        contact_id,
        contact_template_id,
        timeperiod_tp_id,
        timeperiod_tp_id2,
        contact_name,
        contact_alias,
        contact_host_notification_options,
        contact_service_notification_options,
        contact_email,
        contact_enable_notifications,
        contact_register,
        contact_location,
        reach_api,
        reach_api_rt
    ';
    protected $attributes_write = [
        'contact_id',
        'contact_template_id',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'contact_name',
        'contact_alias',
        'contact_email',
        'contact_register',
        'contact_location',
        'contact_enable_notifications',
        'reach_api',
        'reach_api_rt',
        'contact_register'
    ];
    protected $stmt_contact = null;
    protected $stmt_commands = ['host' => null, 'service' => null];
    protected $stmt_contact_service = null;

    private function getContactCache()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contact
                WHERE contact_activate = '1'
        ");
        $stmt->execute();
        $this->contacts_cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    private function getContactForServiceCache()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    contact_id, service_service_id
                FROM contact_service_relation
        ");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->contacts_service_linked_cache[$value['service_service_id']])) {
                $this->contacts_service_linked_cache[$value['service_service_id']][] = $value['contact_id'];
            } else {
                $this->contacts_service_linked_cache[$value['service_service_id']] = [$value['contact_id']];
            }
        }
    }

    public function getContactForService($service_id)
    {
        $this->buildCache();

        # Get from the cache
        if (isset($this->contacts_service_linked_cache[$service_id])) {
            return $this->contacts_service_linked_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return [];
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

    protected function getContactFromId($contact_id)
    {
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
    }

    protected function getContactNotificationCommands($contact_id, $label, $instance)
    {
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
            $this->contacts[$contact_id][$label . '_commands_cache'] =
                $this->stmt_commands[$label]->fetchAll(PDO::FETCH_COLUMN);
        }

        $command = Command::getInstance($this->dependencyInjector);
        foreach ($this->contacts[$contact_id][$label . '_commands_cache'] as $command_id) {
            $command->generateFromCommandId($command_id);
            $instance->addRelation($contact_id, $command_id);
        }
    }

    protected function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->getContactCache();
        $this->getContactForServiceCache();
        $this->done_cache = 1;
    }

    public function generateFromContactId($contact_id)
    {
        if (is_null($contact_id)) {
            return null;
        }

        $this->buildCache();

        if ($this->use_cache == 1) {
            if (!isset($this->contacts_cache[$contact_id])) {
                return null;
            }
            $this->contacts[$contact_id] = &$this->contacts_cache[$contact_id];
        } elseif (!isset($this->contacts[$contact_id])) {
            $this->getContactFromId($contact_id);
        }

        if (is_null($this->contacts[$contact_id])) {
            return null;
        }
        if ($this->checkGenerate($contact_id)) {
            return $this->contacts[$contact_id]['contact_register'] == 1
                ? $this->contacts[$contact_id]['contact_name']
                : $this->contacts[$contact_id]['contact_alias'];
        }

        $this->generateFromContactId($this->contacts[$contact_id]['contact_template_id']);
        $this->getContactNotificationCommands($contact_id,
            'host',
            contactHostcommandsRelation::getInstance($this->dependencyInjector));
        $this->getContactNotificationCommands($contact_id,
            'service',
            contactServicecommandsRelation::getInstance($this->dependencyInjector));

        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($this->contacts[$contact_id]['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($this->contacts[$contact_id]['timeperiod_tp_id2']);

        $this->contacts[$contact_id]['contact_id'] = $contact_id;
        $this->generateObjectInFile($this->contacts[$contact_id], $contact_id);
        return $this->contacts[$contact_id]['contact_register'] == 1
            ? $this->contacts[$contact_id]['contact_name']
            : $this->contacts[$contact_id]['contact_alias'];
    }

    public function isTemplate($contact_id)
    {
        if ($this->contacts[$contact_id]['contact_register'] == 0) {
            return 1;
        }
        return 0;
    }
}
