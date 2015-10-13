<?php

require_once dirname(__FILE__) . '/abstract/service.class.php';

class Service extends AbstractService {
    private $use_cache = 0;
    private $use_cache_poller = 1;
    private $done_cache = 0;
    protected $service_cache = null;
    protected $generated_services = array(); # for index_data build and escalation
    protected $generate_filename = 'services.cfg';
    protected $object_name = 'service';
    public $poller_id = null; # for by poller cache
    
    public function use_cache() {
        $this->use_cache = 1;
    }
    
    private function getServiceGroups($service_id, $host_id, $host_name) {        
        $servicegroup = Servicegroup::getInstance();
        $this->service_cache[$service_id]['sg'] = &$servicegroup->getServiceGroupsForService($host_id, $service_id);
        foreach ($this->service_cache[$service_id]['sg'] as &$value) {
            if (is_null($value['host_host_id']) || $host_id == $value['host_host_id']) {
                $servicegroup->addServiceInSg($value['servicegroup_sg_id'], $service_id, $this->service_cache[$service_id]['service_description'], $host_id, $host_name);
            }
        }
    }
    
    private function getServiceByPollerCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM ns_host_relation, host_service_relation, service
                LEFT JOIN extended_service_information ON extended_service_information.service_service_id = service.service_id                 
            WHERE ns_host_relation.nagios_server_id = :server_id 
                AND ns_host_relation.host_host_id = host_service_relation.host_host_id 
                AND host_service_relation.service_service_id = service.service_id AND service_activate = '1'");
        $stmt->bindParam(':server_id', $this->poller_id, PDO::PARAM_INT);
        $stmt->execute();
        
        while (($value = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->service_cache[$value['service_id']] = $value;
        }
    }
    
