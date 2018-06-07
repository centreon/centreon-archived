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
require_once "centreonManufacturer.class.php";
require_once "centreonHost.class.php";
require_once "centreonService.class.php";
require_once "Centreon/Object/Trap/Trap.php";
require_once "Centreon/Object/Trap/Matching.php";
require_once "Centreon/Object/Relation/Trap/Service.php";

/**
 *
 * @author sylvestre
 */
class CentreonTrap extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_OID               = 1;
    const UNKNOWN_STATUS          = "Unknown status";
    const INCORRECT_PARAMETER     = "Incorrect parameter";

    public static $aDepends = array(
        'VENDOR'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Trap();
        $this->manufacturerObj = new CentreonManufacturer();
        $this->params = array();
        $this->insertParams = array('traps_name', 'traps_oid');
        $this->action = "TRAP";
        $this->nbOfCompulsoryParams = count($this->insertParams);
    }

    /**
     * Add action
     *
     * @param string $parameters
     * @return void
     */
    public function add($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['traps_oid'] = $params[self::ORDER_OID];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Get monitoring status
     *
     * @param string $val
     * @return int
     * @throws CentreonClapiException
     */
    public function getStatusInt($val)
    {
        $val = strtolower($val);
        if (!is_numeric($val)) {
            $statusTab = array('ok' => 0, 'warning' => 1, 'critical' => 2, 'unknown' => 3);
            if (isset($statusTab[$val])) {
                return $statusTab[$val];
            } else {
                throw new CentreonClapiException(self::UNKNOWN_STATUS.":".$val);
            }
        } elseif ($val > 3) {
            throw new CentreonClapiException(self::UNKNOWN_STATUS.":".$val);
        }
        return $val;
    }

    /**
     * Set Parameters
     *
     * @param string $parameters
     * @return void
     * @throws Exception
     */
    public function setparam($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if ($params[1] == "manufacturer" || $params[1] == "vendor") {
                $params[1] = "manufacturer_id";
                $params[2] = $this->manufacturerObj->getId($params[2]);
            } elseif ($params[1] == "status") {
                $params[1] = "traps_status";
                $params[2] = $this->getStatusInt($params[2]);
            } elseif ($params[1] == "output") {
                $params[1] = 'traps_args';
            } elseif ($params[1] == "matching_mode") {
                $params[1] = "traps_advanced_treatment";
            } elseif (!preg_match('/^traps_/', $params[1])) {
                $params[1] = 'traps_'.$params[1];
            }
            $params[2]=str_replace("<br/>", "\n", $params[2]);
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
        $params = array("traps_id", "traps_name", "traps_oid", "manufacturer_id");
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("traps ", "", $paramString);
        $paramString = str_replace("manufacturer id", "manufacturer", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                if ($key == "manufacturer_id") {
                    $value = $this->manufacturerObj->getName($value);
                }
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }

    /**
     * Get matching rules
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getmatching($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $trapId = $this->getObjectId($parameters);
        if (!$trapId) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$parameters);
        }
        $matchObj = new \Centreon_Object_Trap_Matching();
        $params = array('tmo_id', 'tmo_string', 'tmo_regexp', 'tmo_status', 'tmo_order');
        $elements = $matchObj->getList($params, -1, 0, 'tmo_order', 'ASC', array('trap_id' => $trapId));
        $status = array(0 => 'OK', 1 => 'WARNING', 2 => 'CRITICAL', 3 => 'UNKNOWN');
        echo "id".$this->delim."string".$this->delim."regexp".$this->delim."status".$this->delim."order\n";
        foreach ($elements as $element) {
            echo $element['tmo_id'].$this->delim.
                 $element['tmo_string'].$this->delim.
                 $element['tmo_regexp'].$this->delim.
                 $status[$element['tmo_status']].$this->delim.
                 $element['tmo_order']."\n";
        }
    }

    /**
     * Add matching rule
     *
     * @param string $parameters
     * @return void
     */
    public function addmatching($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < 4) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $trapId = $this->getObjectId($params[0]);
        if (!$trapId) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[0]);
        }
        $string= $params[1];
        $regexp = $params[2];
        $status = $this->getStatusInt($params[3]);
        $matchObj = new \Centreon_Object_Trap_Matching();
        $elements = $matchObj->getList(
            "*",
            -1,
            0,
            null,
            null,
            array(
                'trap_id' => $trapId,
                'tmo_regexp' => $regexp,
                'tmo_string' => $string,
                'tmo_status' => $status),
            'AND'
        );
        if (!count($elements)) {
            $elements = $matchObj->getList("*", -1, 0, null, null, array('trap_id' => $trapId));
            $order = count($elements)+1;
            $matchObj->insert(array(
                'trap_id'    => $trapId,
                'tmo_regexp' => $regexp,
                'tmo_string' => $string,
                'tmo_status' => $status,
                'tmo_order'  => $order
            ));
        }
    }

    /**
     * Delete matching rule
     *
     * @param string $parameters
     * @return void
     */
    public function delmatching($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (!is_numeric($parameters)) {
            throw new CentreonClapiException('Incorrect id parameters');
        }
        $matchObj = new \Centreon_Object_Trap_Matching();
        $matchObj->delete($parameters);
    }

    /**
     * Update matching rules
     *
     * @param string $parameters
     * @return void
     */
    public function updatematching($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $matchingId = $params[0];
        if (!is_numeric($matchingId)) {
            throw new CentreonClapiException('Incorrect id parameters');
        }
        $key = $params[1];
        $value = $params[2];
        if ($key == 'trap_id') {
            throw new CentreonClapiException(self::INCORRECT_PARAMETER);
        }
        if (!preg_match("/tmo_/", $key)) {
            $key = 'tmo_'.$key;
        }
        if ($key == 'tmo_status') {
            $value = $this->getStatusInt($value);
        }
        $matchObj = new \Centreon_Object_Trap_Matching();
        $matchObj->update($matchingId, array($key => $value));
    }

    /**
     * Export
     *
     * @return void
     */
    public function export($filterName)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array();
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }

        $elements = $this->object->getList("*", -1, 0, null, null, $filters, "AND");
        foreach ($elements as $element) {
            $addStr = $this->action.$this->delim."ADD";
            foreach ($this->insertParams as $param) {
                $addStr .= $this->delim.$element[$param];
            }
            $addStr .= "\n";
            echo $addStr;
            foreach ($element as $parameter => $value) {
                if ($parameter != 'traps_id') {
                    if (!is_null($value) && $value != "") {
                        $value = str_replace("\n", "<br/>", $value);
                        if ($parameter == 'manufacturer_id') {
                            $parameter = 'vendor';
                            $value = $this->manufacturerObj->getName($value);
                        }
                        $value = CentreonUtils::convertLineBreak($value);
                        echo $this->action . $this->delim
                            . "setparam" . $this->delim
                            . $element[$this->object->getUniqueLabelField()] . $this->delim
                            . $parameter . $this->delim
                            . $value . "\n";
                    }
                }
            }
            $matchingObj = new \Centreon_Object_Trap_Matching();
            $matchingProps = $matchingObj->getList("*", -1, 0, null, null, array('trap_id' => $element['traps_id']));
            foreach ($matchingProps as $prop) {
                echo $this->action.$this->delim.
                     "addmatching".$this->delim.
                     $element['traps_name'].$this->delim.
                     $prop['tmo_string'].$this->delim.
                     $prop['tmo_regexp'].$this->delim.
                     $prop['tmo_status']."\n";
            }
        }
    }
}
