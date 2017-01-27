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
 */
class CentreonTimeperiod
{
    /**
     *
     * @var type
     */
    protected $db;

    /**
     *  Constructor
     *
     * @param CentreonDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param type $values
     * @return type
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $items = array();

        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected timeperiods
        $query = "SELECT tp_id, tp_name "
            . "FROM timeperiod "
            . "WHERE tp_id IN (" . $explodedValues . ") "
            . "ORDER BY tp_name ";

        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['tp_id'],
                'text' => $row['tp_name']
            );
        }

        return $items;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function getTimperiodIdByName($name)
    {
        $query = "SELECT tp_id FROM timeperiod 
                WHERE tp_name = '" . $this->db->escape($name) . "'";

        $res = $this->db->query($query);

        if (!$res->numRows()) {
            return null;
        }
        $row = $res->fetchRow();

        return $row['tp_id'];
    }

    /**
     *
     * @param integer $tpId
     * @return string
     */
    public function getTimeperiodException($tpId)
    {
        $query = "SELECT `exception_id` FROM `timeperiod_exceptions`
                WHERE `timeperiod_id` = " . (int)$tpId;
        $res = $this->db->query($query);
        if (!$res->numRows()) {
            return null;
        }

        $row = $res->fetchRow();
        return $row['exception_id'];
    }

    /**
     * Insert in database a command
     *
     * @param array $parameters Values to insert (command_name and command_line is mandatory)
     * @throws Exception
     */
    public function insert($parameters)
    {
        $sQuery = "INSERT INTO `timeperiod` "
            . "(`tp_name`, `tp_alias`, `tp_sunday`, `tp_monday`, `tp_tuesday`, `tp_wednesday`, "
            . "`tp_thursday`, `tp_friday`, `tp_saturday`) "
            . "VALUES ('" . $parameters['name'] . "',"
            . "'" . $parameters['alias'] . "',"
            . "'" . $parameters['sunday'] . "',"
            . "'" . $parameters['monday'] . "',"
            . "'" . $parameters['tuesday'] . "',"
            . "'" . $parameters['wednesday'] . "',"
            . "'" . $parameters['thursday'] . "',"
            . "'" . $parameters['friday'] . "',"
            . "'" . $parameters['saturday'] . "')";

        $res = $this->db->query($sQuery);
        if (\PEAR::isError($res)) {
            throw new \Exception('Error while insert timeperiod ' . $parameters['name']);
        }
    }

