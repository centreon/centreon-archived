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

namespace ConfigGenerateRemote\Abstracts;

use \PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;
use ConfigGenerateRemote\Command;
use ConfigGenerateRemote\Contact;
use ConfigGenerateRemote\ContactGroup;
use ConfigGenerateRemote\HostTemplate;
use ConfigGenerateRemote\Media;
use ConfigGenerateRemote\TimePeriod;
use ConfigGenerateRemote\Relations\ContactHostRelation;
use ConfigGenerateRemote\Relations\ContactGroupHostRelation;
use ConfigGenerateRemote\Relations\HostTemplateRelation;
use ConfigGenerateRemote\Relations\HostPollerRelation;
use ConfigGenerateRemote\Relations\MacroHost;

abstract class AbstractHost extends AbstractObject
{
    protected $attributesSelect = '
        host_id,
        command_command_id,
        command_command_id_arg1,
        timeperiod_tp_id,
        timeperiod_tp_id2,
        command_command_id2,
        command_command_id_arg2,
        host_name,
        host_alias,
        host_address,
        display_name,
        host_max_check_attempts,
        host_check_interval,
        host_retry_check_interval,
        host_active_checks_enabled,
        host_passive_checks_enabled,
        host_event_handler_enabled,
        host_notification_interval,
        host_notification_options,
        host_notifications_enabled,
        host_snmp_community,
        host_snmp_version,
        host_register,
        ehi_notes,
        ehi_notes_url,
        ehi_action_url,
        ehi_icon_image,
        ehi_icon_image_alt,
        ehi_statusmap_image,
        ehi_2d_coords,
        ehi_3d_coords,
        host_location,
        host_acknowledgement_timeout,
        geo_coords
    ';

    protected $attributesWrite = [
        'host_id',
        'command_command_id',
        'command_command_id_arg1',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'command_command_id2',
        'command_command_id_arg2',
        'host_name',
        'host_alias',
        'host_address',
        'display_name',
        'host_max_check_attempts',
        'host_check_interval',
        'host_retry_check_interval',
        'host_active_checks_enabled',
        'host_passive_checks_enabled',
        'host_event_handler_enabled',
        'host_notification_interval',
        'host_notification_options',
        'host_notifications_enabled',
        'host_snmp_community',
        'host_snmp_version',
        'host_register',
        'host_location',
        'host_acknowledgement_timeout',
        'geo_coords'
    ];

    protected $loopHtpl = []; // To be reset
    protected $stmtMacro = null;
    protected $stmtHtpl = null;
    protected $stmtContact = null;
    protected $stmtCg = null;
    protected $stmtPoller = null;

    /**
     * Get host extended information
     * extended information are unset on host object
     *
     * @param array $host the host to parse
     * @return array the extended information
     */
    protected function getExtendedInformation(array &$host): array
    {
        $extendedInformation = [
            'host_host_id' => $host['host_id'],
            'ehi_notes' => $host['ehi_notes'],
            'ehi_notes_url' => $host['ehi_notes_url'],
            'ehi_action_url' => $host['ehi_action_url'],
            'ehi_icon_image' => $host['ehi_icon_image'],
            'ehi_icon_image_alt' => $host['ehi_icon_image_alt'],
            'ehi_2d_coords' => $host['ehi_2d_coords'],
            'ehi_3d_coords' => $host['ehi_3d_coords'],
        ];

        unset($host['ehi_notes']);
        unset($host['ehi_notes_url']);
        unset($host['ehi_action_url']);
        unset($host['ehi_icon_image']);
        unset($host['ehi_icon_image_alt']);
        unset($host['ehi_2d_coords']);
        unset($host['ehi_3d_coords']);

        return $extendedInformation;
    }

    /**
     * Get host icons
     *
     * @param array $host
     * @return void
     */
    protected function getImages(array &$host): void
    {
        $media = Media::getInstance($this->dependencyInjector);
        $media->getMediaPathFromId($host['ehi_icon_image']);
        $media->getMediaPathFromId($host['ehi_statusmap_image']);
    }

    /**
     * Get host macros
     *
     * @param array $host
     * @return int
     */
    protected function getMacros(array &$host): int
    {
        if (isset($host['macros'])) {
            return 1;
        }

        if (is_null($this->stmtMacro)) {
            $this->stmtMacro = $this->backendInstance->db->prepare(
                "SELECT host_macro_id, host_macro_name, host_macro_value, is_password, description, host_host_id
                FROM on_demand_macro_host
                WHERE host_host_id = :host_id"
            );
        }
        $this->stmtMacro->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmtMacro->execute();
        $macros = $this->stmtMacro->fetchAll(PDO::FETCH_ASSOC);

        $host['macros'] = [];
        foreach ($macros as $macro) {
            $host['macros'][$macro['host_macro_name']] = $macro['host_macro_value'];
            MacroHost::getInstance($this->dependencyInjector)->add($macro, $host['host_id']);
        }

        return 0;
    }

