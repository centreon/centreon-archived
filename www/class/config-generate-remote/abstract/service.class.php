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
