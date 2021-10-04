<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once __DIR__ . '/centreonInstance.class.php';
require_once __DIR__ . '/centreonService.class.php';
require_once __DIR__ . '/centreonCommand.class.php';
require_once __DIR__ . '/centreonLogAction.class.php';

/*
 *  Class that contains various methods for managing hosts
 */
class CentreonHost
{
    /**
     *
     * @var \CentreonDB
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
     * get all host templates saved in the DB
     *
     * @param bool $enable
     * @param bool $template
     * @param null|int $exclude - host id to exclude in returned result
     *
     * @return array
     * @throws Exception
     */
    public function getList($enable = false, $template = false, $exclude = null)
    {
        $hostType = 1;
        if ($template) {
            $hostType = 0;
        }
        $queryList = 'SELECT host_id, host_name ' .
            'FROM host ' .
            'WHERE host_register = :register ';
        if ($enable) {
            $queryList .= 'AND host_activate = "1" ';
        }
        if ($exclude !== null) {
            $queryList .= 'AND host_id <> :exclude_id ';
        }
        $queryList .= 'ORDER BY host_name';
        $stmt = $this->db->prepare($queryList);
        $stmt->bindParam(':register', $hostType, PDO::PARAM_STR);
        if ($exclude !== null) {
            $stmt->bindParam(':exclude_id', $exclude, PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $listHost = array();
        while ($row = $stmt->fetch()) {
            $listHost[$row['host_id']] = $row['host_name'];
        }
        return $listHost;
    }

    /**
     * get the template currently saved for this host
     *
     * @param $hostId
     *
     * @return array
     */
    public function getSavedTpl($hostId): array
    {
        $mTp = [];
        $dbResult = $this->db->prepare(
            'SELECT host_tpl_id, host.host_name
            FROM host_template_relation, host
            WHERE host_host_id = :hostId
            AND host_tpl_id = host.host_id'
        );
        $dbResult->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $dbResult->execute();
        while ($multiTp = $dbResult->fetch()) {
            $mTp[$multiTp["host_tpl_id"]] = $multiTp["host_name"];
        }

        return $mTp;
    }

    /**
     *  get number of hosts
     *
     * @return int
     */
    private function getHostNumber(): int
    {
        $query = $this->db->query('SELECT COUNT(*) AS `num` FROM host WHERE host_register = "1"');
        return ((int)$query->fetch()['num']);
    }

    /**
     * Returns a filtered array with only integer ids
     *
     * @param  int[] $ids
     * @return int[] filtered
     */
    private function filteredArrayId(array $ids): array
    {
        return array_filter($ids, function ($id) {
            return is_numeric($id);
        });
    }

    /**
     *  get list of inherited templates from plugin pack
     *
     * @return array
     * @throws Exception
     */
    public function getLimitedList(): array
    {
        $freePp = array(
            'applications-databases-mysql',
            'applications-monitoring-centreon-central',
            'applications-monitoring-centreon-database',
            'applications-monitoring-centreon-poller',
            'base-generic',
            'hardware-printers-standard-rfc3805-snmp',
            'hardware-ups-standard-rfc1628-snmp',
            'network-cisco-standard-snmp',
            'operatingsystems-linux-snmp',
            'operatingsystems-windows-snmp'
        );
        $ppList = array();
        $dbResult = $this->db->query('SELECT `name` FROM modules_informations WHERE `name` = "centreon-pp-manager"');
        if (empty($dbResult->fetch()) || true === $this->isAllowed()) {
            return $ppList;
        }
        $dbResult = $this->db->query(
            'SELECT ph.host_id 
            FROM mod_ppm_pluginpack_host ph, mod_ppm_pluginpack pp
            WHERE ph.pluginpack_id = pp.pluginpack_id
            AND pp.slug NOT IN ("' . implode('","', $freePp) . '")'
        );
        while ($row = $dbResult->fetch()) {
            $this->getHostChain($row['host_id'], $ppList);
        }
        asort($ppList);
        return $ppList;
    }

    /**
     * @param $hostId
     * @param bool $withHg
     * @return array
     * @throws Exception
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
            'AND hp.host_parent_hp_id = :hostId';
        $stmt = $this->db->prepare($queryGetChildren);
        $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $listHostChildren = array();
        while ($row = $stmt->fetch()) {
            $listHostChildren[$row['host_id']] = $row['host_alias'];
        }
        return $listHostChildren;
    }

    /**
     * @param bool $withHg
     * @return array
     * @throws Exception
     */
    public function getHostRelationTree($withHg = false)
    {
        $queryGetRelationTree = 'SELECT hp.host_parent_hp_id, h.host_id, h.host_name ' .
            'FROM host h, host_hostparent_relation hp ' .
            'WHERE hp.host_host_id = h.host_id ' .
            'AND h.host_register = "1" ' .
            'AND h.host_activate = "1"';
        $dbResult = $this->db->query($queryGetRelationTree);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $listHostRelactionTree = array();
        while ($row = $dbResult->fetch()) {
            if (!isset($listHostRelactionTree[$row['host_parent_hp_id']])) {
                $listHostRelactionTree[$row['host_parent_hp_id']] = array();
            }
            $listHostRelactionTree[$row['host_parent_hp_id']][$row['host_id']] = $row['host_alias'];
        }
        return $listHostRelactionTree;
    }

    /**
     * @param $hostId
     * @param bool $withHg
     * @param bool $withDisabledServices
     * @return array
     * @throws Exception
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
            'AND hsr.host_host_id = :hostId';

        $stmt = $this->db->prepare($queryGetServices);
        $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $listServices = array();
        while ($row = $stmt->fetch()) {
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
                'AND hgr.host_host_id = :hostId';
            $stmt = $this->db->prepare($queryGetServicesWithHg);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
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
        if (!$res) {
            throw new \Exception("An error occured");
        }
        $listServices = array();
        while ($row = $res->fetch()) {
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
    public function getHostName($hostId)
    {
        static $hosts = null;

        if (!isset($hostId) || !$hostId) {
            return null;
        }

        if (is_null($hosts)) {
            $hosts = array();
            $query = 'SELECT host_id, host_name FROM host';
            $res = $this->db->query($query);
            if (!$res) {
                throw new \Exception("An error occured");
            }
            while ($row = $res->fetch()) {
                $hosts[$row['host_id']] = $row['host_name'];
            }
        }
        if (isset($hosts[$hostId])) {
            return $hosts[$hostId];
        }
        return null;
    }

    /**
     * @param $hostId
     * @return mixed
     * @throws Exception
     */
    public function getOneHostName($hostId)
    {
        if (isset($hostId) && is_numeric($hostId)) {
            $query = 'SELECT host_id, host_name FROM host where host_id = ?';
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute(array((int)$hostId));
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            $row = $stmt->fetch();
            return $row['host_name'];
        }
    }

    /**
     * @param int[] $hostId
     * @return array $hosts [['id' => integer, 'name' => string],...]
     */
    public function getHostsNames($hostId = []): array
    {
        $hosts = [];
        if (!empty($hostId)) {
            /*
            * Checking here that the array provided as parameter
             * is exclusively made of integers (host ids)
             */
            $filteredHostIds = $this->filteredArrayId($hostId);
            $hostParams = [];
            if (count($filteredHostIds) > 0) {
                /*
                 * Building the hostParams hash table in order to correctly
                 * bind ids as ints for the request.
                 */
                foreach ($filteredHostIds as $index => $filteredHostId) {
                    $hostParams[':hostId' . $index] = $filteredHostId;
                }

                $stmt = $this->db->prepare('SELECT host_id, host_name ' .
                    'FROM host where host_id IN ( ' . implode(',', array_keys($hostParams)) . ' )');

                foreach ($hostParams as $index => $value) {
                    $stmt->bindValue($index, $value, \PDO::PARAM_INT);
                }

                $dbResult = $stmt->execute();

                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $hosts[] = [
                        'id' => $row['host_id'],
                        'name' => $row['host_name']
                    ];
                }
            }
        }
        return $hosts;
    }

    /**
     * @param $hostId
     * @return mixed
     * @throws Exception
     */
    public function getHostCommandId($hostId)
    {
        if (isset($hostId) && is_numeric($hostId)) {
            $query = 'SELECT host_id, command_command_id FROM host where host_id = :hostId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            $row = $stmt->fetch();
            return $row['command_command_id'];
        }
    }

    /**
     * @param $hostId
     * @return mixed|null
     * @throws Exception
     */
    public function getHostAlias($hostId)
    {
        static $aliasTab = array();

        if (!isset($hostId) || !$hostId) {
            return null;
        }
        if (!isset($aliasTab[$hostId])) {
            $query = 'SELECT host_alias FROM host WHERE host_id = :hostId LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $aliasTab[$hostId] = $row['host_alias'];
            }
        }
        if (isset($aliasTab[$hostId])) {
            return $aliasTab[$hostId];
        }
        return null;
    }

