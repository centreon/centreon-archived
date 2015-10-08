<?php

class MetaService extends AbstractObject {
    private $has_meta_services = 0;
    private $meta_services = array();
    private $generated_services = array(); # for index_data build
    protected $generate_filename = 'meta_services.cfg';
    protected $object_name = 'service';
    protected $attributes_select = '
        meta_id,
        meta_display as display_name,
        check_period as check_period_id,
        max_check_attempts,
        normal_check_interval,
        retry_check_interval,
        notification_interval,
        notification_period as notification_period_id,
        notification_options,
        notifications_enabled
    ';
    protected $attributes_write = array(
        'service_description',
        'display_name',
        'host_name',
        'check_command',
        'max_check_attempts',
        'normal_check_interval',
        'retry_check_interval',
        'active_checks_enabled',
        'passive_checks_enabled',
        'check_period',
        'notification_interval',
        'notification_period',
        'notification_options',
        'register',
    );
    protected $attributes_hash = array(
        'macros'
    );
    protected $attributes_array = array(
        'contact_groups'
    );
    private $stmt_cg = null;
    
    private function getCgFromMetaId($meta_id) {
        if (is_null($this->stmt_cg)) {
            $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    cg_cg_id
                FROM meta_contactgroup_relation
                WHERE meta_id = :meta_id
                ");
        }
        $this->stmt_cg->bindParam(':meta_id', $meta_id);
        $this->stmt_cg->execute();
        $this->meta_services[$meta_id]['contact_groups'] = array();
        foreach ($this->stmt_cg->fetchAll(PDO::FETCH_COLUMN) as $cg_id) {
            $this->meta_services[$meta_id]['contact_groups'][] = Contactgroup::getInstance()->generateFromCgId($cg_id);
        }
    }
    
    private function getMetaServices() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM meta_service
            WHERE meta_activate = '1'
        ");
        $stmt->execute();
        $this->meta_services = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    public function generateObjects() {
        $this->getMetaServices();
        if (count($this->meta_services) == 0) {
            return 0;
        }
        
        $host_id = MetaHost::getInstance()->getHostIdByHostName('_Module_Meta');
        if (is_null($host_id)) {
            return 0;
        }
        MetaCommand::getInstance()->generateObjects();
        MetaTimeperiod::getInstance()->generateObjects();
        MetaHost::getInstance()->generateObject($host_id);
        
        $this->has_meta_services = 1;
        
        foreach ($this->meta_services as $meta_id => &$meta_service) {
            $meta_service['macros'] = array('_SERVICE_ID' => $meta_id);
            $this->getCgFromMetaId($meta_id);            
            $meta_service['check_period'] = Timeperiod::getInstance()->generateFromTimeperiodId($meta_service['check_period_id']);
            $meta_service['notification_period'] = Timeperiod::getInstance()->generateFromTimeperiodId($meta_service['notification_period_id']);
            $meta_service['register'] = 1;
            $meta_service['active_checks_enabled'] = 1;
            $meta_service['passive_checks_enabled'] = 0;
            $meta_service['host_name'] = '_Module_Meta';
            $meta_service['service_description'] = 'meta_' . $meta_id;
            $meta_service['check_command'] = 'check_meta!' . $meta_id;
            
            $this->generated_services[] = $meta_id;
            $this->generateObjectInFile($meta_service, $meta_id);
        }
    }
    
    public function hasMetaServices() {
        return $this->has_meta_services;
    }
    
    public function getGeneratedServices() {
        return $this->generated_services;
    }
}

?>
