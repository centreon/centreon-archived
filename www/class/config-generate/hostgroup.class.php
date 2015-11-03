<?php

class Hostgroup extends AbstractObject {
    private $hg = array();
    protected $generate_filename = 'hostgroups.cfg';
    protected $object_name = 'hostgroup';
    protected $attributes_select = '
        hg_id,
        hg_name as hostgroup_name,
        hg_alias as alias,
        hg_notes as notes,
        hg_notes_url as notes_url,
        hg_action_url as action_url
    ';
    protected $attributes_write = array(
        'hostgroup_id',
        'hostgroup_name',
        'alias',
        'notes',
        'notes_url',
        'action_url'
    );
    protected $attributes_array = array(
        'members'
    );
    protected $stmt_hg = null;
    
    private function getHostgroupFromId($hg_id) {
        if (is_null($this->stmt_hg)) {
                $this->stmt_hg = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM hostgroup
                WHERE hg_id = :hg_id AND hg_activate = '1'
                ");
        }
        $this->stmt_hg->bindParam(':hg_id', $hg_id, PDO::PARAM_INT);
        $this->stmt_hg->execute();
        $results = $this->stmt_hg->fetchAll(PDO::FETCH_ASSOC);
        $this->hg[$hg_id] = array_pop($results);
        if (is_null($this->hg[$hg_id])) {
            return null;
        }
        $this->hg[$hg_id]['members'] = array();
    }
    
    public function addHostInHg($hg_id, $host_id, $host_name) {
        if (!isset($this->hg[$hg_id])) {
            $this->getHostgroupFromId($hg_id);
        }
        if (is_null($this->hg[$hg_id]) || isset($this->hg[$hg_id]['members'][$host_id])) {
            return 1;
        }
        
        $this->hg[$hg_id]['members'][$host_id] = $host_name;
        return 0;
    }
    
    public function generateObjects() {
        foreach ($this->hg as $id => &$value) {
            if (count($value['members']) == 0) {
                continue;
            }
            $value['hostgroup_id'] = $value['hg_id'];
            
            $this->generateObjectInFile($value, $id);
        }
    }
    
    public function getHostgroups() {
        $result = array();
        foreach ($this->hg as $id => &$value) {
            if (is_null($value) || count($value['members']) == 0) {
                continue;
            }
            $result[$id] = &$value;
        }
        return $result;
    }
    
    public function reset() {
        parent::reset();
        foreach ($this->hg as &$value) {
            if (!is_null($value)) {
                $value['members'] = array();
            }
        }
    }
    
    public function getString($hg_id, $attr) {
        if (isset($this->hg[$hg_id][$attr])) {
            return $this->hg[$hg_id][$attr];
        }
        return null;
    }
}

?>
