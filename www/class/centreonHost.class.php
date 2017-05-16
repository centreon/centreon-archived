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

require_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonService.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonCommand.class.php';

/*
 *  Class that contains various methods for managing hosts
 */

class CentreonHost
{
    /**
     *
     * @var type
     */
    protected $db;

    /**
     *
     * @var type
     */
    protected $instanceObj;

    /**
     *
     * @var type
     */
    protected $serviceObj;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->instanceObj = new CentreonInstance($db);
        $this->serviceObj = new CentreonService($db);
    }

    /**
     * Get the list of all host
     *
     * @param bool $enable If get only host enable
     * @return array
     */
    public function getList($enable = false, $template = false)
    {
        $hostType = 1;
        if ($template) {
            $hostType = 0;
        }
        $queryList = 'SELECT host_id, host_name ' .
            'FROM host ' .
            'WHERE host_register = ? ';
        if ($enable) {
            $queryList .= 'AND host_activate = "1" ';
        }
        $queryList .= 'ORDER BY host_name';
        $stmt = $this->db->prepare($queryList);
        $res = $this->db->execute($stmt, array((string)$hostType));
        if (PEAR::isError($res)) {
            return array();
        }
        $listHost = array();
        while ($row = $res->fetchRow()) {
            $listHost[$row['host_id']] = $row['host_name'];
        }
        return $listHost;
    }

    /**
     * Get the list of host children for a host
     *
     * @param int $hostId The parent host id
     * @param bool $withHg If use hostgroup relation (not use yet)
     * @return array
     */
    public function getHostChild($hostId, $withHg = false)
    {
        if (!is_numeric($hostId)) {
            return array();
        }
        $queryGetChildren = 'SELECT h.host_id, h.host_name ' .
            'FROM host h, host_hostparent_relation hp ' .
            'WHERE hp.host_host_id = h.host_id ' .
            'AND h.host_register = "1" ' .
            'AND h.host_activate = "1" ' .
            'AND hp.host_parent_hp_id = ?';
        $stmt = $this->db->prepare($queryGetChildren);
        $res = $this->db->execute($stmt, array((int)$hostId));
        if (PEAR::isError($res)) {
            return array();
        }
        $listHostChildren = array();
        while ($row = $res->fetchRow()) {
            $listHostChildren[$row['host_id']] = $row['host_alias'];
        }
        return $listHostChildren;
    }

    /**
     * Get the relation tree
     *
     * @param bool $withHg If use hostgroup relation (not use yet)
     * @return array
     */
    public function getHostRelationTree($withHg = false)
    {
        $queryGetRelationTree = 'SELECT hp.host_parent_hp_id, h.host_id, h.host_name ' .
            'FROM host h, host_hostparent_relation hp ' .
            'WHERE hp.host_host_id = h.host_id ' .
            'AND h.host_register = "1" ' .
            'AND h.host_activate = "1"';
        $res = $this->db->query($queryGetRelationTree);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHostRelactionTree = array();
        while ($row = $res->fetchRow()) {
            if (!isset($listHostRelactionTree[$row['host_parent_hp_id']])) {
                $listHostRelactionTree[$row['host_parent_hp_id']] = array();
            }
            $listHostRelactionTree[$row['host_parent_hp_id']][$row['host_id']] = $row['host_alias'];
        }
        return $listHostRelactionTree;
    }

    /**
     * Get list of services for a host
     *
     * @param int $hostId The host id
     * @param bool $withHg If use hostgroup relation
     * @return array
     */
    public function getServices($hostId, $withHg = false, $withDisabledServices = false)
    {
        /*
         * Get service for a host
         */
        $queryGetServices = 'SELECT s.service_id, s.service_description ' .
            'FROM service s, host_service_relation hsr, host h ' .
            'WHERE s.service_id = hsr.service_service_id ' .
            'AND s.service_register = "1" ' .
            ($withDisabledServices ? '' : 'AND s.service_activate = "1" ') .
            'AND h.host_id = hsr.host_host_id ' .
            'AND h.host_register = "1" ' .
            'AND h.host_activate = "1" ' .
            'AND hsr.host_host_id = ?';

        $stmt = $this->db->prepare($queryGetServices);
        $res = $this->db->execute($stmt, array((int)$hostId));
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[$row['service_id']] = $row['service_description'];
        }
        /*
         * With hostgroup
         */
        if ($withHg) {
            $queryGetServicesWithHg = 'SELECT s.service_id, s.service_description ' .
                'FROM service s, host_service_relation hsr, hostgroup_relation hgr, host h, hostgroup hg ' .
                'WHERE s.service_id = hsr.service_service_id ' .
                'AND s.service_register = "1" ' .
                ($withDisabledServices ? '' : 'AND s.service_activate = "1" ') .
                'AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id ' .
                'AND h.host_id = hgr.host_host_id ' .
                'AND h.host_register = "1" ' .
                'AND h.host_activate = "1" ' .
                'AND hg.hg_id = hgr.hostgroup_hg_id ' .
                'AND hg.hg_activate = "1" ' .
                'AND hgr.host_host_id = ?';
            $stmt = $this->db->prepare($queryGetServicesWithHg);
            $res = $this->db->execute($stmt, array((int)$hostId));
            if (PEAR::isError($res)) {
                return array();
            }
            while ($row = $res->fetchRow()) {
                $listServices[$row['service_id']] = $row['service_description'];
            }
        }
        return $listServices;
    }

    /**
     * Get the relation tree for host / service
     *
     * @param bool $withHg With Hostgroup
     * @return array
     */
    public function getHostServiceRelationTree($withHg = false)
    {
        /*
         * Get service for a host
         */
        $query = 'SELECT hsr.host_host_id, s.service_id, s.service_description ' .
            'FROM service s, host_service_relation hsr, host h ' .
            'WHERE s.service_id = hsr.service_service_id ' .
            'AND s.service_register = "1" ' .
            'AND s.service_activate = "1" ' .
            'AND h.host_id = hsr.host_host_id ' .
            'AND h.host_register = "1" ' .
            'AND h.host_activate = "1" ';
        if ($withHg == true) {
            $query .= 'UNION ' .
                'SELECT hgr.host_host_id, s.service_id, s.service_description ' .
                'FROM service s, host_service_relation hsr, host h, hostgroup_relation hgr ' .
                'WHERE s.service_id = hsr.service_service_id ' .
                'AND s.service_register = "1" ' .
                'AND s.service_activate = "1" ' .
                'AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id ' .
                'AND hgr.host_host_id = h.host_id ' .
                'AND h.host_register = "1" ' .
                'AND h.host_activate = "1"';
        }
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            if (!isset($listServices[$row['host_host_id']])) {
                $listServices[$row['host_host_id']] = array();
            }
            $listServices[$row['host_host_id']][$row['service_id']] = $row['service_description'];
        }
        return $listServices;
    }

    /**
     * Method that returns a hostname from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostName($host_id)
    {
        static $hosts = null;

        if (!isset($host_id) || !$host_id) {
            return null;
        }

        if (is_null($hosts)) {
            $hosts = array();
            $query = 'SELECT host_id, host_name FROM host';
            $res = $this->db->query($query);
            while ($row = $res->fetchRow()) {
                $hosts[$row['host_id']] = $row['host_name'];
            }
        }
        if (isset($hosts[$host_id])) {
            return $hosts[$host_id];
        }
        return null;
    }

    /**
     * @param $host_id
     * @return mixed
     */
    public function getOneHostName($host_id)
    {
        if (isset($host_id) && is_numeric($host_id)) {
            $query = 'SELECT host_id, host_name FROM host where host_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$host_id));
            $row = $res->fetchRow();
            return $row['host_name'];
        }
    }

    /**
     * @param array $hostId
     * @return array
     */
    public function getHostsNames($hostId = array())
    {
        $arrayReturn = array();
        $explodedValues = '';
        if (!empty($hostId)) {
            $query = 'SELECT host_id, host_name ' .
                'FROM host where host_id IN (';

            for ($i = 1; $i <= count($hostId); $i++) {
                $explodedValues .= '?,';
            }
            $explodedValues = rtrim($explodedValues, ',');
            $hostId = array_map(
                function ($var) {
                    return (int)$var;
                },
                $hostId
            );
            $query .= $explodedValues . ') ';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, $hostId);

            while ($row = $res->fetchRow()) {
                $arrayReturn[] = array("id" => $row['host_id'], "name" => $row['host_name']);
            }
        }
        return $arrayReturn;
    }

    /**
     * @param $host_id
     * @return mixed
     */
    public function getHostCommandId($host_id)
    {
        if (isset($host_id) && is_numeric($host_id)) {
            $query = 'SELECT host_id, command_command_id FROM host where host_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$host_id));
            $row = $res->fetchRow();
            return $row['command_command_id'];
        }
    }


    /**
     * Method that returns a host alias from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostAlias($hostId)
    {
        static $aliasTab = array();

        if (!isset($hostId) || !$hostId) {
            return null;
        }
        if (!isset($aliasTab[$hostId])) {
            $query = 'SELECT host_alias FROM host WHERE host_id = ? LIMIT 1';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$hostId));
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $aliasTab[$hostId] = $row['host_alias'];
            }
        }
        if (isset($aliasTab[$hostId])) {
            return $aliasTab[$hostId];
        }
        return null;
    }

    /**
     * Method that returns a host address from host_id
     *
     * @param int $host_id
     * @return string
     */
    public function getHostAddress($host_id)
    {
        static $addrTab = array();

        if (!isset($host_id) || !$host_id) {
            return null;
        }
        if (!isset($addrTab[$host_id])) {
            $query = 'SELECT host_address FROM host WHERE host_id = ? LIMIT 1';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$host_id));
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $addrTab[$host_id] = $row['host_address'];
            }
        }
        if (isset($addrTab[$host_id])) {
            return $addrTab[$host_id];
        }
        return null;
    }


    /**
     * Method that returns a host address from host_id
     *
     * @param string $address
     * @return array
     */
    public function getHostByAddress($address, $params = array())
    {
        $paramsList = '';
        $hostList = array();
        $queryValues = array();

        if (count($params) > 0) {
            foreach ($params as $k => $v) {
                $paramsList .= '?,';
                $queryValues[] = (string)$v;
            }
            $paramsList = rtrim($paramsList, ',');
        } else {
            $paramsList .= '*';
        }
        $query = 'SELECT ' . $paramsList . ' FROM host WHERE host_address = ?';
        $queryValues[] = (string)$address;

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);

        while ($row = $res->fetchRow()) {
            $hostList[] = $row;
        }

        return $hostList;
    }


    /**
     * Method that returns the id of a host
     *
     * @param string $host_name
     * @return int
     */
    public function getHostId($host_name)
    {
        static $ids = array();

        if (!isset($host_name) || !$host_name) {
            return null;
        }
        if (!isset($ids[$host_name])) {
            $query = 'SELECT host_id ' .
                'FROM host ' .
                'WHERE host_name = ?' .
                'LIMIT 1';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((string)$host_name));

            if ($res->numRows()) {
                $row = $res->fetchRow();
                $ids[$host_name] = $row['host_id'];
            }
        }
        if (isset($ids[$host_name])) {
            return $ids[$host_name];
        }
        return null;
    }

    /**
     * Check illegal char defined into nagios.cfg file
     *
     * @param string $host_name
     * @param int $poller_id
     * @return string
     */
    public function checkIllegalChar($host_name, $poller_id = null)
    {
        if ($poller_id) {
            $query = 'SELECT illegal_object_name_chars FROM cfg_nagios WHERE nagios_server_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$poller_id));
        } else {
            $res = $this->db->query('SELECT illegal_object_name_chars FROM cfg_nagios ');
        }

        while ($data = $res->fetchRow()) {
            $tab = str_split(html_entity_decode($data['illegal_object_name_chars'], ENT_QUOTES, "UTF-8"));
            foreach ($tab as $char) {
                $host_name = str_replace($char, "", $host_name);
            }
        }
        $res->free();
        return $host_name;
    }

    /**
     * Method that returns the poller id that monitors the host
     *
     * @param int $host_id
     * @return int
     */
    public function getHostPollerId($host_id)
    {
        $pollerId = null;
        $query = 'SELECT nagios_server_id FROM ns_host_relation WHERE host_host_id = ? LIMIT 1';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$host_id));

        if ($res->numRows()) {
            $row = $res->fetchRow();
            $pollerId = $row['nagios_server_id'];
        } else {
            $hostName = $this->getHostName($host_id);
            if (preg_match('/^_Module_Meta/', $hostName)) {
                $query = 'SELECT id ' .
                    'FROM nagios_server ' .
                    'WHERE localhost = "1" ' .
                    'LIMIT 1 ';
                $res = $this->db->query($query);
                if ($res->numRows()) {
                    $row = $res->fetchRow();
                    $pollerId = $row['id'];
                }
            }
        }
        return $pollerId;
    }

    /**
     * Returns a string that replaces on demand macros by their values
     *
     * @param mixed $hostParam
     * @param string $string
     * @param int $antiLoop
     * @return string
     */
    public function replaceMacroInString($hostParam, $string, $antiLoop = null)
    {
        if (is_numeric($hostParam)) {
            $host_id = $hostParam;
        } elseif (is_string($hostParam)) {
            $host_id = $this->getHostId($hostParam);
        } else {
            return $string;
        }
        $query = 'SELECT host_register FROM host WHERE host_id = ? LIMIT 1';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$host_id));
        if (!$res->numRows()) {
            return $string;
        }
        $row = $res->fetchRow();

        /*
         * replace if not template
         */
        if ($row['host_register'] == 1) {
            if (strpos($string, "\$HOSTADDRESS$")) {
                $string = str_replace("\$HOSTADDRESS\$", $this->getHostAddress($host_id), $string);
            }
            if (strpos($string, "\$HOSTNAME$")) {
                $string = str_replace("\$HOSTNAME\$", $this->getHostName($host_id), $string);
            }
            if (strpos($string, "\$HOSTALIAS$")) {
                $string = str_replace("\$HOSTALIAS\$", $this->getHostAlias($host_id), $string);
            }
            if (preg_match("\$INSTANCENAME\$", $string)) {
                $string = str_replace(
                    "\$INSTANCENAME\$",
                    $this->instanceObj->getParam($this->getHostPollerId($host_id), 'name'),
                    $string
                );
            }
            if (preg_match("\$INSTANCEADDRESS\$", $string)) {
                $string = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $this->instanceObj->getParam($this->getHostPollerId($host_id), 'ns_ip_address'),
                    $string
                );
            }
        }
        unset($row);

        $matches = array();
        $pattern = '|(\$_HOST[0-9a-zA-Z\_\-]+\$)|';
        preg_match_all($pattern, $string, $matches);
        $i = 0;
        while (isset($matches[1][$i])) {
            $queryValues = array();
            $query = 'SELECT host_macro_value ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = ? ' .
                'AND host_macro_name LIKE ?';
            $queryValues[] = (int)$host_id;
            $queryValues[] = (string)$matches[1][$i];

            $stmt = $this->db->prepare($query);
            $dbResult = $this->db->execute($stmt, $queryValues);
            while ($row = $dbResult->fetchRow()) {
                $string = str_replace($matches[1][$i], $row['host_macro_value'], $string);
            }
            $i++;
        }
        if ($i) {
            $query2 = 'SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = ? ORDER BY `order`';
            $stmt = $this->db->prepare($query2);
            $dbResult2 = $this->db->execute($stmt, array((int)$host_id));
            while ($row2 = $dbResult2->fetchRow()) {
                if (!isset($antiLoop) || !$antiLoop) {
                    $string = $this->replaceMacroInString($row2['host_tpl_id'], $string, $row2['host_tpl_id']);
                } elseif ($row2['host_tpl_id'] != $antiLoop) {
                    $string = $this->replaceMacroInString($row2['host_tpl_id'], $string);
                }
            }
        }
        return $string;
    }

    /**
     * Insert macro
     *
     * @param int $hostId
     * @param array $macroInput
     * @param array $macroValue
     * @param array $macroPassword
     * @param array $macroDescription
     * @param bool $isMassiveChange
     *
     * @return void
     */
    public function insertMacro(
        $hostId,
        $macroInput = array(),
        $macroValue = array(),
        $macroPassword = array(),
        $macroDescription = array(),
        $isMassiveChange = false,
        $cmdId = false
    ) {

        if (false === $isMassiveChange) {
            $query = 'DELETE FROM on_demand_macro_host WHERE host_host_id = ?';
            $stmt = $this->db->prepare($query);
            $this->db->execute($stmt, array((int)$hostId));
        } else {
            $macroList = "";
            $queryValues = array();
            $queryValues[] = $hostId;
            foreach ($macroInput as $v) {
                $macroList .= ' ?,';
                $queryValues[] = (string)'$_HOST' . strtoupper($v) . '$';
            }
            if ($macroList) {
                $macroList = rtrim($macroList, ",");
                $query = 'DELETE FROM on_demand_macro_host ' .
                    'WHERE host_host_id = ? ' .
                    'AND host_macro_name IN (' . $macroList . ')';
                $stmt = $this->db->prepare($query);
                $this->db->execute($stmt, $queryValues);
            }
        }


        $stored = array();
        $cnt = 0;
        $macros = $macroInput;
        $macrovalues = $macroValue;
        $this->hasMacroFromHostChanged($hostId, $macros, $macrovalues, $macroPassword, $cmdId);
        foreach ($macros as $key => $value) {
            if ($value != "" && !isset($stored[strtolower($value)])) {
                $queryValues = array();
                $query = 'INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`, `is_password`, ' .
                    '`description`, `host_host_id`, `macro_order`) ' .
                    'VALUES (?, ?, ';
                $queryValues[] = (string)'$_HOST' . strtoupper($value) . '$';
                $queryValues[] = (string)$macrovalues[$key];
                if (isset($macroPassword[$key])) {
                    $query .= '?, ';
                    $queryValues[] = (int)1;
                } else {
                    $query .= 'NULL, ';
                }
                $query .= '?, ?, ?)';
                $queryValues[] = (string)$macroDescription[$key];
                $queryValues[] = (int)$hostId;
                $queryValues[] = (int)$cnt;
                $stmt = $this->db->prepare($query);
                $dbResult = $this->db->execute($stmt, $queryValues);
                if (PEAR::isError($dbResult)) {
                    throw new \Exception("An error occured");
                }
                $cnt++;
                $stored[strtolower($value)] = true;
            }
        }
    }

    public function getCustomMacroInDb($hostId = null, $template = null)
    {
        $arr = array();
        $i = 0;

        if ($hostId) {
            $sSql = 'SELECT host_macro_name, host_macro_value, is_password, description ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = ? ' .
                'ORDER BY macro_order ASC';
            $stmt = $this->db->prepare($sSql);
            $res = $this->db->execute($stmt, array((int)$hostId));
            while ($row = $res->fetchRow()) {
                if (preg_match('/\$_HOST(.*)\$$/', $row['host_macro_name'], $matches)) {
                    $arr[$i]['macroInput_#index#'] = $matches[1];
                    $arr[$i]['macroValue_#index#'] = $row['host_macro_value'];
                    $arr[$i]['macroPassword_#index#'] = $row['is_password'] ? 1 : null;
                    $arr[$i]['macroDescription_#index#'] = $row['description'];
                    $arr[$i]['macroDescription'] = $row['description'];
                    if (!is_null($template)) {
                        $arr[$i]['macroTpl_#index#'] = "Host template : " . $template['host_name'];
                    }
                    $i++;
                }
            }
        }
        return $arr;
    }


    /**
     * Get host custom macro
     *
     * @param int $hostId
     * @return array
     */
    public function getCustomMacro($hostId = null, $realKeys = false)
    {
        $arr = array();
        $i = 0;

        if (!isset($_REQUEST['macroInput']) && $hostId) {
            $sSql = 'SELECT host_macro_name, host_macro_value, is_password, description ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = ? ' .
                'ORDER BY macro_order ASC';
            $stmt = $this->db->prepare($sSql);
            $res = $this->db->execute($stmt, array((int)$hostId));
            while ($row = $res->fetchRow()) {
                if (preg_match('/\$_HOST(.*)\$$/', $row['host_macro_name'], $matches)) {
                    $arr[$i]['macroInput_#index#'] = $matches[1];
                    $arr[$i]['macroValue_#index#'] = $row['host_macro_value'];
                    $arr[$i]['macroPassword_#index#'] = $row['is_password'] ? 1 : null;
                    $arr[$i]['macroDescription_#index#'] = $row['description'];
                    $arr[$i]['macroDescription'] = $row['description'];
                    $i++;
                }
            }
        } elseif (isset($_REQUEST['macroInput'])) {
            foreach ($_REQUEST['macroInput'] as $key => $val) {
                $index = $i;
                if ($realKeys) {
                    $index = $key;
                }
                $arr[$index]['macroInput_#index#'] = $val;
                $arr[$index]['macroValue_#index#'] = $_REQUEST['macroValue'][$key];
                $arr[$index]['macroPassword_#index#'] = isset($_REQUEST['is_password'][$key]) ? 1 : null;
                $arr[$index]['macroDescription_#index#'] = isset($_REQUEST['description'][$key]) ?
                    $_REQUEST['description'][$key] : null;
                $arr[$index]['macroDescription'] = isset($_REQUEST['description'][$key]) ?
                    $_REQUEST['description'][$key] : null;
                $i++;
            }
        }
        return $arr;
    }

    /**
     * Get list of template linked to a given host
     *
     * @param int $hostId
     * @return array
     */
    public function getTemplates($hostId = null)
    {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['tpSelect']) && $hostId) {
            $query = 'SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = ? ORDER BY `order`';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$hostId));
            while ($row = $res->fetchRow()) {
                $arr[$i]['tpSelect_#index#'] = $row['host_tpl_id'];
                $i++;
            }
        } else {
            if (isset($_REQUEST['tpSelect'])) {
                foreach ($_REQUEST['tpSelect'] as $val) {
                    $arr[$i]['tpSelect_#index#'] = $val;
                    $i++;
                }
            }
        }
        return $arr;
    }

    /**
     * Set templates
     *
     * @param int $hostId
     * @param array $templates
     * @return void
     */
    public function setTemplates($hostId, $templates = array(), $remaining = array())
    {
        $queryValues = array();
        $explodedValues = '';
        $query = 'DELETE FROM host_template_relation WHERE host_host_id = ?';
        $queryValues[] = (int)$hostId;

        $stored = array();
        if (count($remaining)) {
            foreach ($remaining as $k => $v) {
                $explodedValues .= '?,';
                $queryValues[] = (int)$v;
            }
            $explodedValues = rtrim($explodedValues, ',');
            $query .= ' AND host_tpl_id NOT IN (' . $explodedValues . ') ';
            $stored = $remaining;
        }
        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, $queryValues);
        $str = "";
        $i = 1;
        $queryValues = array();
        foreach ($templates as $templateId) {
            if (!isset($templateId)
                || !$templateId
                || isset($stored[$templateId])
                || !$this->hasNoInfiniteLoop($hostId, $templateId)
            ) {
                continue;
            }
            if ($str != "") {
                $str .= ", ";
            }
            $str .= "(?, ?, ?)";
            $queryValues[] = (int)$hostId;
            $queryValues[] = (int)$templateId;
            $queryValues[] = (int)$i;
            $stored[$templateId] = true;
            $i++;
        }
        if ($str) {
            $query = 'INSERT INTO host_template_relation (host_host_id, host_tpl_id, `order`) VALUES ' . $str;
            $stmt = $this->db->prepare($query);
            $this->db->execute($stmt, $queryValues);
        }
    }

    /**
     * Checks if the insertion can be made
     *
     * @return bool
     */
    public function hasNoInfiniteLoop($hostId, $templateId, $antiTplLoop = array())
    {
        if ($hostId === $templateId) {
            return false;
        }

        if (!count($antiTplLoop)) {
            $query = 'SELECT host_host_id, host_tpl_id FROM host_template_relation';
            $res = $this->db->query($query);
            while ($row = $res->fetchRow()) {
                if (!isset($antiTplLoop[$row['host_tpl_id']])) {
                    $antiTplLoop[$row['host_tpl_id']] = array();
                }
                $antiTplLoop[$row['host_tpl_id']][$row['host_host_id']] = $row['host_host_id'];
            }
        }

        if (isset($antiTplLoop[$hostId])) {
            foreach ($antiTplLoop[$hostId] as $hId) {
                if ($hId == $templateId) {
                    return false;
                }
                if (false === $this->hasNoInfiniteLoop($hId, $templateId, $antiTplLoop)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function hasMacroFromHostChanged(
        $host_id,
        &$macroInput,
        &$macroValue,
        &$macroPassword,
        $cmdId = false
    ) {
        $aTemplates = $this->getTemplateChain($host_id, array(), -1, true, "host_name,host_id,command_command_id");

        if (!isset($cmdId)) {
            $cmdId = "";
        }
        $aMacros = $this->getMacros($host_id, false, $aTemplates, $cmdId);
        foreach ($aMacros as $macro) {
            foreach ($macroInput as $ind => $input) {
                if ($input == $macro['macroInput_#index#'] &&
                    $macroValue[$ind] == $macro["macroValue_#index#"] &&
                    $macroPassword[$ind] == $macro['macroPassword_#index#']
                ) {
                    unset($macroInput[$ind]);
                    unset($macroValue[$ind]);
                }
            }
        }
    }

    public function getMacroFromForm($form, $fromKey)
    {
        $Macros = array();
        if (!empty($form['macroInput'])) {
            foreach ($form['macroInput'] as $key => $macroInput) {
                if ($form['macroFrom'][$key] == $fromKey) {
                    $macroTmp = array();
                    $macroTmp['macroInput_#index#'] = $macroInput;
                    $macroTmp['macroValue_#index#'] = $form['macroValue'][$key];
                    $macroTmp['macroPassword_#index#'] = isset($form['is_password'][$key]) ? 1 : null;
                    $macroTmp['macroDescription_#index#'] = isset($form['description'][$key])
                        ? $form['description'][$key]
                        : null;
                    $macroTmp['macroDescription'] = isset($form['description'][$key])
                        ? $form['description'][$key]
                        : null;
                    $Macros[] = $macroTmp;
                }
            }
        }
        return $Macros;
    }

    /**
     * This method get the macro attached to the host
     *
     * @param int $iHostId
     * @param int $bIsTemplate
     * @param array $aListTemplate
     * @param int $iIdCommande
     * @return array
     */
    public function getMacros($iHostId, $bIsTemplate, $aListTemplate, $iIdCommande, $form = array())
    {
        $macroArray = $this->getMacroFromForm($form, "direct");
        $aMacroTemplate[] = $this->getMacroFromForm($form, "fromTpl");
        $aMacroInCommande = $this->getMacroFromForm($form, "fromCommand");
        //Get macro attached to the host
        $macroArray = array_merge($macroArray, $this->getCustomMacroInDb($iHostId));

        //Get macro attached to the template
        $serviceTemplates = array();
        foreach ($aListTemplate as $template) {
            if (!empty($template['host_id'])) {
                $aMacroTemplate[] = $this->getCustomMacroInDb($template['host_id'], $template);
                $tmpServiceTpl = $this->getServicesTemplates($template['host_id']);
                foreach ($tmpServiceTpl as $tmp) {
                    $serviceTemplates[] = $tmp;
                }
            }
        }

        $templateName = "";
        if (empty($iIdCommande)) {
            foreach ($aListTemplate as $template) {
                if (!empty($template['command_command_id'])) {
                    $iIdCommande = $template['command_command_id'];
                    $templateName = "Host template : " . $template['host_name'] . " | ";
                    break;
                }
            }
        }

        //Get macro attached to the command
        $oCommand = new CentreonCommand($this->db);
        if (!empty($iIdCommande)) {
            $macrosCommande = $oCommand->getMacroByIdAndType($iIdCommande, 'host');
            if (!empty($macrosCommande)) {
                foreach ($macrosCommande as $macroscmd) {
                    $macroscmd['macroTpl_#index#'] = $templateName . ' Commande : ' . $macroscmd['macroCommandFrom'];
                    $aMacroInCommande[] = $macroscmd;
                }
            }

        }

        foreach ($serviceTemplates as $svctpl) {
            if (isset($svctpl['command_command_id']) && !empty($svctpl['command_command_id'])) {
                $macrosCommande = $oCommand->getMacroByIdAndType($svctpl['command_command_id'], 'host');
                if (!empty($macrosCommande)) {
                    foreach ($macrosCommande as $macroscmd) {
                        $macroscmd['macroTpl_#index#'] = "Service template : " . $svctpl['service_description'] .
                            ' | Commande : ' . $macroscmd['macroCommandFrom'];
                        $aMacroInCommande[] = $macroscmd;
                    }
                }
            }
        }

        //filter a macro
        $aTempMacro = array();

        if (count($macroArray) > 0) {
            foreach ($macroArray as $directMacro) {
                $directMacro['macroOldValue_#index#'] = $directMacro["macroValue_#index#"];
                $directMacro['macroFrom_#index#'] = 'direct';
                $directMacro['source'] = 'direct';
                $aTempMacro[] = $directMacro;
            }
        }

        if (count($aMacroTemplate) > 0) {
            foreach ($aMacroTemplate as $key => $macr) {
                foreach ($macr as $mm) {
                    $mm['macroOldValue_#index#'] = $mm["macroValue_#index#"];
                    $mm['macroFrom_#index#'] = 'fromTpl';
                    $mm['source'] = 'fromTpl';
                    $aTempMacro[] = $mm;
                }
            }
        }

        if (count($aMacroInCommande) > 0) {
            $macroCommande = $aMacroInCommande;
            for ($i = 0; $i < count($macroCommande); $i++) {
                $macroCommande[$i]['macroOldValue_#index#'] = $macroCommande[$i]["macroValue_#index#"];
                $macroCommande[$i]['macroFrom_#index#'] = 'fromCommand';
                $macroCommande[$i]['source'] = 'fromCommand';
                $aTempMacro[] = $macroCommande[$i];
            }
        }
        $aFinalMacro = $this->macroUnique($aTempMacro);
        return $aFinalMacro;
    }


    public function ajaxMacroControl($form)
    {
        $macroArray = $this->getCustomMacro(null, 'realKeys');
        $this->purgeOldMacroToForm($macroArray, $form, 'fromTpl');
        $aListTemplate = array();
        $serviceTemplates = array();
        if (isset($form['tpSelect']) && is_array($form['tpSelect'])) {
            foreach ($form['tpSelect'] as $template) {
                $tmpTpl = array_merge(
                    array(
                        array(
                            'host_id' => $template,
                            'host_name' => $this->getOneHostName($template),
                            'command_command_id' => $this->getHostCommandId($template)
                        )
                    ),
                    $this->getTemplateChain($template, array(), -1, true, "host_name,host_id,command_command_id")
                );
                $aListTemplate = array_merge($aListTemplate, $tmpTpl);
            }
        }

        $aMacroTemplate = array();
        foreach ($aListTemplate as $template) {
            if (!empty($template['host_id'])) {
                $aMacroTemplate = array_merge(
                    $aMacroTemplate,
                    $this->getCustomMacroInDb($template['host_id'], $template)
                );
                $tmpServiceTpl = $this->getServicesTemplates($template['host_id']);
                foreach ($tmpServiceTpl as $tmp) {
                    $serviceTemplates[] = $tmp;
                }
            }
        }

        $iIdCommande = $form['command_command_id'];
        $templateName = "";
        if (empty($iIdCommande)) {
            foreach ($aListTemplate as $template) {
                if (!empty($template['command_command_id'])) {
                    $iIdCommande = $template['command_command_id'];
                    $templateName = "Host template : " . $template['host_name'] . " | ";
                    break;
                }
            }
        }

        $this->purgeOldMacroToForm($macroArray, $form, 'fromCommand');

        $aMacroInCommande = array();
        //Get macro attached to the command
        $oCommand = new CentreonCommand($this->db);
        if (!empty($iIdCommande) && is_numeric($iIdCommande)) {
            $macrosCommande = $oCommand->getMacroByIdAndType($iIdCommande, 'host');
            if (!empty($macrosCommande)) {
                foreach ($macrosCommande as $macroscmd) {
                    $macroscmd['macroTpl_#index#'] = $templateName . ' Commande : ' . $macroscmd['macroCommandFrom'];
                    $aMacroInCommande[] = $macroscmd;
                }
            }
        }

        foreach ($serviceTemplates as $svctpl) {
            if (isset($svctpl['command_command_id'])) {
                $macrosCommande = $oCommand->getMacroByIdAndType($svctpl['command_command_id'], 'host');
                if (!empty($macrosCommande)) {
                    foreach ($macrosCommande as $macroscmd) {
                        $macroscmd['macroTpl_#index#'] = "Service template : " . $svctpl['service_description'] .
                            ' | Commande : ' . $macroscmd['macroCommandFrom'];
                        $aMacroInCommande[] = $macroscmd;
                    }
                }
            }
        }

        //filter a macro
        $aTempMacro = array();

        if (count($macroArray) > 0) {
            foreach ($macroArray as $key => $directMacro) {
                $directMacro['macroOldValue_#index#'] = $directMacro["macroValue_#index#"];
                $directMacro['macroFrom_#index#'] = $form['macroFrom'][$key];
                $directMacro['source'] = 'direct';
                $aTempMacro[] = $directMacro;
            }
        }

        if (count($aMacroTemplate) > 0) {
            foreach ($aMacroTemplate as $key => $macr) {
                $macr['macroOldValue_#index#'] = $macr["macroValue_#index#"];
                $macr['macroFrom_#index#'] = 'fromTpl';
                $macr['source'] = 'fromTpl';
                $aTempMacro[] = $macr;
            }
        }

        if (count($aMacroInCommande) > 0) {
            $macroCommande = $aMacroInCommande;
            for ($i = 0; $i < count($macroCommande); $i++) {
                $macroCommande[$i]['macroOldValue_#index#'] = $macroCommande[$i]["macroValue_#index#"];
                $macroCommande[$i]['macroFrom_#index#'] = 'fromCommand';
                $macroCommande[$i]['source'] = 'fromCommand';
                $aTempMacro[] = $macroCommande[$i];
            }
        }
        $aFinalMacro = $this->macroUnique($aTempMacro);
        return $aFinalMacro;
    }


    /**
     * Get template chain (id, text)
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed The host templates already processed
     * @param int $depth The depth to search
     * @return array
     */
    public function getTemplateChain(
        $hostId,
        $alreadyProcessed = array(),
        $depth = -1,
        $allFields = false,
        $fields = array()
    ) {
        $templates = array();

        if (($depth == -1) || ($depth > 0)) {
            if ($depth > 0) {
                $depth--;
            }
            if (in_array($hostId, $alreadyProcessed)) {
                return $templates;
            } else {
                $alreadyProcessed[] = $hostId;
                if (empty($fields)) {
                    if (!$allFields) {
                        $fields = "h.host_id, h.host_name";
                    } else {
                        $fields = " * ";
                    }
                }

                $query = 'SELECT ' . $fields . ' ' .
                    'FROM host h, host_template_relation htr ' .
                    'WHERE h.host_id = htr.host_tpl_id ' .
                    'AND htr.host_host_id = ? ' .
                    'AND host_activate = "1" ' .
                    'AND host_register = "0" ' .
                    'ORDER BY `order` ASC';
                $stmt = $this->db->prepare($query);
                $dbResult = $this->db->execute($stmt, array((int)$hostId));

                while ($row = $dbResult->fetchRow()) {
                    if (!$allFields) {
                        $templates[] = array(
                            "id" => $row['host_id'],
                            "host_id" => $row['host_id'],
                            "host_name" => $row['host_name']
                        );
                    } else {
                        $templates[] = $row;
                    }

                    $templates = array_merge(
                        $templates,
                        $this->getTemplateChain($row['host_id'], $alreadyProcessed, $depth, $allFields)
                    );
                }
                return $templates;
            }
        }
        return $templates;
    }

    /**
     * Get host template ids
     *
     * @param int $hostId The host or host template Id
     * @return array
     */
    public function getHostTemplateIds($hostId)
    {



        $hostTemplateIds = array();
        $query = 'SELECT htr.host_tpl_id ' .
            'FROM host_template_relation htr, host ht ' .
            'WHERE htr.host_host_id = ? ' .
            'AND htr.host_tpl_id = ht.host_id ' .
            'AND ht.host_activate = "1" ' .
            'ORDER BY `order` ASC ';
        $stmt = $this->db->prepare($query);
        $dbResult = $this->db->execute($stmt, array((int)$hostId));
        if(PEAR::isError()){
            throw new \Exception('Bad request');
        }
        while ($row = $dbResult->fetchRow()) {
            $hostTemplateIds[] = $row['host_tpl_id'];
        }

        return $hostTemplateIds;
    }

    /**
     * Get inherited values
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed already processed host ids
     * @param int $depth depth to search values (-1 for infinite)
     * @param array $fields fields to search
     * @param array $values found values
     * @return array
     */
    public function getInheritedValues(
        $hostId,
        $alreadyProcessed = array(),
        $depth = -1,
        $fields = array(),
        $values = array()
    ) {

        if ($depth != 0) {
            $depth--;
            if (in_array($hostId, $alreadyProcessed)) {
                return $values;
            } else {
                if (count($alreadyProcessed) && !count($fields)) {
                    return $values;
                } else {
                    $queryValues = array();
                    $queryFields = '';
                    if (count($fields) > 0) {
                        foreach ($fields as $k => $v) {
                            $queryFields .= '?,';
                            $queryValues[] = (string)$v;
                        }
                        $queryFields = rtrim($queryFields, ',');
                    } else {
                        $queryFields .= '*';
                    }
                }

                $query = 'SELECT ' . $queryFields . ' ' .
                    'FROM host h ' .
                    'WHERE host_id = ?';
                $queryValues[] = (int)$hostId;

                $stmt = $this->db->prepare($query);
                $dbResult = $this->db->execute($stmt, $queryValues);

                while ($row = $dbResult->fetchRow()) {
                    if (!count($alreadyProcessed)) {
                        $fields = array_keys($row);
                    }

                    foreach ($row as $field => $value) {
                        if (!isset($values[$field]) && !is_null($value) && $value != '') {
                            unset($fields[$field]);
                            $values[$field] = $value;
                        }
                    }
                }

                $alreadyProcessed[] = $hostId;
                $hostTemplateIds = $this->getHostTemplateIds($hostId);
                foreach ($hostTemplateIds as $hostTemplateId) {
                    $values = $this->getInheritedValues($hostTemplateId, $alreadyProcessed, $depth, $fields, $values);
                }
            }
        }
        return $values;
    }

    /**
     * Returns array of locked host templates
     *
     * @return array
     */
    public function getLockedHostTemplates()
    {
        static $arr = null;

        if (is_null($arr)) {
            $arr = array();
            $res = $this->db->query("SELECT host_id FROM host WHERE host_locked = 1");
            while ($row = $res->fetchRow()) {
                $arr[$row['host_id']] = true;
            }
        }
        return $arr;
    }

    /**
     * @param $hostId
     * @return array
     */
    public function getServicesTemplates($hostId)
    {
        $query = 'SELECT s.service_id,s.command_command_id,s.service_description from host_service_relation hsr ' .
            'INNER JOIN service s on hsr.service_service_id = s.service_id and s.service_register = "0" ' .
            'WHERE hsr.host_host_id = ?';
        $stmt = $this->db->prepare($query);
        $dbResult = $this->db->execute($stmt, array((int)$hostId));
        $arrayTemplate = array();
        while ($row = $dbResult->fetchRow()) {
            $aListTemplate = getListTemplates($this->db, $row['service_id']);
            $aListTemplate = array_reverse($aListTemplate);
            foreach ($aListTemplate as $tpl) {
                $arrayTemplate[] = array(
                    'service_id' => $tpl['service_id'],
                    'command_command_id' => $tpl['command_command_id'],
                    'service_description' => $tpl['service_description']
                );
            }
        }
        return $arrayTemplate;
    }

    /**
     * @param $macroArray
     * @param $form
     * @param $fromKey
     * @param null $macrosArrayToCompare
     */
    public function purgeOldMacroToForm(
        &$macroArray,
        &$form,
        $fromKey,
        $macrosArrayToCompare = null
    ) {

        if (isset($form["macroInput"]["#index#"])) {
            unset($form["macroInput"]["#index#"]);
        }
        if (isset($form["macroValue"]["#index#"])) {
            unset($form["macroValue"]["#index#"]);
        }

        foreach ($macroArray as $key => $macro) {
            if ($macro["macroInput_#index#"] == "") {
                unset($macroArray[$key]);
            }
        }

        if (is_null($macrosArrayToCompare)) {
            foreach ($macroArray as $key => $macro) {
                if ($form['macroFrom'][$key] == $fromKey) {
                    unset($macroArray[$key]);
                }
            }
        } else {
            $inputIndexArray = array();
            foreach ($macrosArrayToCompare as $tocompare) {
                if (isset($tocompare['macroInput_#index#'])) {
                    $inputIndexArray[] = $tocompare['macroInput_#index#'];
                }
            }
            foreach ($macroArray as $key => $macro) {
                if ($form['macroFrom'][$key] == $fromKey) {
                    if (!in_array($macro['macroInput_#index#'], $inputIndexArray)) {
                        unset($macroArray[$key]);
                    }
                }
            }
        }

    }

    /**
     * @param $macroA
     * @param $macroB
     * @param bool $getFirst
     * @return mixed
     */
    private function comparaPriority($macroA, $macroB, $getFirst = true)
    {
        $arrayPrio = array('direct' => 3, 'fromTpl' => 2, 'fromCommand' => 1);
        if ($getFirst) {
            if ($arrayPrio[$macroA['source']] > $arrayPrio[$macroB['source']]) {
                return $macroA;
            } else {
                return $macroB;
            }
        } else {
            if ($arrayPrio[$macroA['source']] >= $arrayPrio[$macroB['source']]) {
                return $macroA;
            } else {
                return $macroB;
            }
        }
    }

    /**
     * @param $aTempMacro
     * @return array
     */
    public function macroUnique($aTempMacro)
    {
        $storedMacros = array();
        foreach ($aTempMacro as $TempMacro) {
            $sInput = $TempMacro['macroInput_#index#'];
            $storedMacros[$sInput][] = $TempMacro;
        }

        $finalMacros = array();
        foreach ($storedMacros as $key => $macros) {
            $choosedMacro = array();
            foreach ($macros as $macro) {
                if (empty($choosedMacro)) {
                    $choosedMacro = $macro;
                } else {
                    $choosedMacro = $this->comparaPriority($macro, $choosedMacro);
                }
            }
            if (!empty($choosedMacro)) {
                $finalMacros[] = $choosedMacro;
            }
        }
        $this->addInfosToMacro($storedMacros, $finalMacros);
        return $finalMacros;
    }

    /**
     * @param $storedMacros
     * @param $finalMacros
     */
    private function addInfosToMacro($storedMacros, &$finalMacros)
    {
        foreach ($finalMacros as &$finalMacro) {
            $sInput = $finalMacro['macroInput_#index#'];
            $this->setInheritedDescription(
                $finalMacro,
                $this->getInheritedDescription($storedMacros[$sInput], $finalMacro)
            );
            switch ($finalMacro['source']) {
                case 'direct':
                    $this->setTplValue($this->findTplValue($storedMacros[$sInput]), $finalMacro);
                    break;
                case 'fromTpl':
                    $this->setTplValue($this->findTplValue($storedMacros[$sInput]), $finalMacro);
                    break;
                case 'fromCommand':
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param $storedMacros
     * @param $finalMacro
     * @return string
     */
    private function getInheritedDescription($storedMacros, $finalMacro)
    {
        $description = "";
        if (empty($finalMacro['macroDescription'])) {
            $choosedMacro = array();
            foreach ($storedMacros as $storedMacro) {
                if (!empty($storedMacro['macroDescription'])) {
                    if (empty($choosedMacro)) {
                        $choosedMacro = $storedMacro;
                    } else {
                        $choosedMacro = $this->comparaPriority($storedMacro, $choosedMacro);
                    }
                    $description = $choosedMacro['macroDescription'];
                }
            }
        } else {
            $description = $finalMacro['macroDescription'];
        }
        return $description;
    }

    /**
     * @param $finalMacro
     * @param $description
     */
    private function setInheritedDescription(&$finalMacro, $description)
    {
        $finalMacro['macroDescription_#index#'] = $description;
        $finalMacro['macroDescription'] = $description;
    }

    /**
     * @param $tplValue
     * @param $finalMacro
     */
    private function setTplValue($tplValue, &$finalMacro)
    {
        if ($tplValue !== false) {
            $finalMacro['macroTplValue_#index#'] = $tplValue;
            $finalMacro['macroTplValToDisplay_#index#'] = 1;
        } else {
            $finalMacro['macroTplValue_#index#'] = "";
            $finalMacro['macroTplValToDisplay_#index#'] = 0;
        }
    }

    /**
     * @param $storedMacro
     * @param bool $getFirst
     * @return bool
     */
    private function findTplValue($storedMacro, $getFirst = true)
    {
        if ($getFirst) {
            foreach ($storedMacro as $macros) {
                if ($macros['source'] == 'fromTpl') {
                    return $macros['macroValue_#index#'];
                }
            }
        } else {
            $macroReturn = false;
            foreach ($storedMacro as $macros) {
                if ($macros['source'] == 'fromTpl') {
                    $macroReturn = $macros['macroValue_#index#'];
                }
            }
            return $macroReturn;
        }
        return false;
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'host';
        $parameters['currentObject']['id'] = 'host_id';
        $parameters['currentObject']['name'] = 'host_name';
        $parameters['currentObject']['comparator'] = 'host_id';

        switch ($field) {
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timeperiod';
                $parameters['externalObject']['id'] = 'tp_id';
                $parameters['externalObject']['name'] = 'tp_name';
                $parameters['externalObject']['comparator'] = 'tp_id';
                break;
            case 'command_command_id':
            case 'command_command_id2':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'command';
                $parameters['externalObject']['id'] = 'command_id';
                $parameters['externalObject']['name'] = 'command_name';
                $parameters['externalObject']['comparator'] = 'command_id';
                break;
            case 'host_cs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonContact';
                $parameters['externalObject']['table'] = 'contact';
                $parameters['externalObject']['id'] = 'contact_id';
                $parameters['externalObject']['name'] = 'contact_name';
                $parameters['externalObject']['comparator'] = 'contact_id';
                $parameters['relationObject']['table'] = 'contact_host_relation';
                $parameters['relationObject']['field'] = 'contact_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_parents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'host_hostparent_relation';
                $parameters['relationObject']['field'] = 'host_parent_hp_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_childs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'host_hostparent_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'host_parent_hp_id';
                break;
            case 'host_hgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHostgroups';
                $parameters['externalObject']['table'] = 'hostgroup';
                $parameters['externalObject']['id'] = 'hg_id';
                $parameters['externalObject']['name'] = 'hg_name';
                $parameters['externalObject']['comparator'] = 'hg_id';
                $parameters['relationObject']['table'] = 'hostgroup_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_hcs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHostcategories';
                $parameters['externalObject']['table'] = 'hostcategories';
                $parameters['externalObject']['id'] = 'hc_id';
                $parameters['externalObject']['name'] = 'hc_name';
                $parameters['externalObject']['comparator'] = 'hc_id';
                $parameters['relationObject']['table'] = 'hostcategories_relation';
                $parameters['relationObject']['field'] = 'hostcategories_hc_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_cgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonContactgroup';
                $parameters['externalObject']['table'] = 'contactgroup';
                $parameters['externalObject']['id'] = 'cg_id';
                $parameters['externalObject']['name'] = 'cg_name';
                $parameters['externalObject']['comparator'] = 'cg_id';
                $parameters['relationObject']['table'] = 'contactgroup_host_relation';
                $parameters['relationObject']['field'] = 'contactgroup_cg_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_svTpls':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'service';
                $parameters['externalObject']['id'] = 'service_id';
                $parameters['externalObject']['name'] = 'service_description';
                $parameters['externalObject']['comparator'] = 'service_id';
                $parameters['relationObject']['table'] = 'host_service_relation';
                $parameters['relationObject']['field'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'host_host_id';
                break;
            case 'host_location':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timezone';
                $parameters['externalObject']['id'] = 'timezone_id';
                $parameters['externalObject']['name'] = 'timezone_name';
                $parameters['externalObject']['comparator'] = 'timezone_id';
                break;
        }
        return $parameters;
    }

    /**
     * Get list of services template for a host template
     *
     * @param int $hostTplId The host template id
     * @return array
     */
    public function getServicesTplInHostTpl($hostTplId)
    {
        // Get service for a host
        $queryGetServices = 'SELECT s.service_id, s.service_description, s.service_alias ' .
            'FROM service s, host_service_relation hsr, host h ' .
            'WHERE s.service_id = hsr.service_service_id ' .
            'AND s.service_register = "0" ' .
            'AND s.service_activate = "1" ' .
            'AND h.host_id = hsr.host_host_id ' .
            'AND h.host_register = "0" ' .
            'AND h.host_activate = "1" ' .
            'AND hsr.host_host_id = ?';

        $stmt = $this->db->prepare($queryGetServices);
        $res = $this->db->execute($stmt, array((int)$hostTplId));

        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[$row['service_id']] = array(
                "service_description" => $row['service_description'],
                "service_alias" => $row['service_alias']
            );
        }
        return $listServices;
    }


    /**
     * Deploy services
     * Recursive method
     *
     * @param int $hostId
     * @param mixed $hostTemplateId
     * @return void
     */
    public function deployServices($hostId, $hostTemplateId = null)
    {
        if (!isset($hostTemplateId)) {
            $id = $hostId;
        } else {
            $id = $hostTemplateId;
        }
        $templates = $this->getTemplateChain($id);

        foreach ($templates as $templateId) {
            $serviceTemplates = $this->getServicesTplInHostTpl($templateId['id']);

            foreach ($serviceTemplates as $serviceTemplateId => $service) {
                $queryValues = array();
                $query = 'SELECT service_id ' .
                    'FROM service s, host_service_relation hsr ' .
                    'WHERE s.service_id = hsr.service_service_id ' .
                    'AND s.service_description = ? ' .
                    'AND hsr.host_host_id = ? ' .
                    'UNION ' .
                    'SELECT service_id ' .
                    'FROM service s, host_service_relation hsr ' .
                    'WHERE s.service_id = hsr.service_service_id ' .
                    'AND s.service_description = ? ' .
                    'AND hsr.hostgroup_hg_id IN ( ' .
                    'SELECT hostgroup_hg_id ' .
                    'FROM hostgroup_relation ' .
                    'WHERE host_host_id = ?  )';

                $queryValues[] = (string)$service['service_alias'];
                $queryValues[] = (int)$hostId;
                $queryValues[] = (string)$service['service_alias'];
                $queryValues[] = (int)$hostId;
                $stmt = $this->db->prepare($query);
                $res = $this->db->execute($stmt, $queryValues);

                if (!$res->numRows()) {
                    $svcId = $this->serviceObj->insert(
                        array(
                            'service_description' => $service['service_alias'],
                            'service_activate' => '1',
                            'service_register' => '1',
                            'service_template_model_stm_id' => $serviceTemplateId
                        )
                    );

                    $this->insertRelHostService($hostId, $svcId);
                }
                unset($res);
            }
            $this->deployServices($hostId, $templateId['id']);
        }
    }

    /**
     *
     * Insert host in DB
     *
     */
    public function insert($ret)
    {
        $ret["host_name"] = $this->checkIllegalChar($ret["host_name"]);

        if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null) {
            $ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
            $ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
            $ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
        }
        if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null) {
            $ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
            $ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
            $ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
        }

        $rq = 'INSERT INTO host ' .
            '(host_template_model_htm_id, command_command_id, command_command_id_arg1, timeperiod_tp_id, ' .
            ' timeperiod_tp_id2, command_command_id2, command_command_id_arg2, host_name, host_alias, host_address, ' .
            'host_max_check_attempts, host_check_interval, host_retry_check_interval, host_active_checks_enabled, ' .
            'host_passive_checks_enabled, host_checks_enabled, host_obsess_over_host, host_check_freshness, ' .
            'host_freshness_threshold, host_event_handler_enabled, host_low_flap_threshold, ' .
            'host_high_flap_threshold, host_flap_detection_enabled, host_process_perf_data, ' .
            'host_retain_status_information, host_retain_nonstatus_information, host_notification_interval, ' .
            'host_first_notification_delay, host_notification_options, host_notifications_enabled, ' .
            'contact_additive_inheritance, cg_additive_inheritance, host_stalking_options, host_snmp_community, ' .
            'host_snmp_version, host_location, host_comment, host_locked, host_register, host_activate, ' .
            'host_acknowledgement_timeout) ' .
            'VALUES ( ';
        isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != null ?
            $rq .= "'" . $ret["host_template_model_htm_id"] . "', " : $rq .= "NULL, ";
        isset($ret["command_command_id"]) && $ret["command_command_id"] != null ?
            $rq .= "'" . $ret["command_command_id"] . "', " : $rq .= "NULL, ";
        isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null ?
            $rq .= "'" . $ret["command_command_id_arg1"] . "', " : $rq .= "NULL, ";
        isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != null ?
            $rq .= "'" . $ret["timeperiod_tp_id"] . "', " : $rq .= "NULL, ";
        isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != null ?
            $rq .= "'" . $ret["timeperiod_tp_id2"] . "', " : $rq .= "NULL, ";
        isset($ret["command_command_id2"]) && $ret["command_command_id2"] != null ?
            $rq .= "'" . $ret["command_command_id2"] . "', " : $rq .= "NULL, ";
        isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null ?
            $rq .= "'" . $ret["command_command_id_arg2"] . "', " : $rq .= "NULL, ";
        isset($ret["host_name"]) && $ret["host_name"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_name"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_alias"]) && $ret["host_alias"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_alias"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_address"]) && $ret["host_address"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_address"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != null ?
            $rq .= "'" . $ret["host_max_check_attempts"] . "', " : $rq .= "NULL, ";
        isset($ret["host_check_interval"]) && $ret["host_check_interval"] != null ?
            $rq .= "'" . $ret["host_check_interval"] . "', " : $rq .= "NULL, ";
        isset($ret["host_retry_check_interval"]) && $ret["host_retry_check_interval"] != null ?
            $rq .= "'" . $ret["host_retry_check_interval"] . "', " : $rq .= "NULL, ";
        isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) &&
        $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_active_checks_enabled"]["host_active_checks_enabled"] . "', " : $rq .= "'2', ";
        isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) &&
        $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] . "', " : $rq .= "'2', ";
        isset($ret["host_checks_enabled"]["host_checks_enabled"]) &&
        $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_checks_enabled"]["host_checks_enabled"] . "', " : $rq .= "'2', ";
        isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) &&
        $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ?
            $rq .= "'" . $ret["host_obsess_over_host"]["host_obsess_over_host"] . "', " : $rq .= "'2', ";
        isset($ret["host_check_freshness"]["host_check_freshness"]) &&
        $ret["host_check_freshness"]["host_check_freshness"] != 2 ?
            $rq .= "'" . $ret["host_check_freshness"]["host_check_freshness"] . "', " : $rq .= "'2', ";
        isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != null ?
            $rq .= "'" . $ret["host_freshness_threshold"] . "', " : $rq .= "NULL, ";
        isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) &&
        $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ?
            $rq .= "'" . $ret["host_event_handler_enabled"]["host_event_handler_enabled"] . "', " : $rq .= "'2', ";
        isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"] != null ?
            $rq .= "'" . $ret["host_low_flap_threshold"] . "', " : $rq .= "NULL, ";
        isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != null ?
            $rq .= "'" . $ret["host_high_flap_threshold"] . "', " : $rq .= "NULL, ";
        isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) &&
        $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ?
            $rq .= "'" . $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] . "', " : $rq .= "'2', ";
        isset($ret["host_process_perf_data"]["host_process_perf_data"]) &&
        $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ?
            $rq .= "'" . $ret["host_process_perf_data"]["host_process_perf_data"] . "', " : $rq .= "'2', ";
        isset($ret["host_retain_status_information"]["host_retain_status_information"]) &&
        $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ?
            $rq .= "'" . $ret["host_retain_status_information"]["host_retain_status_information"] . "', " :
            $rq .= "'2', ";
        isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) &&
        $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ?
            $rq .= "'" . $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] . "', " :
            $rq .= "'2', ";
        isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != null ?
            $rq .= "'" . $ret["host_notification_interval"] . "', " : $rq .= "NULL, ";
        isset($ret["host_first_notification_delay"]) && $ret["host_first_notification_delay"] != null ?
            $rq .= "'" . $ret["host_first_notification_delay"] . "', " : $rq .= "NULL, ";
        isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != null ?
            $rq .= "'" . implode(",", array_keys($ret["host_notifOpts"])) . "', " : $rq .= "NULL, ";
        isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) &&
        $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ?
            $rq .= "'" . $ret["host_notifications_enabled"]["host_notifications_enabled"] . "', " : $rq .= "'2', ";
        $rq .= (isset($ret["contact_additive_inheritance"]) ? 1 : 0) . ', ';
        $rq .= (isset($ret["cg_additive_inheritance"]) ? 1 : 0) . ', ';
        isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != null ?
            $rq .= "'" . implode(",", array_keys($ret["host_stalOpts"])) . "', " : $rq .= "NULL, ";
        isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_snmp_community"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_snmp_version"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_location"]) && $ret["host_location"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_location"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_comment"]) && $ret["host_comment"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_comment"]) . "', " : $rq .= "NULL, ";
        isset($ret["host_locked"]) && $ret["host_locked"] != null ?
            $rq .= "'" . $ret["host_locked"] . "', " : $rq .= "0, ";
        isset($ret["host_register"]) && $ret["host_register"] != null ?
            $rq .= "'" . $ret["host_register"] . "', " : $rq .= "NULL, ";
        isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != null ?
            $rq .= "'" . $ret["host_activate"]["host_activate"] . "'," : $rq .= "NULL, ";
        isset($ret["host_acknowledgement_timeout"]["host_acknowledgement_timeout"]) &&
        $ret["host_acknowledgement_timeout"]["host_acknowledgement_timeout"] != null ?
            $rq .= "'" . $ret["host_acknowledgement_timeout"]["host_acknowledgement_timeout"] . "'" : $rq .= "NULL";
        $rq .= ")";
        $DBRESULT = $this->db->query($rq);

        if (\PEAR::isError($DBRESULT)) {
            throw new \Exception('Error while insert host ' . $ret['host_name']);
        }

        $DBRESULT = $this->db->query("SELECT MAX(host_id) AS host_id FROM host");
        $host_id = $DBRESULT->fetchRow();

        $ret['host_id'] = $host_id['host_id'];
        $this->insertExtendedInfos($ret);

        return $host_id['host_id'];
    }

    /**
     *
     * Insert host extended informations in DB
     *
     */
    public function insertExtendedInfos($ret)
    {
        if (empty($ret['host_id'])) {
            return;
        }

        $rq = "INSERT INTO `extended_host_information` " .
            "( `ehi_id` , `host_host_id` , `ehi_notes` , `ehi_notes_url` , " .
            "`ehi_action_url` , `ehi_icon_image` , `ehi_icon_image_alt` , " .
            "`ehi_vrml_image` , `ehi_statusmap_image` , `ehi_2d_coords` , " .
            "`ehi_3d_coords` )" .
            "VALUES (NULL, " . $ret['host_id'] . ", ";
        isset($ret["ehi_notes"]) && $ret["ehi_notes"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_notes"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_notes_url"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_action_url"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image_alt"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_vrml_image"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_statusmap_image"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_2d_coords"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_3d_coords"]) . "' " : $rq .= "NULL ";
        $rq .= ")";
        $DBRESULT = $this->db->query($rq);
        if (\PEAR::isError($DBRESULT)) {
            throw new \Exception('Error while insert host extended info ' . $ret['host_name']);
        }
    }

    /**
     *
     * @param type $iHostId
     * @param type $iServiceId
     * @return type
     */
    public function insertRelHostService($iHostId, $iServiceId)
    {
        if (empty($iHostId) || empty($iServiceId)) {
            return;
        }
        $query = 'INSERT INTO host_service_relation (host_host_id, service_service_id) VALUES (?, ?)';
        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, array((int)$iHostId, (int)$iServiceId));
    }

    /**
     *
     * @param int $hostId
     * @param array $parameters
     */
    public function update($host_id, $ret)
    {

        if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null) {
            $ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
            $ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
            $ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
        }
        if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null) {
            $ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
            $ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
            $ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
        }

        $rq = "UPDATE host SET ";
        $rq .= "command_command_id = ";
        isset($ret["command_command_id"]) && $ret["command_command_id"] != null ?
            $rq .= "'" . $ret["command_command_id"] . "', " : $rq .= "NULL, ";
        $rq .= "command_command_id_arg1 = ";
        isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null ?
            $rq .= "'" . $ret["command_command_id_arg1"] . "', " : $rq .= "NULL, ";
        $rq .= "timeperiod_tp_id = ";
        isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != null ?
            $rq .= "'" . $ret["timeperiod_tp_id"] . "', " : $rq .= "NULL, ";
        $rq .= "command_command_id2 = ";
        isset($ret["command_command_id2"]) && $ret["command_command_id2"] != null ?
            $rq .= "'" . $ret["command_command_id2"] . "', " : $rq .= "NULL, ";
        $rq .= "command_command_id_arg2 = ";
        isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null ?
            $rq .= "'" . $ret["command_command_id_arg2"] . "', " : $rq .= "NULL, ";
        $rq .= "host_name = ";
        $ret["host_name"] = $this->checkIllegalChar($ret["host_name"]);
        isset($ret["host_name"]) && $ret["host_name"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_name"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_alias = ";
        isset($ret["host_alias"]) && $ret["host_alias"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_alias"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_address = ";
        isset($ret["host_address"]) && $ret["host_address"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_address"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_max_check_attempts = ";
        isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != null ?
            $rq .= "'" . $ret["host_max_check_attempts"] . "', " : $rq .= "NULL, ";
        $rq .= "host_check_interval = ";
        isset($ret["host_check_interval"]) && $ret["host_check_interval"] != null ?
            $rq .= "'" . $ret["host_check_interval"] . "', " : $rq .= "NULL, ";
        $rq .= "host_acknowledgement_timeout = ";
        isset($ret["host_acknowledgement_timeout"]) && $ret["host_acknowledgement_timeout"] != null ?
            $rq .= "'" . $ret["host_acknowledgement_timeout"] . "', " : $rq .= "NULL, ";
        $rq .= "host_retry_check_interval = ";
        isset($ret["host_retry_check_interval"]) && $ret["host_retry_check_interval"] != null ?
            $rq .= "'" . $ret["host_retry_check_interval"] . "', " : $rq .= "NULL, ";
        $rq .= "host_active_checks_enabled = ";
        isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) &&
        $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_active_checks_enabled"]["host_active_checks_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "host_passive_checks_enabled = ";
        isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) &&
        $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "host_checks_enabled = ";
        isset($ret["host_checks_enabled"]["host_checks_enabled"]) &&
        $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ?
            $rq .= "'" . $ret["host_checks_enabled"]["host_checks_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "host_obsess_over_host = ";
        isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) &&
        $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ?
            $rq .= "'" . $ret["host_obsess_over_host"]["host_obsess_over_host"] . "', " : $rq .= "'2', ";
        $rq .= "host_check_freshness = ";
        isset($ret["host_check_freshness"]["host_check_freshness"]) &&
        $ret["host_check_freshness"]["host_check_freshness"] != 2 ?
            $rq .= "'" . $ret["host_check_freshness"]["host_check_freshness"] . "', " : $rq .= "'2', ";
        $rq .= "host_freshness_threshold = ";
        isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != null ?
            $rq .= "'" . $ret["host_freshness_threshold"] . "', " : $rq .= "NULL, ";
        $rq .= "host_event_handler_enabled = ";
        isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) &&
        $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ?
            $rq .= "'" . $ret["host_event_handler_enabled"]["host_event_handler_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "host_low_flap_threshold = ";
        isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"] != null ?
            $rq .= "'" . $ret["host_low_flap_threshold"] . "', " : $rq .= "NULL, ";
        $rq .= "host_high_flap_threshold = ";
        isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != null ?
            $rq .= "'" . $ret["host_high_flap_threshold"] . "', " : $rq .= "NULL, ";
        $rq .= "host_flap_detection_enabled = ";
        isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) &&
        $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ?
            $rq .= "'" . $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "host_process_perf_data = ";
        isset($ret["host_process_perf_data"]["host_process_perf_data"]) &&
        $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ?
            $rq .= "'" . $ret["host_process_perf_data"]["host_process_perf_data"] . "', " : $rq .= "'2', ";
        $rq .= "host_retain_status_information = ";
        isset($ret["host_retain_status_information"]["host_retain_status_information"]) &&
        $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ?
            $rq .= "'" . $ret["host_retain_status_information"]["host_retain_status_information"] . "', " :
            $rq .= "'2', ";
        $rq .= "host_retain_nonstatus_information = ";
        isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) &&
        $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ?
            $rq .= "'" . $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] . "', " :
            $rq .= "'2', ";
        $rq .= "host_notifications_enabled = ";
        isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) &&
        $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ?
            $rq .= "'" . $ret["host_notifications_enabled"]["host_notifications_enabled"] . "', " : $rq .= "'2', ";
        $rq .= "contact_additive_inheritance = ";
        $rq .= (isset($ret['contact_additive_inheritance']) ? 1 : 0) . ', ';
        $rq .= "cg_additive_inheritance = ";
        $rq .= (isset($ret['cg_additive_inheritance']) ? 1 : 0) . ', ';
        $rq .= "host_stalking_options = ";
        isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != null ?
            $rq .= "'" . implode(",", array_keys($ret["host_stalOpts"])) . "', " : $rq .= "NULL, ";
        $rq .= "host_snmp_community = ";
        isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_snmp_community"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_snmp_version = ";
        isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_snmp_version"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_location = ";
        isset($ret["host_location"]) && $ret["host_location"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_location"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_comment = ";
        isset($ret["host_comment"]) && $ret["host_comment"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["host_comment"]) . "', " : $rq .= "NULL, ";
        $rq .= "host_register = ";
        isset($ret["host_register"]) && $ret["host_register"] != null ?
            $rq .= "'" . $ret["host_register"] . "', " : $rq .= "NULL, ";
        $rq .= "host_activate = ";
        isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != null ?
            $rq .= "'" . $ret["host_activate"]["host_activate"] . "' " : $rq .= "NULL ";
        $rq .= "WHERE host_id = '" . $host_id . "'";

        $DBRESULT = $this->db->query($rq);

        $this->updateExtendedInfos($host_id, $ret);
    }

    /**
     *
     * Insert host extended informations in DB
     *
     */
    public function updateExtendedInfos($host_id, $ret)
    {
        $fields = array(
            'ehi_notes' => 'ehi_notes',
            'ehi_notes_url' => 'ehi_notes_url',
            'ehi_action_url' => 'ehi_action_url',
            'ehi_icon_image_alt' => 'ehi_icon_image_alt',
            'ehi_2d_coords' => 'ehi_2d_coords',
            'ehi_3d_coords' => 'ehi_3d_coords'
        );

        $integerFields = array(
            'ehi_icon_image' => 'ehi_icon_image',
            'ehi_vrml_image' => 'ehi_vrml_image',
            'ehi_statusmap_image' => 'ehi_statusmap_image',
        );

        $query = 'UPDATE extended_host_information SET ';
        $updateFields = array();
        $queryValues = array();
        foreach ($ret as $key => $value) {
            if (isset($fields[$key])) {
                $updateFields[] = '`' . $fields[$key] . '` = ? ';
                $queryValues[] = (string)$value;
            } elseif (isset($integerFields[$key])) {
                $updateFields[] = '`' . $integerFields[$key] . '` = ? ';
                $queryValues[] = (int)$value;
            }
        }

        if (count($updateFields)) {
            $query .= implode(',', $updateFields) . 'WHERE host_host_id = ? ';
            $queryValues[] = (int)$host_id;
            $stmt = $this->db->prepare($query);
            $dbResult = $this->db->execute($stmt, $queryValues);

            if (\PEAR::isError($dbResult)) {
                throw new \Exception('Error while updating extendeded infos of host ' . $host_id);
            }
        }
    }


    /**
     *
     * @param int host_id
     * @param int poller_id
     */
    public function setPollerInstance($host_id, $poller_id)
    {
        $query = 'INSERT INTO ns_host_relation (host_host_id, nagios_server_id) VALUES (?,?)';
        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, array((int)$host_id, (int)$poller_id));
    }

    /**
     *
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array(), $register = '1')
    {
        global $centreon;
        $items = array();
        $useAcl = false;
        if (!$centreon->user->access->admin && $register == '1') {
            $useAcl = true;
        }

        # get list of authorized hosts
        if ($useAcl) {
            $hAcl = $centreon->user->access->getHostAclConf(
                null,
                'broker',
                array(
                    'distinct' => true,
                    'fields' => array('host.host_id'),
                    'get_row' => 'host_id',
                    'keys' => array('host_id'),
                    'conditions' => array(
                        'host.host_id' => array(
                            'IN',
                            $values
                        )
                    )
                ),
                false
            );
        }


        $explodedValues = '';
        $queryValues = array();
        $queryValues[] = (string)$register;
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $explodedValues .= '?,';
                $queryValues[] = (int)$v;
            }
            $explodedValues = rtrim($explodedValues, ',');
        } else {
            $explodedValues .= "''";
        }

        # get list of selected hosts
        $query = 'SELECT host_id, host_name ' .
            'FROM host ' .
            'WHERE host_register = ? ' .
            'AND host_id IN (' . $explodedValues . ') ' .
            'ORDER BY host_name ';

        $stmt = $this->db->prepare($query);
        $resRetrieval = $this->db->execute($stmt, $queryValues);

        while ($row = $resRetrieval->fetchRow()) {
            # hide unauthorized hosts
            $hide = false;
            if ($useAcl && !in_array($row['host_id'], $hAcl)) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['host_id'],
                'text' => $row['host_name'],
                'hide' => $hide
            );
        }

        return $items;
    }

    /**
     * Delete host in database
     *
     * @param string $host_name Hostname
     * @throws Exception
     */
    public function deleteHostByName($host_name)
    {
        $query = 'DELETE FROM host WHERE host_name = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$host_name));

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while delete host ' . $host_name);
        }
    }
}
