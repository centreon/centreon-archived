<?php

class Timeperiod extends AbstractObject {
    private $timeperiods = null;
    protected $generate_filename = 'timeperiods.cfg';
    protected $object_name = 'timeperiod';
    protected $attributes_select = '
        tp_id,
        tp_name as timeperiod_name,
        tp_alias as alias,
        tp_sunday as sunday,
        tp_monday as monday,
        tp_tuesday as tuesday,
        tp_wednesday as wednesday,
        tp_thursday as thursday,
        tp_friday as friday,
        tp_saturday as saturday
    ';
    protected $attributes_write = array(
        'name',
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
    protected $attributes_array = array(
        'use',
        'exclude'
    );
    protected $attributes_hash = array(
        'exceptions'
    );
    protected $stmt_extend = array('include' => null, 'exclude' => null);
    
    private function getTimeperiods() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM timeperiod
            ");
        $stmt->execute();
        $this->timeperiods = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    protected function getTimeperiodExceptionFromId($timeperiod_id) {
        if (isset($this->timeperiods[$timeperiod_id]['exceptions'])) {
            return 1;
        }

        $stmt = $this->backend_instance->db->prepare("SELECT 
              days, timerange
            FROM timeperiod_exceptions
            WHERE timeperiod_id = :timeperiod_id
        ");
        $stmt->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
        $stmt->execute();
        $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->timeperiods[$timeperiod_id]['exceptions'] = array();
        foreach ($exceptions as $exception) {
            $this->timeperiods[$timeperiod_id]['exceptions'][$exception['days']] = $exception['timerange'];
        }
    }
    
    protected function getTimeperiodExtendFromId($timeperiod_id, $db_label, $label) {
        if (!isset($this->timeperiods[$timeperiod_id][$label . '_cache'])) {
            if (is_null($this->stmt_extend[$db_label])) {
                $this->stmt_extend[$db_label] = $this->backend_instance->db->prepare("SELECT 
                    timeperiod_" . $db_label . "_id as period_id
                    FROM timeperiod_" . $db_label . "_relations
                    WHERE timeperiod_id = :timeperiod_id
                ");
            }
            $this->stmt_extend[$db_label]->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
            $this->stmt_extend[$db_label]->execute();
            $this->timeperiods[$timeperiod_id][$label . '_cache'] = $this->stmt_extend[$db_label]->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $this->timeperiods[$timeperiod_id][$label] = array();
        foreach ($this->timeperiods[$timeperiod_id][$label . '_cache'] as $period_id) {
            $this->timeperiods[$timeperiod_id][$label][] = $this->generateFromTimeperiodId($period_id);
        }
    }
    
    public function generateFromTimeperiodId($timeperiod_id) {        
        if (is_null($timeperiod_id)) {
            return null;
        }
        if (is_null($this->timeperiods)) {
            $this->getTimeperiods();
        }
        
        if (!isset($this->timeperiods[$timeperiod_id])) {
            return null;
        }
        if ($this->checkGenerate($timeperiod_id)) {
            return $this->timeperiods[$timeperiod_id]['timeperiod_name'];
        }
        
        $this->timeperiods[$timeperiod_id]['name'] = $this->timeperiods[$timeperiod_id]['timeperiod_name'];
        $this->getTimeperiodExceptionFromId($timeperiod_id);
        $this->getTimeperiodExtendFromId($timeperiod_id, 'exclude', 'exclude');
        $this->getTimeperiodExtendFromId($timeperiod_id, 'include', 'use');
        
        $this->generateObjectInFile($this->timeperiods[$timeperiod_id], $timeperiod_id);
        return $this->timeperiods[$timeperiod_id]['timeperiod_name'];
    }
}

?>
