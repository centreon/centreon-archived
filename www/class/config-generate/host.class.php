<?php

require_once dirname(__FILE__) . '/abstract/host.class.php';
require_once dirname(__FILE__) . '/abstract/service.class.php';

class Host extends AbstractHost {
    protected $hosts_by_name = array();
    protected $hosts = null;
    protected $generate_filename = 'hosts.cfg';
    protected $object_name = 'host';
    protected $stmt_hg = null;
    protected $stmt_parent = null;
    protected $stmt_service = null;
    protected $stmt_service_sg = null;
    protected $generated_parentship = array();
    
    private function getHostGroups(&$host) {
        if (!isset($host['hg'])) {
            if (is_null($this->stmt_hg)) {
                $this->stmt_hg = $this->backend_instance->db->prepare("SELECT 
                    hostgroup_hg_id
                FROM hostgroup_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_hg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_hg->execute();
            $host['hg'] = $this->stmt_hg->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $hostgroup = Hostgroup::getInstance();
        foreach ($host['hg'] as $hg_id) {
            $hostgroup->addHostInHg($hg_id, $host['host_id'], $host['host_name']);
        }
    }
    
    private function getParents(&$host) {
        if (is_null($this->stmt_parent)) {
            $this->stmt_parent = $this->backend_instance->db->prepare("SELECT 
                    host_parent_hp_id
                FROM host_hostparent_relation
                WHERE host_host_id = :host_id
                ");
        }
        $this->stmt_parent->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_parent->execute();
        $result = $this->stmt_parent->fetchAll(PDO::FETCH_COLUMN);
        
        $host['parents'] = array();
        foreach ($result as $parent_id) {
            if (isset($this->hosts[$parent_id])) {
                $host['parents'][] = $this->hosts[$parent_id]['host_name'];

                $correlation_instance = Correlation::getInstance();
                if ($correlation_instance->hasCorrelation()) {
                    $this->generated_parentship[] = array(
                        '@attributes' => array(
                            'parent' => $parent_id,
                            'host' => $host['host_id'],
                            'instance_id' => $this->backend_instance->getPollerId()
                        )
                    );
                }
            }
        }
    }
    
    private function getServices(&$host) {
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    service_service_id
                FROM host_service_relation
                WHERE host_host_id = :host_id AND service_service_id IS NOT NULL
                ");
        }
        $this->stmt_service->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_service->execute();
        $host['services_cache'] = $this->stmt_service->fetchAll(PDO::FETCH_COLUMN);
        
        $service = Service::getInstance();
        foreach ($host['services_cache'] as $service_id) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $service_id);
        }
    }
    
    private function getServicesByHg(&$host) {
        if (count($host['hg']) == 0) {
            return 1;
        }
        if (is_null($this->stmt_service_sg)) {
            $this->stmt_service_sg = $this->backend_instance->db->prepare("SELECT 
                    service_service_id
                FROM host_service_relation
                WHERE hostgroup_hg_id IN (:hg_ids)
                ");
        }
        $this->stmt_service_sg->bindParam(':hg_ids', join(',', $host['hg']));
        $this->stmt_service_sg->execute();
        $host['services_hg_cache'] = $this->stmt_service_sg->fetchAll(PDO::FETCH_COLUMN);
        
        $service = Service::getInstance();
        foreach ($host['services_hg_cache'] as $service_id) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $service_id, 1);
        }
    }
    
    public function getSeverityForService($host_id) {
        return $this->hosts[$host_id]['severity_id_for_services'];
    }
    
