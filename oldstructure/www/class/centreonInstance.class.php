<?php
/**
 * Class for handling Instances
 */
class CentreonInstance {
    protected $db;
    protected $params;
    protected $instances;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db)
    {
        $this->db = $db;
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
}