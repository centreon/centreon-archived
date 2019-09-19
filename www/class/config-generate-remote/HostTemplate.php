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
use ConfigGenerateRemote\Abstracts\AbstractHost;

class HostTemplate extends AbstractHost
{
    public $hosts = null;
    protected $generateFilename = 'hostTemplates.infile';
    protected $table = 'host';
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
        host_location,
        display_name,
        host_max_check_attempts,
        host_check_interval,
        host_retry_check_interval,
        host_active_checks_enabled,
        host_passive_checks_enabled,
        initial_state,
        host_obsess_over_host,
        host_check_freshness,
        host_freshness_threshold,
        host_event_handler_enabled,
        host_low_flap_threshold,
        host_high_flap_threshold,
        host_flap_detection_enabled,
        flap_detection_options,
        host_process_perf_data,
        host_retain_status_information,
        host_retain_nonstatus_information,
        host_notification_interval,
        host_notification_options,
        host_notifications_enabled,
        contact_additive_inheritance,
        cg_additive_inheritance,
        host_first_notification_delay,
        host_recovery_notification_delay,
        host_stalking_options,
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
        'host_acknowledgement_timeout'
    ];

    /**
     * Get hosts
     *
     * @return void
     */
    private function getHosts()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT $this->attributesSelect
            FROM host
            LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id
            WHERE host.host_register = '0' AND host.host_activate = '1'"
        );
        $stmt->execute();
        $this->hosts = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Get severity from host id
     *
     * @param integer $hostId
     * @return int|void
     */
    private function getSeverity(int $hostId)
    {
        if (isset($this->hosts[$hostId]['severity_id'])) {
            return 0;
        }

        $this->hosts[$hostId]['severity_id'] =
            HostCategory::getInstance($this->dependencyInjector)->getHostSeverityByHostId($hostId);
        if (!is_null($this->hosts[$hostId]['severity_id'])) {
            Relations\HostCategoriesRelation::getInstance($this->dependencyInjector)
                ->addRelation($this->hosts[$hostId]['severity_id'], $hostId);
        }
    }

    /**
     * Generate from host id and get host name
     *
     * @param integer $hostId
     * @return null|string
     */
    public function generateFromHostId(int $hostId)
    {
        if (is_null($this->hosts)) {
            $this->getHosts();
        }

        if (!isset($this->hosts[$hostId])) {
            return null;
        }
        if ($this->checkGenerate($hostId)) {
            return $this->hosts[$hostId]['host_name'];
        }

        // Avoid infinite loop!
        if (isset($this->loopHtpl[$hostId])) {
            return $this->hosts[$hostId]['host_name'];
        }
        $this->loopHtpl[$hostId] = 1;

        $this->hosts[$hostId]['host_id'] = $hostId;
        $this->getImages($this->hosts[$hostId]);
        $this->getMacros($this->hosts[$hostId]);
        $this->getHostTimezone($this->hosts[$hostId]);
        $this->getHostTemplates($this->hosts[$hostId]);
        $this->getHostCommands($this->hosts[$hostId]);
        $this->getHostPeriods($this->hosts[$hostId]);
        if ($this->backendInstance->isExportContact()) {
            $this->getContactGroups($this->hosts[$hostId]);
            $this->getContacts($this->hosts[$hostId]);
        }
        $this->getSeverity($hostId);

        $extendedInformation = $this->getExtendedInformation($this->hosts[$hostId]);
        Relations\ExtendedHostInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $hostId);

        $this->generateObjectInFile($this->hosts[$hostId], $hostId);
        return $this->hosts[$hostId]['host_name'];
    }

    /**
     * Reset object
     *
     * @param boolean $createfile
     * @return void
     */
    public function reset($createfile = false): void
    {
        $this->loopHtpl = [];
        parent::reset($createfile);
    }
}
