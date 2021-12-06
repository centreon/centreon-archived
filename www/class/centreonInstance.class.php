<?php
/**
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

/**
 * Class for handling Instances
 */
class CentreonInstance
{
    protected $db;
    protected $dbo;
    protected $params;
    protected $instances;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db, $dbo = null)
    {
        $this->db = $db;
        if (!empty($dbo)) {
            $this->dbo = $dbo;
        }
        $this->instances = array();
        $this->initParams();
    }

    /**
     * Initialize Parameters
     *
     * @return void
     */
    protected function initParams()
    {
        $this->params = array();
        $this->paramsByName = array();
        $query = "SELECT id, name, localhost, last_restart, ns_ip_address FROM nagios_server";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $instanceId = $row['id'];
            $instanceName = $row['name'];
            $this->instances[$instanceId] = $instanceName;
            $this->params[$instanceId] = array();
            $this->paramsByName[$instanceName] = array();
            foreach ($row as $key => $value) {
                $this->params[$instanceId][$key] = $value;
                $this->paramsByName[$instanceName][$key] = $value;
            }
        }
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
     * Get instance_id and name from instances ids
     *
     * @param  int[] $pollerIds
     * @return array $pollers [['instance_id => integer, 'name' => string],...]
     */
    public function getInstancesMonitoring($pollerIds = [])
    {
        $pollers = [];

        if (!empty($pollerIds)) {
            /* checking here that the array provided as parameter
             * is exclusively made of integers (servicegroup ids)
             */
            $filteredPollerIds = $this->filteredArrayId($pollerIds);
            $pollerParams = [];
            if (count($filteredPollerIds) > 0) {
                /*
                 * Building the pollerParams hash table in order to correctly
                 * bind ids as ints for the request.
                 */
                foreach ($filteredPollerIds as $index => $filteredPollerId) {
                    $pollerParams[':pollerId' . $index] = $filteredPollerId;
                }
                $stmt = $this->DB->prepare(
                    'SELECT i.instance_id, i.name FROM instances i ' .
                    'WHERE i.instance_id IN ( ' . implode(',', array_keys($pollerParams)) . ' )'
                );
                foreach ($pollerParams as $index => $value) {
                    $stmt->bindValue($index, $value, \PDO::PARAM_INT);
                }
                $stmt->execute();

                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $pollers[] = [
                        'id' => $row['instance_id'],
                        'name' => $row['name']
                    ];
                }
            }
        }

        return $pollers;
    }


    /**
     * Get Parameter
     *
     * @param mixed $instance
     * @param string $paramName
     * @return string
     */
    public function getParam($instance, $paramName)
    {
        if (is_numeric($instance)) {
            if (isset($this->params[$instance]) && isset($this->params[$instance][$paramName])) {
                return $this->params[$instance][$paramName];
            }
        } else {
            if (isset($this->paramsByName[$instance]) && isset($this->paramsByName[$instance][$paramName])) {
                return $this->paramsByName[$instance][$paramName];
            }
        }
        return null;
    }

    /**
     * Get Instances
     *
     * @return array
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Get command data from poller id
     *
     * @param int $pollerId
     * @return array
     */
    public function getCommandData($pollerId)
    {
        $sql = "SELECT c.command_id, c.command_name, c.command_line 
            FROM command c, poller_command_relations pcr
            WHERE pcr.poller_id = ?
            AND pcr.command_id = c.command_id
            ORDER BY pcr.command_order";
        $res = $this->db->query($sql, array($pollerId));
        $arr = array();
        while ($row = $res->fetchRow()) {
            $arr[] = $row;
        }
        return $arr;
    }

    /**
     * Return list of commands used by poller
     *
     * @param int $pollerId
     * @return array
     */
    public function getCommandsFromPollerId($pollerId = null)
    {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['pollercmd']) && $pollerId) {
            $sql = "SELECT command_id 
                FROM poller_command_relations 
                WHERE poller_id = ?
                ORDER BY command_order";
            $res = $this->db->query($sql, array($pollerId));
            while ($row = $res->fetchRow()) {
                $arr[$i]['pollercmd_#index#'] = $row['command_id'];
                $i++;
            }
        } elseif (isset($_REQUEST['pollercmd'])) {
            foreach ($_REQUEST['pollercmd'] as $val) {
                $arr[$i]['pollercmd_#index#'] = $val;
                $i++;
            }
        }
        return $arr;
    }

    /**
     * Set post-restart commands
     *
     * @param int $pollerId
     * @param array $commands
     * @return void
     */
    public function setCommands($pollerId, $commands)
    {
        $this->db->query("DELETE FROM poller_command_relations
                WHERE poller_id = " . $this->db->escape($pollerId));

        $stored = array();
        $i = 1;
        foreach ($commands as $value) {
            if ($value != "" &&
                !isset($stored[$value])
            ) {
                $this->db->query("INSERT INTO poller_command_relations
                        (`poller_id`, `command_id`, `command_order`) 
                        VALUES (" . $this->db->escape($pollerId) . ", " . $this->db->escape($value) . ", " . $i . ")");
                $stored[$value] = true;
                $i++;
            }
        }
    }

    /**
     * @param array $values
     * @param array $options
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $selectedInstances = '';
        $items = array();
        $listValues = '';
        $queryValues = array();
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':instance' . $v . ',';
                $queryValues['instance' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
            $selectedInstances .= "AND rel.instance_id IN ($listValues) ";
        } else {
            $listValues .= '""';
        }

        $query = 'SELECT DISTINCT p.name as name, p.id  as id FROM cfg_resource r, nagios_server p, ' .
            'cfg_resource_instance_relations rel ' .
            ' WHERE r.resource_id = rel.resource_id' .
            ' AND p.id = rel.instance_id ' .
            ' AND p.id IN (' . $listValues . ')' . $selectedInstances .
            ' ORDER BY p.name';

        $stmt = $this->db->prepare($query);
        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($data = $stmt->fetch()) {
            $items[] = array(
                'id' => $data['id'],
                'text' => $data['name']
            );
        }

        return $items;
    }

    /**
     * @param $instanceName
     * @return array
     */
    public function getHostsByInstance($instanceName)
    {
        $instanceList = array();

        $query = "SELECT host_name, name " .
            " FROM host h, nagios_server ns, ns_host_relation nshr " .
            " WHERE ns.name = '" . $this->db->escape($instanceName) . "'" .
            " AND nshr.host_host_id = h.host_id " .
            " AND h.host_activate = '1' " .
            " ORDER BY h.host_name";
        $result = $this->db->query($query);

        while ($elem = $result->fetchrow()) {
            $instanceList[] = array(
                'host' => $elem['host_name'],
                'name' => $instanceName
            );
        }
        return $instanceList;
    }

    public function getInstanceId($instanceName)
    {
        $query = "SELECT ns.id " .
            " FROM nagios_server ns " .
            " WHERE ns.name = '" . $this->db->escape($instanceName) . "'";
        $result = $this->db->query($query);

        return $result->fetchrow();
    }
}
