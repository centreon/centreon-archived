<?php
/**
 * Class for handling Instances
 */
class CentreonInstance {
    protected $db;
    protected $dbo;
    protected $params;
    protected $instances;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db,$dbo = null)
    {
        $this->db = $db;
        if(!empty($dbo)){
            $this->dbo = $dbo;
        }
        $this->instances = array();
        $this->initParams();
    }

    /**
     * Initialize Parameters
     *
     * @return void
     */
    protected function initParams()
    {
        $this->params = array();
        $this->paramsByName = array();
        $query = "SELECT id, name, localhost, last_restart, ns_ip_address
        		  FROM nagios_server";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $instanceId = $row['id'];
            $instanceName = $row['name'];
            $this->instances[$instanceId] = $instanceName;
            $this->params[$instanceId] = array();
            $this->paramsByName[$instanceName] = array();
            foreach ($row as $key => $value) {
                $this->params[$instanceId][$key] = $value;
                $this->paramsByName[$instanceName][$key] = $value;
            }
        }
    }
    
    public function getInstancesMonitoring($poller_id = array()){
        $pollers = array();
        if(!empty($poller_id)){
            $query = "SELECT instance_id, instance_name
                FROM instance
                WHERE instance_id IN (".$this->dbo->escape(implode(",",$poller_id)).") ";
            $res = $this->dbo->query($query);
            while ($row = $res->fetchRow()) {
                $pollers[] = array('id' => $row['instance_id'], 'name' => $row['instance_name']);
            }  
        }
        
        return $pollers;
    }
    

    /**
     * Get Parameter
     *
     * @param mixed $instance
     * @param string $paramName
     * @return string
     */
    public function getParam($instance, $paramName)
    {
        if (is_numeric($instance)) {
            if (isset($this->params[$instance]) && isset($this->params[$instance][$paramName])) {
                return $this->params[$instance][$paramName];
            }
        } else {
            if (isset($this->paramsByName[$instance]) && isset($this->paramsByName[$instance][$paramName])) {
                return $this->paramsByName[$instance][$paramName];
            }
        }
        return null;
    }

    /**
     * Get Instances
     *
     * @return array
     */
    public function getInstances()
    {
        return $this->instances;
    }
    
    /**
     * Get command data from poller id
     * 
     * @param int $pollerId
     * @return array
     */
    public function getCommandData($pollerId) {
        $sql = "SELECT c.command_id, c.command_name, c.command_line 
            FROM command c, poller_command_relations pcr
            WHERE pcr.poller_id = ?
            AND pcr.command_id = c.command_id
            ORDER BY pcr.command_order";
        $res = $this->db->query($sql, array($pollerId));
        $arr = array();
        while ($row = $res->fetchRow()) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    /**
     * Return list of commands used by poller
     * 
     * @param int $pollerId
     * @return array
     */
    public function getCommandsFromPollerId($pollerId = null) {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['pollercmd']) && $pollerId) {
            $sql = "SELECT command_id 
                FROM poller_command_relations 
                WHERE poller_id = ?
                ORDER BY command_order";
            $res = $this->db->query($sql, array($pollerId));
            while ($row = $res->fetchRow()) {
                $arr[$i]['pollercmd_#index#'] = $row['command_id'];
                $i++;
            }
        } elseif (isset($_REQUEST['pollercmd'])) {
            foreach($_REQUEST['pollercmd'] as $val) {
                $arr[$i]['pollercmd_#index#'] = $val;
                $i++;
            }
        }
        return $arr;
    }
    
    /**
     * Set post-restart commands
     * 
     * @param int $pollerId
     * @param array $commands
     * @return void
     */
    public function setCommands($pollerId, $commands) {
        $this->db->query("DELETE FROM poller_command_relations
                WHERE poller_id = ".$this->db->escape($pollerId));
            
        $stored = array();
        $i = 1;
        foreach ($commands as $value) {
            if ($value != "" && 
                !isset($stored[$value])) {
                    $this->db->query("INSERT INTO poller_command_relations (`poller_id`, `command_id`, `command_order`) 
                                VALUES (". $this->db->escape($pollerId) .", ". $this->db->escape($value) .", ". $i .")");
                    $stored[$value] = true;
                    $i++;
            }
        }
    }
    
    /**
     * 
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $selectedInstances = '';
        $aInstanceList = array();
        
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        } else {
            $selectedInstances .= "AND r.resource_id IN ($explodedValues) ";
        }
        
        $queryInstance = "SELECT DISTINCT p.name as name, p.id  as id"
            . " FROM cfg_resource r, nagios_server p, cfg_resource_instance_relations rel "
            . " WHERE r.resource_id = rel.resource_id "
            . " AND p.id = rel.instance_id "
            . $selectedInstances
            . " ORDER BY p.name";
        
        $DBRESULT = $this->db->query($queryInstance);
        while ($data = $DBRESULT->fetchRow()) {
            $aInstanceList[] = array(
                'id' => $data['id'],
                'text' =>  htmlentities($data['name'])
            );
        }
        
        return $aInstanceList;
    }
}