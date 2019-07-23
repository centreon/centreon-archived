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

use \PDO;

require_once dirname(__FILE__) . '/abstract/host.class.php';

class HostTemplate extends AbstractHost
{
    public $hosts = null;
    protected $generate_filename = 'hostTemplates.infile';
    protected $table = 'host';
    protected $attributes_select = '
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
    protected $attributes_write = array(
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
    );

    private function getHosts()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM host 
                LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id 
            WHERE  
                host.host_register = '0' AND host.host_activate = '1'");
        $stmt->execute();
        $this->hosts = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    private function getSeverity($host_id)
    {
        if (isset($this->hosts[$host_id]['severity_id'])) {
            return 0;
        }

        $this->hosts[$host_id]['severity_id'] =
            hostCategory::getInstance($this->dependencyInjector)->getHostSeverityByHostId($host_id);
        if (!is_null($this->hosts[$host_id]['severity_id'])) {
            hostcategoriesRelation::getInstance($this->dependencyInjector)->addRelation($this->hosts[$host_id]['severity_id'], $host_id);
        }
    }

    public function generateFromHostId($host_id)
    {
        if (is_null($this->hosts)) {
            $this->getHosts();
        }

        if (!isset($this->hosts[$host_id])) {
            return null;
        }
        if ($this->checkGenerate($host_id)) {
            return $this->hosts[$host_id]['host_name'];
        }

        # Avoid infinite loop!
        if (isset($this->loop_htpl[$host_id])) {
            return $this->hosts[$host_id]['host_name'];
        }
        $this->loop_htpl[$host_id] = 1;

        $this->hosts[$host_id]['host_id'] = $host_id;
        $this->getImages($this->hosts[$host_id]);
        $this->getMacros($this->hosts[$host_id]);
        $this->getHostTimezone($this->hosts[$host_id]);
        $this->getHostTemplates($this->hosts[$host_id]);
        $this->getHostCommands($this->hosts[$host_id]);
        $this->getHostPeriods($this->hosts[$host_id]);
        if ($this->backend_instance->isExportContact()) {
            $this->getContactGroups($this->hosts[$host_id]);
            $this->getContacts($this->hosts[$host_id]);
        }
        $this->getSeverity($host_id);

        $extendedInformation = $this->getExtendedInformation($this->hosts[$host_id]);
        extendedHostInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $host_id);

        $this->generateObjectInFile($this->hosts[$host_id], $host_id);
        return $this->hosts[$host_id]['host_name'];
    }

    public function reset()
    {
        $this->loop_htpl = array();
        parent::reset();
    }
}
