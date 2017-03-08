<?php
/**
 * Copyright 2005-2014 Centreon
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
 */

/**
 * Class for Access Control List management
 *
 */
class CentreonACL
{

    private $userID; /* ID of the user */
    private $parentTemplates = null;
    public $admin; /* Flag that tells us if the user is admin or not */
    private $accessGroups = array(); /* Access groups the user belongs to */
    private $resourceGroups = array(); /* Resource groups the user belongs to */
    public $hostGroups = array(); /* Hostgroups the user can see */
    protected $pollers = array(); /* Pollers the user can see */
    private $hostGroupsAlias = array(); /* Hostgroups by alias the user can see */
    private $serviceGroups = array(); /* Servicegroups the user can see */
    private $serviceGroupsAlias = array(); /* Servicegroups by alias the user can see */
    private $serviceCategories = array(); /* Service categories the user can see */
    private $hostCategories = array();
    private $actions = array(); /* Actions the user can do */
    private $hostGroupsFilter = array();
    private $serviceGroupsFilter = array();
    private $serviceCategoriesFilter = array();
    public $topology = array();
    public $topologyStr = "";
    private $metaServices = array();
    private $metaServiceStr = "";
    private $tempTableArray = array();

    /**
     * Constructor
     *
     * @param int $user_id The user identifiant
     * @param bool $is_admin If the user is administrator
     */
    public function __construct($userId, $isAdmin = null)
    {
        $this->userID = $userId;

        if (!isset($isAdmin)) {
            $localPearDB = new CentreonDB();
            $query = "SELECT contact_admin "
                . "FROM `contact` "
                . "WHERE contact_id = '" . CentreonDB::escape($userId) . "' "
                . "LIMIT 1 ";
            $RESULT = $localPearDB->query($query);
            $row = $RESULT->fetchRow();
            $this->admin = $row['contact_admin'];
        } else {
            $this->admin = $isAdmin;
        }

        if (!$this->admin) {
            $this->setAccessGroups();
            $this->setResourceGroups();
            $this->setHostGroups();
            $this->setPollers();
            $this->setServiceGroups();
            $this->setServiceCategories();
            $this->setHostCategories();
            $this->setMetaServices();
            $this->setActions();
        }

        $this->setTopology();
        $this->getACLStr();
    }

    /**
     * Function that will reset ACL
     */
    private function resetACL()
    {
        $this->parentTemplates = null;
        $this->accessGroups = array();
        $this->resourceGroups = array();
        $this->hostGroups = array();
        $this->serviceGroups = array();
        $this->serviceCategories = array();
        $this->actions = array();
        $this->topology = array();
        $this->pollers = array();
        $this->setAccessGroups();
        $this->setResourceGroups();
        $this->setHostGroups();
        $this->setPollers();
        $this->setServiceGroups();
        $this->setServiceCategories();
        $this->setHostCategories();
        $this->setMetaServices();
        $this->setTopology();
        $this->getACLStr();
        $this->setActions();
    }

