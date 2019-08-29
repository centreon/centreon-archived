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

require_once __DIR__ . '/Object.php';

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
        host_acknowledgement_timeout
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
    ];

    protected $loopHtpl = []; # To be reset
    protected $stmtMacro = null;
    protected $stmtHtpl = null;
    protected $stmtContact = null;
    protected $stmtCg = null;

    protected function getExtendedInformation(&$host)
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

    protected function getImages(&$host)
    {
        $media = Media::getInstance($this->dependencyInjector);
        $media->getMediaPathFromId($host['ehi_icon_image']);
        $media->getMediaPathFromId($host['ehi_statusmap_image']);
    }

    protected function getMacros(&$host)
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
            macroHost::getInstance($this->dependencyInjector)->add($macro, $host['host_id']);
        }

        return 0;
    }

    protected function getHostTemplates(&$host)
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
            hostTemplateRelation::getInstance($this->dependencyInjector)
                ->addRelation($host['host_id'], $templateId, $order);
            $order++;
        }
    }

    protected function getContacts(&$host)
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
            contactHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $contactId);
        }
    }

    protected function getContactGroups(&$host)
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
            contactgroupHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $cgId);
        }
    }

    public function isHostTemplate($hostId, $hostTplId)
    {
        $loop = [];
        $stack = [];

        $hosts_tpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $stack = $this->hosts[$hostId]['htpl'];
        while (($hostId = array_shift($stack))) {
            if (isset($loop[$hostId])) {
                continue;
            }
            $loop[$hostId] = 1;
            if ($hostId == $hostTplId) {
                return 1;
            }
            $stack = array_merge($hosts_tpl[$hostId]['htpl'], $stack);
        }

        return 0;
    }

    protected function getHostTimezone(&$host)
    {
        # not needed
    }

    protected function getHostCommand(&$host, $commandIdLabel)
    {
        Command::getInstance($this->dependencyInjector)->generateFromCommandId($host[$commandIdLabel]);

        return 0;
    }

    protected function getHostCommands(&$host)
    {
        $this->getHostCommand($host, 'command_command_id');
        $this->getHostCommand($host, 'command_command_id2');
    }

    protected function getHostPeriods(&$host)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id2']);
    }

    public function getString($hostId, $attr)
    {
        if (isset($this->hosts[$hostId][$attr])) {
            return $this->hosts[$hostId][$attr];
        }

        return null;
    }
}
