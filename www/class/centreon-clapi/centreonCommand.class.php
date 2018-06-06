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
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonUtils.class.php";
require_once "Centreon/Object/Command/Command.php";
require_once "Centreon/Object/Graph/Template/Template.php";

/**
 *
 * Centreon Command Class
 * @author jmathis
 *
 */
class CentreonCommand extends CentreonObject
{

    const ORDER_UNIQUENAME = 0;
    const ORDER_TYPE = 1;
    const ORDER_COMMAND = 2;
    const UNKNOWN_CMD_TYPE = "Unknown command type";

    public $aTypeCommand = array(
        'host'    => array(
            'key' => '$_HOST',
            'preg' => '/\$_HOST(\w+)\$/'
        ),
        'service' => array(
            'key' => '$_SERVICE',
            'preg' => '/\$_SERVICE(\w+)\$/'
        ),
    );

    protected $typeConversion;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Command();
        $this->params = array();
        $this->insertParams = array("command_name", "command_type", "command_line");
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array($this->object->getPrimaryKey(), "graph_id", "cmd_cat_id")
        );
        $this->action = "CMD";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->typeConversion = array(
            "notif" => 1,
            "check" => 2,
            "misc" => 3,
            "discovery" => 4,
            1 => "notif",
            2 => "check",
            3 => "misc",
            4 => "discovery"
        );
    }

    /**
     * Display all commands
     *
     * @param string $parameters
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array('command_id', 'command_name', 'command_type', 'command_line');
        $paramString = str_replace("command_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $tab['command_line'] = CentreonUtils::convertSpecialPattern(html_entity_decode($tab['command_line']));
            $tab['command_line'] = CentreonUtils::convertLineBreak($tab['command_line']);
            $tab['command_type'] = $this->typeConversion[$tab['command_type']];
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Add a command
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $this->checkIllegalChar($params[self::ORDER_UNIQUENAME]);
        if (!isset($this->typeConversion[$params[self::ORDER_TYPE]])) {
            throw new CentreonClapiException(self::UNKNOWN_CMD_TYPE . ":" . $params[self::ORDER_TYPE]);
        }
        $addParams['command_type'] =
            is_numeric($params[self::ORDER_TYPE])
            ? $params[self::ORDER_TYPE]
            : $this->typeConversion[$params[self::ORDER_TYPE]];
        $addParams['command_line'] = $params[self::ORDER_COMMAND];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if (!preg_match("/^command_/", $params[1])) {
                if (!in_array($params[1], array('graph', 'enable_shell', 'connector_id'))) {
                    $params[1] = "command_" . $params[1];
                } elseif ($params[1] == "graph") {
                    $params[1] = "graph_id";
                }
            }
            if ($params[1] == "command_type") {
                if (!isset($this->typeConversion[$params[2]])) {
                    throw new CentreonClapiException(self::UNKNOWN_CMD_TYPE . ":" . $params[2]);
                }
                if (!is_numeric($params[2])) {
                    $params[2] = $this->typeConversion[$params[2]];
                }
            } elseif ($params[1] == "graph_id") {
                $graphObject = new \Centreon_Object_Graph_Template();
                $tmp = $graphObject->getIdByParameter($graphObject->getUniqueLabelField(), $params[2]);
                if (!count($tmp)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                }
                $params[2] = $tmp[0];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Returns command id
     *
     * @param string $commandName
     * @return int
     * @throws CentreonClapiException
     */
    public function getId($commandName)
    {
        $obj = new \Centreon_Object_Command();
        $tmp = $obj->getIdByParameter($obj->getUniqueLabelField(), $commandName);
        if (count($tmp)) {
            $id = $tmp[0];
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $commandName);
        }
        return $id;
    }

    /**
     * Export data
     *
     * @param string $parameters
     * @return void
     */

    public function export($filter_name)
    {
        if (!$this->canBeExported($filter_name)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array($labelField => $filter_name);
        $elements = $this->object->getList(
            "*",
            -1,
            0,
            null,
            null,
            array($filters => $filter_name)
        );
        foreach ($elements as $element) {
            $addStr = $this->action.$this->delim."ADD";
            foreach ($this->insertParams as $param) {
                $addStr .= $this->delim.$element[$param];
            }
            $addStr .= "\n";
            echo $addStr;

            foreach ($element as $parameter => $value) {
                if (!in_array($parameter, $this->exportExcludedParams)) {
                    if (!is_null($value) && $value != "") {
                        $value = CentreonUtils::convertLineBreak($value);
                        echo $this->action . $this->delim
                            . "setparam" . $this->delim
                            . $element[$this->object->getUniqueLabelField()] . $this->delim
                            . $parameter . $this->delim
                            . $value . "\n";
                    }
                }
                if ($parameter == "graph_id" && !empty($value)) {
                    $graphObject = new \Centreon_Object_Graph_Template();
                    $tmp = $graphObject->getParameters($value, array($graphObject->getUniqueLabelField()));

                    if (!count($tmp)) {
                        throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $value);
                    }

                    $v = $tmp[$graphObject->getUniqueLabelField()];
                    $v = CentreonUtils::convertLineBreak($v);

                    echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element[$this->object->getUniqueLabelField()] . $this->delim
                        . $this->getClapiActionName($parameter) . $this->delim
                        . $v . "\n";
                }
            }
        }
    }

    /**
     * Get clapi action name from db column name
     *
     * @param string $columnName
     * @return string
     */
    protected function getClapiActionName($columnName)
    {
        static $table;

        if (!isset($table)) {
            $table = array("graph_id" => "graph");
        }
        if (isset($table[$columnName])) {
            return $table[$columnName];
        }
        return $columnName;
    }


        /**
     * This method gat the list of command containt a specific macro
     * @param int $iIdCommand
     * @param string $sType
     * @param int $iWithFormatData
     *
     * @return array
     */
    public function getMacroByIdAndType($iIdCommand, $sType, $iWithFormatData = 1)
    {
        $inputName = $sType;
        if ($sType == "service") {
            $inputName = "svc";
        }
        $macroToFilter = array("SNMPVERSION","SNMPCOMMUNITY");

        if (empty($iIdCommand) || !array_key_exists($sType, $this->aTypeCommand)) {
            return array();
        }

        $aDescription = $this->getMacroDescription($iIdCommand);

        $sql = "SELECT command_id, command_name, command_line
            FROM command
            WHERE command_type = 2
            AND command_id = ?
            AND command_line like '%".$this->aTypeCommand[$sType]['key']."%'
            ORDER BY command_name";

        $res = $this->db->query($sql, array($iIdCommand));
        $arr = array();
        $i = 0;

        if ($iWithFormatData == 1) {
            while ($row = $res->fetch()) {
                preg_match_all($this->aTypeCommand[$sType]['preg'], $row['command_line'], $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    if (!in_array($match[1], $macroToFilter)) {
                        $sName = $match[1];
                        $sDesc = isset($aDescription[$sName]['description'])
                            ? $aDescription[$sName]['description']
                            : "";
                        $arr[$i][$inputName.'_macro_name'] = $sName;
                        $arr[$i][$inputName.'_macro_value'] = "";
                        $arr[$i]['is_password'] = null;
                        $arr[$i]['macroDescription'] = $sDesc;
                        $i++;
                    }
                }
            }
        } else {
            while ($row = $res->fetch()) {
                $arr[$row['command_id']] = $row['command_name'];
            }
        }
        return $arr;
    }


    /**
     *
     * @param type $iIdCmd
     * @return string
     */
    public function getMacroDescription($iIdCmd)
    {
        $aReturn = array();
        $sSql = "SELECT * FROM `on_demand_macro_command` WHERE `command_command_id` = ".intval($iIdCmd);

        $DBRESULT = $this->db->query($sSql);
        while ($row = $DBRESULT->fetch()) {
            $arr['id']   = $row['command_macro_id'];
            $arr['name'] = $row['command_macro_name'];
            $arr['description'] = $row['command_macro_desciption'];
            $arr['type']        = $row['command_macro_type'];

            $aReturn[$row['command_macro_name']] = $arr;
        }
        return $aReturn;
    }
}
