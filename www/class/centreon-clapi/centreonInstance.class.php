<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreon.Config.Poller.class.php";
require_once "Centreon/Object/Instance/Instance.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Relation/Instance/Host.php";

/**
 *
 * @author sylvestre
 */
class CentreonInstance extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_ADDRESS = 1;
    const ORDER_SSH_PORT = 2;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Instance();
        $this->params = array(
            'localhost' => '0',
            'ns_activate' => '1',
            'ssh_port' => '22',
            'nagios_bin' => '/usr/sbin/centengine',
            'nagiostats_bin' => '/usr/bin/centenginestats',
            'init_script' => 'centengine',
            'centreonbroker_cfg_path' => '/etc/centreon-broker',
            'centreonbroker_module_path' => '/usr/share/centreon/lib/centreon-broker',
            'centreonconnector_path' => '/usr/lib64/centreon-connector'
        );
        $this->insertParams = array('name', 'ns_ip_address', 'ssh_port');
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array(
                $this->object->getPrimaryKey(),
                'last_restart'
            )
        );
        $this->action = "INSTANCE";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "ns_activate";
    }

    /**
     * Add action
     *
     * @param string $parameters
     * @return void
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['ns_ip_address'] = $params[self::ORDER_ADDRESS];
        $addParams['ssh_port'] = $params[self::ORDER_SSH_PORT];
        if ($addParams['ns_ip_address'] == "127.0.0.1" || strtolower($addParams['ns_ip_address']) == "localhost") {
            $this->params['localhost'] = '1';
        }
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Set Parameters
     *
     * @param string $parameters
     * @return void
     * @throws Exception
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Show
     *
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }

        $pollerState = CentreonConfigPoller::getPollerState();

        $params = array(
            'id',
            'name',
            'localhost',
            'ns_ip_address',
            'ns_activate',
            'ns_status',
            'init_script',
            'nagios_bin',
            'nagiostats_bin',
            'ssh_port'
        );
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("ns ", "", $paramString);
        $paramString = str_replace("nagios ", "", $paramString);
        $paramString = str_replace("nagiostats", "stats", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            if (isset($pollerState[$tab["id"]])) {
                $tab["ns_status"] = $poller[$tab["id"]];
            } else {
                $tab["ns_status"] = '-';
            }
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Get instance Id
     *
     * @param string $name
     * @return int
     */
    public function getInstanceId($name)
    {
        $this->object->setCache(true);
        $instanceIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        $this->object->setCache(false);
        if (!count($instanceIds)) {
            throw new CentreonClapiException("Unknown instance");
        }
        return $instanceIds[0];
    }

    /**
     * Get instance name
     *
     * @param int $instanceId
     * @return string
     */
    public function getInstanceName($instanceId)
    {
        $this->object->setCache(true);
        $instanceName = $this->object->getParameters($instanceId, array($this->object->getUniqueLabelField()));
        $this->object->setCache(false);
        return $instanceName[$this->object->getUniqueLabelField()];
    }

    /**
     * Get hosts monitored by instance
     *
     * @param string $instanceName
     * @return string
     */
    public function getHosts($instanceName)
    {
        $relObj = new \Centreon_Object_Relation_Instance_Host();
        $fields = array('host_id', 'host_name', 'host_address');
        $elems = $relObj->getMergedParameters(
            array(),
            $fields,
            -1,
            0,
            "host_name",
            "ASC",
            array('name' => $instanceName),
            'AND'
        );
        foreach ($elems as $elem) {
            echo $elem['host_id'] . $this->delim . $elem['host_name'] . $this->delim . $elem['host_address'] . "\n";
        }
    }
}
