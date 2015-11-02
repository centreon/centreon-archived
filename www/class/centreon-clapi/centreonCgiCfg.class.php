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
require_once "Centreon/Object/Cgi/Cgi.php";

/**
 *
 * @author sylvestre
 */
class CentreonCgiCfg extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_COMMENT           = 1;
    const ORDER_INSTANCE          = 2;
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
        $this->object = new Centreon_Object_Cgi();
        $this->params = array(  'main_config_file'                          => '',
                                'physical_html_path'	                    => '',
                                'url_html_path'                             => '',
                                'nagios_check_command'				        => '',
                                'use_authentication'				        => '',
                                'default_user_name'					        => '',
                                'authorized_for_system_information'         => '',
                                'authorized_for_system_commands'	        => '',
                                'authorized_for_configuration_information'	=> '',
                                'authorized_for_all_hosts'					=> '',
                                'authorized_for_all_host_commands'			=> '',
                                'authorized_for_all_services'				=> '',
                                'authorized_for_all_service_commands'		=> '',
                                'statusmap_background_image'                => '',
                                'default_statusmap_layout'					=> '2',
                                'statuswrl_include'		                    => '',
                                'default_statuswrl_layout'		            => '2',
                                'host_unreachable_sound'		            => '',
                                'host_down_sound'					        => '',
                                'service_critical_sound'					=> '',
                                'service_warning_sound'					    => '',
                                'service_unknown_sound'					    => '',
                                'ping_syntax'					            => '',
                                'cgi_comment'					            => '',
                                'cgi_activate'					            => '1'
                            );
        $this->nbOfCompulsoryParams = 3;
        $this->activateField = "cgi_activate";
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
        $addParams['instance_id'] = $this->instanceObj->getInstanceId($params[self::ORDER_INSTANCE]);
        $addParams['cgi_comment'] = $params[self::ORDER_COMMENT];
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
            if ($params[1] == "instance") {
                $params[1] = "instance_id";
                $params[2] = $this->instanceObj->getInstanceId($params[2]);
            } elseif ($params[1] == "comment" || $params[1] == "activate" || $params['1'] == "name") {
                $params[1] = "cgi_".$params[1];
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
     * @param string $parameters
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array("cgi_id", "cgi_name", "cgi_comment", "instance_id", "cgi_activate");
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("cgi ", "", $paramString);
        $paramString = str_replace("instance id", "instance", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                if ($key == "instance_id") {
                    $value = $this->instanceObj->getInstanceName($value);
                }
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }
}