    /**
     * Update in database a command
     *
     * @param int $command_id Id of command
     * @param array $command Values to set
     * @throws Exception
     */
    public function update($tp_id, $parameters)
    {

        $sQuery = "UPDATE `timeperiod` SET `tp_alias` = '" . $parameters['alias'] . "', "
            . "`tp_sunday` = '" . $parameters['sunday'] . "',"
            . "`tp_monday` = '" . $parameters['monday'] . "',"
            . "`tp_tuesday` = '" . $parameters['tuesday'] . "',"
            . "`tp_wednesday` = '" . $parameters['wednesday'] . "',"
            . "`tp_thursday` = '" . $parameters['thursday'] . "',"
            . "`tp_friday` = '" . $parameters['friday'] . "',"
            . "`tp_saturday` = '" . $parameters['saturday'] . "'"
            . " WHERE `tp_id` = " . $tp_id;

        $res = $this->db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while update timeperiod ' . $parameters['name']);
        }
    }

    /**
     * Insert in database a timeperiod exception
     *
     * @param integer $tpId
     * @param array $parameters Values to insert (days and timerange)
     * @throws Exception
     */
    public function setTimeperiodException($tpId, $parameters)
    {
        foreach ($parameters as $exception) {
            $sQuery = "INSERT INTO `timeperiod_exceptions` "
                . "(`timeperiod_id`, `days`, `timerange`) "
                . "VALUES (" . (int)$tpId . ","
                . "'" . $exception['days'] . "',"
                . "'" . $exception['timerange'] . "')";

            $res = $this->db->query($sQuery);

            if (\PEAR::isError($res)) {
                throw new \Exception('Error while insert timeperiod exception' . $tpId);
            }
        }
    }

    /**
     * Insert in database a timeperiod dependency
     *
     * @param integer $timeperiodId
     * @param integer $depId
     * @throws Exception
     */
    public function setTimeperiodDependency($timeperiodId, $depId)
    {
        $sQuery = "INSERT INTO `timeperiod_include_relations` "
            . "(`timeperiod_id`,`timeperiod_include_id`) "
            . "VALUES (" . (int)$timeperiodId . "," . (int)$depId . ")";

        $res = $this->db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while insert timeperiod dependency' . $timeperiodId);
        }
    }

    /**
     * Delete in database a timeperiod exception
     *
     * @param integer $tpId
     * @throws Exception
     */
    public function deleteTimeperiodException($tpId)
    {
        $sQuery = "DELETE FROM `timeperiod_exceptions` WHERE `timeperiod_id` = " . (int)$tpId;
        $res = $this->db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while delete timeperiod exception' . $tpId);
        }
    }

    /**
     * Delete in database a timeperiod include
     *
     * @param integer $tpId
     * @throws Exception
     */
    public function deleteTimeperiodInclude($tpId)
    {
        $sQuery = "DELETE FROM `timeperiod_include_relations` WHERE `timeperiod_id` = " . (int)$tpId;
        $res = $this->db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while delete timeperiod include' . $tpId);
        }
    }

    /**
     * Delete timeperiod in database
     *
     * @param string $tp_name timperiod name
     * @throws Exception
     */
    public function deleteTimeperiodByName($tp_name)
    {
        $sQuery = 'DELETE FROM timeperiod '
            . 'WHERE tp_name = "' . $this->db->escape($tp_name) . '"';

        $res = $this->db->query($sQuery);

        if (\PEAR::isError($res)) {
            throw new \Exception('Error while delete timperiod ' . $tp_name);
        }
    }

    /**
     * Returns array of Host linked to the timeperiod
     *
     * @return array
     */
    public function getLinkedHostsByName($timeperiodName, $register = false)
    {
        $registerClause = '';
        if ($register === '0' || $register === '1') {
            $registerClause = 'AND h.host_register = "' . $register . '" ';
        }

        $linkedHosts = array();
        $query = 'SELECT DISTINCT h.host_name '
            . 'FROM host h, timeperiod t '
            . 'WHERE (h.timeperiod_tp_id = t.tp_id OR h.timeperiod_tp_id2 = t.tp_id) '
            . $registerClause
            . 'AND t.tp_name = "' . $this->db->escape($timeperiodName) . '" ';

        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked hosts of ' . $timeperiodName);
        }

        while ($row = $result->fetchRow()) {
            $linkedHosts[] = $row['host_name'];
        }

        return $linkedHosts;
    }

    /**
     * Returns array of Service linked to the timeperiod
     *
     * @return array
     */
    public function getLinkedServicesByName($timeperiodName, $register = false)
    {
        $registerClause = '';
        if ($register === '0' || $register === '1') {
            $registerClause = 'AND s.service_register = "' . $register . '" ';
        }

        $linkedServices = array();
        $query = 'SELECT DISTINCT s.service_description '
            . 'FROM service s, timeperiod t '
            . 'WHERE (s.timeperiod_tp_id = t.tp_id OR s.timeperiod_tp_id2 = t.tp_id) '
            . $registerClause
            . 'AND t.tp_name = "' . $this->db->escape($timeperiodName) . '" ';

        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked services of ' . $timeperiodName);
        }

        while ($row = $result->fetchRow()) {
            $linkedServices[] = $row['service_description'];
        }

        return $linkedServices;
    }

    /**
     * Returns array of Contacts linked to the timeperiod
     *
     * @param string $timeperiodName
     * @return array
     * @throws Exception
     */
    public function getLinkedContactsByName($timeperiodName)
    {
        $linkedContacts = array();
        $query = 'SELECT DISTINCT c.contact_name '
            . 'FROM contact c, timeperiod t '
            . 'WHERE (c.timeperiod_tp_id = t.tp_id OR c.timeperiod_tp_id2 = t.tp_id) '
            . 'AND t.tp_name = "' . $this->db->escape($timeperiodName) . '" ';

        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked contacts of ' . $timeperiodName);
        }

        while ($row = $result->fetchRow()) {
            $linkedContacts[] = $row['contact_name'];
        }

        return $linkedContacts;
    }

    /**
     * Returns array of Timeperiods linked to the timeperiod
     *
     * @param string $timeperiodName
     * @return array
     * @throws Exception
     */
    public function getLinkedTimeperiodsByName($timeperiodName)
    {
        $linkedTimeperiods = array();

        $query = 'SELECT DISTINCT t1.tp_name '
            . 'FROM timeperiod t1, timeperiod_include_relations tir1, timeperiod t2 '
            . 'WHERE t1.tp_id = tir1.timeperiod_id '
            . 'AND t2.tp_id = tir1.timeperiod_include_id '
            . 'AND t2.tp_name = "' . $this->db->escape($timeperiodName) . '" '
            . 'UNION '
            . 'SELECT DISTINCT t3.tp_name '
            . 'FROM timeperiod t3, timeperiod_include_relations tir2, timeperiod t4 '
            . 'WHERE t3.tp_id = tir2.timeperiod_include_id '
            . 'AND t4.tp_id = tir2.timeperiod_id '
            . 'AND t4.tp_name = "' . $this->db->escape($timeperiodName) . '" ';

        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked timeperiods of ' . $timeperiodName);
        }

        while ($row = $result->fetchRow()) {
            $linkedTimeperiods[] = $row['tp_name'];
        }

        return $linkedTimeperiods;
    }
}