    /**
     * Function that will check whether or not the user needs to rebuild his ACL
     */
    private function checkUpdateACL()
    {
        global $pearDB;

        if (is_null($this->parentTemplates)) {
            $this->loadParentTemplates();
        }

        if (!$this->admin) {
            $query = "SELECT update_acl "
                . "FROM session "
                . "WHERE update_acl = '1' "
                . "AND user_id IN (" . join(', ', $this->parentTemplates) . ") ";
            $DBRES = $pearDB->query($query);
            if ($DBRES->numRows()) {
                $pearDB->query("UPDATE session SET update_acl = '0'
                    WHERE user_id IN (" . join(', ', $this->parentTemplates) . ")");
                $this->resetACL();
            }
        }
    }

    /*
     * Setter functions
     */

    /**
     * Access groups Setter
     */
    private function setAccessGroups()
    {
        global $pearDB;

        if (is_null($this->parentTemplates)) {
            $this->loadParentTemplates();
        }

        if (count($this->parentTemplates) != 0) {
            $query = "SELECT acl.acl_group_id, acl.acl_group_name "
                . "FROM acl_groups acl, acl_group_contacts_relations agcr "
                . "WHERE acl.acl_group_id = agcr.acl_group_id "
                . "AND acl.acl_group_activate = '1' "
                . "AND agcr.contact_contact_id IN (" . join(', ', $this->parentTemplates) . ") "
                . "ORDER BY acl.acl_group_name ASC";
            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $this->accessGroups[$row['acl_group_id']] = $row['acl_group_name'];
            }
            $DBRESULT->free();

            $query = "SELECT acl.acl_group_id, acl.acl_group_name "
                . "FROM acl_groups acl, acl_group_contactgroups_relations agcgr, contactgroup_contact_relation cgcr "
                . "WHERE acl.acl_group_id = agcgr.acl_group_id "
                . "AND cgcr.contactgroup_cg_id = agcgr.cg_cg_id "
                . "AND acl.acl_group_activate = '1' "
                . "AND cgcr.contact_contact_id IN (" . join(', ', $this->parentTemplates) . ") "
                . "ORDER BY acl.acl_group_name ASC";

            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $this->accessGroups[$row['acl_group_id']] = $row['acl_group_name'];
            }
            $DBRESULT->free();
        }
    }

    /**
     * Resource groups Setter
     */
    private function setResourceGroups()
    {
        global $pearDB;

        $query = "SELECT acl.acl_res_id, acl.acl_res_name "
            . "FROM acl_resources acl, acl_res_group_relations argr "
            . "WHERE acl.acl_res_id = argr.acl_res_id "
            . "AND acl.acl_res_activate = '1' "
            . "AND argr.acl_group_id IN (" . $this->getAccessGroupsString() . ") "
            . "ORDER BY acl.acl_res_name ASC";
        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $this->resourceGroups[$row['acl_res_id']] = $row['acl_res_name'];
        }
        $DBRESULT->free();
    }

    /**
     * Access groups Setter
     */
    private function setHostGroups()
    {
        global $pearDB;

        $query = "SELECT hg.hg_id, hg.hg_name, hg.hg_alias, arhr.acl_res_id "
            . "FROM hostgroup hg, acl_resources_hg_relations arhr "
            . "WHERE hg.hg_id = arhr.hg_hg_id "
            . "AND hg.hg_activate = '1' "
            . "AND arhr.acl_res_id IN (" . $this->getResourceGroupsString() . ") "
            . "ORDER BY hg.hg_name ASC ";
        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $this->hostGroups[$row['hg_id']] = $row['hg_name'];
            $this->hostGroupsAlias[$row['hg_id']] = $row['hg_alias'];
            $this->hostGroupsFilter[$row['acl_res_id']][$row['hg_id']] = $row['hg_id'];
        }
        $DBRESULT->free();
    }

    /**
     * Poller Setter
     */
    private function setPollers()
    {
        global $pearDB;

        $query = "SELECT ns.id, ns.name, arpr.acl_res_id "
            . "FROM nagios_server ns, acl_resources_poller_relations arpr "
            . "WHERE ns.id = arpr.poller_id "
            . "AND ns.ns_activate = '1' "
            . "AND arpr.acl_res_id IN (" . $this->getResourceGroupsString() . ") "
            . "ORDER BY ns.name ASC ";
        $DBRESULT = $pearDB->query($query);
        if ($DBRESULT->numRows()) {
            while ($row = $DBRESULT->fetchRow()) {
                $this->pollers[$row['id']] = $row['name'];
            }
        } else {
            $query = "SELECT ns.id, ns.name "
                . "FROM nagios_server ns "
                . "WHERE ns.ns_activate = '1' "
                . "ORDER BY ns.name ASC ";
            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $this->pollers[$row['id']] = $row['name'];
            }
        }
        $DBRESULT->free();
    }

    /**
     * Service groups Setter
     */
    private function setServiceGroups()
    {
        global $pearDB;

        $query = "SELECT sg.sg_id, sg.sg_name, sg.sg_alias, arsr.acl_res_id "
            . "FROM servicegroup sg, acl_resources_sg_relations arsr "
            . "WHERE sg.sg_id = arsr.sg_id "
            . "AND sg.sg_activate = '1' "
            . "AND arsr.acl_res_id IN (" . $this->getResourceGroupsString() . ") "
            . "ORDER BY sg.sg_name ASC";
        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $this->serviceGroups[$row['sg_id']] = $row['sg_name'];
            $this->serviceGroupsAlias[$row['sg_id']] = $row['sg_alias'];
            $this->serviceGroupsFilter[$row['acl_res_id']][$row['sg_id']] = $row['sg_id'];
        }
        $DBRESULT->free();
    }

    /**
     * Service categories Setter
     */
    private function setServiceCategories()
    {
        global $pearDB;

        $query = "SELECT sc.sc_id, sc.sc_name, arsr.acl_res_id "
            . "FROM service_categories sc, acl_resources_sc_relations arsr "
            . "WHERE sc.sc_id = arsr.sc_id "
            . "AND sc.sc_activate = '1' "
            . "AND arsr.acl_res_id IN (" . $this->getResourceGroupsString() . ") "
            . "ORDER BY sc.sc_name ASC ";

        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $this->serviceCategories[$row['sc_id']] = $row['sc_name'];
            $this->serviceCategoriesFilter[$row['acl_res_id']][$row['sc_id']] = $row['sc_id'];
        }
        $DBRESULT->free();
    }

    /**
     * Host categories setter
     */
    private function setHostCategories()
    {
        global $pearDB;

        $query = "SELECT hc.hc_id, hc.hc_name, arhr.acl_res_id "
            . "FROM hostcategories hc, acl_resources_hc_relations arhr "
            . "WHERE hc.hc_id = arhr.hc_id "
            . "AND hc.hc_activate = '1' "
            . "AND arhr.acl_res_id IN (" . $this->getResourceGroupsString() . ") "
            . "ORDER BY hc.hc_name ASC ";

        $res = $pearDB->query($query);
        while ($row = $res->fetchRow()) {
            $this->hostCategories[$row['hc_id']] = $row['hc_name'];
        }
    }

    /**
     * Access meta Setter
     */
    private function setMetaServices()
    {
        global $pearDB;

        $query = "SELECT ms.meta_id, ms.meta_name, arsr.acl_res_id " .
            "FROM meta_service ms, acl_resources_meta_relations arsr " .
            "WHERE ms.meta_id = arsr.meta_id " .
            "AND arsr.acl_res_id IN (" . $this->getResourceGroupsString() . ") " .
            "ORDER BY ms.meta_name ASC";
        $DBRESULT = $pearDB->query($query);
        $this->metaServiceStr = "";
        while ($row = $DBRESULT->fetchRow()) {
            $this->metaServices[$row['meta_id']] = $row['meta_name'];
            if ($this->metaServiceStr != "") {
                $this->metaServiceStr .= ",";
            }
            $this->metaServiceStr .= "'" . $row['meta_id'] . "'";
        }
        if (!$this->metaServiceStr) {
            $this->metaServiceStr = "''";
        }
        $DBRESULT->free();
    }

    /**
     * Actions Setter
     */
    private function setActions()
    {
        global $pearDB;

        $query = "SELECT ar.acl_action_name "
            . "FROM acl_group_actions_relations agar, acl_actions a, acl_actions_rules ar "
            . "WHERE a.acl_action_id = agar.acl_action_id "
            . "AND agar.acl_action_id = ar.acl_action_rule_id "
            . "AND a.acl_action_activate = '1' "
            . "AND agar.acl_group_id IN (" . $this->getAccessGroupsString() . ") "
            . "ORDER BY ar.acl_action_name ASC ";
        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $this->actions[$row['acl_action_name']] = $row['acl_action_name'];
        }
        $DBRESULT->free();
    }

    /**
     *  Topology setter
     */
    private function setTopology()
    {
        global $pearDB;

        if ($this->admin) {
            $query = "SELECT topology_page "
                . "FROM topology "
                . "WHERE topology_page IS NOT NULL ";
            $DBRES = $pearDB->query($query);
            while ($row = $DBRES->fetchRow()) {
                $this->topology[$row['topology_page']] = 1;
            }
            $DBRES->free();
        } else {
            if (count($this->accessGroups) > 0) {
                # If user is in an access group
                $str_topo = "";
                $query = "SELECT DISTINCT acl_group_topology_relations.acl_topology_id "
                    . "FROM acl_group_topology_relations, acl_topology, acl_topology_relations "
                    . "WHERE acl_topology_relations.acl_topo_id = acl_topology.acl_topo_id "
                    . "AND acl_topology.acl_topo_activate = '1' "
                    . "AND acl_group_topology_relations.acl_group_id IN (" . $this->getAccessGroupsString() . ") ";
                $DBRESULT = $pearDB->query($query);

                if (!$DBRESULT->numRows()) {
                    $this->topology[1] = 1;
                    $this->topology[101] = 1;
                    $this->topology[10101] = 1;
                } else {
                    $topology = array();
                    $tmp_topo_page = array();
                    while ($topo_group = $DBRESULT->fetchRow()) {
                        $query2 = "SELECT topology_topology_id, acl_topology_relations.access_right "
                            . "FROM acl_topology_relations, acl_topology "
                            . "WHERE acl_topology.acl_topo_activate = '1' "
                            . "AND acl_topology.acl_topo_id = acl_topology_relations.acl_topo_id "
                            . "AND acl_topology_relations.acl_topo_id = '" . $topo_group["acl_topology_id"] . "' ";
                        $DBRESULT2 = $pearDB->query($query2);
                        while ($topo_page = $DBRESULT2->fetchRow()) {
                            $topology[] = $topo_page["topology_topology_id"];
                            if (!isset($tmp_topo_page[$topo_page['topology_topology_id']])) {
                                $tmp_topo_page[$topo_page["topology_topology_id"]] = $topo_page["access_right"];
                            } else {
                                if ($topo_page["access_right"] == 1) { // Read/Write
                                    $tmp_topo_page[$topo_page["topology_topology_id"]] = $topo_page["access_right"];
                                } else {
                                    if ($topo_page["access_right"] == 2 &&
                                        $tmp_topo_page[$topo_page["topology_topology_id"]] == 0
                                    ) {
                                        $tmp_topo_page[$topo_page["topology_topology_id"]] = 2;
                                    }
                                }
                            }
                        }
                        $DBRESULT2->free();
                    }
                    $DBRESULT->free();
                    $ACL = "";
                    if (count($topology)) {
                        $ACL = "AND topology_id IN (" . implode(', ', $topology) . ") ";
                    }

                    $query3 = "SELECT topology_page, topology_id "
                        . "FROM topology FORCE INDEX (`PRIMARY`) "
                        . "WHERE topology_page IS NOT NULL "
                        . $ACL;
                    $DBRESULT3 = $pearDB->query($query3);
                    while ($topo_page = $DBRESULT3->fetchRow()) {
                        $this->topology[$topo_page["topology_page"]] = $tmp_topo_page[$topo_page["topology_id"]];
                    }
                    $DBRESULT3->free();
                }
            } else {
                # If user isn't in an access group
                $this->topology[1] = 1;
                $this->topology[101] = 1;
                $this->topology[10101] = 1;
            }
        }
    }

    /**
     * Getter functions
     */

    /**
     * Get ACL by string
     */
    public function getACLStr()
    {
        foreach ($this->topology as $key => $tmp) {
            if (isset($key) && $key) {
                if ($this->topologyStr != "") {
                    $this->topologyStr .= ", ";
                }
                $this->topologyStr .= "'" . $key . "'";
            }
        }
        unset($key);
        if (!$this->topologyStr) {
            $this->topologyStr = "\'\'";
        }
    }

    /**
     * Access groups Getter
     */
    public function getAccessGroups()
    {
        return ($this->accessGroups);
    }

    /**
     *  Access groups string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getAccessGroupsString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $accessGroups = "";
        foreach ($this->accessGroups as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if ($escape === true) {
                        $accessGroups .= "'" . CentreonDB::escape($value) . "',";
                    } else {
                        $accessGroups .= "'" . $value . "',";
                    }
                    break;
                case "ID":
                    $accessGroups .= $key . ",";
                    break;
                default:
                    $accessGroups .= "'" . $key . "',";
                    break;
            }
        }

        $result = "'0'";
        if (strlen($accessGroups)) {
            $result = trim($accessGroups, ',');
        }

        return $result;
    }

    /**
     * Resource groups Getter
     */
    public function getResourceGroups()
    {
        return $this->resourceGroups;
    }

    /**
     * Resource groups string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getResourceGroupsString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $resourceGroups = "";
        foreach ($this->resourceGroups as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if ($escape === true) {
                        $resourceGroups .= "'" . CentreonDB::escape($value) . "',";
                    } else {
                        $resourceGroups .= "'" . $value . "',";
                    }
                    break;
                case "ID":
                    $resourceGroups .= $key . ",";
                    break;
                default:
                    $resourceGroups .= "'" . $key . "',";
                    break;
            }
        }

        $result = "''";
        if (strlen($resourceGroups)) {
            $result = trim($resourceGroups, ',');
        }

        return $result;
    }

    /**
     * Hostgroups Getter
     */
    public function getHostGroups($flag = null)
    {
        $this->checkUpdateACL();

        if (isset($flag) && strtoupper($flag) == "ALIAS") {
            return $this->hostGroupsAlias;
        }
        return $this->hostGroups;
    }

    /**
     * Poller Getter
     */
    public function getPollers()
    {
        return $this->pollers;
    }

    /**
     * Hostgroups string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getHostGroupsString($flag = null)
    {
        $flag = strtoupper($flag);

        $hostgroups = "";
        foreach ($this->hostGroups as $key => $value) {
            switch ($flag) {
                case "NAME":
                    $hostgroups .= "'" . $value . "',";
                    break;
                case "ALIAS":
                    $hostgroups .= "'" . addslashes($this->hostGroupsAlias[$key]) . "',";
                    break;
                case "ID":
                    $hostgroups .= $key . ",";
                    break;
                default:
                    $hostgroups .= "'" . $key . "',";
                    break;
            }
        }

        $result = "''";
        if (strlen($hostgroups)) {
            $result = trim($hostgroups, ',');
        }

        return $result;
    }

    /**
     * Poller string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getPollerString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $pollers = "";
        $flagFirst = true;
        foreach ($this->pollers as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if (!$flagFirst) {
                        $pollers .= ",";
                    }
                    $flagFirst = false;
                    if ($escape === true) {
                        $pollers .= "'" . CentreonDB::escape($value) . "'";
                    } else {
                        $pollers .= "'" . $value . "'";
                    }
                    break;
                case "ID":
                    if (!$flagFirst) {
                        $pollers .= ",";
                    }
                    $flagFirst = false;
                    $pollers .= $key;
                    break;
                default:
                    if (!$flagFirst) {
                        $pollers .= ",";
                    }
                    $flagFirst = false;
                    $pollers .= "'" . $key . "'";
                    break;
            }
        }
        return $pollers;
    }

    /**
     * Service groups Getter
     */
    public function getServiceGroups()
    {
        return $this->serviceGroups;
    }

    /**
     * Service groups string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getServiceGroupsString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $servicegroups = "";
        foreach ($this->serviceGroups as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if ($escape === true) {
                        $servicegroups .= "'" . CentreonDB::escape($value) . "',";
                    } else {
                        $servicegroups .= "'" . $value . "',";
                    }
                    break;
                case "ALIAS":
                    $servicegroups .= "'" . $this->serviceGroupsAlias[$key] . "',";
                    break;
                case "ID":
                    $servicegroups .= $key . ",";
                    break;
                default:
                    $servicegroups .= "'" . $key . "',";
                    break;
            }
        }

        $result = "''";
        if (strlen($servicegroups)) {
            $result = trim($servicegroups, ',');
        }

        return $result;
    }

    /**
     * Service categories Getter
     */
    public function getServiceCategories()
    {
        return $this->serviceCategories;
    }

    /**
     * Get HostCategories
     */
    public function getHostCategories()
    {
        return $this->hostCategories;
    }

    /**
     * Service categories string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getServiceCategoriesString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $serviceCategories = "";
        foreach ($this->serviceCategories as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if ($escape === true) {
                        $serviceCategories .= "'" . CentreonDB::escape($value) . "',";
                    } else {
                        $serviceCategories .= "'" . $value . "',";
                    }
                    break;
                case "ID":
                    $serviceCategories .= $key . ",";
                    break;
                default:
                    $serviceCategories .= "'" . $key . "',";
                    break;
            }
        }

        $result = "''";
        if (strlen($serviceCategories)) {
            $result = trim($serviceCategories, ',');
        }

        return $result;
    }

    /**
     * Get HostCategories String
     *
     * @param mixed $flag
     * @param mixed $escape
     * @return string
     */
    public function getHostCategoriesString($flag = null, $escape = true)
    {
        $flag = strtoupper($flag);

        $hostCategories = "";
        foreach ($this->hostCategories as $key => $value) {
            switch ($flag) {
                case "NAME":
                    if ($escape === true) {
                        $hostCategories .= "'" . CentreonDB::escape($value) . "',";
                    } else {
                        $hostCategories .= "'" . $value . "',";
                    }
                    break;
                case "ID":
                    $hostCategories .= $key . ",";
                    break;
                default:
                    $hostCategories .= "'" . $key . "',";
                    break;
            }
        }

        $result = "''";
        if (strlen($hostCategories)) {
            $result = trim($hostCategories, ',');
        }

        return $result;
    }


    public function checkHost($hostId)
    {
        $pearDBO = new CentreonDB("centstorage");
        $hostArray = $this->getHostsArray("ID", $pearDBO);
        if (in_array($hostId, $hostArray)) {
            return true;
        }
        return false;
    }

    public function checkService($serviceId)
    {
        $pearDBO = new CentreonDB("centstorage");
        $serviceArray = $this->getServicesArray("ID", $pearDBO);
        if (in_array($serviceId, $serviceArray)) {
            return true;
        }
        return false;
    }


    /**
     * Hosts array Getter / same as getHostsString function
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getHostsArray($flag = null, $pearDBndo = null, $escape = true)
    {
        $this->checkUpdateACL();

        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }

        $flag = strtoupper($flag);
        switch ($flag) {
            case "NAME":
                $query = "SELECT DISTINCT h.host_id, h.name "
                    . "FROM centreon_acl ca, hosts h "
                    . "WHERE ca.host_id = h.host_id "
                    . "AND group_id IN (" . implode(',', $groupIds) . ") "
                    . "GROUP BY h.name, h.host_id "
                    . "ORDER BY h.name ASC ";
                $fieldName = 'name';
                break;
            default:
                $query = "SELECT DISTINCT host_id "
                    . "FROM centreon_acl "
                    . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'host_id';
                break;
        }

        $hosts = array();
        $DBRES = $pearDBndo->query($query);
        while ($row = $DBRES->fetchRow()) {
            if ($escape === true) {
                $hosts[] = CentreonDB::escape($row[$fieldName]);
            } else {
                $hosts[] = $row[$fieldName];
            }
        }

        return $hosts;
    }

    private static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function fillTemporaryTable($tmpName, $db, $rows, $fields)
    {
        $queryInsert = "INSERT INTO " . $tmpName . ' (';
        $queryValues = "";
        foreach ($fields as $field) {
            $queryInsert .= $field['key'] . ',';
            $queryValues .= '?,';
        }
        $queryInsert = trim($queryInsert, ',');
        $queryValues = trim($queryValues, ',');
        $queryInsert .= ') VALUES (' . $queryValues . ');';

        $db->autoCommit(false);
        $stmt = $db->prepare($queryInsert);
        $arrayValues = array();
        foreach ($rows as $row) {
            $arrayValue = array();
            foreach ($fields as $field) {
                $arrayValue[] = $row[$field['key']];
            }
            $arrayValues[] = $arrayValue;
        }
        $db->executeMultiple($stmt, $arrayValues);
        $db->commit();
        $db->autoCommit(true);
    }

    private function getRowFields($db, $rows, $originTable = 'centreon_acl')
    {
        if (empty($rows)) {
            return array();
        }

        $row = $rows[0];
        $fieldsArray = array();

        foreach ($row as $fieldKey => $field) {
            $fieldDef = $this->getField($originTable, $fieldKey, $db);
            $options = ($fieldDef['Null'] == 'NO' ? ' Not Null ' : ' Null ')
                . ($fieldDef['Key'] == 'PRI' ? ' PRIMARY KEY ' : ' ');
            $fieldsArray[] = array('key' => $fieldKey, 'type' => $fieldDef['Type'], 'options' => $options);
        }
        return $fieldsArray;
    }


    private function createTemporaryTable($name, $db, $rows, $originTable = 'centreon_acl', $fields = array())
    {
        $tempTableName = 'tmp_' . $name . '_' . self::generateRandomString(5);
        if (empty($fields)) {
            $fields = $this->getRowFields($db, $rows, $originTable);
        }
        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS  " . $tempTableName . " (";
        foreach ($fields as $field) {
            $query .= $field['key'] . ' ' . $field['type'] . ' ' . $field['options'] . ',';
        }
        $query = trim($query, ',') . ');';
        $db->query($query);
        $this->tempTableArray[$name] = $tempTableName;
        $this->fillTemporaryTable($tempTableName, $db, $rows, $fields);
        return $tempTableName;
    }

    private function getField($table, $field, $db)
    {
        $query = "SHOW COLUMNS FROM `$table` WHERE Field = '$field'";
        $DBRES = $db->query($query);
        $row = $DBRES->fetchRow();
        return $row;
    }

    public function getACLTemporaryTable(
        $tmpTableName,
        $db,
        $rows,
        $originTable = 'centreon_acl',
        $force = false,
        $fields = array()
    ) {
        if (!empty($this->tempTableArray[$tmpTableName]) && !$force) {
            return $this->tempTableArray[$tmpTableName];
        }
        if ($force) {
            $this->destroyTemporaryTable($tmpTableName);
        }
        $this->createTemporaryTable($tmpTableName, $db, $rows, $originTable, $fields);
        return $this->tempTableArray[$tmpTableName];
    }

    public function destroyTemporaryTable($db, $name = false)
    {
        if (!$name) {
            foreach ($this->tempTableArray as $tmpTable) {
                $query = 'DROP TEMPORARY TABLE IF EXISTS ' . $tmpTable;
                $db->query($query);
            }
        } else {
            $query = 'DROP TEMPORARY TABLE IF EXISTS ' . $this->tempTableArray[$name];
            $db->query($query);
        }
    }

    public function getACLHostsTemporaryTableJoin($db, $fieldToJoin, $force = false)
    {
        $this->checkUpdateACL();
        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }
        $query = "SELECT DISTINCT host_id "
            . "FROM centreon_acl "
            . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
        $DBRES = $db->query($query);
        $rows = array();
        while ($row = $DBRES->fetchRow()) {
            $rows[] = $row;
        }
        $tableName = $this->getACLTemporaryTable('hosts', $db, $rows, 'centreon_acl', $force);
        $join = ' INNER JOIN ' . $tableName . ' ON ' . $tableName . '.host_id = ' . $fieldToJoin . ' ';
        return $join;
    }

    public function getACLServicesTemporaryTableJoin($db, $fieldToJoin, $force = false)
    {
        $this->checkUpdateACL();
        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return false;
        }
        $query = "SELECT DISTINCT service_id "
            . "FROM centreon_acl "
            . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
        $DBRES = $db->query($query);
        $rows = array();
        while ($row = $DBRES->fetchRow()) {
            $rows[] = $row;
        }
        $tableName = $this->getACLTemporaryTable('services', $db, $rows, 'centreon_acl', $force);
        $join = ' INNER JOIN ' . $tableName . ' ON ' . $tableName . '.service_id = ' . $fieldToJoin . ' ';
        return $join;
    }

    public function getACLHostsTableJoin($db, $fieldToJoin, $force = false)
    {
        $this->checkUpdateACL();
        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "";
        }
        $tempTableName = 'centreon_acl_' . self::generateRandomString(5);
        $join = ' INNER JOIN centreon_acl ' . $tempTableName . ' ON ' . $tempTableName . '.host_id = ' . $fieldToJoin
            . ' AND ' . $tempTableName . '.group_id IN (' . implode(",", $groupIds) . ') ';
        return $join;
    }

    public function getACLServicesTableJoin($db, $fieldToJoin, $force = false)
    {
        $this->checkUpdateACL();
        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "";
        }
        $tempTableName = 'centreon_acl_' . self::generateRandomString(5);
        $join = ' INNER JOIN centreon_acl ' . $tempTableName . ' ON ' . $tempTableName . '.service_id = ' . $fieldToJoin
            . ' AND ' . $tempTableName . '.group_id IN (' . implode(",", $groupIds) . ') ';
        return $join;
    }


    /**
     * Hosts string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getHostsString($flag = null, $pearDBndo = null, $escape = true)
    {
        $this->checkUpdateACL();

        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }

        $flag = strtoupper($flag);
        switch ($flag) {
            case "NAME":
                $query = "SELECT DISTINCT h.host_id, h.name "
                    . "FROM centreon_acl ca, hosts h "
                    . "WHERE ca.host_id = h.host_id "
                    . "AND group_id IN (" . implode(',', $groupIds) . ") "
                    . "GROUP BY h.name, h.host_id "
                    . "ORDER BY h.name ASC ";
                $fieldName = 'name';
                break;
            default:
                $query = "SELECT DISTINCT host_id "
                    . "FROM centreon_acl "
                    . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'host_id';
                break;
        }

        $hosts = "";
        $DBRES = $pearDBndo->query($query);
        while ($row = $DBRES->fetchRow()) {
            if ($escape === true) {
                $hosts .= "'" . CentreonDB::escape($row[$fieldName]) . "',";
            } else {
                if ($flag == "ID") {
                    $hosts .= $row[$fieldName] . ",";
                } else {
                    $hosts .= "'" . $row[$fieldName] . "',";
                }
            }
        }

        $result = "''";
        if (strlen($hosts)) {
            $result = trim($hosts, ',');
        }

        return $result;
    }


    /**
     * Services array Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getServicesArray($flag = null, $pearDBndo = null, $escape = true)
    {
        $this->checkUpdateACL();

        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }

        $flag = strtoupper($flag);
        switch ($flag) {
            case "NAME":
                $query = "SELECT DISTINCT s.service_id, s.description "
                    . "FROM centreon_acl ca, services s "
                    . "WHERE ca.service_id = s.service_id "
                    . "AND group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'description';
                break;
            default:
                $query = "SELECT DISTINCT service_id "
                    . "FROM centreon_acl "
                    . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'service_id';
                break;
        }

        $services = array();

        $DBRES = $pearDBndo->query($query);
        $items = array();
        while ($row = $DBRES->fetchRow()) {
            if (isset($items[$row[$fieldName]])) {
                continue;
            }
            $items[$row[$fieldName]] = true;
            if ($escape === true) {
                $services[] = CentreonDB::escape($row[$fieldName]);
            } else {
                $services[] = $row[$fieldName];
            }
        }

        return $services;
    }


    /**
     * Services string Getter
     *
     *  Possible flags :
     *  - ID => will return the id's of the element
     *  - NAME => will return the names of the element
     */
    public function getServicesString($flag = null, $pearDBndo = null, $escape = true)
    {
        $this->checkUpdateACL();

        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }

        $flag = strtoupper($flag);
        switch ($flag) {
            case "NAME":
                $query = "SELECT DISTINCT s.service_id, s.description "
                    . "FROM centreon_acl ca, services s "
                    . "WHERE ca.service_id = s.service_id "
                    . "AND group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'description';
                break;
            default:
                $query = "SELECT DISTINCT service_id "
                    . "FROM centreon_acl "
                    . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
                $fieldName = 'service_id';
                break;
        }

        $services = "";

        $DBRES = $pearDBndo->query($query);
        $items = array();
        while ($row = $DBRES->fetchRow()) {
            if (isset($items[$row[$fieldName]])) {
                continue;
            }
            $items[$row[$fieldName]] = true;
            if ($escape === true) {
                $services .= "'" . CentreonDB::escape($row[$fieldName]) . "',";
            } else {
                if ($flag == "ID") {
                    $services .= $row[$fieldName] . ",";
                } else {
                    $services .= "'" . $row[$fieldName] . "',";
                }
            }
        }

        $result = "''";
        if (strlen($services)) {
            $result = trim($services, ',');
        }

        return $result;
    }

    /**
     * Get authorized host service ids
     *
     * @param $db CentreonDB
     * @return string | return id combinations like '14_26' (hostId_serviceId)
     */
    public function getHostServiceIds($db)
    {
        $this->checkUpdateACL();

        $groupIds = array_keys($this->accessGroups);
        if (!count($groupIds)) {
            return "''";
        }

        $hostsServices = "";

        $query = "SELECT DISTINCT host_id, service_id "
            . "FROM centreon_acl "
            . "WHERE group_id IN (" . implode(',', $groupIds) . ") ";
        $res = $db->query($query);
        while ($row = $res->fetchRow()) {
            $hostsServices .= "'" . $row['host_id'] . "_" . $row['service_id'] . "',";
        }

        $result = "''";
        if (strlen($hostsServices)) {
            $result = trim($hostsServices, ',');
        }

        return $result;
    }

    /*
     * Actions Getter
     */

    public function getActions()
    {
        $this->checkUpdateACL();
        return $this->actions;
    }

    public function getTopology()
    {
        $this->checkUpdateACL();
        return $this->topology;
    }

    /**
     * Update topologystr value
     */
    public function updateTopologyStr()
    {
        $this->setTopology();
        $this->topologyStr = $this->getTopologyString();
    }

    public function getTopologyString()
    {
        $this->checkUpdateACL();

        $topology = array_keys($this->topology);

        $result = "''";
        if (count($topology)) {
            $result = implode(', ', $topology);
        }

        return $result;
    }

    /**
     *  This functions returns a string that forms a condition of a query
     *  i.e : " WHERE host_id IN ('1', '2', '3') "
     *  or : " AND host_id IN ('1', '2', '3') "
     */
    public function queryBuilder($condition, $field, $stringlist)
    {
        $str = "";
        if ($this->admin) {
            return $str;
        }
        if ($stringlist == "") {
            $stringlist = "''";
        }
        $str .= " " . $condition . " " . $field . " IN (" . $stringlist . ") ";
        return $str;
    }

    /**
     * Function that returns
     *
     * @param string $p
     * @param bool $checkAction
     * @return int | 1 : if user is allowed to access the page
     *               0 : if user is NOT allowed to access the page
     */
    public function page($p, $checkAction = false)
    {
        $this->checkUpdateACL();
        if ($this->admin) {
            return 1;
        } elseif (isset($this->topology[$p])) {
            if ($checkAction && $this->topology[$p] == 2 &&
                isset($_REQUEST['o']) && $_REQUEST['o'] == 'a'
            ) {
                return 0;
            }
            return $this->topology[$p];
        }
        return 0;
    }

    /**
     * Function that checks if the user can execute the action
     *
     *  1 : user can execute it
     *  0 : user CANNOT execute it
     */
    public function checkAction($action)
    {
        $this->checkUpdateACL();
        if ($this->admin || isset($this->actions[$action])) {
            return 1;
        }
        return 0;
    }

    /**
     * Function that returns the pair host/service by ID if $host_id is NULL
     *  Otherwise, it returns all the services of a specific host
     */
    public function getHostsServices($pearDBMonitoring, $get_service_description = null)
    {
        global $pearDB;

        $tab = array();
        if ($this->admin) {
            $req = (!is_null($get_service_description)) ? ", s.service_description " : "";
            $query = "SELECT h.host_id, s.service_id " . $req
                . "FROM host h "
                . "LEFT JOIN host_service_relation hsr on hsr.host_host_id = h.host_id "
                . "LEFT JOIN service s on hsr.service_service_id = s.service_id "
                . "WHERE h.host_activate = '1' "
                . "AND (s.service_activate = '1' OR s.service_id is null) ";
            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                if (!is_null($get_service_description)) {
                    $tab[$row['host_id']][$row['service_id']] = $row['service_description'];
                } else {
                    $tab[$row['host_id']][$row['service_id']] = 1;
                }
            }
            $DBRESULT->free();
            // Used By EventLogs page Only
            if (!is_null($get_service_description)) {
                // Get Services attached to hostgroups
                $query = "SELECT hgr.host_host_id, s.service_id, s.service_description "
                    . "FROM hostgroup_relation hgr, service s, host_service_relation hsr "
                    . "WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id "
                    . "AND s.service_id = hsr.service_service_id ";
                $DBRESULT = $pearDB->query($query);
                while ($elem = $DBRESULT->fetchRow()) {
                    $tab[$elem['host_host_id']][$elem["service_id"]] = $elem["service_description"];
                }
                $DBRESULT->free();
            }
        } else {
            if (!is_null($get_service_description)) {
                $query = "SELECT acl.host_id, acl.service_id, s.description "
                    . "FROM centreon_acl acl "
                    . "LEFT JOIN services s on acl.service_id = s.service_id "
                    . "WHERE group_id IN (" . $this->getAccessGroupsString() . ") "
                    . "GROUP BY acl.host_id, acl.service_id ";
            } else {
                $query = "SELECT host_id, service_id "
                    . "FROM centreon_acl "
                    . "WHERE group_id IN (" . $this->getAccessGroupsString() . ") "
                    . "GROUP BY host_id, service_id ";
            }

            $DBRESULT = $pearDBMonitoring->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                if (!is_null($get_service_description)) {
                    $tab[$row['host_id']][$row['service_id']] = $row['description'];
                } else {
                    $tab[$row['host_id']][$row['service_id']] = 1;
                }
            }
            $DBRESULT->free();
        }

        return $tab;
    }

    public function getHostServices($pearDBMonitoring, $host_id, $get_service_description = null)
    {
        global $pearDB;

        $tab = array();
        if ($this->admin) {
            $query = "SELECT DISTINCT h.host_id, s.service_id, s.service_description "
                . "FROM host_service_relation hsr, host h, service s "
                . "WHERE h.host_activate = '1' "
                . "AND hsr.host_host_id = h.host_id "
                . "AND h.host_id = '" . CentreonDB::escape($host_id) . "'"
                . "AND hsr.service_service_id = s.service_id "
                . "AND s.service_activate = '1' ";
            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $tab[$row['service_id']] = $row['service_description'];
            }
            $DBRESULT->free();

            # Get Services attached to hostgroups
            $query = "SELECT DISTINCT service_id, service_description "
                . "FROM hostgroup_relation hgr, service, host_service_relation hsr "
                . "WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id "
                . "AND service_id = hsr.service_service_id ";
            $DBRESULT = $pearDB->query($query);
            while ($elem = $DBRESULT->fetchRow()) {
                $tab[$elem["service_id"]] = html_entity_decode($elem["service_description"], ENT_QUOTES, "UTF-8");
            }
            $DBRESULT->free();
        } else {
            $query = "SELECT DISTINCT s.service_id, s.description "
                . "FROM services s "
                . "JOIN centreon_acl ca "
                . "ON s.service_id = ca.service_id "
                . "AND ca.host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND ca.group_id IN (" . $this->getAccessGroupsString() . ") ";
            $DBRESULT = $pearDBMonitoring->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $tab[$row['service_id']] = $row['description'];
            }
            $DBRESULT->free();
        }

        return $tab;
    }

    /**
     * Function that returns the pair host/service by NAME if $host_name is NULL
     *  Otherwise, it returns all the services of a specific host
     */
    public function getHostsServicesName($pearDBndo)
    {
        $joinAcl = "";
        if (!$this->admin) {
            $joinAcl = "JOIN centreon_acl ca "
                . "ON h.host_id = ca.host_id "
                . "AND ca.group_id IN (" . $this->getAccessGroupsString() . ") ";
        }

        $tab = array();
        $query = "SELECT DISTINCT h.name, s.description "
            . "FROM hosts h "
            . "LEFT JOIN services s "
            . "ON h.host_id = s.host_id "
            . $joinAcl
            . "ORDER BY h.name, s.description ";
        $DBRESULT = $pearDBndo->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $tab[$row['name']][$row['description']] = 1;
        }
        $DBRESULT->free();

        return $tab;
    }

    /**
     * Function that returns the pair host/service by NAME if $host_name is NULL
     *  Otherwise, it returns all the services of a specific host
     */
    public function getHostServicesName($pearDBndo, $host_name)
    {
        $joinAcl = "";
        if (!$this->admin) {
            $joinAcl = "JOIN centreon_acl ca "
                . "ON h.host_id = ca.host_id "
                . "AND ca.group_id IN (" . $this->getAccessGroupsString() . ") ";
        }

        $tab = array();
        $query = "SELECT DISTINCT s.service_id, s.description "
            . "FROM hosts h "
            . "LEFT JOIN services s "
            . "ON h.host_id = s.host_id "
            . $joinAcl
            . "WHERE h.name = '" . CentreonDB::escape($host_name) . "' "
            . "AND s.service_id IS NOT NULL "
            . "ORDER BY h.name, s.description ";
        $DBRESULT = $pearDBndo->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $tab[$row['service_id']] = $row['description'];
        }
        $DBRESULT->free();

        return $tab;
    }

    /**
     * Function  that returns the hosts of a specific hostgroup
     */
    public function getHostgroupHosts($hg_id, $pearDBndo)
    {
        global $pearDB;

        $tab = array();
        $query = "SELECT DISTINCT h.host_id, h.host_name "
            . "FROM hostgroup_relation hgr, host h "
            . "WHERE hgr.hostgroup_hg_id = '" . CentreonDB::escape($hg_id) . "' "
            . "AND hgr.host_host_id = h.host_id "
            . $this->queryBuilder("AND", "h.host_id", $this->getHostsString("ID", $pearDBndo))
            . " ORDER BY h.host_name ";

        $DBRESULT = $pearDB->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $tab[$row['host_id']] = $row['host_name'];
        }
        return ($tab);
    }

    /**
     * Function that sets the changed flag to 1 for the cron centAcl.php
     */
    public function updateACL($data = null)
    {
        global $pearDB, $pearDBO, $centreon_path;

        if (!$this->admin) {
            $groupIds = array_keys($this->accessGroups);
            if (is_array($groupIds) && count($groupIds)) {
                $DBRESULT = $pearDB->query("UPDATE acl_groups SET acl_group_changed = '1' "
                    . "WHERE acl_group_id IN (" . implode(",", $groupIds) . ")");

                // Manage changes
                if (isset($data['type']) && $data["type"] == 'HOST'
                    && ($data['action'] == 'ADD' || $data['action'] == 'DUP')
                ) {
                    $host_name = getMyHostName($data["id"]);

                    if ($data['action'] == 'ADD') {
                        // Put new entries in the table with group_id
                        foreach ($groupIds as $group_id) {
                            $request2 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                . "VALUES ('" . $host_id . "', NULL, " . $group_id . ")";
                            $pearDBO->query($request2);
                        }

                        // Insert services
                        $svc = getMyHostServices($data['id']);
                        foreach ($svc as $svc_id => $svc_name) {
                            $request2 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                . "VALUES ('" . $data["id"] . "', '" . $svc_id . "', " . $group_id . ")";
                            $pearDBO->query($request2);
                        }
                    } elseif ($data['action'] == 'DUP' && isset($data['duplicate_host'])) {
                        // Get current configuration into Centreon_acl table
                        $request = "SELECT group_id FROM centreon_acl " .
                            "WHERE host_id = " . $data['duplicate_host'] . " AND service_id IS NULL";
                        $DBRESULT = $pearDBO->query($request);
                        while ($row = $DBRESULT->fetchRow()) {
                            // Insert New Host
                            $request1 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                . "VALUES ('" . $data["id"] . "', NULL, " . $row['group_id'] . ")";
                            $pearDBO->query($request1);

                            // Insert services
                            $request = "SELECT service_id, group_id FROM centreon_acl "
                                . "WHERE host_id = " . $data['duplicate_host'] . " AND service_id IS NOT NULL";
                            $DBRESULT = $pearDBO->query($request);
                            while ($row = $DBRESULT->fetchRow()) {
                                $request2 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                    . "VALUES ('" . $data["id"] . "', "
                                    . "'" . $row["service_id"] . "', " . $row['group_id'] . ")";
                                $pearDBO->query($request2);
                            }
                        }
                    }
                } elseif (isset($data['type']) && $data["type"] == 'SERVICE'
                    && ($data['action'] == 'ADD' || $data['action'] == 'DUP')
                ) {
                    $hosts = getMyServiceHosts($data["id"]);
                    $svc_name = getMyServiceName($data["id"]);
                    foreach ($hosts as $host_id) {
                        $host_name = getMyHostName($host_id);

                        if ($data['action'] == 'ADD') {
                            // Put new entries in the table with group_id
                            foreach ($groupIds as $group_id) {
                                $request2 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                    . "VALUES ('" . $host_id . "', '" . $data["id"] . "', " . $group_id . ")";
                                $pearDBO->query($request2);
                            }
                        } elseif ($data['action'] == 'DUP' && isset($data['duplicate_service'])) {
                            // Get current configuration into Centreon_acl table
                            $request = "SELECT group_id FROM centreon_acl "
                                . "WHERE host_id = $host_id AND service_id = " . $data['duplicate_service'];
                            $DBRESULT = $pearDBO->query($request);
                            while ($row = $DBRESULT->fetchRow()) {
                                $request2 = "INSERT INTO centreon_acl (host_id, service_id, group_id) "
                                    . "VALUES ('" . $host_id . "', '" . $data["id"] . "', " . $row['group_id'] . ")";
                                $pearDBO->query($request2);
                            }
                        }
                    }
                }
            }
        } else {
            $pearDB->query("UPDATE `acl_resources` SET `changed` = '1'");
        }
    }

    /**
     * Funtion that return only metaservice table
     */
    public function getMetaServices()
    {
        return $this->metaServices;
    }

    /**
     * Function that return Metaservice list ('', '', '')
     */
    public function getMetaServiceString()
    {
        return $this->metaServiceStr;
    }

    /**
     * Load the list of parent template
     */
    private function loadParentTemplates()
    {
        global $pearDB;

        /* Get parents template */
        $this->parentTemplates = array();
        $currentContact = $this->userID;
        while ($currentContact != 0) {
            $this->parentTemplates[] = $currentContact;
            $query = 'SELECT contact_template_id
                FROM contact
                WHERE contact_id = ' . $currentContact;
            $res = $pearDB->query($query);
            if (PEAR::isError($res)) {
                $currentContact = 0;
            } else {
                if ($row = $res->fetchRow()) {
                    $currentContact = $row['contact_template_id'];
                } else {
                    $currentContact = 0;
                }
            }
        }
    }

    /**
     * Get DB Name
     *
     * @param string $broker
     * @return string
     */
    public function getNameDBAcl($broker = null)
    {
        global $conf_centreon;

        return $conf_centreon["dbcstg"];
    }

    /**
     * build request
     *
     * @param array $options (fields, conditions, order, pages, total)
     * @param bool $hasWhereClause | whether the request already has a where clause
     * @return array
     */
    private function constructRequest($options, $hasWhereClause = false)
    {
        global $pearDB;

        $requests = array();

        // Manage select clause
        $requests['select'] = 'SELECT ';
        if (isset($options['total']) && $options['total'] == true) {
            $requests['select'] .= 'SQL_CALC_FOUND_ROWS DISTINCT ';
        } elseif (isset($options['distinct']) && $options['distinct'] == true) {
            $requests['select'] .= 'DISTINCT ';
        }

        // Manage fields
        if (isset($options['fields']) && is_array($options['fields'])) {
            $requests['fields'] = implode(', ', $options['fields']);
            $tmpFields = preg_replace('/\w+\.(\w+)/', '$1', $options['fields']);
            $requests['simpleFields'] = implode(', ', $tmpFields);
        } elseif (isset($options['fields'])) {
            $requests['fields'] = $options['fields'];
            $requests['simpleFields'] = preg_replace('/\w+\.(\w+)/', '$1', $options['fields']);
        } else {
            $requests['fields'] = '* ';
            $requests['simpleFields'] = '* ';
        }

        // Manage conditions
        $requests['conditions'] = '';
        if (isset($options['conditions']) && is_array($options['conditions'])) {
            $first = true;
            foreach ($options['conditions'] as $key => $opvalue) {
                if ($first) {
                    if ($hasWhereClause) {
                        $clause = ' AND (';
                    } else {
                        $clause = ' WHERE (';
                    }
                    if (is_array($opvalue) && count($opvalue) == 2) {
                        list($op, $value) = $opvalue;
                    } else {
                        $op = " = ";
                        $value = $opvalue;
                    }
                    $first = false;
                } else {
                    if (is_array($opvalue) && count($opvalue) == 3) {
                        list($clause, $op, $value) = $opvalue;
                    } elseif (is_array($opvalue) && count($opvalue) == 2) {
                        $clause = ' AND ';
                        list($op, $value) = $opvalue;
                    } else {
                        $clause = ' AND ';
                        $op = " = ";
                        $value = $opvalue;
                    }
                }

                if ($op == 'IN') {
                    $inValues = "";
                    if (is_array($value) && count($value)) {
                        $inValues = implode("','", $value);
                    }
                    $requests['conditions'] .= $clause . " " . $key . " " . $op . " ('" . $inValues . "') ";
                } else {
                    $requests['conditions'] .= $clause . " " . $key . " " . $op . " '" . $pearDB->escape($value) . "' ";
                }
            }
            if (!$first) {
                $requests['conditions'] .= ') ';
            }
        }

        // Manage order by
        $requests['order'] = '';
        if (isset($options['order'])) {
            if (is_array($options['order'])) {
                $requests['order'] = implode(', ', $options['order']);
            } elseif (!empty($options['order'])) {
                $requests['order'] = $options['order'];
            }
        }
        if ($requests['order'] != '') {
            $requests['order'] = ' ORDER BY ' . $requests['order'];
        }

        // Manage limit and select clause
        $requests['pages'] = '';
        if (isset($options['pages']) && trim($options['pages']) != '') {
            $requests['pages'] = ' LIMIT ' . $options['pages'];
        }

        return $requests;
    }

    private function constructKey($res, $options)
    {
        $key = '';
        $separator = '';
        foreach ($options['keys'] as $value) {
            if ($res[$value] == '') {
                return '';
            }
            $key .= $separator . $res[$value];
            $separator = isset($options['keys_separator']) ? $options['keys_separator'] : '_';
        }

        return $key;
    }

    /**
     * Construct result
     *
     * @param mixed $res
     * @param mixed $options
     * @access private
     * @return void
     */
    private function constructResult($sql, $options)
    {
        global $pearDB;

        $result = array();

        $res = $pearDB->query($sql);
        if (PEAR::isError($res)) {
            return $result;
        }

        while ($elem = $res->fetchRow()) {
            $key = $this->constructKey($elem, $options);

            if ($key != '' && !isset($result[$key])) {
                if (isset($options['get_row'])) {
                    $result[$key] = $elem[$options['get_row']];
                } else {
                    $result[$key] = $elem;
                }
            }
        }

        if (isset($options['total']) && $options['total'] == true) {
            return array(
                'items' => $result,
                'total' => $pearDB->numberRows()
            );
        } else {
            return $result;
        }
    }

    /**
     * Get ServiceGroup from ACL and configuration DB
     */
    public function getServiceGroupAclConf($search = null, $broker = null, $options = null, $sg_empty = null)
    {
        $sg = array();

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(sg_name)'),
                'fields' => array('servicegroup.sg_id', 'servicegroup.sg_name'),
                'keys' => array('sg_id'),
                'keys_separator' => '',
                'get_row' => 'sg_name'
            );
        }

        $request = $this->constructRequest($options);

        $searchCondition = "";
        if ($search != "") {
            $searchCondition = "AND sg_name LIKE '%" . CentreonDB::escape($search) . "%' ";
        }
        if ($this->admin) {
            $empty_exists = "";
            if (!is_null($sg_empty)) {
                $empty_exists = 'AND EXISTS (
                    SELECT * FROM servicegroup_relation 
                        WHERE (servicegroup_relation.servicegroup_sg_id = servicegroup.sg_id
                            AND servicegroup_relation.service_service_id IS NOT NULL)) ';
            }
            $query = $request['select'] . $request['fields'] . " "
                . "FROM servicegroup "
                . "WHERE sg_activate = '1' "
                . $searchCondition
                . $empty_exists;
        } else {
            $groupIds = array_keys($this->accessGroups);
            $query = $request['select'] . $request['simpleFields'] . " "
                . "FROM ( "
                . "SELECT " . $request['fields'] . " "
                . "FROM hostgroup_relation, servicegroup_relation,servicegroup, "
                . "acl_res_group_relations, acl_resources_hg_relations "
                . "WHERE acl_res_group_relations.acl_group_id  IN (" . implode(',', $groupIds) . ") "
                . "AND acl_resources_hg_relations.acl_res_id = acl_res_group_relations.acl_res_id "
                . $searchCondition
                . "AND servicegroup_relation.hostgroup_hg_id = hostgroup_relation.hostgroup_hg_id "
                . "AND servicegroup.sg_id = servicegroup_relation.servicegroup_sg_id "
                . "UNION "
                . "SELECT " . $request['fields'] . " "
                . "FROM servicegroup, acl_resources_sg_relations, acl_res_group_relations "
                . "WHERE acl_res_group_relations.acl_group_id  IN (" . implode(',', $groupIds) . ") "
                . "AND acl_resources_sg_relations.acl_res_id = acl_res_group_relations.acl_res_id "
                . "AND acl_resources_sg_relations.sg_id = servicegroup.sg_id "
                . $searchCondition
                . ") as t ";
        }

        $query .= $request['order'] . $request['pages'];

        return $this->constructResult($query, $options);
    }

    /**
     * Get Services in servicesgroups from ACL and configuration DB
     */
    public function getServiceServiceGroupAclConf($sg_id, $broker = null, $options = null)
    {
        $services = array();

        $db_name_acl = $this->getNameDBAcl($broker);
        if (is_null($db_name_acl) || $db_name_acl == "") {
            return $services;
        }

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(host_name)', 'LOWER(service_description)'),
                'fields' => array(
                    'service.service_description',
                    'service.service_id',
                    'host.host_id',
                    'host.host_name'
                ),
                'keys' => array('host_id', 'service_id'),
                'keys_separator' => '_'
            );
        }

        $request = $this->constructRequest($options);

        $from_acl = "";
        $where_acl = "";
        if (!$this->admin) {
            $groupIds = array_keys($this->accessGroups);
            $from_acl = ", $db_name_acl.centreon_acl ";
            $where_acl = " AND $db_name_acl.centreon_acl.group_id IN (" . implode(',', $groupIds) . ") "
                . "AND $db_name_acl.centreon_acl.host_id = host.host_id "
                . "AND $db_name_acl.centreon_acl.service_id = service.service_id ";
        }
        $query = $request['select'] . $request['simpleFields'] . " "
            . "FROM ( "
            . "SELECT " . $request['fields'] . " "
            . "FROM servicegroup, servicegroup_relation, service, host " . $from_acl . " "
            . "WHERE servicegroup.sg_id = '" . CentreonDB::escape($sg_id) . "' "
            . "AND service.service_activate='1' AND host.host_activate='1' "
            . "AND servicegroup.sg_id = servicegroup_relation.servicegroup_sg_id "
            . "AND servicegroup_relation.service_service_id = service.service_id "
            . "AND servicegroup_relation.host_host_id = host.host_id "
            . $where_acl . " "
            . "UNION "
            . "SELECT " . $request['fields'] . " "
            . "FROM servicegroup, servicegroup_relation, hostgroup_relation, service, host " . $from_acl . " "
            . "WHERE servicegroup.sg_id = '" . CentreonDB::escape($sg_id) . "' "
            . "AND servicegroup.sg_id = servicegroup_relation.servicegroup_sg_id "
            . "AND servicegroup_relation.hostgroup_hg_id = hostgroup_relation.hostgroup_hg_id "
            . "AND hostgroup_relation.host_host_id = host.host_id "
            . "AND servicegroup_relation.service_service_id = service.service_id "
            . $where_acl . " "
            . ") as t ";

        $query .= $request['order'] . $request['pages'];

        $services = $this->constructResult($query, $options);

        return $services;
    }

    /**
     * Get host acl configuration
     *
     * @param mixed $search
     * @param mixed $broker
     * @param mixed $options
     * @param bool $host_empty | if host_empty is true,
     *                           hosts with no authorized
     *                           services will be returned
     * @access public
     * @return void
     */
    public function getHostAclConf($search = null, $broker = null, $options = null, $host_empty = false)
    {
        $hosts = array();

        $db_name_acl = $this->getNameDBAcl($broker);
        if (is_null($db_name_acl) || $db_name_acl == "") {
            return $hosts;
        }

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(host.host_name)'),
                'fields' => array('host.host_id', 'host.host_name'),
                'keys' => array('host_id'),
                'keys_separator' => '',
                'get_row' => 'host_name'
            );
        }

        $request = $this->constructRequest($options, true);

        $searchCondition = "";
        if ($search != "") {
            $searchCondition = "AND (host.host_name LIKE '%" . CentreonDB::escape($search) . "%'
                OR host.host_alias LIKE '%" . CentreonDB::escape($search) . "%') ";
        }

        $emptyJoin = "";
        if ($host_empty) {
            $emptyJoin = "LEFT JOIN host_service_relation on host_service_relation.host_host_id = host.host_id "
                . "AND host_service_relation.service_service_id IS NOT NULL "
                . "LEFT JOIN hostgroup_relation on host.host_id = hostgroup_relation.host_host_id "
                . "AND hostgroup_relation.hostgroup_hg_id = host_service_relation.hostgroup_hg_id "
                . "AND (host_service_relation.hsr_id IS NOT NULL OR hostgroup_relation.hgr_id IS NOT NULL) ";
        }

        if ($this->admin) {
            $query = $request['select'] . $request['fields'] . " "
                . "FROM host "
                . $emptyJoin
                . "WHERE host_register = '1' "
                //. "AND host_activate = '1' "
                . $request['conditions']
                . $searchCondition;
        } else {
            $groupIds = array_keys($this->accessGroups);
            if ($host_empty) {
                $emptyJoin .= "AND $db_name_acl.centreon_acl.service_id IS NOT NULL ";
            }
            $query = $request['select'] . $request['fields'] . " "
                . "FROM host "
                . "JOIN $db_name_acl.centreon_acl "
                . "ON $db_name_acl.centreon_acl.host_id = host.host_id "
                . "AND $db_name_acl.centreon_acl.group_id IN (" . implode(',', $groupIds) . ") "
                . "WHERE host.host_register = '1' "
                //. "AND host.host_activate = '1' "
                . $emptyJoin
                . $request['conditions']
                . $searchCondition;
        }

        $query .= $request['order'] . $request['pages'];

        $hosts = $this->constructResult($query, $options);

        return $hosts;
    }

    public function getHostServiceAclConf($host_id, $broker = null, $options = null)
    {
        $services = array();

        $db_name_acl = $this->getNameDBAcl($broker);
        if (is_null($db_name_acl) || $db_name_acl == "") {
            return $services;
        }

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(service_description)'),
                'fields' => array('s.service_id', 's.service_description'),
                'keys' => array('service_id'),
                'keys_separator' => '',
                'get_row' => 'service_description'
            );
        }

        $request = $this->constructRequest($options);

        if ($this->admin) {
            $query = $request['select'] . $request['simpleFields'] . " "
                . "FROM ( "
                . "SELECT " . $request['fields'] . " "
                . "FROM host_service_relation hsr, host h, service s "
                . "WHERE h.host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND h.host_activate = '1' "
                . "AND h.host_register = '1' "
                . "AND h.host_id = hsr.host_host_id "
                . "AND hsr.service_service_id = s.service_id "
                . "AND s.service_activate = '1' "
                . "UNION "
                . "SELECT " . $request['fields'] . " "
                . "FROM host h, hostgroup_relation hgr, service s, host_service_relation hsr "
                . "WHERE h.host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND h.host_activate = '1' "
                . "AND h.host_register = '1' "
                . "AND h.host_id = hgr.host_host_id "
                . "AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id "
                . "AND hsr.service_service_id = s.service_id "
                . ") as t ";
        } else {
            $query = "SELECT " . $request['fields'] . " "
                . "FROM host_service_relation hsr, host h, service s, $db_name_acl.centreon_acl "
                . "WHERE h.host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND h.host_activate = '1' "
                . "AND h.host_register = '1' "
                . "AND h.host_id = hsr.host_host_id "
                . "AND hsr.service_service_id = s.service_id "
                . "AND s.service_activate = '1' "
                . "AND $db_name_acl.centreon_acl.host_id = h.host_id "
                . "AND $db_name_acl.centreon_acl.service_id IS NOT NULL "
                . "AND $db_name_acl.centreon_acl.service_id = s.service_id "
                . "AND $db_name_acl.centreon_acl.group_id IN (" . $this->getAccessGroupsString() . ") "
                . "UNION "
                . "SELECT " . $request['fields'] . " "
                . "FROM host h, hostgroup_relation hgr, "
                . "service s, host_service_relation hsr, $db_name_acl.centreon_acl "
                . "WHERE h.host_id = '" . CentreonDB::escape($host_id) . "' "
                . "AND h.host_activate = '1' "
                . "AND h.host_register = '1' "
                . "AND h.host_id = hgr.host_host_id "
                . "AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id "
                . "AND hsr.service_service_id = s.service_id "
                . "AND $db_name_acl.centreon_acl.host_id = h.host_id "
                . "AND $db_name_acl.centreon_acl.service_id IS NOT NULL "
                . "AND $db_name_acl.centreon_acl.service_id = s.service_id "
                . "AND $db_name_acl.centreon_acl.group_id IN (" . $this->getAccessGroupsString() . ") ";
        }

        $query .= $request['order'] . $request['pages'];

        $services = $this->constructResult($query, $options);

        return $services;
    }

    /**
     * Get HostGroup from ACL and configuration DB
     */
    public function getHostGroupAclConf($search = null, $broker = null, $options = null, $hg_empty = false)
    {
        $hg = array();

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(hg_name)'),
                'fields' => array('hg_id', 'hg_name'),
                'keys' => array('hg_id'),
                'keys_separator' => '',
                'get_row' => 'hg_name'
            );
        }

        $request = $this->constructRequest($options, true);

        $searchCondition = "";
        if ($search != "") {
            $searchCondition = "AND hg_name LIKE '%" . CentreonDB::escape($search) . "%' ";
        }
        if ($this->admin) {
            $empty_exists = "";
            if ($hg_empty) {
                $empty_exists = 'AND EXISTS (SELECT * FROM hostgroup_relation WHERE
                    (hostgroup_relation.hostgroup_hg_id = hostgroup.hg_id
                        AND hostgroup_relation.host_host_id IS NOT NULL)) ';
            }
            // We should check if host is activate (maybe)
            $query = $request['select'] . $request['fields'] . " "
                . "FROM hostgroup "
                . "WHERE hg_activate = '1' "
                . $request['conditions']
                . $searchCondition
                . $empty_exists;
        } else {
            // Cant manage empty hostgroup with ACLs. We'll have a problem with acl for conf...
            $groupIds = array_keys($this->accessGroups);
            $query = $request['select'] . $request['fields'] . " "
                . "FROM hostgroup, acl_res_group_relations, acl_resources_hg_relations "
                . "WHERE hg_activate = '1' "
                . "AND acl_res_group_relations.acl_group_id  IN (" . implode(',', $groupIds) . ") "
                . "AND acl_res_group_relations.acl_res_id = acl_resources_hg_relations.acl_res_id "
                . "AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id "
                . $request['conditions']
                . $searchCondition;
        }

        $query .= $request['order'] . $request['pages'];

        $hg = $this->constructResult($query, $options);

        return $hg;
    }

    public function getHostHostGroupAclConf($hg_id, $broker = null, $options = null)
    {
        $hg = array();

        if (is_null($options)) {
            $options = array(
                'order' => array('LOWER(host_name)'),
                'fields' => array('host_id', 'host_name'),
                'keys' => array('host_id'),
                'keys_separator' => '',
                'get_row' => 'host_name'
            );
        }

        $request = $this->constructRequest($options);

        $searchCondition = "";

        if ($this->admin) {
            $query = $request['select'] . $request['fields'] . " "
                . "FROM hostgroup, hostgroup_relation, host "
                . "WHERE hg_id = '" . CentreonDB::escape($hg_id) . "' "
                . "AND hg_activate = '1' "
                . "AND host_activate='1' "
                . "AND hostgroup_relation.hostgroup_hg_id = hostgroup.hg_id "
                . "AND hostgroup_relation.host_host_id = host.host_id ";
        } else {
            // Cant manage empty hostgroup with ACLs. We'll have a problem with acl for conf...
            $groupIds = array_keys($this->accessGroups);
            $query = $request['select'] . $request['fields'] . " "
                . "FROM hostgroup, hostgroup_relation, host, acl_res_group_relations, acl_resources_hg_relations "
                . "WHERE hg_id = '" . CentreonDB::escape($hg_id) . "' "
                . "AND hg_activate = '1' "
                . "AND host_activate='1' "
                . "AND hostgroup_relation.hostgroup_hg_id = hostgroup.hg_id "
                . "AND hostgroup_relation.host_host_id = host.host_id "
                . "AND acl_res_group_relations.acl_group_id  IN (" . implode(',', $groupIds) . ") "
                . "AND acl_res_group_relations.acl_res_id = acl_resources_hg_relations.acl_res_id "
                . "AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id ";
        }

        $query .= $request['order'] . $request['pages'];

        $hg = $this->constructResult($query, $options);

        return $hg;
    }

    /**
     * Get poller acl configuration
     *
     * @access public
     * @param array $options
     * @return void
     */
    public function getPollerAclConf($options = array())
    {
        if (!count($options)) {
            $options = array(
                'fields' => array('id', 'name'),
                'order' => array('name'),
                'keys' => array('id')
            );
        }

        $request = $this->constructRequest($options);

        $pollerstring = $this->getPollerString();
        $pollerfilter = "";
        if (!$this->admin && $pollerstring != "''") {
            $pollerfilter = $this->queryBuilder($request['conditions'] ? 'AND' : 'WHERE', 'id', $pollerstring);
        }

        $sql = $request['select'] . $request['fields'] . " "
            . "FROM nagios_server "
            . $request['conditions']
            . $pollerfilter;

        $sql .= $request['order'] . $request['pages'];

        $result = $this->constructResult($sql, $options);

        return $result;
    }

    /**
     * Get contact acl configuration
     *
     * @param array $options
     * @access public
     * @return void
     */
    public function getContactAclConf($options = array())
    {
        $request = $this->constructRequest($options, true);

        if ($this->admin) {
            $sql = $request['select'] . $request['fields'] . " "
                . "FROM contact "
                . "WHERE contact_register = '1' "
                . $request['conditions'];
        } else {
            $sql = $request['select'] . $request['fields'] . " "
                . "FROM ( "
                . "SELECT " . $request['fields'] . " "
                . "FROM acl_group_contacts_relations agcr, contact c "
                . "WHERE c.contact_id = agcr.contact_contact_id "
                . "AND c.contact_register = '1'"
                . "AND agcr.acl_group_id IN (" . $this->getAccessGroupsString() . ") "
                . $request['conditions']
                . " UNION "
                . "SELECT " . $request['fields'] . " "
                . "FROM acl_group_contactgroups_relations agccgr, contactgroup_contact_relation ccr, contact c "
                . "WHERE c.contact_id = ccr.contact_contact_id "
                . "AND c.contact_register = '1' "
                . "AND ccr.contactgroup_cg_id = agccgr.cg_cg_id "
                . "AND agccgr.acl_group_id IN (" . $this->getAccessGroupsString() . ") "
                . $request['conditions']
                . ") as t ";
        }

        $sql .= $request['order'] . $request['pages'];

        $result = $this->constructResult($sql, $options);

        return $result;
    }

    /**
     * Get contact group acl configuration
     *
     * @access public
     * @param array $options
     * @return void
     */
    public function getContactGroupAclConf($options = array(), $localOnly = true)
    {
        $request = $this->constructRequest($options, true);

        $ldapCondition = "";
        $sJointure = "";
        $sCondition = "";

        if (!$localOnly) {
            $ldapCondition = "OR cg.cg_type = 'ldap' ";
            $sJointure = " LEFT JOIN  auth_ressource auth ON cg.ar_id =  auth.ar_id  ";
        }


        if ($this->admin) {
            $sql = $request['select'] . $request['fields'] . " "
                . "FROM contactgroup cg " . $sJointure
                . "WHERE (cg.cg_type = 'local' " . $ldapCondition . ") "
                . $sCondition
                . $request['conditions'];
        } else {
            $sql = $request['select'] . $request['fields'] . " "
                . "FROM acl_group_contactgroups_relations agccgr, contactgroup cg " . $sJointure
                . "WHERE cg.cg_id = agccgr.cg_cg_id "
                . "AND (cg.cg_type = 'local' " . $ldapCondition . ") "
                . "AND agccgr.acl_group_id IN (" . $this->getAccessGroupsString() . ") "
                . $request['conditions'];
        }

        $sql .= $request['order'] . $request['pages'];
        $result = $this->constructResult($sql, $options);

        return $result;
    }

    /**
     *
     * @param type $options
     * @return type
     */
    public function getAclGroupAclConf($options = array())
    {
        $request = $this->constructRequest($options);

        $sql = $request['select'] . $request['fields'] . " "
            . "FROM acl_groups "
            . $request['conditions'];

        $sql .= $request['order'] . $request['pages'];

        $result = $this->constructResult($sql, $options);

        return $result;
    }

    /**
     * Duplicate Host ACL
     *
     * @param array $hosts | hosts to duplicate
     * @return void
     */
    public function duplicateHostAcl($hosts = array())
    {
        global $pearDB;

        $sql = "INSERT INTO %s 
                    (host_host_id, acl_res_id)
                    (SELECT %d, acl_res_id 
                    FROM %s 
                    WHERE host_host_id = %d)";
        $tbHost = "acl_resources_host_relations";
        $tbHostEx = "acl_resources_hostex_relations";
        foreach ($hosts as $copyId => $originalId) {
            $pearDB->query(sprintf($sql, $tbHost, $copyId, $tbHost, $originalId));
            $pearDB->query(sprintf($sql, $tbHostEx, $copyId, $tbHostEx, $originalId));
        }
    }

    /**
     * Duplicate Host Group ACL
     *
     * @param array $hgs | host groups to duplicate
     * @return void
     */
    public function duplicateHgAcl($hgs = array())
    {
        global $pearDB;

        $sql = "INSERT INTO %s 
                    (hg_hg_id, acl_res_id)
                    (SELECT %d, acl_res_id 
                    FROM %s 
                    WHERE hg_hg_id = %d)";
        $tb = "acl_resources_hg_relations";
        foreach ($hgs as $copyId => $originalId) {
            $pearDB->query(sprintf($sql, $tb, $copyId, $tb, $originalId));
        }
    }

    /**
     * Duplicate Service Group ACL
     *
     * @param array $sgs | service groups to duplicate
     * @return void
     */
    public function duplicateSgAcl($sgs = array())
    {
        global $pearDB;

        $sql = "INSERT INTO %s 
                    (sg_id, acl_res_id)
                    (SELECT %d, acl_res_id 
                    FROM %s 
                    WHERE sg_id = %d)";
        $tb = "acl_resources_sg_relations";
        foreach ($sgs as $copyId => $originalId) {
            $pearDB->query(sprintf($sql, $tb, $copyId, $tb, $originalId));
        }
    }

    /**
     * Duplicate Host Category ACL
     *
     * @param array $hcs | host categories to duplicate
     * @return void
     */
    public function duplicateHcAcl($hcs = array())
    {
        global $pearDB;

        $sql = "INSERT INTO %s 
                    (hc_id, acl_res_id)
                    (SELECT %d, acl_res_id 
                    FROM %s 
                    WHERE hc_id = %d)";
        $tb = "acl_resources_hc_relations";
        foreach ($hcs as $copyId => $originalId) {
            $pearDB->query(sprintf($sql, $tb, $copyId, $tb, $originalId));
        }
    }

    /**
     * Duplicate Service Category ACL
     *
     * @param array $scs | service categories to duplicate
     * @return void
     */
    public function duplicateScAcl($scs = array())
    {
        global $pearDB;

        $sql = "INSERT INTO %s 
                    (sc_id, acl_res_id)
                    (SELECT %d, acl_res_id 
                    FROM %s 
                    WHERE sc_id = %d)";
        $tb = "acl_resources_sc_relations";
        foreach ($scs as $copyId => $originalId) {
            $pearDB->query(sprintf($sql, $tb, $copyId, $tb, $originalId));
        }
    }
}
