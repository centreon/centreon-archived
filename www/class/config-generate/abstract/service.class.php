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

abstract class AbstractService extends AbstractObject
{
    const VERTICAL_NOTIFICATION = 1;
    const CLOSE_NOTIFICATION = 2;
    const CUMULATIVE_NOTIFICATION = 3;

    // no flap_detection_options attribute
    protected $attributes_select = '
        service_id,
        service_template_model_stm_id,
        command_command_id as check_command_id,
        command_command_id_arg as check_command_arg,
        timeperiod_tp_id as check_period_id,
        timeperiod_tp_id2 as notification_period_id,
        command_command_id2 as event_handler_id,
        command_command_id_arg2 as event_handler_arg,
        service_description,
        service_alias as name,
        display_name,
        service_is_volatile as is_volatile,
        service_max_check_attempts as max_check_attempts,
        service_normal_check_interval as check_interval,
        service_retry_check_interval as retry_interval,
        service_active_checks_enabled as active_checks_enabled,
        service_passive_checks_enabled as passive_checks_enabled,
        initial_state,
        service_obsess_over_service as obsess_over_service,
        service_check_freshness as check_freshness,
        service_freshness_threshold as freshness_threshold,
        service_event_handler_enabled as event_handler_enabled,
        service_low_flap_threshold as low_flap_threshold,
        service_high_flap_threshold as high_flap_threshold,
        service_flap_detection_enabled as flap_detection_enabled,
        service_process_perf_data as process_perf_data,
        service_retain_status_information as retain_status_information,
        service_retain_nonstatus_information as retain_nonstatus_information,
        service_notification_interval as notification_interval,
        service_notification_options as notification_options,
        service_notifications_enabled as notifications_enabled,
        contact_additive_inheritance,
        service_use_only_contacts_from_host,
        cg_additive_inheritance,
        service_first_notification_delay as first_notification_delay,
        service_recovery_notification_delay as recovery_notification_delay,
        service_stalking_options as stalking_options,
        service_register as register,
        esi_notes as notes,
        esi_notes_url as notes_url,
        esi_action_url as action_url,
        esi_icon_image as icon_image_id,
        esi_icon_image_alt as icon_image_alt,
        service_acknowledgement_timeout as acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'host_name',
        'service_description',
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
        'acknowledgement_timeout'
    );
    protected $attributes_default = array(
        'is_volatile',
        'active_checks_enabled',
        'passive_checks_enabled',
        'event_handler_enabled',
        'flap_detection_enabled',
        'notifications_enabled',
        'obsess_over_service',
        'check_freshness',
        'process_perf_data',
        'retain_status_information',
        'retain_nonstatus_information',
    );
    protected $attributes_array = array(
        'use',
    );
    protected $attributes_hash = array(
        'macros'
    );
    protected $loop_stpl = array(); # To be reset
    protected $stmt_macro = null;
    protected $stmt_stpl = null;
    protected $stmt_contact = null;
    protected $notificationOption = null;
    protected $stmt_service = null;

    protected function getImages(&$service)
    {
        $media = Media::getInstance($this->dependencyInjector);
        if (!isset($service['icon_image'])) {
            $service['icon_image'] = $media->getMediaPathFromId($service['icon_image_id']);
        }
    }

    protected function getMacros(&$service)
    {
        if (isset($service['macros'])) {
            return 1;
        }

        $service['macros'] = Macro::getInstance($this->dependencyInjector)
            ->getServiceMacroByServiceId($service['service_id']);
        return 0;
    }

    protected function getServiceTemplates(&$service)
    {
        $service['use'] = array(
            ServiceTemplate::getInstance($this->dependencyInjector)
                ->generateFromServiceId($service['service_template_model_stm_id'])
        );
    }

    /**
     * @param $service
     */
    protected function getContacts(&$service)
    {
        if (isset($service['service_use_only_contacts_from_host']) &&
            $service['service_use_only_contacts_from_host'] == 1
        ) {
            $service['contacts_cache'] = array();
            $service['contacts'] = "";
        } else {
            if(is_null($this->notificationOption)){
                $stmtNotification =$this->backend_instance->db->query(
                    "SELECT `value` FROM options WHERE `key` = 'inheritance_mode'"
                );
                $notificationOption = $stmtNotification->fetch();
                $this->notificationOption = $notificationOption['value'];
            }

            $contact = Contact::getInstance($this->dependencyInjector);
            $service['contacts_cache'] = $contact->getContactForService($service['service_id']);
            $contactResult = '';
            $contactResultAppend = '';
            $serviceListing = array();
            foreach ($service['contacts_cache'] as $contactId) {
                $tmp = $contact->generateFromContactId($contactId);
                if (!is_null($tmp)) {
                    $contactResult .= $contactResultAppend . $tmp;
                    $contactResultAppend = ',';
                }
            }

            switch ((int)$this->notificationOption) {
                case self::VERTICAL_NOTIFICATION:
                    //check if the inheritance is enable
                    if ((int)$service['contact_additive_inheritance'] === 1) {
                        $this->getContactVerticalInheritance($service['service_id'], $serviceListing);
                    }
                    break;
                case self::CLOSE_NOTIFICATION:
                    //check if a contact is already present
                    if (empty($contactResult)) {
                        $this->getContactCloseInheritance($service['service_id'], $serviceListing);
                    }
                    break;
                case self::CUMULATIVE_NOTIFICATION:
                    // get all service / template inheritance
                    $this->getCumulativeInheritance($service['service_id'], $serviceListing);
                    break;
                default:
                    break;
            }

            if (!empty($serviceListing)) {
                $contactResult = implode(',', $this->getInheritanceContact(array_unique($serviceListing)));
            }
            $service['contacts'] = $contactResult;
        }
    }

    /**
     * @param $serviceId
     * @param array $serviceListing
     */
    protected function getContactVerticalInheritance($serviceId, &$serviceListing = array())
    {
        $serviceListing[] = $serviceId;
        while (1) {
            $query = "SELECT service_template_model_stm_id, contact_additive_inheritance FROM service
                    WHERE `service_id` = " . $serviceId;
            $stmt = $this->backend_instance->db->query($query);
            $serviceAdd = $stmt->fetch();

            if (isset($serviceAdd['service_template_model_stm_id'])
                && (int)$serviceAdd['contact_additive_inheritance'] === 1
            ) {
                $this->getContactVerticalInheritance($serviceAdd['service_template_model_stm_id'], $serviceListing);
            }
            break;
        }
    }

    /**
     * @param $serviceId
     * @param array $serviceListing
     */
    protected function getCumulativeInheritance($serviceId, &$serviceListing = array())
    {
        $serviceListing[] = $serviceId;
        $stmt = $this->backend_instance->db->query(
            "SELECT service_template_model_stm_id FROM service 
                WHERE `service_id` = " . (int)$serviceId
        );
        $row = $stmt->fetch();
        if ($row['service_template_model_stm_id']) {
            $this->getCumulativeInheritance($row['service_template_model_stm_id'], $serviceListing);
        }
    }

    /**
     * @param $serviceId
     * @param array $serviceListing
     */
    protected function getContactCloseInheritance($serviceId, &$serviceListing = array())
    {
        $stmt = $this->backend_instance->db->query(
            "SELECT cs.contact_id, s.service_template_model_stm_id FROM service s
                LEFT JOIN contact_service_relation cs ON cs.`service_service_id` = s.`service_id`
            WHERE s.`service_id` = " . (int)$serviceId
        );
        while ($row = $stmt->fetch()) {
            if (empty($serviceListing)) {
                if (!empty($row['contact_id'])) {
                    $serviceListing[] = (int)$serviceId;
                    break;
                } elseif (!empty($row['service_template_model_stm_id'])) {
                    $this->getContactCloseInheritance($row['service_template_model_stm_id'], $serviceListing);
                }
            } else {
                break;
            }
        }
    }

    /**
     * @param array $service
     * @return array
     */
    protected function getInheritanceContact(array $service)
    {
        $contacts = array();
        $stmt = $this->backend_instance->db->query(
            'SELECT c.contact_id , c.contact_name FROM contact c, contact_service_relation cs
            WHERE cs.service_service_id IN (' . implode(',', $service) . ') AND cs.contact_id = c.contact_id'
        );
        while (($row = $stmt->fetch())) {
            $contacts[$row['contact_id']] = $row['contact_name'];
        }
        return $contacts;
    }

    /**
     * @param $service
     */
    protected function getContactGroups(&$service)
    {
        if (isset($service['service_use_only_contacts_from_host']) &&
            $service['service_use_only_contacts_from_host'] == 1
        ) {
            $service['contact_groups_cache'] = array();
            $service['contact_groups'] = "";
        } else {

            if(is_null($this->notificationOption)){
                $stmtNotification =$this->backend_instance->db->query(
                    "SELECT `value` FROM options WHERE `key` = 'inheritance_mode'"
                );
                $notificationOption = $stmtNotification->fetch();
                $this->notificationOption = $notificationOption['value'];
            }

            $cg = Contactgroup::getInstance($this->dependencyInjector);
            $service['contact_groups_cache'] = $cg->getCgForService($service['service_id']);
            $cgResult = '';
            $cgResultAppend = '';
            $serviceListing = array();

            foreach ($service['contact_groups_cache'] as $cgId) {
                $tmp = $cg->generateFromCgId($cgId);
                if (!is_null($tmp)) {
                    $cgResult .= $cgResultAppend . $tmp;
                    $cgResultAppend = ',';
                }
            }

            switch ((int)$this->notificationOption) {
                case self::VERTICAL_NOTIFICATION:
                    //check if the inheritance is enable
                    if ((int)$service['cg_additive_inheritance'] === 1) {
                        $this->getContactGroupsVerticalInheritance($service['service_id'], $serviceListing);
                    }
                    break;
                case self::CLOSE_NOTIFICATION:
                    //check if a contact is already present
                    if (empty($contactResult)) {
                        $this->getContactGroupsCloseInheritance($service['service_id'], $serviceListing);
                    }
                    break;
                case self::CUMULATIVE_NOTIFICATION:
                    // get all service / template inheritance
                    $this->getCumulativeInheritance($service['service_id'], $serviceListing);
                    break;
                default:
                    break;
            }

            if (!empty($serviceListing)) {
                $cgResult = implode(',', $this->getInheritanceContactGroups(array_unique($serviceListing)));
            }
            $service['contact_groups'] = $cgResult;
        }
    }

    /**
     * @param $serviceId
     * @param array $serviceListing
     */
    protected function getContactGroupsVerticalInheritance($serviceId, &$serviceListing = array())
    {
        $serviceListing[] = $serviceId;
        while (1) {
            $query = "SELECT service_template_model_stm_id, cg_additive_inheritance FROM service
                    WHERE `service_id` = " . $serviceId;
            $stmt = $this->backend_instance->db->query($query);
            $serviceAdd = $stmt->fetch();

            if (isset($serviceAdd['service_template_model_stm_id'])
                && (int)$serviceAdd['cg_additive_inheritance'] === 1
            ) {
                $this->getContactGroupsVerticalInheritance($serviceAdd['service_template_model_stm_id'], $serviceListing);
            }
            break;
        }
    }

    /**
     * @param $serviceId
     * @param array $serviceListing
     */
    protected function getContactGroupsCloseInheritance($serviceId, &$serviceListing = array())
    {
        $stmt = $this->backend_instance->db->query(
            "SELECT cs.contactgroup_cg_id, s.service_template_model_stm_id FROM service s
                LEFT JOIN contactgroup_service_relation cs ON cs.`service_service_id` = s.`service_id`
            WHERE s.`service_id` = " . (int)$serviceId
        );

        while ($row = $stmt->fetch()) {
            if (empty($serviceListing)) {
                if (!empty($row['contactgroup_cg_id'])) {
                    $serviceListing[] = (int)$serviceId;
                    break;
                } elseif (!empty($row['service_template_model_stm_id'])) {
                    $this->getContactGroupsCloseInheritance($row['service_template_model_stm_id'], $serviceListing);
                }
            } else {
                break;
            }
        }
    }

    /**
     * @param $service
     * @return array
     */
    protected function getInheritanceContactGroups($service)
    {
        $contactGroups = array();
        $stmt = $this->backend_instance->db->query(
            'SELECT c.cg_id , c.cg_name FROM contactgroup c, contactgroup_service_relation cs
            WHERE cs.service_service_id IN (' . implode(',', $service) . ') AND cs.contactgroup_cg_id = c.cg_id'
        );

        while (($row = $stmt->fetch())) {
            $contactGroups[$row['cg_id']] = $row['cg_name'];
        }
        return $contactGroups;
    }






    protected function findCommandName($service_id, $command_label)
    {
        $loop = array();

        $services_tpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $service_id = isset($this->service_cache[$service_id]['service_template_model_stm_id'])
            ? $this->service_cache[$service_id]['service_template_model_stm_id']
            : null;
        while (!is_null($service_id)) {
            if (isset($loop[$service_id])) {
                break;
            }
            $loop[$service_id] = 1;
            if (isset($services_tpl[$service_id][$command_label]) &&
                !is_null($services_tpl[$service_id][$command_label])
            ) {
                return $services_tpl[$service_id][$command_label];
            }
            $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id'])
                ? $services_tpl[$service_id]['service_template_model_stm_id']
                : null;
        }

        return null;
    }

    protected function getServiceCommand(&$service, $result_name, $command_id_label, $command_arg_label)
    {
        $command_name = Command::getInstance($this->dependencyInjector)
            ->generateFromCommandId($service[$command_id_label]);
        $command_arg = '';

        if (isset($service[$result_name])) {
            return 1;
        }
        $service[$result_name] = $command_name;
        if (isset($service[$command_arg_label]) &&
            !is_null($service[$command_arg_label]) &&
            $service[$command_arg_label] != ''
        ) {
            $command_arg = $service[$command_arg_label];
            if (is_null($command_name)) {
                # Find Command Name in templates
                $command_name = $this->findCommandName($service['service_id'], $result_name);
                # Can have 'args after'. We replace
                if (!is_null($command_name)) {
                    $command_name = preg_replace('/!.*/', '', $command_name);
                    $service[$result_name] = $command_name . $command_arg;
                }
            } else {
                $service[$result_name] = $command_name . $command_arg;
            }
        }

        return 0;
    }

    protected function getServiceCommands(&$service)
    {
        $this->getServiceCommand($service, 'check_command', 'check_command_id', 'check_command_arg');
        $this->getServiceCommand($service, 'event_handler', 'event_handler_id', 'event_handler_arg');
    }

    protected function getServicePeriods(&$service)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $service['check_period'] = $period->generateFromTimeperiodId($service['check_period_id']);
        $service['notification_period'] = $period->generateFromTimeperiodId($service['notification_period_id']);
    }

    public function getString($service_id, $attr)
    {
        if (isset($this->service_cache[$service_id][$attr])) {
            return $this->service_cache[$service_id][$attr];
        }
        return null;
    }
}
