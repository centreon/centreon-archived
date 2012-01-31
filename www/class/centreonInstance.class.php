<?php
/**
 * Class for handling Instances
 */
class CentreonInstance {
    protected $db;
    protected $params;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db)
    {
        $this->db = $db;
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
}