    /**
     * @param $hostId
     * @return mixed|null
     * @throws Exception
     */
    public function getHostAddress($hostId)
    {
        static $addrTab = array();

        if (!isset($hostId) || !$hostId) {
            return null;
        }
        if (!isset($addrTab[$hostId])) {
            $query = 'SELECT host_address FROM host WHERE host_id = :hostId LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $addrTab[$hostId] = $row['host_address'];
            }
        }
        if (isset($addrTab[$hostId])) {
            return $addrTab[$hostId];
        }
        return null;
    }

    /**
     * @param $address
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getHostByAddress($address, $params = array())
    {
        $paramsList = '';
        $hostList = array();

        if (count($params) > 0) {
            foreach ($params as $k => $v) {
                $paramsList .= "`$v`,";
            }
            $paramsList = rtrim($paramsList, ',');
        } else {
            $paramsList .= '*';
        }
        $query = 'SELECT ' . $paramsList . ' FROM host WHERE host_address = :address';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            $hostList[] = $row;
        }
        return $hostList;
    }

    /**
     * @param $hostName
     * @return mixed|null
     * @throws Exception
     */
    public function getHostId($hostName)
    {
        static $ids = array();

        if (!isset($hostName) || !$hostName) {
            return null;
        }
        if (!isset($ids[$hostName])) {
            $query = 'SELECT host_id ' .
                'FROM host ' .
                'WHERE host_name = :hostName ' .
                'LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostName', $hostName, PDO::PARAM_STR);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $ids[$hostName] = $row['host_id'];
            }
        }
        if (isset($ids[$hostName])) {
            return $ids[$hostName];
        }
        return null;
    }

    /**
     * @param $hostName
     * @param null $pollerId
     * @return mixed
     * @throws Exception
     */
    public function checkIllegalChar($hostName, $pollerId = null)
    {
        if ($pollerId) {
            $query = 'SELECT illegal_object_name_chars FROM cfg_nagios WHERE nagios_server_id = :pollerId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pollerId', $pollerId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        } else {
            $stmt = $this->db->query('SELECT illegal_object_name_chars FROM cfg_nagios ');
        }

        while ($data = $stmt->fetch()) {
            $tab = str_split(html_entity_decode($data['illegal_object_name_chars'], ENT_QUOTES, "UTF-8"));
            foreach ($tab as $char) {
                $hostName = str_replace($char, "", $hostName);
            }
        }
        $stmt->closeCursor();
        return $hostName;
    }

    /**
     * Returns the poller id of the host linked to hostId provided
     * @param int $hostId
     * @return int|null $pollerId
     * @throws Exception
     */
    public function getHostPollerId(int $hostId): ?int
    {
        $pollerId = null;
        if ($hostId) {
            $query = 'SELECT nagios_server_id FROM ns_host_relation WHERE host_host_id = :hostId LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $pollerId = (int) $row['nagios_server_id'];
            } else {
                $hostName = $this->getHostName($hostId);
                if (preg_match('/^_Module_Meta/', $hostName)) {
                    $query = 'SELECT id ' .
                        'FROM nagios_server ' .
                        'WHERE localhost = "1" ' .
                        'LIMIT 1 ';
                    $res = $this->db->query($query);
                    if ($res->rowCount()) {
                        $row = $res->fetch();
                        $pollerId = (int) $row['id'];
                    }
                }
            }
        }
        return $pollerId;
    }

    /**
     * @param $hostParam
     * @param $string
     * @param null $antiLoop
     * @return mixed
     * @throws Exception
     */
    public function replaceMacroInString($hostParam, $string, $antiLoop = null)
    {
        if (is_numeric($hostParam)) {
            $hostId = $hostParam;
        } elseif (is_string($hostParam)) {
            $hostId = $this->getHostId($hostParam);
        } else {
            return $string;
        }
        $query = 'SELECT host_register FROM host WHERE host_id = :hostId LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if (!$stmt->rowCount()) {
            return $string;
        }
        $row = $stmt->fetch();

        /*
         * replace if not template
         */
        if ($row['host_register'] == 1) {
            if (strpos($string, "\$HOSTADDRESS$")) {
                $string = str_replace("\$HOSTADDRESS\$", $this->getHostAddress($hostId), $string);
            }
            if (strpos($string, "\$HOSTNAME$")) {
                $string = str_replace("\$HOSTNAME\$", $this->getHostName($hostId), $string);
            }
            if (strpos($string, "\$HOSTALIAS$")) {
                $string = str_replace("\$HOSTALIAS\$", $this->getHostAlias($hostId), $string);
            }
            if (preg_match("\$INSTANCENAME\$", $string)) {
                $string = str_replace(
                    "\$INSTANCENAME\$",
                    $this->instanceObj->getParam($this->getHostPollerId($hostId), 'name'),
                    $string
                );
            }
            if (preg_match("\$INSTANCEADDRESS\$", $string)) {
                $string = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $this->instanceObj->getParam($this->getHostPollerId($hostId), 'ns_ip_address'),
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
            $query = 'SELECT host_macro_value ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = :hostId ' .
                'AND host_macro_name LIKE :macro';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $stmt->bindParam(':macro', $matches[1][$i], PDO::PARAM_STR);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
                $string = str_replace($matches[1][$i], $row['host_macro_value'], $string);
            }
            $i++;
        }
        if ($i) {
            $query2 = 'SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = :host ORDER BY `order`';
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':host', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt2->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row2 = $stmt2->fetch()) {
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
     * @param $hostId
     * @param array $macroInput
     * @param array $macroValue
     * @param array $macroPassword
     * @param array $macroDescription
     * @param bool $isMassiveChange
     * @param bool $cmdId
     * @throws Exception
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
            $query = 'DELETE FROM on_demand_macro_host WHERE host_host_id = :hostId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
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
                $dbResult = $stmt->execute($queryValues);
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
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
                $dbResult = $stmt->execute($queryValues);
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
                $cnt++;
                $stored[strtolower($value)] = true;
            }
        }
    }

    /**
     * @param null $hostId
     * @param null $template
     * @return array
     * @throws Exception
     */
    public function getCustomMacroInDb($hostId = null, $template = null)
    {
        $arr = array();
        $i = 0;

        if ($hostId) {
            $sSql = 'SELECT host_macro_name, host_macro_value, is_password, description ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = :hostId ' .
                'ORDER BY macro_order ASC';
            $stmt = $this->db->prepare($sSql);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
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
     * @param null $hostId
     * @param bool $realKeys
     * @return array
     * @throws Exception
     */
    public function getCustomMacro($hostId = null, $realKeys = false)
    {
        $arr = array();
        $i = 0;

        if (!isset($_REQUEST['macroInput']) && $hostId) {
            $sSql = 'SELECT host_macro_name, host_macro_value, is_password, description ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id = :hostId ' .
                'ORDER BY macro_order ASC';
            $stmt = $this->db->prepare($sSql);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
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
                $arr[$index]['macroPassword_#index#'] = isset($_REQUEST['macroPassword'][$key]) ? 1 : null;
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
     * @param null $hostId
     * @return array
     * @throws Exception
     */
    public function getTemplates($hostId = null)
    {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['tpSelect']) && $hostId) {
            $query = 'SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = :host ORDER BY `order`';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':host', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
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
     * @param $hostId
     * @param array $templates
     * @param array $remaining
     * @throws Exception
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
        $dbResult = $stmt->execute($queryValues);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

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
            $dbResult = $stmt->execute($queryValues);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
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
            $stmt = $this->db->query($query);
            while ($row = $stmt->fetch()) {
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
        $aMacros = $this->getMacros($host_id, $aTemplates, $cmdId);
        foreach ($aMacros as $macro) {
            foreach ($macroInput as $ind => $input) {
                if (isset($macro['macroInput_#index#'])) {
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
     * @param int $iHostId
     * @param $aListTemplate
     * @param int $iIdCommande
     * @param array $form
     * @return array
     * @throws Exception
     */
    public function getMacros($iHostId, $aListTemplate, $iIdCommande, $form = array())
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
        $macros = array();

        /* Direct macros */
        $macroArray = $this->getCustomMacro(null, 'realKeys');
        $this->purgeOldMacroToForm($macroArray, $form, 'fromTpl');
        $this->purgeOldMacroToForm($macroArray, $form, 'fromCommand');
        foreach ($macroArray as $key => $directMacro) {
            $directMacro['macroOldValue_#index#'] = $directMacro["macroValue_#index#"];
            $directMacro['macroFrom_#index#'] = $form['macroFrom'][$key];
            $directMacro['source'] = 'direct';
            $macros[] = $directMacro;
        }

        /* Template macros */
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

            foreach ($aMacroTemplate as $key => $macr) {
                $macr['macroOldValue_#index#'] = $macr["macroValue_#index#"];
                $macr['macroFrom_#index#'] = 'fromTpl';
                $macr['source'] = 'fromTpl';
                $macros[] = $macr;
            }
        }

        /* Command macros */
        if (isset($form['command_command_id'])) {
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

            $aMacroInCommande = array();
            //Get macro attached to the command
            $oCommand = new CentreonCommand($this->db);
            if (!empty($iIdCommande) && is_numeric($iIdCommande)) {
                $macrosCommande = $oCommand->getMacroByIdAndType($iIdCommande, 'host');
                if (!empty($macrosCommande)) {
                    foreach ($macrosCommande as $macroscmd) {
                        $macroscmd['macroTpl_#index#'] =
                            $templateName . ' Commande : ' . $macroscmd['macroCommandFrom'];
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

            $macroCommande = $aMacroInCommande;
            foreach ($macroCommande as $macroCommand) {
                $macroCommand['macroOldValue_#index#'] = $macroCommand["macroValue_#index#"];
                $macroCommand['macroFrom_#index#'] = 'fromCommand';
                $macroCommand['source'] = 'fromCommand';
                $macros[] = $macroCommand;
            }
        }

        $macros = $this->macroUnique($macros);
        return $macros;
    }

    /**
     * @param $hostId
     * @param array $alreadyProcessed
     * @param int $depth
     * @param bool $allFields
     * @param array $fields
     * @return array
     * @throws Exception
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
                    'AND htr.host_host_id = :hostId ' .
                    'AND host_activate = "1" ' .
                    'AND host_register = "0" ' .
                    'ORDER BY `order` ASC';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }

                while ($row = $stmt->fetch()) {
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
     * @param $hostId
     * @param $alreadyProcessed
     * @throws Exception
     */
    private function getHostChain(
        $hostId,
        &$alreadyProcessed
    ) {
        if (!in_array($hostId, $alreadyProcessed)) {
            $alreadyProcessed[$hostId] = $hostId;
            $query = 'SELECT host_host_id FROM host_template_relation htr 
                WHERE htr.host_tpl_id = :hostId 
                ORDER BY `order` ASC';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
                $this->getHostChain($row['host_host_id'], $alreadyProcessed);
            }
        }
    }

    /**
     * @param $hostId
     * @return array
     * @throws Exception
     */
    public function getHostTemplateIds($hostId)
    {
        $hostTemplateIds = array();
        $query = 'SELECT htr.host_tpl_id ' .
            'FROM host_template_relation htr, host ht ' .
            'WHERE htr.host_host_id = :hostId ' .
            'AND htr.host_tpl_id = ht.host_id ' .
            'AND ht.host_activate = "1" ' .
            'ORDER BY `order` ASC ';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            $hostTemplateIds[] = $row['host_tpl_id'];
        }
        return $hostTemplateIds;
    }

    /**
     * @param $hostId
     * @param array $alreadyProcessed
     * @param int $depth
     * @param array $fields
     * @param array $values
     * @return array
     * @throws Exception
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
                    $queryFields = '';
                    if (count($fields) > 0) {
                        foreach ($fields as $k => $v) {
                            $queryFields .= "`$v`,";
                        }
                        $queryFields = rtrim($queryFields, ',');
                    } else {
                        $queryFields .= '*';
                    }
                }
                $query = 'SELECT ' . $queryFields . ' ' .
                    'FROM host h, extended_host_information ehi ' .
                    'WHERE host_id = :hostId AND host_id = ehi.host_host_id';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
                while ($row = $stmt->fetch()) {
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
     * check host limitation
     *
     * @return bool
     * @throws \Exception
     */
    private function isAllowed(): bool
    {
        $dbResult = $this->db->query(
            'SELECT `name` FROM modules_informations
            WHERE `name` = "centreon-license-manager"'
        );

        if (empty($dbResult->fetch())) {
            return false;
        }
        try {
            $container = \Centreon\LegacyContainer::getInstance();
        } catch (Exception $e) {
            throw new Exception('Cannot instantiate container');
        }

        $container[\CentreonLicense\ServiceProvider::LM_PRODUCT_NAME] = 'epp';
        $container[\CentreonLicense\ServiceProvider::LM_HOST_CHECK] = true;

        if (!$container[\CentreonLicense\ServiceProvider::LM_LICENSE]) {
            return false;
        }

        $licenceManager = $container[\CentreonLicense\ServiceProvider::LM_LICENSE];
        if (!$licenceManager->validate()) {
            return false;
        }
        $licenseData = ((int)$licenceManager->getData()['licensing']['hosts']) ?? 0;
        $num = $this->getHostNumber();

        return ($licenseData === -1 || $licenseData > $num);
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
            $stmt = $this->db->query("SELECT host_id FROM host WHERE host_locked = 1");
            while ($row = $stmt->fetch()) {
                $arr[$row['host_id']] = true;
            }
        }
        return $arr;
    }

    /**
     * @param $hostId
     * @return array
     * @throws Exception
     */
    public function getServicesTemplates($hostId)
    {
        $query = 'SELECT s.service_id,s.command_command_id,s.service_description from host_service_relation hsr ' .
            'INNER JOIN service s on hsr.service_service_id = s.service_id and s.service_register = "0" ' .
            'WHERE hsr.host_host_id = :hostId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $arrayTemplate = array();
        while ($row = $stmt->fetch()) {
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
                $parameters['externalObject']['table'] = 'hostcategories';
                $parameters['externalObject']['id'] = 'hc_id';
                $parameters['externalObject']['name'] = 'hc_name';
                $parameters['externalObject']['comparator'] = 'hc_id';
                $parameters['externalObject']['additionalComparator'] = array('level' => null);
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
     * @param $hostTplId
     * @return array
     * @throws Exception
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
            'AND hsr.host_host_id = :hostId';

        $stmt = $this->db->prepare($queryGetServices);
        $stmt->bindParam(':hostId', $hostTplId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $listServices = array();
        while ($row = $stmt->fetch()) {
            $listServices[$row['service_id']] = array(
                "service_description" => $row['service_description'],
                "service_alias" => $row['service_alias']
            );
        }
        return $listServices;
    }

    /**
     * @param $hostId
     * @param null $hostTemplateId
     * @throws Exception
     */
    public function deployServices($hostId, $hostTemplateId = null)
    {
        global $centreon;

        if (!isset($hostTemplateId)) {
            $id = $hostId;
        } else {
            $id = $hostTemplateId;
        }
        $templates = $this->getTemplateChain($id);

        foreach ($templates as $templateId) {
            $serviceTemplates = $this->getServicesTplInHostTpl($templateId['id']);

            foreach ($serviceTemplates as $serviceTemplateId => $service) {
                $query = 'SELECT service_id ' .
                    'FROM service s, host_service_relation hsr ' .
                    'WHERE s.service_id = hsr.service_service_id ' .
                    'AND s.service_description = :serviceDescription ' .
                    'AND hsr.host_host_id = :hostId ' .
                    'UNION ' .
                    'SELECT service_id ' .
                    'FROM service s, host_service_relation hsr ' .
                    'WHERE s.service_id = hsr.service_service_id ' .
                    'AND s.service_description = :serviceDescription ' .
                    'AND hsr.hostgroup_hg_id IN ( ' .
                    'SELECT hostgroup_hg_id ' .
                    'FROM hostgroup_relation ' .
                    'WHERE host_host_id = :hostId  )';

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':serviceDescription', $service['service_alias'], PDO::PARAM_STR);
                $stmt->bindParam(':hostId', $hostId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
                if (!$stmt->rowCount()) {
                    $serviceDesc = array(
                        'service_description' => $service['service_alias'],
                        'service_activate' => array('service_activate' => '1'),
                        'service_register' => '1',
                        'service_template_model_stm_id' => $serviceTemplateId,
                        'service_hPars' => $hostId
                    );

                    $svcId = $this->serviceObj->insert($serviceDesc);
                    $fields = CentreonLogAction::prepareChanges($serviceDesc);
                    $centreon->CentreonLogAction->insertLog(
                        "service",
                        $svcId,
                        CentreonDB::escape($service['service_alias']),
                        "a",
                        $fields
                    );
                    $this->insertRelHostService($hostId, $svcId);
                }
                $stmt->closeCursor();
            }
            $this->deployServices($hostId, $templateId['id']);
        }
    }

    /**
     * @param $ret
     * @return mixed
     * @throws Exception
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
        $dbResult = $this->db->query($rq);
        if (!$dbResult) {
            throw new \Exception('Error while insert host ' . $ret['host_name']);
        }

        $stmt = $this->db->query("SELECT MAX(host_id) AS host_id FROM host");
        $lastHost = $stmt->fetch();
        $ret['host_id'] = $lastHost['host_id'];
        $this->insertExtendedInfos($ret);

        return $lastHost['host_id'];
    }

    /**
     * @param $ret
     * @throws Exception
     */
    public function insertExtendedInfos($ret)
    {
        if (empty($ret['host_id'])) {
            return;
        }

        $rq = "INSERT INTO `extended_host_information` " .
            "( `ehi_id` , `host_host_id` , `ehi_notes` , `ehi_notes_url` , " .
            "`ehi_action_url` , `ehi_icon_image` , `ehi_icon_image_alt` , " .
            "`ehi_statusmap_image` , `ehi_2d_coords` , " .
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
        isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_statusmap_image"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_2d_coords"]) . "', " : $rq .= "NULL, ";
        isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != null ?
            $rq .= "'" . CentreonDB::escape($ret["ehi_3d_coords"]) . "' " : $rq .= "NULL ";
        $rq .= ")";
        $dbResult = $this->db->query($rq);
        if (!$dbResult) {
            throw new \Exception('Error while insert host extended info ' . $ret['host_name']);
        }
    }

    /**
     * @param $iHostId
     * @param $iServiceId
     * @throws Exception
     */
    public function insertRelHostService($iHostId, $iServiceId)
    {
        if (empty($iHostId) || empty($iServiceId)) {
            return;
        }
        $query = 'INSERT INTO host_service_relation (host_host_id, service_service_id) VALUES (:host, :service)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':host', $iHostId, PDO::PARAM_INT);
        $stmt->bindParam(':service', $iServiceId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * @param $hostId
     * @param $ret
     * @throws Exception
     */
    public function update($hostId, $ret)
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
        $rq .= "WHERE host_id = '" . $hostId . "'";

        $dbResult = $this->db->query($rq);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $this->updateExtendedInfos($hostId, $ret);
    }

    /**
     * @param $hostId
     * @param $ret
     * @throws Exception
     */
    public function updateExtendedInfos($hostId, $ret)
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
            $queryValues[] = (int)$hostId;
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryValues);
            if (!$dbResult) {
                throw new \Exception('Error while updating extendeded infos of host ' . $hostId);
            }
        }
    }

    /**
     * @param $hostId
     * @param $pollerId
     * @throws Exception
     */
    public function setPollerInstance($hostId, $pollerId)
    {
        $query = 'INSERT INTO ns_host_relation (host_host_id, nagios_server_id) VALUES (:host,:poller)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':host', $hostId, PDO::PARAM_INT);
        $stmt->bindParam(':poller', $pollerId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * @param array $values
     * @param array $options
     * @param string $register
     *
     * @return array
     */
    public function getObjectForSelect2($values = [], $options = [], $register = '1')
    {
        global $centreon;
        $items = [];
        $useAcl = false;
        if (!$centreon->user->access->admin && $register == '1') {
            $useAcl = true;
        }

        # get list of authorized hosts
        if ($useAcl) {
            $hAcl = $centreon->user->access->getHostAclConf(
                null,
                'broker',
                [
                    'distinct' => true,
                    'fields' => ['host.host_id'],
                    'get_row' => 'host_id',
                    'keys' => ['host_id'],
                    'conditions' => [
                        'host.host_id' => [
                            'IN',
                            $values,
                        ],
                    ],
                ],
                false
            );
        }

        $listValues = '';
        $queryValues = [];
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':host' . $v . ',';
                $queryValues['host' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= "''";
        }

        # get list of selected hosts
        $query = 'SELECT host_id, host_name FROM host ' .
            'WHERE host_register = :register ' .
            'AND host_id IN (' . $listValues . ') ' .
            'ORDER BY host_name ';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':register', $register, PDO::PARAM_STR);

        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            # hide unauthorized hosts
            $hide = false;
            if ($useAcl && !in_array($row['host_id'], $hAcl)) {
                $hide = true;
            }

            $items[] = [
                'id' => $row['host_id'],
                'text' => $row['host_name'],
                'hide' => $hide,
            ];
        }

        return $items;
    }

    /**
     * @param $hostName
     * @throws Exception
     */
    public function deleteHostByName($hostName)
    {
        $query = 'DELETE FROM host WHERE host_name = :hostName';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':hostName', $hostName, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception('Error while delete host ' . $hostName);
        }
    }
}
