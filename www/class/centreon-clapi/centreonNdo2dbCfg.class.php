<?php
/**
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
 * SVN : $URL$
 * SVN : $Id$
 */
require_once "centreonObject.class.php";
require_once "centreonInstance.class.php";
require_once "Centreon/Object/Ndo2db/Ndo2db.php";

/**
 *
 * @author sylvestre
 */
class CentreonNdo2dbCfg extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_INSTANCE          = 1;
    protected $instanceObj;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->instanceObj = new CentreonInstance();
        $this->object = new Centreon_Object_Ndo2db();
        $this->params = array(  'ndo2db_user'               => 'nagios',
                                'ndo2db_group'	            => 'nagios',
                                'local'                     => '0',
                                'socket_type'				=> 'tcp',
                                'socket_name'				=> '/var/run/ndo.sock',
                                'tcp_port'					=> '5668',
                                'db_servertype'             => 'mysql',
                                'db_host'					=> 'localhost',
                                'db_name'					=> 'centreon_status',
                                'db_port'					=> '3306',
                                'db_prefix'					=> 'nagios_',
                                'db_user'					=> 'centreon',
                                'max_timedevents_age'		=> '1440',
                                'max_systemcommands_age'    => '1440',
                                'max_servicechecks_age'		=> '1440',
                                'max_hostchecks_age'		=> '1440',
                                'max_eventhandlers_age'		=> '1440',
                                'activate'					=> '1'
                            );
        $this->nbOfCompulsoryParams = 2;
        $this->activateField = "activate";
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
        $addParams['ns_nagios_server'] = $this->instanceObj->getInstanceId($params[self::ORDER_INSTANCE]);
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
            if ($params[1] == "instance" || $params[1] == "ns_nagios_server") {
                $params[1] = "ns_nagios_server";
                $params[2] = $this->instanceObj->getInstanceId($params[2]);
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
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
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array("id", "description", "ns_nagios_server", "socket_type", "tcp_port", "db_servertype", "db_host", "db_name", "db_port", "db_user");
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("ns nagios server", "instance", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                if ($key == "ns_nagios_server") {
                    $value = $this->instanceObj->getInstanceName($value);
                }
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }
}