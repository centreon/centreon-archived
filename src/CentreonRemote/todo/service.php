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
        $serverID = $this->insertNagiosServer();

        if (!$serverID) {
            die(' ERROR at nagios_server ');//TODO
        }

        if (!$this->insertConfigNagios($serverID)) {
            die(' ERROR at cfg_nagios ');//TODO
        }

        if ($this->insertConfigNagiosBroker($serverID)) {
            die(' ERROR at cfg_nagios_broker_module ');//TODO
        }

        if (!$this->insertConfigResource()) {
            die(' ERROR at cfg_resource ');//TODO
        }

        if (!$this->insertConfigResoureRelations()) {
            die(' ERROR at cfg_resource_instance_relations ');//TODO
        }

        if (!$this->insertConfigCentreonBroker()) {
            die(' ERROR at cfg_centreonbroker ');//TODO
        }

        if (!$this->insertConfigCentreonBrokerInfo()) {
            die(' ERROR at cfg_centreonbroker_info ');//TODO
        }
    }

    private function insertNagiosServer()
    {
        $nagiosServerData = require_once 'nagios_server.php';

        return $this->db->insert('nagios_server', $nagiosServerData($this->name, $this->ip));
    }

    private function insertConfigNagios($serverID)
    {
        $configNagiosData = require_once 'cfg_nagios.php';

        return $this->db->insert('cfg_nagios', $configNagiosData($this->name, $serverID));
    }

    private function insertConfigNagiosBroker($serverID)
    {
        $configNagiosBrokerData = require_once 'cfg_nagios_broker_module.php';
        $data = $configNagiosBrokerData($serverID, $this->name);

        $configBrokerFirstID = $this->db->insert('cfg_nagios_broker_module', $data[0]);
        $configBrokerSecondID = $this->db->insert('cfg_nagios_broker_module', $data[1]);

        return $configBrokerFirstID && $configBrokerSecondID;
    }

    private function insertConfigResource()
    {
        //TODO add some ids to data?
        $configResourceData = require_once 'cfg_resource.php';

        return $this->db->insert('cfg_resource', $configResourceData);
    }

    private function insertConfigResoureRelations()
    {
        //TODO add some ids to data?
        $configResourceRelationsData = require_once 'cfg_resource_instance_relations.php';

        return $this->db->insert('cfg_resource_instance_relations', $configResourceRelationsData);
    }

    private function insertConfigCentreonBroker()
    {
        $configCentreonBrokerData = require_once 'cfg_centreonbroker.php';

        return $this->db->insert('cfg_centreonbroker', $configCentreonBrokerData($this->name));
    }

    private function insertConfigCentreonBrokerInfo()
    {
        //TODO some relation to the centreon broker id?
        $configCentreonBrokerInfoData = require_once 'cfg_centreonbroker_info.php';

        return $this->db->insert('cfg_centreonbroker_info', $configCentreonBrokerInfoData);
    }
}
