<?php

/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

class CentreonCommand
{
    protected $db;

    public $aTypeMacro = array(
        '1' => 'HOST',
        '2' => 'SERVICE'
    );

    public $aTypeCommand = array(
        'host' => array(
            'key' => '$_HOST',
            'preg' => '/\$_HOST([\w_-]+)\$/'
        ),
        'service' => array(
            'key' => '$_SERVICE',
            'preg' => '/\$_SERVICE([\w_-]+)\$/'
        ),
    );

    /**
     * Constructor
     *
     * @param CentreonDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $commandType
     * @return array
     * @throws Exception
     */
    protected function getCommandList($commandType)
    {
        $query = 'SELECT command_id, command_name ' .
            'FROM command ' .
            'WHERE command_type = :type ' .
            'ORDER BY command_name';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type', $commandType, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $arr = array();
        while ($row = $stmt->fetch()) {
            $arr[$row['command_id']] = $row['command_name'];
        }
        return $arr;
    }

    /**
     * Get list of check commands
     *
     * @return array
     */
    public function getCheckCommands()
    {
        return $this->getCommandList(2);
    }

    /**
     * Get list of notification commands
     *
     * @return array
     */
    public function getNotificationCommands()
    {
        return $this->getCommandList(1);
    }

    /**
     * Get list of misc commands
     *
     * @return array
     */
    public function getMiscCommands()
    {
        return $this->getCommandList(3);
    }

    /**
     * Returns array of locked commands
     *
     * @return array
     */
    public function getLockedCommands()
    {
        static $arr = null;
        if (is_null($arr)) {
            $arr = array();
            $res = $this->db->query('SELECT command_id FROM command WHERE command_locked = 1');
            while ($row = $res->fetch()) {
                $arr[$row['command_id']] = true;
            }
        }
        return $arr;
    }

    /**
     * @param $iIdCommand
     * @param $sType
     * @param int $iWithFormatData
     * @return array
     * @throws Exception
     */
    public function getMacroByIdAndType($iIdCommand, $sType, $iWithFormatData = 1)
    {
        $macroToFilter = array("SNMPVERSION", "SNMPCOMMUNITY");
        if (empty($iIdCommand) || !array_key_exists($sType, $this->aTypeCommand)) {
            return array();
        }
        $aDescription = $this->getMacroDescription($iIdCommand);
        $query = 'SELECT command_id, command_name, command_line ' .
            'FROM command ' .
            'WHERE command_type = 2 ' .
            'AND command_id = :id ' .
            'AND command_line like :command ' .
            'ORDER BY command_name';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $iIdCommand, PDO::PARAM_INT);
        $commandLine = '%' . $this->aTypeCommand[$sType]['key'] . '%';
        $stmt->bindParam(':command', $commandLine, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $arr = array();
        $i = 0;
        if ($iWithFormatData == 1) {
            while ($row = $stmt->fetch()) {
                preg_match_all($this->aTypeCommand[$sType]['preg'], $row['command_line'], $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    if (!in_array($match[1], $macroToFilter)) {
                        $sName = $match[1];
                        $sDesc = isset($aDescription[$sName]['description']) ?
                            $aDescription[$sName]['description'] : "";
                        $arr[$i]['macroInput_#index#'] = $sName;
                        $arr[$i]['macroValue_#index#'] = "";
                        $arr[$i]['macroPassword_#index#'] = null;
                        $arr[$i]['macroDescription_#index#'] = $sDesc;
                        $arr[$i]['macroDescription'] = $sDesc;
                        $arr[$i]['macroCommandFrom'] = $row['command_name'];
                        $i++;
                    }
                }
            }
        } else {
            while ($row = $stmt->fetch()) {
                $arr[$row['command_id']] = $row['command_name'];
            }
        }
        return $arr;
    }

