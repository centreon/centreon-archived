<?php

class MetaCommand extends AbstractObject {
    protected $generate_filename = 'meta_commands.cfg';
    protected $object_name = 'command';
    protected $attributes_write = array(
        'command_name',
        'command_line',
    );
    
    public function generateObjects() {
        if ($this->checkGenerate(0)) {
            return 0;
        }
        
        $object = array();
        $object['command_name'] = 'check_meta';
        $object['command_line'] = '/usr/lib/nagios/plugins/check_meta_service -i $ARG1$';        
        $this->generateObjectInFile($object, 0);
        
        $object['command_name'] = 'check_meta_host_alive';
        $object['command_line'] = '$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1';        
        $this->generateObjectInFile($object, 0);
    }
}

?>
