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

/**
 *
 * Hostgroups objects
 * @author jmathis
 *
 */
class CentreonHostgroups
{
    /**
     *
     * @var \CentreonDB
     */
    private $DB;

    /**
     *
     * @var array
     */
    private $relationCache = [];

    /**
     *
     * @var type
     */
    private $dataTree;

    /**
     *
     * Constructor
     * @param $pearDB
     */
    public function __construct($pearDB)
    {
        $this->DB = $pearDB;
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
     *
     * Enter description here ...
     * @param unknown_type $hg_id
     * @param unknown_type $searchHost
     * @param unknown_type $level
     */
    public function getHostGroupHosts($hg_id = null)
    {
        if (!$hg_id) {
            return;
        }

        if (!count($this->relationCache)) {
            $this->setHgHgCache();
        }

        $hosts = array();
        $statement = $this->DB->prepare("SELECT hgr.host_host_id " .
            "FROM hostgroup_relation hgr, host h " .
            "WHERE hgr.hostgroup_hg_id = :hgId " .
            "AND h.host_id = hgr.host_host_id " .
            "ORDER by h.host_name");
        $statement->bindValue(':hgId', (int) $hg_id, \PDO::PARAM_INT);
        $statement->execute();

        while ($elem = $statement->fetchRow()) {
            $ref[$elem["host_host_id"]] = $elem["host_host_id"];
            $hosts[] = $elem["host_host_id"];
        }
        $statement->closeCursor();
        unset($elem);

        if (isset($hostgroups) && count($hostgroups)) {
            foreach ($hostgroups as $hg_id2) {
                $ref[$hg_id2] = array();
                $tmp = $this->getHostGroupHosts($hg_id2, "", 1);
                foreach ($tmp as $id) {
                    print "     host: $id<br>";
                }
                unset($tmp);
            }
        }
        return $hosts;
    }

    /**
     * Get Hostgroup Name
     *
     * @param int $hg_id
     * @return string
     */
    public function getHostgroupName($hg_id)
    {
        static $names = array();

        if (!isset($names[$hg_id])) {
            $query = "SELECT hg_name FROM hostgroup WHERE hg_id = " . $this->DB->escape($hg_id);
            $res = $this->DB->query($query);
            if ($res->rowCount()) {
                $row = $res->fetchRow();
                $names[$hg_id] = $row['hg_name'];
            }
        }
        if (isset($names[$hg_id])) {
            return $names[$hg_id];
        }
        return "";
    }


    /**
     * Get Hostgroups ids and names from ids
     *
     * @param int[] $hostGroupsIds
     * @return array $hostsGroups [['id' => integer, 'name' => string],...]
     */
    public function getHostsgroups($hostGroupsIds = []): array
    {
        $hostsGroups = [];

        if (!empty($hostGroupsIds)) {
            $filteredHgIds = $this->filteredArrayId($hostGroupsIds);
            $hgParams = [];
            if (count($filteredHgIds) > 0) {
                /*
                 * Building the hgParams hash table in order to correctly
                 * bind ids as ints for the request.
                 */
                foreach ($filteredHgIds as $index => $filteredHgId) {
                    $hgParams[':hgId' . $index] = $filteredHgIds;
                }

                $stmt = $this->DB->prepare(
                    'SELECT hg_id, hg_name FROM hostgroup ' .
                    'WHERE hg_id IN ( ' . implode(',', array_keys($hgParams)) . ' )'
                );

                foreach ($hgParams as $index => $value) {
                    $stmt->bindValue($index, $value, \PDO::PARAM_INT);
                }

                $stmt->execute();

                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $hostsGroups[] = [
                        'id' => $row['hg_id'],
                        'name' => $row['hg_name']
                    ];
                }
            }
        }

        return $hostsGroups;
    }


    /**
     * Get Hostgroup Id
     *
     * @param string $hg_name
     * @return int
     */
    public function getHostgroupId($hg_name)
    {
        static $ids = array();

        if (!isset($ids[$hg_name])) {
            $query = "SELECT hg_id FROM hostgroup WHERE hg_name = '" . $this->DB->escape($hg_name) . "'";
            $res = $this->DB->query($query);
            if ($res->rowCount()) {
                $row = $res->fetchRow();
                $ids[$hg_name] = $row['hg_id'];
            }
        }
        if (isset($ids[$hg_name])) {
            return $ids[$hg_name];
        }
        return 0;
    }