    /**
     * @param $iIdCmd
     * @return array
     * @throws Exception
     */
    public function getMacroDescription($iIdCmd)
    {
        $aReturn = array();
        $query = 'SELECT * FROM `on_demand_macro_command` WHERE `command_command_id` = :command';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':command', $iIdCmd, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            $arr['id'] = $row['command_macro_id'];
            $arr['name'] = $row['command_macro_name'];
            $arr['description'] = $row['command_macro_desciption'];
            $arr['type'] = $row['command_macro_type'];

            $aReturn[$row['command_macro_name']] = $arr;
        }
        $stmt->closeCursor();
        return $aReturn;
    }

    /**
     * @param $iCommandId
     * @param $aMacro
     * @param $sType
     * @return array
     * @throws Exception
     */
    public function getMacrosCommand($iCommandId, $aMacro, $sType)
    {
        $aReturn = array();

        if (count($aMacro) > 0 && array_key_exists($sType, $this->aTypeMacro)) {
            $queryValues = array();
            $explodedValues = '';

            $query = 'SELECT * FROM `on_demand_macro_command` ' .
                'WHERE command_command_id = ? ' .
                'AND command_macro_type = ? ' .
                'AND command_macro_name IN (';

            $queryValues[] = (int)$iCommandId;
            $queryValues[] = (string)$sType;
            if (!empty($aMacro)) {
                foreach ($aMacro as $k => $v) {
                    $explodedValues .= '?,';
                    $queryValues[] = (string)$v;
                }
                $explodedValues = rtrim($explodedValues, ',');
            } else {
                $explodedValues .= '""';
            }
            $query .= $explodedValues . ')';
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryValues);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                $arr['id'] = $row['command_macro_id'];
                $arr['name'] = $row['command_macro_name'];
                $arr['description'] = htmlentities($row['command_macro_desciption']);
                $arr['type'] = $sType;
                $aReturn[] = $arr;
            }
            $stmt->closeCursor();
        }
        return $aReturn;
    }

    /**
     * @param $iCommandId
     * @param $sStr
     * @param $sType
     * @return array
     */
    public function matchObject($iCommandId, $sStr, $sType)
    {
        $macros = array();
        $macrosDesc = array();

        if (array_key_exists($sType, $this->aTypeMacro)) {
            preg_match_all(
                $this->aTypeCommand[strtolower($this->aTypeMacro[$sType])]['preg'],
                $sStr,
                $matches1,
                PREG_SET_ORDER
            );

            foreach ($matches1 as $match) {
                $macros[] = $match[1];
            }

            if (count($macros) > 0) {
                $macrosDesc = $this->getMacrosCommand($iCommandId, $macros, $sType);
                $aNames = array_column($macrosDesc, 'name');

                foreach ($macros as $detail) {
                    if (!in_array($detail, $aNames) && !empty($detail)) {
                        $arr['id'] = "";
                        $arr['name'] = $detail;
                        $arr['description'] = "";
                        $arr['type'] = $sType;
                        $macrosDesc[] = $arr;
                    }
                }
            }
        }
        return $macrosDesc;
    }

    /**
     * @param array $values
     * @param array $options
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $items = array();
        $listValues = '';
        $queryValues = array();
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':command' . $v . ',';
                $queryValues['command' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= '""';
        }

        # get list of selected connectors
        $query = 'SELECT command_id, command_name FROM command ' .
            'WHERE command_id IN (' . $listValues . ') ' .
            'ORDER BY command_name ';
        $stmt = $this->db->prepare($query);

        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $items[] = array(
                'id' => $row['command_id'],
                'text' => $row['command_name']
            );
        }
        return $items;
    }

    /**
     * @param $id
     * @param array $parameters
     * @return array|mixed
     * @throws Exception
     */
    public function getParameters($id, $parameters = array())
    {
        $queryValues = array();
        $explodedValues = '';
        $arr = array();
        if (empty($id)) {
            return array();
        }
        if (count($parameters) > 0) {
            foreach ($parameters as $k => $v) {
                $explodedValues .= "`$v`,";
            }
            $explodedValues = rtrim($explodedValues, ',');
        } else {
            $explodedValues = "*";
        }

        $query = 'SELECT ' . $explodedValues . ' FROM command WHERE command_id = ?';
        $queryValues[] = (int)$id;
        $stmt = $this->db->prepare($query);
        $dbResult = $stmt->execute($queryValues);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if ($stmt->rowCount()) {
            $arr = $stmt->fetch();
        }
        return $arr;
    }

    /**
     * @param $name
     * @return array|mixed
     * @throws Exception
     */
    public function getCommandByName($name)
    {
        $arr = array();
        $query = 'SELECT * FROM command WHERE command_name = :commandName';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':commandName', $name, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if ($stmt->rowCount()) {
            $arr = $stmt->fetch();
        }
        return $arr;
    }

    /**
     * @param $name
     * @return null
     * @throws Exception
     */
    public function getCommandIdByName($name)
    {
        $query = 'SELECT command_id FROM command WHERE command_name = :commandName';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':commandName', $name, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if (!$stmt->rowCount()) {
            return null;
        }
        $row = $stmt->fetch();
        return $row['command_id'];
    }

    /**
     * @param $parameters
     * @param bool $locked
     * @throws Exception
     */
    public function insert($parameters, $locked = false)
    {
        $queryValues = array();
        $sQuery = 'INSERT INTO command ' .
            '(command_name, command_line, command_type, command_locked) ' .
            'VALUES (';

        if (isset($parameters['command_name']) && $parameters['command_name'] != "") {
            $sQuery .= '?, ';
            $queryValues[] = (string)$parameters['command_name'];
        } else {
            $sQuery .= '"", ';
        }
        if (isset($parameters['command_line']) && $parameters['command_line'] != "") {
            $sQuery .= '?, ';
            $queryValues[] = (string)$parameters['command_line'];
        } else {
            $sQuery .= '"", ';
        }
        if (isset($parameters['command_type']) && $parameters['command_type'] != "") {
            $sQuery .= '?, ';
            $queryValues[] = (int)$parameters['command_type'];
        } else {
            $sQuery .= "2, ";
        }

        if ($locked === true) {
            $sQuery .= '1';
        } else {
            $sQuery .= '0';
        }

        $sQuery .= ")";
        $stmt = $this->db->prepare($sQuery);
        $dbResult = $stmt->execute($queryValues);
        if (!$dbResult) {
            throw new \Exception('Error while insert command ' . $parameters['command_name']);
        }
    }

    /**
     * @param $commandId
     * @param $command
     * @throws Exception
     */
    public function update($commandId, $command)
    {
        $sQuery = 'UPDATE `command` SET `command_line` = :line, `command_type` = :cType WHERE `command_id` = :id';
        $stmt = $this->db->prepare($sQuery);
        $stmt->bindParam(':line', $command['command_line'], PDO::PARAM_STR);
        $stmt->bindParam(':cType', $command['command_type'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $commandId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception('Error while update command ' . $command['command_name']);
        }
    }

    /**
     * @param $commandName
     * @throws Exception
     */
    public function deleteCommandByName($commandName)
    {
        $sQuery = 'DELETE FROM command WHERE command_name = :commandName';
        $stmt = $this->db->prepare($sQuery);
        $stmt->bindParam(':commandName', $commandName, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception('Error while delete command ' . $commandName);
        }
    }

    /**
     * @param $commandName
     * @param bool $checkTemplates
     * @return array
     * @throws Exception
     */
    public function getLinkedServicesByName($commandName, $checkTemplates = true)
    {
        if ($checkTemplates) {
            $register = 0;
        } else {
            $register = 1;
        }

        $linkedCommands = array();
        $query = 'SELECT DISTINCT s.service_description ' .
            'FROM service s, command c ' .
            'WHERE s.command_command_id = c.command_id ' .
            'AND s.service_register = :register ' .
            'AND c.command_name = :commandName ';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':register', $register, PDO::PARAM_STR);
        $stmt->bindParam(':commandName', $commandName, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception('Error while getting linked services of ' . $commandName);
        }

        while ($row = $stmt->fetch()) {
            $linkedCommands[] = $row['service_description'];
        }
        return $linkedCommands;
    }

    /**
     * @param $commandName
     * @param bool $checkTemplates
     * @return array
     * @throws Exception
     */
    public function getLinkedHostsByName($commandName, $checkTemplates = true)
    {
        if ($checkTemplates) {
            $register = 0;
        } else {
            $register = 1;
        }

        $linkedCommands = array();
        $query = 'SELECT DISTINCT h.host_name ' .
            'FROM host h, command c ' .
            'WHERE h.command_command_id = c.command_id ' .
            'AND h.host_register = :register ' .
            'AND c.command_name = :commandName ';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':register', $register, PDO::PARAM_STR);
        $stmt->bindParam(':commandName', $commandName, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception('Error while getting linked hosts of ' . $commandName);
        }

        while ($row = $stmt->fetch()) {
            $linkedCommands[] = $row['host_name'];
        }
        return $linkedCommands;
    }
}
