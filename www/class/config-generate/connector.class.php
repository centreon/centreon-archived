<?php

class Connector extends AbstractObject {
    private $connectors = null;
    protected $generate_filename = 'connectors.cfg';
    protected $object_name = 'connector';
    protected $attributes_select = '
        id,
        name as connector_name,
        command_line as connector_line
    ';
    protected $attributes_write = array(
        'connector_name',
        'connector_line',
    );
    
    private function getConnectors() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM connector 
                WHERE enabled = '1'
            ");
        $stmt->execute();
        $this->connectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateObjects($connector_path) {
        if (is_null($connector_path)) {
            return 0;
        }
        
        $this->getConnectors();
        foreach ($this->connectors as $connector) {
            $connector['connector_line'] = $connector_path . '/' . $connector['connector_line'];
            $this->generateObjectInFile($connector, $connector['id']);
        }
    }
}

?>