    /**
     * Get linked host templates
     *
     * @param array $host
     * @return void
     */
    protected function getHostTemplates(array &$host): void
    {
        if (!isset($host['htpl'])) {
            if (is_null($this->stmt_htpl)) {
                $this->stmt_htpl = $this->backendInstance->db->prepare(
                    "SELECT host_tpl_id
                    FROM host_template_relation
                    WHERE host_host_id = :host_id
                    ORDER BY `order` ASC"
                );
            }
            $this->stmt_htpl->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_htpl->execute();
            $host['htpl'] = $this->stmt_htpl->fetchAll(PDO::FETCH_COLUMN);
        }

        $host_template = HostTemplate::getInstance($this->dependencyInjector);
        $order = 1;
        foreach ($host['htpl'] as $templateId) {
            $host_template->generateFromHostId($templateId);
            HostTemplateRelation::getInstance($this->dependencyInjector)
                ->addRelation($host['host_id'], $templateId, $order);
            $order++;
        }
    }

    /**
     * Get linked poller
     *
     * @param array $host
     * @return void
     */
    protected function getHostPoller(array $host): void
    {
        if (is_null($this->stmtPoller)) {
            $this->stmtPoller = $this->backendInstance->db->prepare(
                "SELECT nagios_server_id
                FROM ns_host_relation
                WHERE host_host_id = :host_id"
            );
        }
        $this->stmtPoller->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmtPoller->execute();
        $pollerId = $this->stmtPoller->fetchAll(PDO::FETCH_COLUMN);

        HostPollerRelation::getInstance($this->dependencyInjector)
            ->addRelation($pollerId[0], $host['host_id']);
    }

    /**
     * Get linked contacts
     *
     * @param array $host
     * @return void
     */
    protected function getContacts(array &$host): void
    {
        if (!isset($host['contacts_cache'])) {
            if (is_null($this->stmtContact)) {
                $this->stmtContact = $this->backendInstance->db->prepare(
                    "SELECT contact_id
                    FROM contact_host_relation
                    WHERE host_host_id = :host_id"
                );
            }
            $this->stmtContact->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmtContact->execute();
            $host['contacts_cache'] = $this->stmtContact->fetchAll(PDO::FETCH_COLUMN);
        }

        $contact = Contact::getInstance($this->dependencyInjector);
        foreach ($host['contacts_cache'] as $contactId) {
            $contact->generateFromContactId($contactId);
            ContactHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $contactId);
        }
    }

    /**
     * Get linked contact groups
     *
     * @param array $host
     * @return void
     */
    protected function getContactGroups(array &$host): void
    {
        if (!isset($host['contact_groups_cache'])) {
            if (is_null($this->stmtCg)) {
                $this->stmtCg = $this->backendInstance->db->prepare(
                    "SELECT contactgroup_cg_id
                    FROM contactgroup_host_relation
                    WHERE host_host_id = :host_id"
                );
            }
            $this->stmtCg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmtCg->execute();
            $host['contact_groups_cache'] = $this->stmtCg->fetchAll(PDO::FETCH_COLUMN);
        }

        $cg = Contactgroup::getInstance($this->dependencyInjector);
        foreach ($host['contact_groups_cache'] as $cgId) {
            $cg->generateFromCgId($cgId);
            ContactGroupHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $cgId);
        }
    }

    /**
     * Check if a host id is a host template
     *
     * @param integer $hostId
     * @param integer $hostTplId
     * @return boolean
     */
    public function isHostTemplate(int $hostId, int $hostTplId): bool
    {
        $loop = [];
        $stack = [];

        $hostsTpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $stack = $this->hosts[$hostId]['htpl'];
        while (($hostId = array_shift($stack))) {
            if (isset($loop[$hostId])) {
                continue;
            }
            $loop[$hostId] = 1;
            if ($hostId == $hostTplId) {
                return true;
            }
            $stack = array_merge($hostsTpl[$hostId]['htpl'], $stack);
        }

        return false;
    }

    /**
     * Get host timezone
     *
     * @param array $host
     * @return void
     */
    protected function getHostTimezone(array &$host): void
    {
        // not needed
    }

    /**
     * Generate host command
     *
     * @param array $host
     * @param string $commandIdLabel
     * @return integer
     */
    protected function getHostCommand(array &$host, string $commandIdLabel): int
    {
        Command::getInstance($this->dependencyInjector)->generateFromCommandId($host[$commandIdLabel]);

        return 0;
    }

    /**
     * Get host linked commands
     *
     * @param array $host
     * @return void
     */
    protected function getHostCommands(array &$host): void
    {
        $this->getHostCommand($host, 'command_command_id');
        $this->getHostCommand($host, 'command_command_id2');
    }

    /**
     * Get host linked timeperiods
     *
     * @param array $host
     * @return void
     */
    protected function getHostPeriods(array &$host): void
    {
        $period = TimePeriod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id2']);
    }

    /**
     * Get host attribute
     *
     * @param integer $hostId
     * @param string $attr
     * @return string|null
     */
    public function getString(int $hostId, string $attr): ?string
    {
        if (isset($this->hosts[$hostId][$attr])) {
            return $this->hosts[$hostId][$attr];
        }

        return null;
    }
}
