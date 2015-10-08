<?php

require_once dirname(__FILE__) . '/abstract/host.class.php';

class HostTemplate extends AbstractHost {
    public $hosts = null;
    protected $generate_filename = 'hostTemplates.cfg';
    protected $object_name = 'host';
    protected $attributes_select = '
        host_id,
        command_command_id as check_command_id,
        command_command_id_arg1 as check_command_arg,
        timeperiod_tp_id as check_period_id,
        timeperiod_tp_id2 as notification_period_id,
        command_command_id2 as event_handler_id,
        command_command_id_arg2 as event_handler_arg,
        host_name as name,
        host_alias as alias,
        display_name,
        host_max_check_attempts as max_check_attempts,
        host_check_interval as check_interval,
        host_retry_check_interval as retry_interval,
        host_active_checks_enabled as active_checks_enabled,
        host_passive_checks_enabled as passive_checks_enabled,
        initial_state,
        host_obsess_over_host as obsess_over_host,
        host_check_freshness as check_freshness,
        host_freshness_threshold as freshness_threshold,
        host_event_handler_enabled as event_handler_enabled,
        host_low_flap_threshold as low_flap_threshold,
        host_high_flap_threshold as high_flap_threshold,
        host_flap_detection_enabled as flap_detection_enabled,
        flap_detection_options,
        host_process_perf_data as process_perf_data,
        host_retain_status_information as retain_status_information,
        host_retain_nonstatus_information as retain_nonstatus_information,
        host_notification_interval as notification_interval,
        host_notification_options as notification_options,
        host_notifications_enabled as notifications_enabled,
        contact_additive_inheritance,
        cg_additive_inheritance,
        host_first_notification_delay as first_notification_delay,
        host_stalking_options as stalking_options,
        host_snmp_community,
        host_snmp_version,
        host_register as register,
        ehi_notes as notes,
        ehi_notes_url as notes_url,
        ehi_action_url as action_url,
        ehi_icon_image as icon_image_id,
        ehi_icon_image_alt as icon_image_alt,
        ehi_vrml_image as vrml_image_id,
        ehi_statusmap_image as statusmap_image_id,
        ehi_2d_coords as 2d_coords,
        ehi_3d_coords as 3d_coords
    ';
    protected $attributes_write = array(
        'name',
        'alias',
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
        'vrml_image',
        'statusmap_image',
        '2d_coords',
        '3d_coords'
    );
    protected $attributes_array = array(
        'use'
    );
    
    private function getHosts() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM host 
                LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id 
            WHERE  
                host.host_register = '0' AND host.host_activate = '1'");
        $stmt->execute();
        $this->hosts = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    private function getSeverity($host_id) {
        if (isset($this->hosts[$host_id]['severity_id'])) {
            return 0;
        }
        
        $this->hosts[$host_id]['severity_id'] = Severity::getInstance()->getHostSeverityByHostId($host_id);
        $severity = Severity::getInstance()->getHostSeverityById($this->hosts[$host_id]['severity_id']);
        if (!is_null($severity)) {
            $this->hosts[$host_id]['macros']['_CRITICALITY_LEVEL'] = $severity['level'];
            $this->hosts[$host_id]['macros']['_CRITICALITY_ID'] = $severity['hc_id'];
        }
    }
    
    public function generateFromHostId($host_id) {
        if (is_null($this->hosts)) {
            $this->getHosts();
        }
        
        if (!isset($this->hosts[$host_id])) {
            return null;
        }
        if ($this->checkGenerate($host_id)) {
            return $this->hosts[$host_id]['name'];
        }
        
        # Avoid infinite loop!
        if (isset($this->loop_htpl[$host_id])) {
            return $this->hosts[$host_id]['name'];
        }
        $this->loop_htpl[$host_id] = 1;
        
        $this->hosts[$host_id]['host_id'] = $host_id;
        $this->getImages($this->hosts[$host_id]);
        $this->getMacros($this->hosts[$host_id]);
        $this->getHostTemplates($this->hosts[$host_id]);
        $this->getHostCommands($this->hosts[$host_id]);
        $this->getHostPeriods($this->hosts[$host_id]);
        $this->getContactGroups($this->hosts[$host_id]);
        $this->getContacts($this->hosts[$host_id]);
        $this->getSeverity($host_id);
        
        $this->generateObjectInFile($this->hosts[$host_id], $host_id);
        return $this->hosts[$host_id]['name'];
    }
    
    public function reset() {
        $this->loop_htpl = array();
        parent::reset();
    }
}

?>
