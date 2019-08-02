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

require_once dirname(__FILE__) . '/abstract/service.class.php';

class ServiceTemplate extends AbstractService
{
    protected $hosts = null;
    protected $table = 'service';
    protected $generate_filename = 'serviceTemplates.infile';
    public $service_cache = array();
    public $current_host_id = null;
    public $current_host_name = null;
    public $current_service_description = null;
    public $current_service_id = null;
    protected $loop_tpl = array();
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
        service_acknowledgement_timeout,
        graph_id
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

    private function getServiceGroups($service_id)
    {
        $host = Host::getInstance($this->dependencyInjector);
        $servicegroup = Servicegroup::getInstance($this->dependencyInjector);
        $this->service_cache[$service_id]['sg'] = $servicegroup->getServiceGroupsForStpl($service_id);
        foreach ($this->service_cache[$service_id]['sg'] as &$sg) {
            if ($host->isHostTemplate($this->current_host_id, $sg['host_host_id'])) {
                $servicegroup->addServiceInSg(
                    $sg['servicegroup_sg_id'],
                    $this->current_service_id,
                    $this->current_service_description,
                    $this->current_host_id,
                    $this->current_host_name
                );
                servicegroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
                    $sg['servicegroup_sg_id'],
                    $sg['host_host_id'],
                    $service_id
                );
                break;
            }
        }
    }

    private function getServiceFromId($service_id)
    {
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare(
                "SELECT " . $this->attributes_select . " " .
                "FROM service " .
                "LEFT JOIN extended_service_information " .
                "ON extended_service_information.service_service_id = service.service_id " .
                "WHERE service_id = :service_id AND service_activate = '1' "
            );
        }
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $results = $this->stmt_service->fetchAll(PDO::FETCH_ASSOC);
        $this->service_cache[$service_id] = array_pop($results);
    }

    private function getSeverity($service_id)
    {
        if (isset($this->service_cache[$service_id]['severity_id'])) {
            return 0;
        }

        $this->service_cache[$service_id]['severity_id'] = serviceCategory::getInstance($this->dependencyInjector)->getServiceSeverityByServiceId($service_id);
        if (!is_null($this->service_cache[$service_id]['severity_id'])) {
            serviceCategoriesRelation::getInstance($this->dependencyInjector)->addRelation($this->service_cache[$service_id]['severity_id'], $service_id);
        }
    }

    public function generateFromServiceId($service_id)
    {
        if (is_null($service_id)) {
            return null;
        }

        if (!isset($this->service_cache[$service_id])) {
            $this->getServiceFromId($service_id);
        }

        if (is_null($this->service_cache[$service_id])) {
            return null;
        }
        if ($this->checkGenerate($service_id)) {
            if (!isset($this->loop_tpl[$service_id])) {
                $this->loop_tpl[$service_id] = 1;
                // Need to go in only to check servicegroup <-> stpl link
                $this->getServiceTemplates($this->service_cache[$service_id]);
                $this->getServiceGroups($service_id);
            }
            return $this->service_cache[$service_id]['service_alias'];
        }

        # avoid loop. we return nothing
        if (isset($this->loop_tpl[$service_id])) {
            return null;
        }
        $this->loop_tpl[$service_id] = 1;

        $this->getImages($this->service_cache[$service_id]);
        $this->getMacros($this->service_cache[$service_id]);
        $this->getServiceTemplates($this->service_cache[$service_id]);
        $this->getServiceCommands($this->service_cache[$service_id]);
        $this->getServicePeriods($this->service_cache[$service_id]);
        $this->getTraps($this->service_cache[$service_id]);
        if ($this->backend_instance->isExportContact()) {
            $this->getContactGroups($this->service_cache[$service_id]);
            $this->getContacts($this->service_cache[$service_id]);
        }
        $this->getServiceGroups($service_id);
        $this->getSeverity($service_id);

        $extendedInformation = $this->getExtendedInformation($this->service_cache[$service_id]);
        extendedServiceInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $service_id);
        graph::getInstance($this->dependencyInjector)->getGraphFromId($extendedInformation['graph_id']);

        $this->service_cache[$service_id]['service_id'] = $service_id;
        $this->generateObjectInFile($this->service_cache[$service_id], $service_id);
        return $this->service_cache[$service_id]['service_alias'];
    }

    public function resetLoop()
    {
        $this->loop_tpl = array();
    }

    public function reset()
    {
        $this->current_host_id = null;
        $this->current_host_name = null;
        $this->current_service_description = null;
        $this->current_service_id = null;
        $this->loop_stpl = array();
        parent::reset();
    }
}
