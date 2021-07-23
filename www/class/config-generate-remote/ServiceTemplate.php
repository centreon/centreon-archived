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
use ConfigGenerateRemote\Abstracts\AbstractService;

class ServiceTemplate extends AbstractService
{
    protected $hosts = null;
    protected $table = 'service';
    protected $generateFilename = 'serviceTemplates.infile';
    public $serviceCache = [];
    public $currentHostId = null;
    public $currentHostName = null;
    public $currentServiceDescription = null;
    public $currentServiceId = null;
    protected $loopTpl = [];
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
        service_acknowledgement_timeout,
        graph_id
    ';
    protected $attributesWrite = [
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
    ];

    /**
     * Get linked service groups and generate relations
     *
     * @param integer $serviceId
     * @return void
     */
    private function getServiceGroups(int $serviceId)
    {
        $host = Host::getInstance($this->dependencyInjector);
        $servicegroup = ServiceGroup::getInstance($this->dependencyInjector);
        $this->serviceCache[$serviceId]['sg'] = $servicegroup->getServiceGroupsForStpl($serviceId);
        foreach ($this->serviceCache[$serviceId]['sg'] as &$sg) {
            if ($host->isHostTemplate($this->currentHostId, $sg['host_host_id'])) {
                $servicegroup->addServiceInSg(
                    $sg['servicegroup_sg_id'],
                    $this->currentServiceId,
                    $this->currentServiceDescription,
                    $this->currentHostId,
                    $this->currentHostName
                );
                Relations\ServiceGroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
                    $sg['servicegroup_sg_id'],
                    $sg['host_host_id'],
                    $serviceId
                );
            }
        }
    }

    /**
     * Get service template from id
     *
     * @param integer $serviceId
     * @return void
     */
    private function getServiceFromId(int $serviceId)
    {
        if (is_null($this->stmtService)) {
            $this->stmtService = $this->backendInstance->db->prepare(
                "SELECT $this->attributesSelect
                FROM service
                LEFT JOIN extended_service_information
                ON extended_service_information.service_service_id = service.service_id
                WHERE service_id = :service_id AND service_activate = '1'"
            );
        }
        $this->stmtService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtService->execute();
        $results = $this->stmtService->fetchAll(PDO::FETCH_ASSOC);
        $this->serviceCache[$serviceId] = array_pop($results);
    }

    /**
     * Get severity from service id
     *
     * @param integer $serviceId
     * @return void|int
     */
    private function getSeverity(int $serviceId)
    {
        if (isset($this->serviceCache[$serviceId]['severity_id'])) {
            return 0;
        }

        $this->serviceCache[$serviceId]['severity_id'] =
            ServiceCategory::getInstance($this->dependencyInjector)->getServiceSeverityByServiceId($serviceId);
        if (!is_null($this->serviceCache[$serviceId]['severity_id'])) {
            Relations\ServiceCategoriesRelation::getInstance($this->dependencyInjector)
                ->addRelation($this->serviceCache[$serviceId]['severity_id'], $serviceId);
        }
    }

    /**
     * Generate service template
     *
     * @param null|integer $serviceId
     * @return void
     */
    public function generateFromServiceId(?int $serviceId)
    {
        if (is_null($serviceId)) {
            return null;
        }

        if (!isset($this->serviceCache[$serviceId])) {
            $this->getServiceFromId($serviceId);
        }

        if (is_null($this->serviceCache[$serviceId])) {
            return null;
        }
        if ($this->checkGenerate($serviceId)) {
            if (!isset($this->loopTpl[$serviceId])) {
                $this->loopTpl[$serviceId] = 1;
                // Need to go in only to check servicegroup <-> stpl link
                $this->getServiceTemplates($this->serviceCache[$serviceId]);
                $this->getServiceGroups($serviceId);
            }
            return $this->serviceCache[$serviceId]['service_alias'];
        }

        // avoid loop. we return nothing
        if (isset($this->loopTpl[$serviceId])) {
            return null;
        }
        $this->loopTpl[$serviceId] = 1;

        $this->getImages($this->serviceCache[$serviceId]);
        $this->getMacros($this->serviceCache[$serviceId]);
        $this->getServiceTemplates($this->serviceCache[$serviceId]);
        $this->getServiceCommands($this->serviceCache[$serviceId]);
        $this->getServicePeriods($this->serviceCache[$serviceId]);
        $this->getTraps($this->serviceCache[$serviceId]);
        if ($this->backendInstance->isExportContact()) {
            $this->getContactGroups($this->serviceCache[$serviceId]);
            $this->getContacts($this->serviceCache[$serviceId]);
        }
        $this->getServiceGroups($serviceId);
        $this->getSeverity($serviceId);

        $extendedInformation = $this->getExtendedInformation($this->serviceCache[$serviceId]);
        Relations\ExtendedServiceInformation::getInstance($this->dependencyInjector)
            ->add($extendedInformation, $serviceId);
        Graph::getInstance($this->dependencyInjector)->getGraphFromId($extendedInformation['graph_id']);

        $this->serviceCache[$serviceId]['service_id'] = $serviceId;
        $this->generateObjectInFile($this->serviceCache[$serviceId], $serviceId);

        return $this->serviceCache[$serviceId]['service_alias'];
    }

    /**
     * Reset loop
     *
     * @return void
     */
    public function resetLoop()
    {
        $this->loopTpl = [];
    }

    /**
     * Reset object
     *
     * @param boolean $createfile
     * @return void
     */
    public function reset($createfile = false): void
    {
        $this->currentHostId = null;
        $this->currentHostName = null;
        $this->currentServiceDescription = null;
        $this->currentServiceId = null;
        $this->loop_stpl = [];
        parent::reset($createfile);
    }
}
