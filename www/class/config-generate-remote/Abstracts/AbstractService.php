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

use ConfigGenerateRemote\Abstracts\AbstractObject;
use ConfigGenerateRemote\Command;
use ConfigGenerateRemote\Contact;
use ConfigGenerateRemote\ContactGroup;
use ConfigGenerateRemote\MacroService;
use ConfigGenerateRemote\Media;
use ConfigGenerateRemote\TimePeriod;
use ConfigGenerateRemote\ServiceTemplate;
use ConfigGenerateRemote\Trap;
use ConfigGenerateRemote\Relations\ContactServiceRelation;
use ConfigGenerateRemote\Relations\ContactGroupServiceRelation;

abstract class AbstractService extends AbstractObject
{
    protected $attributesSelect = '
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
    protected $attributesWrite = array(
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
    protected $loopStpl = []; // To be reset
    protected $stmtMacro = null;
    protected $stmtStpl = null;
    protected $stmtContact = null;
    protected $stmtService = null;

    /**
     * Get service extended information
     * extended information are unset on service object
     *
     * @param array $service the service to parse
     * @return array the extended information
     */
    protected function getExtendedInformation(array &$service): array
    {
        $extendedInformation = [
            'service_service_id' => $service['service_id'],
            'esi_notes' => $service['esi_notes'],
            'esi_notes_url' => $service['esi_notes_url'],
            'esi_action_url' => $service['esi_action_url'],
            'esi_icon_image' => $service['esi_icon_image'],
            'esi_icon_image_alt' => $service['esi_icon_image_alt'],
            'graph_id' => $service['graph_id']
        ];

        unset($service['esi_notes']);
        unset($service['esi_notes_url']);
        unset($service['esi_action_url']);
        unset($service['esi_icon_image']);
        unset($service['esi_icon_image_alt']);
        unset($service['graph_id']);

        return $extendedInformation;
    }

    /**
     * Get service linked icons
     *
     * @param array $service
     * @return void
     */
    protected function getImages(array &$service): void
    {
        $media = Media::getInstance($this->dependencyInjector);
        $media->getMediaPathFromId($service['esi_icon_image']);
    }

    /**
     * Get service linked macros
     *
     * @param array $service
     * @return integer
     */
    protected function getMacros(array &$service): int
    {
        if (isset($service['macros'])) {
            return 1;
        }

        $service['macros'] = MacroService::getInstance($this->dependencyInjector)
            ->getServiceMacroByServiceId($service['service_id']);
        return 0;
    }

    protected function getTraps(array &$service): void
    {
        Trap::getInstance($this->dependencyInjector)
            ->getTrapsByServiceId($service['service_id']);
    }

    /**
     * Get service templates linked to the service
     *
     * @param array $service
     * @return void
     */
    protected function getServiceTemplates(array &$service): void
    {
        ServiceTemplate::getInstance($this->dependencyInjector)
            ->generateFromServiceId($service['service_template_model_stm_id']);
    }

    /**
     * Get service linked contacts
     *
     * @param array $service
     * @return void
     */
    protected function getContacts(array &$service): void
    {
        $contact = Contact::getInstance($this->dependencyInjector);
        $service['contacts_cache'] = $contact->getContactForService($service['service_id']);
        foreach ($service['contacts_cache'] as $contactId) {
            $contact->generateFromContactId($contactId);
            ContactServiceRelation::getInstance($this->dependencyInjector)
                ->addRelation($service['service_id'], $contactId);
        }
    }

    /**
     * Get service linked contact groups
     *
     * @param array $service
     * @return void
     */
    protected function getContactGroups(array &$service): void
    {
        $cg = Contactgroup::getInstance($this->dependencyInjector);
        $service['contact_groups_cache'] = $cg->getCgForService($service['service_id']);
        foreach ($service['contact_groups_cache'] as $cgId) {
            $cg->generateFromCgId($cgId);
            ContactGroupServiceRelation::getInstance($this->dependencyInjector)
                ->addRelation($service['service_id'], $cgId);
        }
    }

    /**
     * Generate service linked command
     *
     * @param array $service
     * @param string $commandIdLabel
     * @return integer
     */
    protected function getServiceCommand(array &$service, string $commandIdLabel): int
    {
        Command::getInstance($this->dependencyInjector)
            ->generateFromCommandId($service[$commandIdLabel]);
        return 0;
    }

    /**
     * Get service linked commands
     *
     * @param array $service
     * @return void
     */
    protected function getServiceCommands(array &$service)
    {
        $this->getServiceCommand($service, 'command_command_id');
        $this->getServiceCommand($service, 'command_command_id2');
    }

    /**
     * Get service linked timeperiods
     *
     * @param array $service
     * @return void
     */
    protected function getServicePeriods(array &$service)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($service['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($service['timeperiod_tp_id2']);
    }

    /**
     * Get service attribute
     *
     * @param integer $serviceId
     * @param string $attr
     * @return string|null
     */
    public function getString(int $serviceId, string $attr): ?string
    {
        if (isset($this->serviceCache[$serviceId][$attr])) {
            return $this->serviceCache[$serviceId][$attr];
        }
        return null;
    }
}
