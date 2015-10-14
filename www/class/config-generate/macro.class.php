<?php

class Macro extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    
    private $macro_service_cache = array();
    
    protected $generate_filename = null;
    protected $object_name = null;
    protected $stmt_service = null;
    
    public function __construct() {
        parent::__construct();
        $this->buildCache();
    }
    
    private function cacheMacroService() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              svc_svc_id, svc_macro_name, svc_macro_value
            FROM on_demand_macro_service
        ");
        $stmt->execute();
        while (($macro = $stmt->fetch(PDO::FETCH_ASSOC))) {
            if (!isset($this->macro_service_cache[$macro['svc_svc_id']])) {
                $this->macro_service_cache[$macro['svc_svc_id']] = array();
            }
            $this->macro_service_cache[$macro['svc_svc_id']][preg_replace('/\$_SERVICE(.*)\$/', '_$1', $macro['svc_macro_name'])] = $macro['svc_macro_value'];
        }
    }
    
    public function getServiceMacroByServiceId($service_id) {
        # Get from the cache
        if (isset($this->macro_service_cache[$service_id])) {
            return $this->macro_service_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return null;
        }
        
        # We get unitary
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    svc_macro_name, svc_macro_value
                FROM on_demand_macro_service
                WHERE svc_svc_id = :service_id
            ");
        }
        
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_host->execute();
        $this->macro_service_cache[$service_id] = array();
        while (($macro = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->macro_service_cache[$service_id][preg_replace('/\$_SERVICE(.*)\$/', '_$1', $macro['svc_macro_name'])] = $macro['svc_macro_value'];
        }
        
        return $this->macro_service_cache[$service_id];
    }
    
    private function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->cacheMacroService();
        $this->done_cache = 1;
    }
}

?>