    private function getServiceCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM service
                    LEFT JOIN extended_service_information ON extended_service_information.service_service_id = service.service_id 
                WHERE service_register = '1' AND service_activate = '1'
        ");
        $stmt->execute();
        $this->service_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }

    public function addServiceCache($service_id, $attr = array()) {
        $this->service_cache[$service_id] = $attr;
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
    
    private function browseContactsInStpl($service_id) {
        $services_tpl = &ServiceTemplate::getInstance()->service_cache;
        $service_tpl_id = isset($this->service_cache[$service_id]['service_template_model_stm_id']) ? $this->service_cache[$service_id]['service_template_model_stm_id'] : null;        
        if (isset($this->service_cache[$service_id]['has_tpl_contacts'])) {
            return 0;
        }
        $this->service_cache[$service_id]['has_tpl_contacts'] = 0;
        $this->service_cache[$service_id]['has_tpl_contact_groups'] = 0;
        if (is_null($service_tpl_id)) {
            return 0;
        }
        if (isset($services_tpl[$service_tpl_id]['has_tpl_contacts'])) {
            $this->service_cache[$service_id]['has_tpl_contacts'] = $services_tpl[$service_tpl_id]['has_tpl_contacts'];
            $this->service_cache[$service_id]['has_tpl_contact_groups'] = $services_tpl[$service_tpl_id]['has_tpl_contact_groups'];
            return 0;
        }
        
        $service_tpl_top_id = $service_tpl_id;
        $services_tpl[$service_tpl_top_id]['has_tpl_contacts'] = 0;
        $services_tpl[$service_tpl_top_id]['has_tpl_contact_groups'] = 0;
        while (!is_null($service_tpl_id)) {
            if (isset($loop[$service_tpl_id])) {
                break;
            }
            $loop[$service_tpl_id] = 1;
            if (isset($services_tpl[$service_tpl_id]['has_tpl_contacts'])) {
                $services_tpl[$service_tpl_top_id]['has_tpl_contacts'] = $services_tpl[$service_tpl_id]['has_tpl_contacts'];
                $services_tpl[$service_tpl_top_id]['has_tpl_contact_groups'] = $services_tpl[$service_tpl_id]['has_tpl_contact_groups'];
                break;
            }
            
            foreach (array('contact_groups', 'contacts') as $type) {
                if (!is_null($services_tpl[$service_tpl_id][$type]) && $services_tpl[$service_tpl_id][$type] != '') {
                    $services_tpl[$service_tpl_top_id]['has_tpl_contacts'] = 1;
                }
            }
            $service_tpl_id = isset($services_tpl[$service_tpl_id]['service_template_model_stm_id']) ? $services_tpl[$service_tpl_id]['service_template_model_stm_id'] : null;
        }
        
        $this->service_cache[$service_id]['has_tpl_contacts'] = $services_tpl[$service_tpl_top_id]['has_tpl_contacts'];
        $this->service_cache[$service_id]['has_tpl_contact_groups'] = $services_tpl[$service_tpl_top_id]['has_tpl_contact_groups'];
    }
    
    private function isServiceHasContacts($service_id) {
        $this->browseContactsInStpl($service_id);
        if ($this->service_cache[$service_id]['has_tpl_contacts'] == 1 || $this->service_cache[$service_id]['has_tpl_contact_groups'] == 1) {
            return 1;
        }
        if ((isset($this->service_cache[$service_id]['contacts']) && $this->service_cache[$service_id]['contacts'] != '') ||
            (isset($this->service_cache[$service_id]['contact_groups']) && $this->service_cache[$service_id]['contact_groups'] != '')) {
            return 1;
        }
        return 0;
    }
    
    private function getContactsFromHost($host_id, $service_id) {
        $this->service_cache[$service_id]['contact_from_host'] = 0;
        if (is_null($this->service_cache[$service_id]['service_inherit_contacts_from_host']) || $this->service_cache[$service_id]['service_inherit_contacts_from_host'] == 0) {
            return 0;
        }
        if ($this->isServiceHasContacts($service_id)) {
            return 0;
        }
        $host = Host::getInstance();
        $this->service_cache[$service_id]['contacts'] = $host->getString($host_id, 'contacts');
        $this->service_cache[$service_id]['contact_groups'] = $host->getString($host_id, 'contact_groups');
        $this->service_cache[$service_id]['contact_from_host'] = 1;
    }
    
    private function getSeverityInServiceChain($service_id_arg) {
        if (isset($this->service_cache[$service_id_arg]['severity_id'])) {
            return 0;
        }
        
        $this->service_cache[$service_id_arg]['severity_id'] = Severity::getInstance()->getServiceSeverityByServiceId($service_id_arg);
        $severity = Severity::getInstance()->getServiceSeverityById($this->service_cache[$service_id_arg]['severity_id']);
        if (!is_null($severity)) {
            $this->service_cache[$service_id_arg]['macros']['_CRITICALITY_LEVEL'] = $severity['level'];
            $this->service_cache[$service_id_arg]['macros']['_CRITICALITY_ID'] = $severity['sc_id'];
            return 0;
        }        
        
        # Check from service templates
        $loop = array();        
        $services_tpl = &ServiceTemplate::getInstance()->service_cache;
        $services_top_tpl = isset($this->service_cache[$service_id_arg]['service_template_model_stm_id']) ? $this->service_cache[$service_id_arg]['service_template_model_stm_id'] : null;
        $service_id = $services_top_tpl;
        $severity_id = null;
        while (!is_null($service_id)) {
            if (isset($loop[$service_id])) {
                break;
            }
            if (isset($services_tpl[$service_id]['severity_id_from_below'])) {
                $this->service_cache[$service_id_arg]['severity_id'] = $services_tpl[$service_id]['severity_id_from_below'];
                break;
            }
            $loop[$service_id] = 1;
            if (isset($services_tpl[$service_id]['severity_id']) && !is_null($services_tpl[$service_id]['severity_id'])) {
                $this->service_cache[$service_id_arg]['severity_id'] = $services_tpl[$service_id]['severity_id'];
                $services_tpl[$services_top_tpl]['severity_id_from_below'] = $services_tpl[$service_id]['severity_id'];
                break;
            }
            $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id']) ? $services_tpl[$service_id]['service_template_model_stm_id'] : null;
        }
        
        return 0;
    }
    
    protected function getSeverity($host_id, $service_id) {
        $this->service_cache[$service_id]['severity_from_host'] = 0;
        $this->getSeverityInServiceChain($service_id);
        # Get from the hosts
        if (is_null($this->service_cache[$service_id]['severity_id'])) {
            $this->service_cache[$service_id]['severity_from_host'] = 1;
            $severity = Host::getInstance()->getSeverityForService($host_id);
            if (!is_null($severity)) {
                $service_severity = Severity::getInstance()->getServiceSeverityMappingHostSeverityByName($severity['hc_name']);
                if (!is_null($service_severity)) {
                    $this->service_cache[$service_id]['macros']['_CRITICALITY_LEVEL'] = $service_severity['level'];
                    $this->service_cache[$service_id]['macros']['_CRITICALITY_ID'] = $service_severity['sc_id'];
                }
            }
        }
        
        return null;
    }
    
    private function clean(&$service) {
        #if ($service['contact_from_host'] == 1) {
        #    $service['contacts'] = null;
        #    $service['contact_groups'] = null;
        #    $service['contact_from_host'] = 0;
        #}
        
        if ($service['severity_from_host'] == 1) {
            unset($service['macros']['_CRITICALITY_LEVEL']);
            unset($service['macros']['_CRITICALITY_ID']);
        }
    }
    
    public function addGeneratedServices($host_id, $service_id) {
        if (!isset($this->generated_services[$host_id])) {
            $this->generated_services[$host_id] = array();
        }
        $this->generated_services[$host_id][] = $service_id;
    }
    
    public function getGeneratedServices() {
        return $this->generated_services;
    }

    private function buildCache() {
        if ($this->done_cache == 1 || 
            ($this->use_cache == 0 && $this->use_cache_poller == 0)) {
            return 0;
        }
        
        if ($this->use_cache_poller == 1) {
            $this->getServiceByPollerCache();
        } else {
            $this->getServiceCache();
        }

        $this->done_cache = 1;
    }
    
    public function generateFromServiceId($host_id, $host_name, $service_id, $by_hg=0) {
        if (is_null($service_id)) {
            return null;
        }
        
        $this->buildCache();
        
        if (($this->use_cache == 0 || $by_hg == 1) && !isset($this->service_cache[$service_id])) {
            $this->getServiceFromId($service_id);
        }        
        if (!isset($this->service_cache[$service_id]) || is_null($this->service_cache[$service_id])) {
            return null;
        }
        if ($this->checkGenerate($host_id . '.' . $service_id)) {
            return $this->service_cache[$service_id]['service_description'];
        }
                
        $this->getImages($this->service_cache[$service_id]);
        $this->getMacros($this->service_cache[$service_id]);
        $this->service_cache[$service_id]['macros']['_SERVICE_ID'] = $service_id;        
        # useful for servicegroup on servicetemplate
        $service_template = ServiceTemplate::getInstance();
        $service_template->current_host_id = $host_id;
        $service_template->current_host_name = $host_name;
        $service_template->current_service_id = $service_id;
        $service_template->current_service_description = $this->service_cache[$service_id]['service_description'];
        $this->getServiceTemplates($this->service_cache[$service_id]);
        $this->getServiceCommands($this->service_cache[$service_id]);
        $this->getServicePeriods($this->service_cache[$service_id]);
        $this->getContactGroups($this->service_cache[$service_id]);
        $this->getContacts($this->service_cache[$service_id]);
        # By default in centengine 1.4.15
        #$this->getContactsFromHost($host_id, $service_id);
        $this->getSeverity($host_id, $service_id);
        $this->getServiceGroups($service_id, $host_id, $host_name);        
        $this->generateObjectInFile($this->service_cache[$service_id] + array('host_name' => $host_name), $host_id . '.' . $service_id);
        $this->addGeneratedServices($host_id, $service_id);
        $this->clean($this->service_cache[$service_id]);
        return $this->service_cache[$service_id]['service_description'];
    }
    
    public function set_poller($poller_id) {
        $this->poller_id = $poller_id;
    }
    
    public function reset() {
        # We reset it by poller (dont need all. We save memory)
        if ($this->use_cache_poller == 1) {
            $this->service_cache = array();
            $this->done_cache  = 0;
        }
        $this->generated_services = array();
        parent::reset();
    }
}

?>
