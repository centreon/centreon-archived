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

require_once dirname(__FILE__) . '/abstract/service.class.php';

class ServiceTemplate extends AbstractService {
    protected $hosts = null;
    protected $generate_filename = 'serviceTemplates.cfg';
    protected $object_name = 'service';
    public $service_cache = array();
    public $current_host_id = null;
    public $current_host_name = null;
    public $current_service_description = null;
    public $current_service_id = null;
    protected $loop_tpl = array();
    protected $attributes_select = '
        service_id,
        service_template_model_stm_id,
        command_command_id as check_command_id,
        command_command_id_arg as check_command_arg,
        timeperiod_tp_id as check_period_id,
        timeperiod_tp_id2 as notification_period_id,
        command_command_id2 as event_handler_id,
        command_command_id_arg2 as event_handler_arg,
        service_description as name,
        service_alias as service_description,
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
        cg_additive_inheritance,
        service_first_notification_delay as first_notification_delay,
        service_stalking_options as stalking_options,
        service_register as register,
        service_inherit_contacts_from_host,
        service_use_only_contacts_from_host,
        esi_notes as notes,
        esi_notes_url as notes_url,
        esi_action_url as action_url,
        esi_icon_image as icon_image_id,
        esi_icon_image_alt as icon_image_alt,
        service_acknowledgement_timeout as acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'service_description',
        'name',
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
        'stalking_options',
        'register',
        'notes',
        'notes_url',
        'action_url',
        'icon_image',
        'icon_image_alt',
        'acknowledgement_timeout'
    );
    
    private function getServiceGroups($service_id) {        
        $host = Host::getInstance();
        $servicegroup = Servicegroup::getInstance();
        $this->service_cache[$service_id]['sg'] = &$servicegroup->getServiceGroupsForStpl($service_id);        
        foreach ($this->service_cache[$service_id]['sg'] as &$sg) {
            if ($host->isHostTemplate($this->current_host_id, $sg['host_host_id'])) {
                $servicegroup->addServiceInSg($sg['servicegroup_sg_id'], $this->current_service_id, $this->current_service_description, $this->current_host_id, $this->current_host_name);
                break;
            }
        }
    }
    
    private function getServiceFromId($service_id) {
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM service
                    LEFT JOIN extended_service_information ON extended_service_information.service_service_id = service.service_id 
                WHERE service_id = :service_id AND service_activate = '1'
            ");
        }
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $results = $this->stmt_service->fetchAll(PDO::FETCH_ASSOC);
        $this->service_cache[$service_id] = array_pop($results);
    }
    
    private function getSeverity($service_id) {
        if (isset($this->service_cache[$service_id]['severity_id'])) {
            return 0;
        }
        
        $this->service_cache[$service_id]['severity_id'] = Severity::getInstance()->getServiceSeverityByServiceId($service_id);
        $severity = Severity::getInstance()->getServiceSeverityById($this->service_cache[$service_id]['severity_id']);
        if (!is_null($severity)) {
            $this->service_cache[$service_id]['macros']['_CRITICALITY_LEVEL'] = $severity['level'];
            $this->service_cache[$service_id]['macros']['_CRITICALITY_ID'] = $severity['sc_id'];
        }
    }
    
    public function generateFromServiceId($service_id) {
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
            return $this->service_cache[$service_id]['name'];
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
        $this->getContactGroups($this->service_cache[$service_id]);
        $this->getContacts($this->service_cache[$service_id]);
        $this->getServiceGroups($service_id);
        $this->getSeverity($service_id);
        
        $this->generateObjectInFile($this->service_cache[$service_id], $service_id);
        return $this->service_cache[$service_id]['name'];
    }
    
    public function resetLoop() {
        $this->loop_tpl = array();
    }
    
    public function reset() {
        $this->current_host_id = null;
        $this->current_host_name = null;
        $this->current_service_description = null;
        $this->current_service_id = null;
        $this->loop_stpl = array();
        parent::reset();
    }
}
