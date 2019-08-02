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

require_once dirname(__FILE__) . '/object.class.php';

abstract class AbstractService extends AbstractObject
{
    protected $attributes_select = '
        service_id,
        service_template_model_stm_id,
        command_command_id,
        command_command_id_arg,
        timeperiod_tp_id,
        timeperiod_tp_id2,
        command_command_id2,
        command_command_id_arg2,
        service_description,
        service_alias,
        display_name,
        service_is_volatile,
        service_max_check_attempts,
        service_normal_check_interval,
        service_retry_check_interval,
        service_active_checks_enabled,
        service_passive_checks_enabled,
        service_event_handler_enabled,
        service_notification_interval,
        service_notification_options,
        service_notifications_enabled,
        service_register,
        esi_notes,
        esi_notes_url,
        esi_action_url,
        esi_icon_image,
        esi_icon_image_alt,
        graph_id,
        service_acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'service_id',
        'service_template_model_stm_id',
        'command_command_id',
        'command_command_id_arg',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'command_command_id2',
        'command_command_id_arg2',
        'service_description',
        'service_alias',
        'display_name',
        'service_is_volatile',
        'service_max_check_attempts',
        'service_normal_check_interval',
        'service_retry_check_interval',
        'service_active_checks_enabled',
        'service_passive_checks_enabled',
        'service_event_handler_enabled',
        'service_notification_interval',
        'service_notification_options',
        'service_notifications_enabled',
        'service_register',
        'service_acknowledgement_timeout',
    );
    protected $loop_stpl = array(); # To be reset
    protected $stmt_macro = null;
    protected $stmt_stpl = null;
    protected $stmt_contact = null;
    protected $stmt_service = null;

    protected function getExtendedInformation(&$service)
    {
        $extended_information = array(
            'service_service_id' => $service['service_id'],
            'esi_notes' => $service['esi_notes'],
            'esi_notes_url' => $service['esi_notes_url'],
            'esi_action_url' => $service['esi_action_url'],
            'esi_icon_image' => $service['esi_icon_image'],
            'esi_icon_image_alt' => $service['esi_icon_image_alt'],
            'graph_id' => $service['graph_id']
        );
        unset($service['esi_notes']);
        unset($service['esi_notes_url']);
        unset($service['esi_action_url']);
        unset($service['esi_icon_image']);
        unset($service['esi_icon_image_alt']);
        unset($service['graph_id']);
        return $extended_information;
    }

    protected function getImages(&$service)
    {
        $media = Media::getInstance($this->dependencyInjector);
        $media->getMediaPathFromId($service['esi_icon_image']);
    }

    protected function getMacros(&$service)
    {
        if (isset($service['macros'])) {
            return 1;
        }

        $service['macros'] = macroService::getInstance($this->dependencyInjector)
            ->getServiceMacroByServiceId($service['service_id']);
        return 0;
    }

    protected function getTraps(&$service)
    {
        Trap::getInstance($this->dependencyInjector)
                ->getTrapsByServiceId($service['service_id']);
    }

    protected function getServiceTemplates(&$service)
    {
        ServiceTemplate::getInstance($this->dependencyInjector)
                ->generateFromServiceId($service['service_template_model_stm_id']);
    }

    protected function getContacts(&$service)
    {
        $contact = Contact::getInstance($this->dependencyInjector);
        $service['contacts_cache'] = $contact->getContactForService($service['service_id']);
        foreach ($service['contacts_cache'] as $contact_id) {
            $contact->generateFromContactId($contact_id);
            contactServiceRelation::getInstance($this->dependencyInjector)->addRelation($service['service_id'], $contact_id);
        }
    }

    protected function getContactGroups(&$service)
    {
        $cg = Contactgroup::getInstance($this->dependencyInjector);
        $service['contact_groups_cache'] = $cg->getCgForService($service['service_id']);
        foreach ($service['contact_groups_cache'] as $cg_id) {
            $cg->generateFromCgId($cg_id);
            contactgroupServiceRelation::getInstance($this->dependencyInjector)->addRelation($service['service_id'], $cg_id);
        }
    }

    protected function getServiceCommand(&$service, $command_id_label)
    {
        Command::getInstance($this->dependencyInjector)
            ->generateFromCommandId($service[$command_id_label]);
        return 0;
    }

    protected function getServiceCommands(&$service)
    {
        $this->getServiceCommand($service, 'command_command_id');
        $this->getServiceCommand($service, 'command_command_id2');
    }

    protected function getServicePeriods(&$service)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($service['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($service['timeperiod_tp_id2']);
    }

    public function getString($service_id, $attr)
    {
        if (isset($this->service_cache[$service_id][$attr])) {
            return $this->service_cache[$service_id][$attr];
        }
        return null;
    }
}
