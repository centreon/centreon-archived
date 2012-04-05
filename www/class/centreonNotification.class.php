<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

class CentreonContact
{
    protected $db;
    protected $svcTpl;
    protected $svcNotifType;
    protected $svcBreak;
    protected $hostNotifType;
    protected $hostBreak;
    const     HOST = 0;
    const     SVC = 1;
    const     HOST_ESC = 2;
    const     SVC_ESC = 3;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->svcTpl = array();
        $this->svcNotifType = array();
        $this->svcBreak = array(1 => false, 2 => false);
        $this->hostNotifType = array();
        $this->hostBreak = array(1 => false, 2 => false);
    }

    /**
     * Get list of contact
     *
     * @return void
     */
    public function getList()
    {
        $sql = "SELECT contact_id, contact_alias FROM contact ORDER BY contact_name";
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$row['contact_id']] = $row['contact_alias'];
        }
        return $tab;
    }

    /**
     * Checks if notification is enabled
     *
     * @param int $contactId
     * @return bool true if notification is enabled, false otherwise
     */
    protected function isNotificationEnabled($contactId)
    {
        $sql = "SELECT contact_enable_notifications FROM contact WHERE contact_id = " . $this->db->escape($contactId);
        $res = $this->db->query($sql);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            if ($row['contact_enable_notifications']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get contact groups
     *
     * @param int $contactId
     * @return array
     */
    public function getContactGroups($contactId)
    {
        $sql = "SELECT cg_id, cg_name
        		FROM contactgroup cg, contactgroup_contact_relation ccr
        		WHERE cg.cg_id = ccr.contactgroup_cg_id
        		AND ccr.contact_contact_id = " . $this->db->escape($contactId);
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$row['cg_id']] = $row['cg_name'];
        }
        return $tab;
    }

    /**
     * Get notifications
     *
     * @param int $notifType 0 for Hosts, 1 for Services, 2 for Host Escalations, 3 for Service Escalations
     * @param int $contactId
     * @return array
     */
    public function getNotifications($notifType, $contactId)
    {
        if (false === $this->isNotificationEnabled($contactId)) {
            return array();
        }
        $contactgroups = $this->getContactGroups($contactId);
        if ($notifType == self::HOST) {
            $resources = $this->getHostNotifications($contactId, $contactgroups);
        } elseif ($notifType == self::SVC) {
            $resources = $this->getServiceNotifications($contactId, $contactgroups);
        } elseif ($notifType == self::HOST_ESC || $notifType == self::SVC_ESC) {
            $resources = $this->getEscalationNotifications($notifType, $contactgroups);
        }
        return $resources;
    }

    /**
     * Get host escalatiosn
     *
     * @param array $escalations
     * @return array
     */
    protected function getHostEscalations($escalations)
    {
        $sql = "SELECT h.host_id, h.host_name
        		FROM escalation_host_relation ehr, host h
        		WHERE h.host_id = ehr.host_host_id
        		AND ehr.escalation_esc_id IN (".implode(array_keys($escalations)).")
        		UNION
        		SELECT h.host_id, h.host_name
        		FROM escalation_hostgroup_relation ehr, hostgroup_relation hgr, host h
        		WHERE ehr.hostgroup_hg_id = hgr.hostgroup_hg_id
        		AND hgr.host_host_id = h.host_id
        		AND ehr.escalation_esc_id IN (".implode(array_keys($escalations)).")";
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$row['host_id']] = $row['host_name'];
        }
        return $tab;
    }

    /**
     * Get service escalations
     *
     * @param array $escalations
     * @return array
     */
    protected function getServiceEscalations($escalations)
    {
        $sql = "SELECT h.host_id, h.host_name, s.service_id, s.service_description
        		FROM escalation_service_relation esr, host h, service s
        		WHERE h.host_id = esr.host_host_id
        		AND esr.service_service_id = s.service_id
        		AND esr.escalation_esc_id IN (".implode(array_keys($escalations)).")
        		UNION
        		SELECT h.host_id, h.host_name, s.service_id, s.service_description
        		FROM escalation_servicegroup_relation esr, servicegroup_relation sgr, host h, service s
        		WHERE esr.servicegroup_sg_id = sgr.servicegroup_sg_id
        		AND sgr.host_host_id = h.host_id
        		AND sgr.service_service_id = s.service_id
        		AND esr.escalation_esc_id IN (".implode(array_keys($escalations)).")";
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            if (!isset($tab[$row['host_id']])) {
                $tab[$row['host_id']] = array();
            }
            $tab[$row['host_id']][$row['service_id']]['host_name'] = $row['host_name'];
            $tab[$row['host_id']][$row['service_id']]['service_description'] = $row['service_description'];
        }
        return $tab;
    }

    /**
     * Get escalation notifications
     *
     * @param array $contactgroups
     * @return array
     */
    protected function getEscalationNotifications($notifType, $contactgroups)
    {
        if (!count($contactgroups)) {
            return array();
        }
        $sql = "SELECT ecr.escalation_esc_id, e.esc_name
        		FROM escalation_contactgroup_relation ecr, escalation e
        		WHERE e.esc_id = ecr.escalation_esc_id
        		AND ecr.contactgroup_cg_id IN (".implode(array_keys($contactgroups)).")";
        $res = $this->db->query($sql);
        $escTab = array();
        while ($row = $res->fetchRow()) {
            $escTab[$row['escalation_esc_id']] = $row['esc_name'];
        }
        if (!count($escTab)) {
            return array();
        }
        if ($notifType == self::HOST_ESC) {
            return $this->getHostEscalations($escTab);
        } else {
            return $this->getServiceEscalations($escTab);
        }
    }


    /**
     * Get Host Notifications
     *
     * @param int $contactId
     * @param array $contactgroups
     * @return array
     */
    protected function getHostNotifications($contactId, $contactgroups)
    {
        $sql = "SELECT host_id, host_name, host_register, 1 as notif_type
        		FROM contact_host_relation chr, host h
        		WHERE chr.contact_id = " . $this->db->escape($contactId) . "
        		AND chr.host_host_id = h.host_id ";
        if (count($contactgroups)) {
            $sql .= " UNION
        			  SELECT host_id, host_name, host_register, 2 as notif_type
        			  FROM contactgroup_host_relation chr, host h
        			  WHERE chr.contactgroup_cg_id IN (" . implode(',', array_keys($contactgroups)) . ")
        			  AND chr.host_host_id = h.host_id ";
        }
        $res = $this->db->query($sql);
        $hostTab = array();
        $templates = array();
        while ($row = $res->fetchRow()) {
            if ($row['host_register'] == 1) {
                $hostTab[$row['host_id']] = $row['host_name'];
            } else {
                $templates[$row['host_id']] = $row['host_name'];
                $this->hostNotifType[$row['host_id']] = $row['notif_type'];
            }
        }
        unset($res);

        if (count($hostTab)) {
            $sql2 = "SELECT host_id, host_name FROM host WHERE host_id NOT IN (".implode(',', array_keys($hostTab)).") AND host_register = '1'";
        } else {
            $sql2 = "SELECT host_id, host_name FROM host WHERE host_register = '1'";
        }
        $res2 = $this->db->query(trim($sql2));
        while ($row = $res2->fetchRow()) {
            $this->hostBreak = array(1 => false, 2 => false);
            if ($this->getHostTemplateNotifications($row['host_id'], $templates) === true) {
                $hostTab[$row['host_id']] = $row['host_name'];
            }
        }
        return $hostTab;
    }

    /**
     * Recursive method
     *
     * @param int $hostId
     * @param array $templates
     * @return bool
     */
    protected function getHostTemplateNotifications($hostId, $templates)
    {
        $sql = "SELECT htr.host_tpl_id, ctr.contact_id, ctr2.contactgroup_cg_id
        		FROM host_template_relation htr
        		LEFT JOIN contact_host_relation ctr ON htr.host_host_id = ctr.host_host_id
        		LEFT JOIN contactgroup_host_relation ctr2 ON htr.host_host_id = ctr2.host_host_id
        		WHERE htr.host_host_id = ".$this->db->escape($hostId)."
        		ORDER BY `order`";
        $res = $this->db->query($sql);
        while ($row = $res->fetchRow()) {
            if ($row['contact_id']) {
                $this->hostBreak[1] = true;
            }
            if ($row['contactgroup_cg_id']) {
                $this->hostBreak[2] = true;
            }
            if (isset($templates[$row['host_tpl_id']])) {
                if ($this->hostNotifType[$row['host_tpl_id']] == 1 && $this->hostBreak[1] == true) {
                    return false;
                }
                if ($this->hostNotifType[$row['host_tpl_id']] == 2 && $this->hostBreak[2] == true) {
                    return false;
                }
                return true;
            }
            return $this->getHostTemplateNotifications($row['host_tpl_id'], $templates);
        }
        return false;
    }

    /**
     * Get Service notifications
     *
     * @param int $contactId
     * @param array $contactgroups
     * @return array
     */
    protected function getServiceNotifications($contactId, $contactgroups)
    {
        $sql = "SELECT h.host_id, h.host_name, s.service_id, s.service_description, s.service_register, 1 as notif_type
        		FROM contact_service_relation csr, service s
        		LEFT JOIN host_service_relation hsr ON hsr.service_service_id = s.service_id
        		LEFT JOIN host h ON h.host_id = hsr.host_host_id
        		WHERE csr.contact_id = " . $this->db->escape($contactId) . "
        		AND csr.service_service_id = s.service_id
        		UNION
        		SELECT h.host_id, h.host_name, s.service_id, s.service_description, s.service_register, 1 as notif_type
        		FROM contact_service_relation csr, service s, host h, host_service_relation hsr, hostgroup_relation hgr
        		WHERE csr.contact_id = " . $this->db->escape($contactId) . "
        		AND csr.service_service_id = s.service_id
        		AND s.service_id = hsr.service_service_id
        		AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
        		AND hgr.host_host_id = h.host_id ";
        if (count($contactgroups)) {
            $sql .= " UNION
        			  SELECT h.host_id, h.host_name, s.service_id, s.service_description, s.service_register, 2 as notif_type
        			  FROM contactgroup_service_relation csr, service s
        			  LEFT JOIN host_service_relation hsr ON hsr.service_service_id = s.service_id
        			  LEFT JOIN host h ON h.host_id = hsr.host_host_id
        			  WHERE csr.contactgroup_cg_id IN (" . implode(',', array_keys($contactgroups)) . ")
        			  AND csr.service_service_id = s.service_id
        			  UNION
        			  SELECT h.host_id, h.host_name, s.service_id, s.service_description, s.service_register, 2 as notif_type
        			  FROM contactgroup_service_relation csr, service s, host h, host_service_relation hsr, hostgroup_relation hgr
        			  WHERE csr.contactgroup_cg_id IN (" . implode(',', array_keys($contactgroups)) . ")
        			  AND csr.service_service_id = s.service_id
        			  AND s.service_id = hsr.service_service_id
        			  AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
        			  AND hgr.host_host_id = h.host_id ";
        }
        $res = $this->db->query($sql);
        $svcTab = array();
        $templates = array();
        while ($row = $res->fetchRow()) {
            if ($row['service_register'] == 1) {
                if (!isset($svcTab[$row['host_id']])) {
                    $svcTab[$row['host_id']] = array();
                }
                $svcTab[$row['host_id']][$row['service_id']] = array();
                $svcTab[$row['host_id']][$row['service_id']]['host_name'] = $row['host_name'];
                $svcTab[$row['host_id']][$row['service_id']]['service_description'] = $row['service_description'];
            } else {
                $templates[$row['service_id']] = $row['service_description'];
                $this->svcNotifType[$row['service_id']] = $row['notif_type'];
            }
        }
        unset($res);

        if (count($svcTab)) {
            $tab = array();
            foreach ($svcTab as $tmp) {
                $tab = array_merge(array_keys($tmp), $tab);
            }
            $sql2 = "SELECT service_id, service_description
            		 FROM service
            		 WHERE service_id NOT IN (".implode(',', $tab).") AND service_register = '1'";
        } else {
            $sql2 = "SELECT service_id, service_description
            		 FROM service
            		 WHERE service_register = '1'";
        }
        $res2 = $this->db->query(trim($sql2));
        while ($row = $res2->fetchRow()) {
            $this->svcBreak = array(1 => false, 2 => false);
            $flag = false;
            if ($this->getServiceTemplateNotifications($row['service_id'], $templates) === true) {
                $sql3 = "SELECT h.host_id, h.host_name
                    		 FROM host h, host_service_relation hsr
                    		 WHERE h.host_id = hsr.host_host_id
                    		 AND hsr.service_service_id = " . $this->db->escape($row['service_id']) . "
                    		 UNION
                    		 SELECT h.host_id, h.host_name
                    		 FROM host h, host_service_relation hsr, hostgroup_relation hgr
                    		 WHERE h.host_id = hgr.host_host_id
                    		 AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
                    		 AND hsr.service_service_id = " . $this->db->escape($row['service_id']);
                $res3 = $this->db->query($sql3);
                while ($row3 = $res3->fetchRow()) {
                    if (!isset($svcTab[$row3['host_id']])) {
                        $svcTab[$row3['host_id']] = array();
                    }
                    $svcTab[$row3['host_id']][$row['service_id']] = array();
                    $svcTab[$row3['host_id']][$row['service_id']]['host_name'] = $row3['host_name'];
                    $svcTab[$row3['host_id']][$row['service_id']]['service_description'] = $row['service_description'];
                }
            }
        }
        return $svcTab;
    }

    /**
     * Recursive method
     *
     * @param int $serviceId
     * @param array $templates
     * @return bool
     */
    protected function getServiceTemplateNotifications($serviceId, $templates)
    {
        $tplId = 0;
        if (!isset($this->svcTpl[$serviceId])) {
            $sql = "SELECT s.service_template_model_stm_id, csr.contact_id, csr2.contactgroup_cg_id
        			FROM service s
        			LEFT JOIN contact_service_relation csr ON csr.service_service_id = s.service_id
        			LEFT JOIN contactgroup_service_relation csr2 ON csr2.service_service_id = s.service_id
        			WHERE service_id = ".$this->db->escape($serviceId);
            $res = $this->db->query($sql);
            $row = $res->fetchRow();
            $tplId = $row['service_template_model_stm_id'];
        } else {
            $tplId = $this->svcTpl[$serviceId];
        }
        if ($row['contact_id']) {
            $this->svcBreak[1] = true;
        }
        if ($row['contactgroup_cg_id']) {
            $this->svcBreak[2] = true;
        }
        if (isset($templates[$tplId]) && $templates[$tplId]) {
            if ($this->svcNotifType[$tplId] == 1 && $this->svcBreak[1] == true) {
                return false;
            }
            if ($this->svcNotifType[$tplId] == 2 && $this->svcBreak[2] == true) {
                return false;
            }
            return true;
        }
        if ($tplId) {
            return $this->getServiceTemplateNotifications($tplId, $templates);
        }
        return false;
    }
}
?>