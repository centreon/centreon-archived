<?php

class Resource extends AbstractObject {
    private $connectors = null;
    protected $generate_filename = 'resource.cfg';
    protected $object_name = null;
    protected $stmt = null;
    protected $attributes_hash = array(
        'resources'
    );
    
    public function generateFromPollerId($poller_id) {
        if (is_null($poller_id)) {
            return 0;
        }
        
        if (is_null($this->stmt)) {
            $this->stmt = $this->backend_instance->db->prepare("SELECT resource_name, resource_line FROM
                    cfg_resource_instance_relations, cfg_resource 
                WHERE instance_id = :poller_id AND cfg_resource_instance_relations.resource_id = cfg_resource.resource_id AND cfg_resource.resource_activate = '1';
            "); 
        }
        $this->stmt->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt->execute();
    
        $object = array('resources' => array()); 
        foreach ($this->stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $object['resources'][$value['resource_name']] = $value['resource_line'];
        }

        $this->generateFile($object);
    }
}

?>