    /**
     * @param null $hg_id
     * @return array|void
     */
    public function getHostGroupHostGroups($hg_id = null)
    {
        if (!$hg_id) {
            return;
        }

        $hosts = array();
        $DBRESULT = $this->DB->query(
            "SELECT hg_child_id " .
            "FROM hostgroup_hg_relation, hostgroup " .
            "WHERE hostgroup_hg_relation.hg_parent_id = '" . $this->DB->escape($hg_id) . "' " .
            "AND hostgroup.hg_id = hostgroup_hg_relation.hg_child_id " .
            "ORDER BY hostgroup.hg_name"
        );
        while ($elem = $DBRESULT->fetchRow()) {
            $hosts[$elem["hg_child_id"]] = $elem["hg_child_id"];
        }
        $DBRESULT->closeCursor();
        unset($elem);
        return $hosts;
    }

    /**
     *
     * Enter description here ...
     */
    private function setHgHgCache()
    {
        $this->relationCache = array();
        $DBRESULT = $this->DB->query("SELECT /* SQL_CACHE */ hg_parent_id, hg_child_id FROM hostgroup_hg_relation");
        while ($data = $DBRESULT->fetchRow()) {
            if (!isset($this->relationCache[$data["hg_parent_id"]])) {
                $this->relationCache[$data["hg_parent_id"]] = array();
            }
            $this->relationCache[$data["hg_parent_id"]][$data["hg_child_id"]] = 1;
        }
        $DBRESULT->closeCursor();
        unset($data);
    }

    public function getAllHostgroupsInCache($DB)
    {
        $hostgroups = array();

        $this->unsetCache();

        $DBRESULT = $DB->query(
            "SELECT * FROM hostgroup WHERE hg_id NOT IN (SELECT hg_child_id FROM hostgroup_hg_relation)"
        );
        while ($data = $DBRESULT->fetchRow()) {
            $this->dataTree[$data['hg_id']] = $this->getHostGroupHosts($data['hg_id'], $this->dataTree);
        }
        $DBRESULT->closeCursor();
        return $hostgroups;
    }

    /**
     *
     */
    private function unsetCache()
    {
        $this->dataTree = array();
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'hostgroup';
        $parameters['currentObject']['id'] = 'hg_id';
        $parameters['currentObject']['name'] = 'hg_name';
        $parameters['currentObject']['comparator'] = 'hg_id';

        switch ($field) {
            case 'hg_hosts':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'hostgroup_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'hostgroup_hg_id';
                break;
        }

        return $parameters;
    }

    /**
     * @param array $values
     * @param array $options
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        global $centreon;
        $items = array();

        if (empty($values)) {
            return $items;
        }

        $hostgroups = [];
        // $values structure: ['1,2,3,4'], keeping the foreach in case it could have more than one index
        foreach ($values as $value) {
            $hostgroups = array_merge($hostgroups, explode(',', $value));
        }

        // get list of authorized hostgroups
        if (!$centreon->user->access->admin) {
            $hgAcl = $centreon->user->access->getHostGroupAclConf(
                null,
                'broker',
                array(
                    'distinct' => true,
                    'fields' => array('hostgroup.hg_id'),
                    'get_row' => 'hg_id',
                    'keys' => array('hg_id'),
                    'conditions' => array(
                        'hostgroup.hg_id' => array(
                            'IN',
                            $hostgroups
                        )
                    )
                ),
                true
            );
        }

        // get list of selected hostgroups
        $listValues = '';
        $queryValues = array();

        foreach ($hostgroups as $item) {
            // the below explode may not be useful
            $ids = explode('-', $item);
            $listValues .= ':hgId_' . $ids[0] . ', ';
            $queryValues['hgId_' . $ids[0]] = (int)$ids[0];
        }

        $listValues = rtrim($listValues, ', ');
        $query = 'SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (' . $listValues . ') ORDER BY hg_name ';
        $stmt = $this->DB->prepare($query);
        foreach ($queryValues as $key => $id) {
            $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            // hide unauthorized hostgroups
            $hide = false;
            if (!$centreon->user->access->admin && !in_array($row['hg_id'], $hgAcl)) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['hg_id'],
                'text' => $row['hg_name'],
                'hide' => $hide
            );
        }
        return $items;
    }

    /**
     * @param $hgName
     * @return array
     */
    public function getHostsByHostgroupName($hgName)
    {
        $hostList = array();
        $query = "SELECT host_name, host_id  " .
            "FROM hostgroup_relation hgr, host h, hostgroup hg " .
            "WHERE hgr.host_host_id = h.host_id " .
            "AND hgr.hostgroup_hg_id = hg.hg_id " .
            "AND h.host_activate = '1' " .
            "AND hg.hg_name = '" . $this->DB->escape($hgName) . "'";
        $result = $this->DB->query($query);
        while ($elem = $result->fetchrow()) {
            $hostList[] = array(
                'host' => $elem['host_name'],
                'host_id' => $elem['host_id'],
                'hg_name' => $hgName
            );
        }
        return $hostList;
    }
}
