<?php

require_once "centreonObject.class.php";
require_once "centreonInstance.class.php";
require_once "Centreon/Object/Ndomod/Ndomod.php";

/**
 *
 * @author sylvestre
 */
class CentreonNdomodCfg extends CentreonObject
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
        $this->object = new Centreon_Object_Ndomod();
        $this->params = array(  'output_type'               => 'tcpsocket',
                                'tcp_port'	                => '5668',
                                'output_buffer_items'       => '5000',
                                'file_rotation_interval'	=> '14400',
                                'file_rotation_timeout'		=> '60',
                                'reconnect_interval'		=> '15',
                                'reconnect_warning_interval'=> '900',
                                'data_processing_options'	=> '-1',
                                'config_output_options'		=> '3',
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
        $params = array("id", "description", "ns_nagios_server", "output_type", "output", "tcp_port");
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