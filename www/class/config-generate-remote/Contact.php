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

use PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class Contact extends AbstractObject
{
    protected $useCache = 1;
    private $doneCache = 0;
    private $contactsServiceLinkedCache = [];
    protected $contactsCache = [];
    protected $contacts = [];
    protected $table = 'contact';
    protected $generateFilename = 'contacts.infile';
    protected $objectName = 'contact';
    protected $attributesSelect = '
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
    protected $attributesWrite = [
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
    protected $stmtContact = null;
    protected $stmtCommands = ['host' => null, 'service' => null];
    protected $stmtContactService = null;

    /**
     * Store contacts in cache
     *
     * @return void
     */
    private function getContactCache()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT $this->attributesSelect
            FROM contact
            WHERE contact_activate = '1'"
        );
        $stmt->execute();
        $this->contactsCache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Store contacts linked to a service in cache
     *
     * @return void
     */
    private function getContactForServiceCache()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT contact_id, service_service_id
            FROM contact_service_relation"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->contactsServiceLinkedCache[$value['service_service_id']])) {
                $this->contactsServiceLinkedCache[$value['service_service_id']][] = $value['contact_id'];
            } else {
                $this->contactsServiceLinkedCache[$value['service_service_id']] = [$value['contact_id']];
            }
        }
    }

    /**
     * Get contact information linked to a service id
     *
     * @param integer $serviceId
     * @return array
     */
    public function getContactForService(int $serviceId): array
    {
        $this->buildCache();

        // Get from the cache
        if (isset($this->contactsServiceLinkedCache[$serviceId])) {
            return $this->contactsServiceLinkedCache[$serviceId];
        }
        if ($this->doneCache == 1) {
            return [];
        }

        if (is_null($this->stmtContactService)) {
            $this->stmtContactService = $this->backendInstance->db->prepare(
                "SELECT contact_id
                FROM contact_service_relation
                WHERE service_service_id = :service_id"
            );
        }

        $this->stmtContactService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtContactService->execute();
        $this->contactsServiceLinkedCache[$serviceId] = $this->stmtContactService->fetchAll(PDO::FETCH_COLUMN);
        return $this->contactsServiceLinkedCache[$serviceId];
    }

    /**
     * Store contact in contacts cache
     *
     * @param int $contactId
     * @return void
     */
    protected function getContactFromId(int $contactId)
    {
        if (is_null($this->stmtContact)) {
            $this->stmtContact = $this->backendInstance->db->prepare(
                "SELECT $this->attributesSelect
                FROM contact
                WHERE contact_id = :contact_id AND contact_activate = '1'"
            );
        }
        $this->stmtContact->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
        $this->stmtContact->execute();
        $results = $this->stmtContact->fetchAll(PDO::FETCH_ASSOC);
        $this->contacts[$contactId] = array_pop($results);
        if (is_null($this->contacts[$contactId])) {
            return 1;
        }
    }

    /**
     * Generate notification commands linked to contact id
     *
     * @param integer $contactId
     * @param string $label
     * @param object $instance
     * @return void|null
     */
    protected function getContactNotificationCommands(int $contactId, string $label, object $instance)
    {
        // avoid sql injection with label
        if (in_array($label, ['host', 'service'])) {
            return null;
        }

        if (!isset($this->contacts[$contactId][$label . '_commands_cache'])) {
            if (is_null($this->stmtCommands[$label])) {
                $this->stmtCommands[$label] = $this->backendInstance->db->prepare(
                    "SELECT command_command_id
                    FROM contact_" . $label . "commands_relation
                    WHERE contact_contact_id = :contact_id"
                );
            }
            $this->stmtCommands[$label]->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
            $this->stmtCommands[$label]->execute();
            $this->contacts[$contactId][$label . '_commands_cache'] =
                $this->stmtCommands[$label]->fetchAll(PDO::FETCH_COLUMN);
        }

        $command = Command::getInstance($this->dependencyInjector);
        foreach ($this->contacts[$contactId][$label . '_commands_cache'] as $commandId) {
            $command->generateFromCommandId($commandId);
            $instance->addRelation($contactId, $commandId);
        }
    }

    /**
     * Build contact cache
     *
     * @return integer|null
     */
    protected function buildCache(): ?int
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $this->getContactCache();
        $this->getContactForServiceCache();
        $this->doneCache = 1;
    }

    /**
     * Generation configuration from a contact id
     *
     * @param null|integer $contactId
     * @return string|null the contact name or alias
     */
    public function generateFromContactId(?int $contactId): ?string
    {
        if (is_null($contactId)) {
            return null;
        }

        $this->buildCache();

        if ($this->useCache == 1) {
            if (!isset($this->contactsCache[$contactId])) {
                return null;
            }
            $this->contacts[$contactId] = &$this->contactsCache[$contactId];
        } elseif (!isset($this->contacts[$contactId])) {
            $this->getContactFromId($contactId);
        }

        if (is_null($this->contacts[$contactId])) {
            return null;
        }
        if ($this->checkGenerate($contactId)) {
            return $this->contacts[$contactId]['contact_register'] == 1
                ? $this->contacts[$contactId]['contact_name']
                : $this->contacts[$contactId]['contact_alias'];
        }

        $this->generateFromContactId($this->contacts[$contactId]['contact_template_id']);
        $this->getContactNotificationCommands(
            $contactId,
            'host',
            Relations\ContactHostCommandsRelation::getInstance($this->dependencyInjector)
        );
        $this->getContactNotificationCommands(
            $contactId,
            'service',
            Relations\ContactServiceCommandsRelation::getInstance($this->dependencyInjector)
        );

        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($this->contacts[$contactId]['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($this->contacts[$contactId]['timeperiod_tp_id2']);

        $this->contacts[$contactId]['contact_id'] = $contactId;
        $this->generateObjectInFile($this->contacts[$contactId], $contactId);
        return $this->contacts[$contactId]['contact_register'] == 1
            ? $this->contacts[$contactId]['contact_name']
            : $this->contacts[$contactId]['contact_alias'];
    }
}
