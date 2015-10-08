<?php

class Command extends AbstractObject {
    private $commands = null;
    protected $generate_filename = 'commands.cfg';
    protected $object_name = 'command';
    protected $attributes_select = '
        command_id,
        command_name,
        command.command_line as command_line_base,
        connector.name as connector,
        enable_shell
    ';
    protected $attributes_write = array(
        'command_name',
        'command_line',
        'connector',
    );
    
    private function getCommands() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM command 
                LEFT JOIN connector ON connector.id = command.connector_id AND connector.enabled = '1'
            ");
        $stmt->execute();
        $this->commands = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    public function generateFromCommandId($command_id) {
        $name = null;
        if (is_null($this->commands)) {
            $this->getCommands();
        }
        
        if (!isset($this->commands[$command_id])) {
            return null;
        }
        if ($this->checkGenerate($command_id)) {
            return $this->commands[$command_id]['command_name'];
        }
        
        # enable_shell is 0 we remove it
        $command_line = $this->commands[$command_id]['command_line_base'];
        if (!is_null($this->commands[$command_id]['enable_shell']) && $this->commands[$command_id]['enable_shell'] == 1) {
            $command_line = '/bin/sh -c ' . escapeshellarg($this->commands[$command_id]['command_line_base']);
        }
        
        $this->generateObjectInFile(array_merge($this->commands[$command_id], array('command_line' => $command_line)), $command_id);
        return $this->commands[$command_id]['command_name'];
    }
}

?>
