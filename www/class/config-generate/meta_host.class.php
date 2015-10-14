<?php

class MetaHost extends AbstractObject {
    protected $generate_filename = 'meta_host.cfg';
    protected $object_name = 'host';
    protected $attributes_write = array(
        'host_name',
        'alias',
        'address',
        'check_command',
        'max_check_attempts',
        'check_interval',
        'active_checks_enabled',
        'passive_checks_enabled',
        'check_period',
        'notification_interval',
        'notification_period',
        'notification_options',
        'notifications_enabled',
        'register',
    );
    protected $attributes_hash = array(
        'macros'
    );
    
    public function getHostIdByHostName($host_name) {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              host_id
            FROM host
            WHERE host_name = :host_name
            ");
        $stmt->bindParam(':host_name', $host_name, PDO::PARAM_STR);
        $stmt->execute();
        return array_pop($stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    
    public function generateObject($host_id) {
        if ($this->checkGenerate($host_id)) {
            return 0;
        }
        
        $object = array();
        $object['host_name'] = '_Module_Meta';
        $object['alias'] = 'Meta Service Calculate Module For Centreon';
        $object['address'] = '127.0.0.1';
        $object['check_command'] = 'check_meta_host_alive';
        $object['max_check_attempts'] = 3;
        $object['check_interval'] = 1;
        $object['active_checks_enabled'] = 0;
        $object['passive_checks_enabled'] = 0;
        $object['check_period'] = 'meta_timeperiod';
        $object['notification_interval'] = 60;
        $object['notification_period'] = 'meta_timeperiod';
        $object['notification_period'] = 'meta_timeperiod';
        $object['notification_options'] = 'd';
        $object['notifications_enabled'] = 0;
        $object['register'] = 1;
        $object['macros'] = array('_HOST_ID' => $host_id);
        
        $this->generateObjectInFile($object, $host_id);
    }
}

?>