    protected function getSeverity($host_id_arg) {
        $host_id = null;
        $loop = array();
        $stack = array();
        $stack2 = array();
        
        $severity_instance = Severity::getInstance();
        $severity_id = $severity_instance->getHostSeverityByHostId($host_id_arg);
        $this->hosts[$host_id_arg]['severity'] = $severity_instance->getHostSeverityById($severity_id);
        if (!is_null($this->hosts[$host_id_arg]['severity']) ) {
            $this->hosts[$host_id_arg]['macros']['_CRITICALITY_LEVEL'] = $this->hosts[$host_id_arg]['severity']['level'];
            $this->hosts[$host_id_arg]['macros']['_CRITICALITY_ID'] = $this->hosts[$host_id_arg]['severity']['hc_id'];
        }
                
        $hosts_tpl = &HostTemplate::getInstance()->hosts;
        $stack = $this->hosts[$host_id_arg]['htpl'];
        while ((is_null($severity_id) && ($host_id = array_shift($stack)))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if (isset($hosts_tpl[$host_id]['severity_id']) && !is_null($hosts_tpl[$host_id]['severity_id'])) {
                $severity_id = $hosts_tpl[$host_id]['severity_id'];
                break;
            }
            if (isset($hosts_tpl[$host_id]['severity_id_from_below'])) {
                $severity_id = $hosts_tpl[$host_id]['severity_id_from_below'];
                break;
            }
            
            $stack2 = $hosts_tpl[$host_id]['htpl'];
            while ((is_null($severity_id) && ($host_id2 = array_shift($stack2)))) {
                if (isset($loop[$host_id2])) {
                    continue;
                }
                $loop[$host_id2] = 1;
                if (isset($hosts_tpl[$host_id2]['severity_id']) && !is_null($hosts_tpl[$host_id2]['severity_id'])) {
                    $severity_id = $hosts_tpl[$host_id2]['severity_id'];
                }
                if (isset($hosts_tpl[$host_id2]['severity_id'])) {
                    $severity_id = $hosts_tpl[$host_id2]['severity_id'];
                    break;
                }
                $stack2 = array_merge($hosts_tpl[$host_id2]['htpl'], $stack2);
            }
            
            $hosts_tpl[$host_id]['severity_id_from_below'] = $severity_id;
        }
        
        # For applied on services without severity
        $this->hosts[$host_id_arg]['severity_id_for_services'] = $severity_instance->getHostSeverityById($severity_id);
    }
    
    private function getHosts($poller_id) {        
        # We use host_register = 1 because we don't want _Module_* hosts
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM ns_host_relation, host 
                LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id 
            WHERE ns_host_relation.nagios_server_id = :server_id 
                AND ns_host_relation.host_host_id = host.host_id 
                AND host.host_activate = '1' AND host.host_register = '1'");
        $stmt->bindParam(':server_id', $poller_id, PDO::PARAM_INT);
        $stmt->execute();
        $this->hosts = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    public function generateFromHostId(&$host) {
        $this->getImages($host);
        $this->getMacros($host);
        $host['macros']['_HOST_ID'] = $host['host_id'];
        $this->getHostTemplates($host);
        $this->getHostCommands($host);
        $this->getHostPeriods($host);
        $this->getContactGroups($host);
        $this->getContacts($host);
        $this->getHostGroups($host);
        $this->getParents($host);
        $this->getSeverity($host['host_id']);
        $this->getServices($host);
        $this->getServicesByHg($host);
        
        $this->generateObjectInFile($host, $host['host_id']);
    }
    
    public function generateFromPollerId($poller_id, $localhost=0) {
        if (is_null($this->hosts)) {
            $this->getHosts($poller_id);
        }
        
        Service::getInstance()->set_poller($poller_id);
        
        foreach ($this->hosts as $host_id => &$host) {
            $this->hosts_by_name[$host['host_name']] = $host_id;
            $host['host_id'] = $host_id;
            $this->generateFromHostId($host);
        }
        
        if ($localhost == 1) {
            MetaService::getInstance()->generateObjects();
        }
        
        Hostgroup::getInstance()->generateObjects();
        Servicegroup::getInstance()->generateObjects();        
        Escalation::getInstance()->generateObjects();
        Dependency::getInstance()->generateObjects();
    }
    
    public function getHostIdByHostName($host_name) {
        if (isset($this->hosts_by_name[$host_name])) {
            return $this->hosts_by_name[$host_name];
        }
        return null;
    }

    public function getGeneratedParentship() {
        return $this->generated_parentship;
    }
    
    public function reset() {
        $this->hosts_by_name = array();
        $this->hosts = null;
        $this->generated_parentship = array();
        parent::reset();
    }
}

?>
