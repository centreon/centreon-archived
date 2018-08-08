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

        $configNagiosBrokerData = require_once 'cfg_nagios_broker_module.php';
        $configNagiosBrokerData[0]['cfg_nagios_id'] = $serverID;
        $configNagiosBrokerData[1]['cfg_nagios_id'] = $serverID;

        $configBrokerFirstID = $this->db->insert('cfg_nagios_broker_module', $configNagiosBrokerData[0]);
        $configBrokerSecondID = $this->db->insert('cfg_nagios_broker_module', $configNagiosBrokerData[1]);

        if (!$configBrokerFirstID || !$configBrokerSecondID) {
            die(' ERROR at cfg_nagios_broker_module ');//TODO
        }

        //TODO add some ids to data?
        $configResourceData = require_once 'cfg_resource.php';
        $configResourceID = $this->db->insert('cfg_resource', $configResourceData);

        if (!$configResourceID) {
            die(' ERROR at cfg_resource ');//TODO
        }

        //TODO add some ids to data?
        $configResourceRelationsData = require_once 'cfg_resource_instance_relations.php';
        $configResourceInstanceID = $this->db->insert('cfg_resource_instance_relations', $configResourceRelationsData);

        if (!$configResourceInstanceID) {
            die(' ERROR at cfg_resource_instance_relations ');//TODO
        }

        //TODO $pollerName
        $configCentreonBrokerData = require_once 'cfg_centreonbroker.php';
        $configCentreonBrokerID = $this->db->insert('cfg_centreonbroker', $configCentreonBrokerData);

        if (!$configCentreonBrokerID) {
            die(' ERROR at cfg_centreonbroker ');//TODO
        }

        //TODO some relation to the centreon broker id?
        $configCentreonBrokerInfoData = require_once 'cfg_centreonbroker_info.php';
        $configCentreonBrokerInfoID = $this->db->insert('cfg_centreonbroker_info', $configCentreonBrokerInfoData);

        if (!$configCentreonBrokerInfoID) {
            die(' ERROR at cfg_centreonbroker_info ');//TODO
        }
    }
}
