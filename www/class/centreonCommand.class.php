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
    protected $_db;
    
    public $aTypeMacro = array(
        '1' => 'HOST',
        '2' => 'SERVICE'
    );
    
    public $aTypeCommand = array(
            'host'    => array(
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
    public function __construct($db) {
        $this->_db = $db;
    }
    
    /**
     * Get command list
     * 
     * @parma int $commandType
     * @return array
     */
    protected function getCommandList($commandType) {
        $sql = "SELECT command_id, command_name
            FROM command
            WHERE command_type = ?
            ORDER BY command_name";
        $res = $this->_db->query($sql, array($commandType));
        $arr = array();
        while ($row = $res->fetchRow()) {
            $arr[$row['command_id']] = $row['command_name'];
        }
        return $arr;
    }
    
    /**
     * Get list of check commands
     * 
     * @return array
     */
    public function getCheckCommands() {
        return $this->getCommandList(2);
    }
    
    /**
     * Get list of notification commands
     * 
     * @return array
     */
    public function getNotificationCommands() {
        return $this->getCommandList(1);
    }
    
    /**
     * Get list of misc commands
     * 
     * @return array
     */
    public function getMiscCommands() {
        return $this->getCommandList(3);
    }

    /**
     * Returns array of locked commands
     *
     * @return array
     */
    public function getLockedCommands() {
        static $arr = null;

        if (is_null($arr)) {
            $arr = array();
            $res = $this->_db->query("SELECT command_id
               FROM command
               WHERE command_locked = 1");
            while ($row = $res->fetchRow()) {
                $arr[$row['command_id']] = true;
            }
        }
        return $arr;
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
        
        $res = $this->_db->query($sql, array($iIdCommand));
        $arr = array();
        $i = 0;
        
        if ($iWithFormatData == 1) {
             while ($row = $res->fetchRow()) {
                 
                preg_match_all($this->aTypeCommand[$sType]['preg'], $row['command_line'], $matches, PREG_SET_ORDER);
                
                foreach ($matches as $match) {
                    if(!in_array($match[1], $macroToFilter)){
                        $sName = $match[1];
                        $sDesc = isset($aDescription[$sName]['description']) ? $aDescription[$sName]['description'] : "";
                        $arr[$i]['macroInput_#index#'] = $sName;
                        $arr[$i]['macroValue_#index#'] = "";
                        $arr[$i]['macroPassword_#index#'] = NULL;
                        $arr[$i]['macroDescription_#index#'] = $sDesc;
                        $arr[$i]['macroDescription'] = $sDesc;
                        $arr[$i]['macroCommandFrom'] = $row['command_name'];
                        $i++;
                    }
                }
            }
        } else {
            while ($row = $res->fetchRow()) {
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
        
        $DBRESULT = $this->_db->query($sSql);
        while ($row = $DBRESULT->fetchRow()){ 
            $arr['id']   = $row['command_macro_id'];
            $arr['name'] = $row['command_macro_name'];
            $arr['description'] = $row['command_macro_desciption'];
            $arr['type']        = $row['command_macro_type'];
            
            $aReturn[$row['command_macro_name']] = $arr;
        }
        $DBRESULT->free();
        
        return $aReturn;
    }
    
   /**
    * This method search macro in commande by name and type 
    * 
    * @param int $iIdCommande
    * @param array $aMacro
    * @param string $sType
    * 
    * @return array $aReturn
    */
   function getMacrosCommand($iIdCommande, $aMacro, $sType)
   {
       $aReturn = array();

       if (count($aMacro) > 0 && array_key_exists($sType, $this->aTypeMacro)) {
            $sRq = "SELECT * FROM `on_demand_macro_command` WHERE "
                    ." command_command_id = " . intval($iIdCommande)
                    . " AND command_macro_type = '".$sType."' "
                    . " AND command_macro_name IN ('".  implode("', '", $aMacro)."') "; 

            $DBRESULT = $this->_db->query($sRq);
            while ($row = $DBRESULT->fetchRow()){

                $arr['id']   = $row['command_macro_id'];
                $arr['name'] = $row['command_macro_name'];
                $arr['description'] = $row['command_macro_desciption'];
                $arr['type']        = $sType;
                $aReturn[] = $arr;
            }
            $DBRESULT->free();
       }

       return $aReturn;
   }
   
   /**
    * 
    * @param int $iIdCommande
    * @param string $sStr
    * @param string $sType
    * 
    * @return array
    */
    function match_object($iIdCommande, $sStr, $sType)
    {
        $macros = array();
        $macrosDesc = array();

        if (array_key_exists($sType, $this->aTypeMacro)) {

            preg_match_all($this->aTypeCommand[strtolower($this->aTypeMacro[$sType])]['preg'], $sStr, $matches1, PREG_SET_ORDER);   

            foreach ($matches1 as $match) {
              $macros[] = $match[1];
            }

            if (count($macros) > 0) {
                $macrosDesc = $this->getMacrosCommand($iIdCommande, $macros, $sType);

                $aNames = array_column($macrosDesc, 'name');

                foreach ($macros as $detail) {
                    if (!in_array($detail, $aNames) && !empty($detail)) {
                        $arr['id']          = "";
                        $arr['name']        = $detail;
                        $arr['description'] = "";
                        $arr['type']        = $sType;

                        $macrosDesc[] = $arr;
                    }
                }
            }
        }
        
        return $macrosDesc;
    }
    
    /**
     * 
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $items = array();
        
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected connectors
        $query = "SELECT command_id, command_name "
            . "FROM command "
            . "WHERE command_id IN (" . $explodedValues . ") "
            . "ORDER BY command_name ";
        
        $resRetrieval = $this->_db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['command_id'],
                'text' => $row['command_name']
            );
        }

        return $items;
    }
    
    /**
     * Returns command details
     * 
     * @param int $id
     * @return array
     */
    public function getParameters($id, $parameters = array())
    {
        $sElement = "*";
        $arr = array();
        if (empty($id)) {
            return array();
        }
        if (count($parameters) > 0) {
            $sElement = implode(",", $parameters);
        }

        $res = $this->_db->query("SELECT ".$sElement." FROM command 
                WHERE command_id = ".$this->_db->escape($id));
        
        if ($res->numRows()) {
            $arr = $res->fetchRow();
        }

        return $arr;
    }

    /**
     * 
     * 
     * @param $name
     * @return array
     * @throws Exception
     */
    public function getCommandByName($name)
    {
        $arr = array();
        $query = "SELECT * FROM command 
                WHERE command_name = '".$this->_db->escape($name)."'";

        $res = $this->_db->query($query);

        if ($res->numRows()) {
            $arr = $res->fetchRow();
        }

        return $arr;
    }
    
    /**
     * 
     * @param string $name
     * @return string
     */
    public function getCommandIdByName($name)
    {
        $query = "SELECT command_id FROM command 
                WHERE command_name = '".$this->_db->escape($name)."'";

        $res = $this->_db->query($query);

        if (!$res->numRows()) {
            return null;
        }
        $row = $res->fetchRow();
        
        return $row['command_id'];
    }

    /**
     * Insert in database a command
     *
     * @param array $parameters Values to insert (command_name and command_line is mandatory)
     * @throws Exception
     */
    public function insert($parameters)
    {
        $sQuery = "INSERT INTO command "
            . "(command_name, command_line, command_type) "
            . "VALUES (";

        (isset($parameters['command_name']) && $parameters['command_name'] != "") ? $sQuery .= '"' . $this->_db->escape($parameters['command_name']) . '", ' : '"", ';
        (isset($parameters['command_line']) && $parameters['command_line'] != "") ? $sQuery .= '"' . $this->_db->escape($parameters['command_line']) . '", ' : '"", ';
        (isset($parameters['command_type']) && $parameters['command_type'] != "") ? $sQuery .= '"' . $this->_db->escape($parameters['command_type']) . '"' : $sQuery .= "'2' ";

        $sQuery .= ")";

        $res = $this->_db->query($sQuery);
        if (\PEAR::isError($res)) {
            throw new \Exception('Error while insert command '.$parameters['command_name']);
        }
    }

    /**
     * Update in database a command
     *
     * @param int $command_id Id of command
     * @param array $command Values to set
     * @throws Exception
     */
    public function update($command_id, $command)
    {
        $sQuery = "UPDATE `command` SET ";
        $sQuery .= "`command_line` = '".$this->_db->escape($command['command_line'])."', `command_type` = '".$this->_db->escape($command['command_type']);
        $sQuery .= "' WHERE `command_id` = ".$command_id;

        $res = $this->_db->query($sQuery);
        if (\PEAR::isError($res)) {
            throw new \Exception('Error while update command ' . $command['command_name']);
        }
    }

    /**
     * Delete command in database
     *
     * @param string $command_name Command name
     * @throws Exception
     */
    public function deleteCommandByName($command_name)
    {
        $sQuery = 'DELETE FROM command '
            . 'WHERE command_name = "' . $this->_db->escape($command_name) . '"';

        $res = $this->_db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while delete command ' . $command_name);
        }
    }
    
    /**
     * 
     * @param type $sCommandName
     * @param type $aHost
     * @param type $bTpl
     * @return array
     */
    public function checkCommandUsedInHost($sCommandName, $aHost, $bTpl = false)
    {
        $aReturn = array();
        if (empty($sCommandName) || count($aHost) == 0) {
            return $aReturn;
        }
        $sQuery = "select host_name from command "
            . " join host on (command_id in (command_command_id, command_command_id2)) "
            . " where command_name = '".$this->_db->escape($sCommandName)."'";
        
        if ($bTpl) {
            $sQuery .= " AND host_register = '1' ";
        } else {
            $sQuery .= " AND host_register = '0' ";
        }
        
        $sQuery .= " AND host_name not in ('".  implode("'", $aHost)."')";
        
        $res = $this->_db->query($sQuery);
        if (PEAR::isError($res)) {
            return $aReturn;
        }
        while ($row = $res->fetchRow()) {
            if (!empty($row['host_name'])) {
                $aReturn[] = $row['host_name'];
            }
        }
        
        return $aReturn;
    }
    
    /**
     * 
     * @param type $sCommandName
     * @param type $aervice
     * @param type $bTpl
     * @return array
     */
    public function checkCommandUsedInService($sCommandName, $aService, $bTpl = false)
    {
        $aReturn = array();
        if (empty($sCommandName) || count($aService) == 0) {
            return $aReturn;
        }
        $sQuery = "select service_description from command "
            . " join service on (command_id in (command_command_id, command_command_id2)) "
            . " where command_name = '".$this->_db->escape($sCommandName)."'";
        
        if ($bTpl) {
            $sQuery .= " AND service_register = '1' ";
        } else {
            $sQuery .= " AND service_register = '0' ";
        }
        
        $sQuery .= " AND service_description not in ('".  implode("'", $aService)."')";
        
        $res = $this->_db->query($sQuery);
        if (PEAR::isError($res)) {
            return $aReturn;
        }
        while ($row = $res->fetchRow()) {
            if (!empty($row['service_description'])) {
                $aReturn[] = $row['service_description'];
            }
        }
        
        return $aReturn;
    }
}
