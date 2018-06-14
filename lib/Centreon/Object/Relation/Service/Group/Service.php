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

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Service_Group_Service extends Centreon_Object_Relation
{
    protected $relationTable = "servicegroup_relation";
    protected $firstKey = "servicegroup_sg_id";
    protected $secondKey = "service_service_id";

    /**
     * Used for inserting relation into database
     * @param int $fkey
     * @param null $key
     */
    public function insert($fkey, $key = null)
    {
        $hostId = $key['hostId'];
        $serviceId = $key['serviceId'];
        $sql = "INSERT INTO $this->relationTable ($this->firstKey, host_host_id, $this->secondKey) VALUES (?, ?, ?)";
        $this->db->query($sql, array($fkey, $hostId, $serviceId));
    }

    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $hostId
     * @param int $serviceId
     * @return void
     */
    public function delete($fkey, $hostId = null, $serviceId = null)
    {
        if (isset($fkey) && isset($hostId) && isset($serviceId)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ? AND host_host_id = ? AND $this->secondKey = ?";
            $args = array($fkey, $hostId, $serviceId);
        } elseif (isset($hostId) && isset($serviceId)) {
            $sql = "DELETE FROM $this->relationTable WHERE host_host_id = ? AND $this->secondKey = ?";
            $args = array($hostId, $serviceId);
        } else {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ?";
            $args = array($fkey);
        }
        $this->db->query($sql, $args);
    }

    /**
     * Get service group id from host id, service id
     *
     * @param int $hostId
     * @param int $serviceId
     * @return array
     */
    public function getServicegroupIdFromHostIdServiceId($hostId, $serviceId)
    {
        $sql = "SELECT $this->firstKey FROM $this->relationTable WHERE host_host_id = ? AND $this->secondKey = ?";
        $result = $this->getResult($sql, array($hostId, $serviceId));
        $tab = array();
        foreach ($result as $rez) {
            $tab[] = $rez[$this->firstKey];
        }
        return $tab;
    }

    /**
     * Get Host id service id from service group id
     *
     * @param int $servicegroupId
     * @return array multidimentional array with host_id and service_id indexes
     */
    public function getHostIdServiceIdFromServicegroupId($servicegroupId)
    {
        $sql = "SELECT host_host_id, $this->secondKey FROM $this->relationTable WHERE $this->firstKey = ?";
        $result = $this->getResult($sql, array($servicegroupId));
        $tab = array();
        $i = 0;
        foreach ($result as $rez) {
            $tab[$i]['host_id'] = $rez['host_host_id'];
            $tab[$i]['service_id'] = $rez[$this->secondKey];
            $i++;
        }
        return $tab;
    }

    /**
     * This call will directly throw an exception
     *
     * @param string $name
     * @param array $arg
     * @throws Exception
     */
    public function __call($name, $arg = array())
    {
        throw new Exception('Unknown method');
    }
}
