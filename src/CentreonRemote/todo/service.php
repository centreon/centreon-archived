<?php

class service
{

    protected $ip;

    /** @var db */
    protected $db;

    protected $name;


    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setDbInstance(db $db)
    {
        $this->db = $db;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function insertRemoteConfiguration()
    {
        $nagiosServerData = require_once 'nagios_server.php';
        $nagiosServerData['name'] = $this->name;
        $nagiosServerData['ns_ip_address'] = $this->ip;

        $serverID = $this->db->insert('nagios_server', $nagiosServerData);

        if (!$serverID) {
            die(' ERROR at nagios_server ');//TODO
        }

        $configNagiosData = require_once 'cfg_nagios.php';
        $configNagiosData['nagios_name'] = $this->name;
        $configNagiosData['nagios_server_id'] = $serverID;

        $configID = $this->db->insert('cfg_nagios', $configNagiosData);

        if (!$configID) {
            die(' ERROR at cfg_nagios ');//TODO
        }
    }
}
