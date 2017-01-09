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
 * Enter description here ...
 * @author jmathis
 *
 */
class CentreonMonitoring
{

    protected $poller;
    protected $DB;
    protected $objBroker;

    /**
     *
     * Enter description here ...
     */
    public function __construct($DB)
    {
        $this->DB = $DB;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $pollerId
     */
    public function setPoller($pollerId)
    {
        $this->poller = $pollerId;
    }

    /**
     *
     * Enter description here ...
     */
    public function getPoller()
    {
        return $this->poller;
    }

    /**
     *
     * Proxy function
     * @param unknown_type $hostList
     * @param unknown_type $objXMLBG
     * @param unknown_type $o
     * @param unknown_type $instance
     * @param unknown_type $hostgroups
     */
    public function getServiceStatusCount($host_name, $objXMLBG, $o, $status, $obj)
    {
            $rq = "SELECT count(distinct s.service_id) as count "
                . "FROM services s, hosts h " . (!$objXMLBG->is_admin ? ", centreon_acl " : "")
                . "WHERE s.state = '" . $status . "' "
                . "AND s.host_id = h.host_id "
                . "AND s.enabled = '1' "
                . "AND h.enabled = '1' "
                . "AND h.name = '" . $host_name . "' ";

            # Acknowledgement filter
        if ($o == "svcSum_ack_0") {
            $rq .= "AND s.acknowledged = 0 AND s.state != 0 ";
        } elseif ($o == "svcSum_ack_1") {
            $rq .= "AND s.acknowledged = 1 AND s.state != 0 ";
        }

        if (!$objXMLBG->is_admin) {
            $rq .=  "AND h.host_id = centreon_acl.host_id "
                . "AND s.service_id = centreon_acl.service_id "
                . "AND centreon_acl.group_id IN (" .  $obj->access->getAccessGroupsString() . ") ";
        }

            $DBRESULT = $objXMLBG->DBC->query($rq);

            $cpt = 0;
        if ($DBRESULT->numRows()) {
            $row = $DBRESULT->fetchRow();
            $cpt = $row['count'];
        }
            $DBRESULT->free();

            return $cpt;
    }

    /**
     *
     * Proxy function
     * @param unknown_type $hostList
     * @param unknown_type $objXMLBG
     * @param unknown_type $o
     * @param unknown_type $instance
     * @param unknown_type $hostgroups
     */
    public function getServiceStatus($hostList, $objXMLBG, $o, $instance, $hostgroups)
    {
        if ($hostList == "") {
            return array();
        }

        $rq = "SELECT h.name, s.description as service_name, s.state, s.service_id, "
            . " (case s.state when 0 then 3 when 2 then 0 when 3 then 2  when 3 then 2 else s.state END) as tri "
            . "FROM hosts h, services s ";

        if (!$objXMLBG->is_admin) {
            $rq .= ", centreon_acl ";
        }
            $rq .= "WHERE h.host_id = s.host_id "
                . "AND s.enabled = '1' "
                . "AND h.enabled = '1' "
                . "AND h.name NOT LIKE '_Module_%' ";

        if ($o == "svcgrid_pb" || $o == "svcOV_pb") {
            $rq .= "AND s.state != 0 ";
        } elseif ($o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
            $rq .= "AND s.acknowledged = 0 AND s.state != 0 ";
        } elseif ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
            $rq .= "AND s.acknowledged = 1 ";
        }

        $rq .= "AND h.name IN (" . $hostList . ") ";

        # Instance filter
        if ($instance != -1) {
            $rq .=  "AND h.instance_id = " . $instance . " ";
        }

        $grouplistStr = $objXMLBG->access->getAccessGroupsString();
        if (!$objXMLBG->is_admin) {
            $rq .= "AND h.host_id = centreon_acl.host_id AND s.service_id = centreon_acl.service_id "
                . $objXMLBG->access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr)
                . " ";
        }

        $rq .= " order by tri asc";
        
        $tab = array();
        $DBRESULT = $objXMLBG->DBC->query($rq);
        while ($svc = $DBRESULT->fetchRow()) {
            if (!isset($tab[$svc["name"]])) {
                $tab[$svc["name"]] = array();
            }
            $tab[$svc["name"]][$svc["service_name"]] = $svc["state"];
        }
        $DBRESULT->free();

        return $tab;
    }
}
