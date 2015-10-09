<?php

class MetaTimeperiod extends AbstractObject {
    protected $generate_filename = 'meta_timeperiod.cfg';
    protected $object_name = 'timeperiod';
    protected $attributes_write = array(
        'timeperiod_name',
        'alias',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    );
    
    public function generateObjects() {
        if ($this->checkGenerate(0)) {
            return 0;
        }
        
        $object = array();
        $object['timeperiod_name'] = 'meta_timeperiod';
        $object['alias'] = 'meta_timeperiod';
        $object['sunday'] = '00:00-24:00';
        $object['monday'] = '00:00-24:00';
        $object['tuesday'] = '00:00-24:00';
        $object['wednesday'] = '00:00-24:00';
        $object['thursday'] = '00:00-24:00';
        $object['friday'] = '00:00-24:00';
        $object['saturday'] = '00:00-24:00';
        $this->generateObjectInFile($object, 0);
    }
}

?>
