<?php
/**
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

/**
 *
 * Class for Access Control List management
 * @author jmathis
 *
 */
class CentreonACL
{
 	private $userID; /* ID of the user */
 	private $parentTemplates = null;
 	public $admin; /* Flag that tells us if the user is admin or not */
 	private $accessGroups = array(); /* Access groups the user belongs to */
 	private $resourceGroups = array(); /* Resource groups the user belongs to */
 	public  $hostGroups = array(); /* Hostgroups the user can see */
 	protected $pollers = array(); /* Pollers the user can see */
 	private $hostGroupsAlias = array(); /* Hostgroups by alias the user can see */
 	private $serviceGroups = array(); /* Servicegroups the user can see */
 	private $serviceGroupsAlias = array(); /* Servicegroups by alias the user can see */
 	private $serviceCategories = array(); /* Service categories the user can see */
 	private $actions = array(); /* Actions the user can do */
 	private $hostGroupsFilter = array();
 	private $serviceGroupsFilter = array();
 	private $serviceCategoriesFilter = array();
 	public  $topology = array();
 	public  $topologyStr = "";
 	private $metaServices = array();
 	private $metaServiceStr = "";

 	/*
 	 *  Constructor that takes the user_id
 	 */
 	function CentreonACL($user_id, $is_admin = null)
 	{
 		$this->userID = $user_id;

 		if (!isset($is_admin)) {
 			$localPearDB = new CentreonDB();
 			$rq = "SELECT contact_admin FROM `contact` WHERE contact_id = '".$user_id."' LIMIT 1";
 			$RES = $localPearDB->query($rq);
 			$row = $RES->fetchRow();
 			$this->admin = $row['contact_admin'];
 		} else {
 			$this->admin = $is_admin;
 		}

 		if (!$this->admin) {
	 		$this->setAccessGroups();
	 		$this->setResourceGroups();
	 		$this->setHostGroups();
	 		$this->setPollers();
	 		$this->setServiceGroups();
	 		$this->setServiceCategories();
	 		$this->setMetaServices();
	 		$this->setActions();
 		}

		$this->setTopology();
 		$this->getACLStr();

 	}

 	/*
 	 *  Function that will reset ACL
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
 		$this->setMetaServices();
 		$this->setTopology();
 		$this->getACLStr();
 		$this->setActions();
 	}

 	/*
 	 *  Function that will check whether or not the user needs to rebuild his ACL
 	 */
 	private function checkUpdateACL()
 	{
 		global $pearDB;

 		if (is_null($this->parentTemplates)) {
 		    $this->loadParentTemplates();
 		}

 		if (!$this->admin) {
	 		$query = "SELECT update_acl FROM session WHERE update_acl = '1' AND user_id IN (" . join(', ', $this->parentTemplates) . ")";
	 		$DBRES = $pearDB->query($query);
	 		if ($DBRES->numRows()) {
	 			$pearDB->query("UPDATE session SET update_acl = '0' WHERE user_id IN (" . join(', ', $this->parentTemplates) . ")");
	 			$this->resetACL();
	 		}
 		}
 	}

 	/*
 	 *  Setter functions
 	 */

 	/*
 	 *  Access groups Setter
 	 */
 	private function setAccessGroups()
 	{
 		global $pearDB;

 		if (is_null($this->parentTemplates)) {
            $this->loadParentTemplates();
 		}

 		$query = "SELECT acl.acl_group_id, acl.acl_group_name " .
 				"FROM acl_groups acl, acl_group_contacts_relations agcr " .
 				"WHERE acl.acl_group_id = agcr.acl_group_id " .
 				"AND agcr.contact_contact_id IN (" . join(', ', $this->parentTemplates) . ") " .
 				"AND acl.acl_group_activate = '1' " .
                "ORDER BY acl.acl_group_name ASC";
 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->accessGroups[$row['acl_group_id']] = $row['acl_group_name'];
 		}
 		$DBRESULT->free();

 		$query = "SELECT acl.acl_group_id, acl.acl_group_name " .
 				"FROM acl_groups acl, acl_group_contactgroups_relations agcgr, contactgroup_contact_relation cgcr " .
 				"WHERE acl.acl_group_id = agcgr.acl_group_id " .
 				"AND cgcr.contactgroup_cg_id = agcgr.cg_cg_id " .
 				"AND cgcr.contact_contact_id IN (" . join(', ', $this->parentTemplates) . ") " .
 				"AND acl.acl_group_activate = '1' " .
                "ORDER BY acl.acl_group_name ASC";

 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->accessGroups[$row['acl_group_id']] = $row['acl_group_name'];
 		}
 		$DBRESULT->free();
 	}

 	/*
 	 *  Resource groups Setter
 	 */
 	private function setResourceGroups()
 	{
 		global $pearDB;

 		$query = "SELECT acl.acl_res_id, acl.acl_res_name " .
 				"FROM acl_resources acl, acl_res_group_relations argr " .
 				"WHERE acl.acl_res_id = argr.acl_res_id " .
 				"AND argr.acl_group_id IN (".$this->getAccessGroupsString().") " .
 				"AND acl.acl_res_activate = '1' " .
                "ORDER BY acl.acl_res_name ASC";
 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->resourceGroups[$row['acl_res_id']] = $row['acl_res_name'];
 		}
 		$DBRESULT->free();
 	}


 	/*
 	 *  Access groups Setter
 	 */
 	private function setHostGroups()
 	{
 		global $pearDB;

 		$query = "SELECT hg.hg_id, hg.hg_name, hg.hg_alias, arhr.acl_res_id " .
 				"FROM hostgroup hg, acl_resources_hg_relations arhr " .
 				"WHERE hg.hg_id = arhr.hg_hg_id " .
 				"AND arhr.acl_res_id IN (".$this->getResourceGroupsString().") " .
 				"AND hg.hg_activate = '1' " .
                "ORDER BY hg.hg_name ASC";
 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->hostGroups[$row['hg_id']] = $row['hg_name'];
 			$this->hostGroupsAlias[$row['hg_id']] = $row['hg_alias'];
 			$this->hostGroupsFilter[$row['acl_res_id']][$row['hg_id']] = $row['hg_id'];
 		}
 		$DBRESULT->free();
 	}

	/**
 	 *  Poller Setter
 	 */
 	private function setPollers()
 	{
 		global $pearDB;

 		$query = "SELECT ns.id, ns.name, arpr.acl_res_id " .
 				"FROM nagios_server ns, acl_resources_poller_relations arpr " .
 				"WHERE ns.id = arpr.poller_id " .
 				"AND arpr.acl_res_id IN (".$this->getResourceGroupsString().") " .
 				"AND ns.ns_activate = '1' ".
                "ORDER BY ns.name ASC";
 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->pollers[$row['id']] = $row['name'];
 		}
 		$DBRESULT->free();
 	}

 	/**
 	 *  Service groups Setter
 	 */
 	private function setServiceGroups()
 	{
 		global $pearDB;

 		$query = "SELECT sg.sg_id, sg.sg_name, sg.sg_alias, arsr.acl_res_id " .
 				"FROM servicegroup sg, acl_resources_sg_relations arsr " .
 				"WHERE sg.sg_id = arsr.sg_id " .
 				"AND arsr.acl_res_id IN (".$this->getResourceGroupsString().") " .
 				"AND sg.sg_activate = '1' ".
                "ORDER BY sg.sg_name ASC";
 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->serviceGroups[$row['sg_id']] = $row['sg_name'];
 			$this->serviceGroupsAlias[$row['sg_id']] = $row['sg_alias'];
 			$this->serviceGroupsFilter[$row['acl_res_id']][$row['sg_id']] = $row['sg_id'];
 		}
 		$DBRESULT->free();
 	}

 	/*
 	 *  Service categories Setter
 	 */
 	private function setServiceCategories()
 	{
 		global $pearDB;

 		$query = "SELECT sc.sc_id, sc.sc_name, arsr.acl_res_id " .
 				"FROM service_categories sc, acl_resources_sc_relations arsr " .
 				"WHERE sc.sc_id = arsr.sc_id " .
 				"AND arsr.acl_res_id IN (".$this->getResourceGroupsString().") " .
 				"AND sc.sc_activate = '1' ".
                "ORDER BY sc.sc_name ASC";

 		$DBRESULT = $pearDB->query($query);
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->serviceCategories[$row['sc_id']] = $row['sc_name'];
 			$this->serviceCategoriesFilter[$row['acl_res_id']][$row['sc_id']] = $row['sc_id'];
 		}
 		$DBRESULT->free();
 	}

  	/*
 	 *  Access meta Setter
 	 */

 	private function setMetaServices()
 	{
 		global $pearDB;

 		$query = "SELECT ms.meta_id, ms.meta_name, arsr.acl_res_id " .
 				"FROM meta_service ms, acl_resources_meta_relations arsr " .
 				"WHERE ms.meta_id = arsr.meta_id " .
 				"AND arsr.acl_res_id IN (".$this->getResourceGroupsString().") ".
                "ORDER BY ms.meta_name ASC";
 		$DBRESULT = $pearDB->query($query);
 		$this->metaServiceStr = "";
 		while ($row = $DBRESULT->fetchRow()) {
 			$this->metaServices[$row['meta_id']] = $row['meta_name'];
 			if ($this->metaServiceStr != "")
 				$this->metaServiceStr .= ",";
 			$this->metaServiceStr .= "'".$row['meta_id']."'";
 		}
 		$DBRESULT->free();
 	}

 	/*
 	 *  Actions Setter
 	 */
 	private function setActions()
 	{
		global $pearDB;

		$query = "SELECT ar.acl_action_name " .
				"FROM acl_group_actions_relations agar, acl_actions a, acl_actions_rules ar " .
				"WHERE a.acl_action_id = agar.acl_action_id " .
				"AND agar.acl_action_id = ar.acl_action_rule_id " .
				"AND a.acl_action_activate = '1'" .
				"AND agar.acl_group_id IN (".$this->getAccessGroupsString().") ".
                "ORDER BY ar.acl_action_name ASC";
		$DBRESULT = $pearDB->query($query);
		while ($row = $DBRESULT->fetchRow()) {
			$this->actions[$row['acl_action_name']] = $row['acl_action_name'];
		}
		$DBRESULT->free();
 	}


 	/*
 	 *  Topology setter
 	 */
 	private function setTopology() {
	  	global $pearDB;

	  	if ($this->admin) {
 			$query = "SELECT topology_page FROM topology WHERE topology_page IS NOT NULL";
 			$DBRES = $pearDB->query($query);
 			while ($row = $DBRES->fetchRow()) {
 				$this->topology[$row['topology_page']] = 1;
 			}
			$DBRES->free();
 		} else {
		  	if (count($this->accessGroups) > 0) {
		  	 	/*
		  	 	 * If user is in an access group
		  	 	 */
			  	$str_topo = "";
				$DBRESULT = $pearDB->query(	"SELECT DISTINCT acl_group_topology_relations.acl_topology_id " .
												"FROM `acl_group_topology_relations`, `acl_topology`, `acl_topology_relations` " .
												"WHERE acl_topology_relations.acl_topo_id = acl_topology.acl_topo_id " .
												"AND acl_group_topology_relations.acl_group_id IN (". $this->getAccessGroupsString() .")" .
												"AND acl_topology.acl_topo_activate = '1'");

				if (!$DBRESULT->numRows()){
					$this->topology[1] = 1;
			  		$this->topology[101] = 1;
			  		$this->topology[10101] = 1;
				} else {
					$count = 0;
					$tmp_topo_page = array();
					while ($topo_group = $DBRESULT->fetchRow()) {
						$DBRESULT2 = $pearDB->query(	"SELECT topology_topology_id, acl_topology_relations.access_right " .
				  										"FROM `acl_topology_relations`, acl_topology " .
				  										"WHERE acl_topology_relations.acl_topo_id = '".$topo_group["acl_topology_id"]."' " .
														"AND acl_topology.acl_topo_activate = '1' " .
														"AND acl_topology.acl_topo_id = acl_topology_relations.acl_topo_id");

						while ($topo_page = $DBRESULT2->fetchRow()) {
							if ($str_topo != "") {
								$str_topo .= ", ";
							}
							$str_topo .= $topo_page["topology_topology_id"];
					 		$count++;
                                                        if (!isset($tmp_topo_page[$topo_page['topology_topology_id']]) || !$tmp_topo_page[$topo_page['topology_topology_id']]) {
                                                            $tmp_topo_page[$topo_page["topology_topology_id"]] = $topo_page["access_right"];
                                                        }
						}
						$DBRESULT2->free();
					}
					$DBRESULT->free();
					unset($topo_group);
					unset($topo_page);
					$count ? $ACL = "topology_id IN ($str_topo) AND " : $ACL = "";
					unset($DBRESULT);

					$DBRESULT = $pearDB->query("SELECT topology_page, topology_id FROM topology WHERE $ACL topology_page IS NOT NULL");
					while ($topo_page = $DBRESULT->fetchRow()) {
						$this->topology[$topo_page["topology_page"]] = $tmp_topo_page[$topo_page["topology_id"]];
					}
					$DBRESULT->free();
					unset($topo_page);
					unset($tmp_topo_page);
				}
				unset($DBRESULT);
		  	} else  {
		  		/*
		  		 * If user isn't in an access group
		  		 */
		  		$this->topology[1] = 1;
		  		$this->topology[101] = 1;
		  		$this->topology[10101] = 1;
		  	}
	  	}
 	}

 	/*
 	 *  Getter functions
 	 */

 	/*
 	 * Get ACL by string
 	 */
 	public function getACLStr()
 	{
 		foreach ($this->topology as $key => $tmp) {
	  		if (isset($key) && $key) {
		  		if ($this->topologyStr != "")
		  			$this->topologyStr .= ", ";
		  		$this->topologyStr .= "'".$key."'";
	  		}
	  	}
	  	unset($key);
	  	if (!$this->topologyStr) {
	  		$this->topologyStr = "\'\'";
	  	}
 	}

 	/*
 	 *  Access groups Getter
 	 */
 	public function getAccessGroups() {
 		return ($this->accessGroups);
 	}

 	/*
 	 *  Access groups string Getter
 	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getAccessGroupsString($flag = null, $escape = true) {
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->accessGroups as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch ($flag) {
 				case "ID" :
 				    $string .= "'".$key."'";
 				    break;
 				case "NAME" :
 				    if ($escape === true) {
 				        $string .= "'".CentreonDB::escape($value)."'";
 				    } else {
 				        $string .= "'".$value."'";
 				    }
 				    break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "'0'";
 		}
 		return $string;
 	}

 	/*
 	 *  Resource groups Getter
 	 */
 	public function getResourceGroups()
 	{
 		return $this->resourceGroups;
 	}

 	/*
 	 *  Resource groups string Getter
 	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getResourceGroupsString($flag = null, $escape = true)
 	{
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->resourceGroups as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch($flag) {
 				case "ID" :
 				    $string .= "'".$key."'";
 				    break;
 				case "NAME" :
 				    if ($escape === true) {
 				        $string .= "'".CentreonDB::escape($value)."'";
 				    } else {
 				        $string .= "'".$value."'";
 				    }
 				    break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
 	 *  Hostgroups Getter
 	 */
 	public function getHostGroups($flag = null)
 	{
 		$this->checkUpdateACL();
 		if (isset($flag) && $flag == "ALIAS") {
 			return $this->hostGroupsAlias;
 		}
 		return $this->hostGroups;
 	}


	/*
 	 *  Poller Getter
 	 */
 	public function getPollers()
 	{
 		return $this->pollers;
 	}

 	/*
 	 *  Hostgroups string Getter
  	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getHostGroupsString($flag = null)
 	{
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->hostGroups as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch($flag) {
 				case "ID" : $string .= "'".$key."'"; break;
 				case "NAME" : $string .= "'".$value."'"; break;
 				case "ALIAS" : $string .= "'".addslashes($this->hostGroupsAlias[$key])."'"; break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
 	 *  Poller string Getter
  	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getPollerString($flag = null, $escape = true)
 	{
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->pollers as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch ($flag) {
 				case "ID" : $string .= "'".$key."'"; break;
 				case "NAME" :
 				    if ($escape === true) {
 				        $string .= "'".CentreonDB::escape($value)."'";
 				    } else {
 				        $string .= "'".$value."'";
 				    }
 				    break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
 	 *  Service groups Getter
 	 */
 	public function getServiceGroups()
 	{
 		return $this->serviceGroups;
 	}

 	/*
 	 *  Service groups string Getter
   	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getServiceGroupsString($flag = null, $escape = true)
 	{
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->serviceGroups as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch ($flag) {
 				case "ID" :
 				    $string .= "'".$key."'";
 				    break;
 				case "NAME" :
 				    if ($escape === true) {
 				        $string .= "'".CentreonDB::escape($value)."'";
 				    } else {
 				        $string .= "'".$value."'";
 				    }
 				    break;
 				case "ALIAS" :
 				    $string .= "'".$this->serviceGroupsAlias[$key]."'";
 				    break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
 	 *  Service categories Getter
 	 */
 	public function getServiceCategories()
 	{
 		return $this->serviceCategories;
 	}

 	/*
 	 *  Service categories string Getter
  	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getServiceCategoriesString($flag = null, $escape = true)
 	{
 		$string = "";
 		$i = 0;
 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		foreach ($this->serviceCategories as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			switch($flag) {
 				case "ID" :
 				    $string .= "'".$key."'";
 				    break;
 				case "NAME" :
 				    if ($escape === true) {
 				        $string .= "'".CentreonDB::escape($value)."'";
 				    } else {
 				        $string .= "'".$value."'";
 				    }
 				    break;
 				default : $string .= "'".$key."'"; break;
 			}
 			$i++;
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
 	 *  Hosts string Getter
   	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getHostsString($flag = null, $pearDBndo, $escape = true)
 	{
 		$this->checkUpdateACL();

 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		$string = "";
 		$i = 0;
 		$groupIds = array_keys($this->accessGroups);
 		if (count($groupIds)) {
 		    $query = "SELECT DISTINCT host_name, host_id
 		    		  FROM centreon_acl
 		    		  WHERE group_id IN (".implode(',', $groupIds).")
 		    		  GROUP BY host_name, host_id
                      ORDER BY host_name ASC";
 			$DBRES = $pearDBndo->query($query);
 			while ($row = $DBRES->fetchRow()) {
 				if ($i) {
 					$string .= ", ";
 				}
 				switch ($flag) {
 					case "ID" :
 					    $string .= "'".$row['host_id']."'";
 					    break;
 					case "NAME" :
 					    if ($escape === true) {
 					        $string .= "'".CentreonDB::escape($row['host_name'])."'";
 					    } else {
 					        $string .= "'".$row['host_name']."'";
 					    }
 					    break;
 					default : $string .= "'".$row['host_id']."'"; break;
 				}
 				$i++;
 			}
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

  	/*
 	 *  Services string Getter
   	 *  Possible flags :
 	 *  - ID => will return the id's of the element
 	 *  - NAME => will return the names of the element
 	 */
 	public function getServicesString($flag = null, $pearDBndo, $escape = true)
 	{
 		$this->checkUpdateACL();

 		if (!isset($flag)) {
 			$flag = "ID";
 		}
 		$flag = strtoupper($flag);
 		$string = "";
 		$i = 0;
 		$groupIds = array_keys($this->accessGroups);
 		if (count($groupIds)) {
 		    $query = "SELECT DISTINCT service_id, service_description
 		    		  FROM centreon_acl
 		    		  WHERE group_id IN (".implode(',', $groupIds).")";
 			$DBRES = $pearDBndo->query($query);
 			while ($row = $DBRES->fetchRow()) {
 				if ($i) {
 					$string .= ", ";
 				}
 				switch ($flag) {
 					case "ID" :
 					    $string .= "'".$row['service_id']."'";
 					    break;
 					case "NAME" :
 					    if ($escape === true) {
 					        $string .= "'".CentreonDB::escape($row['service_description'])."'";
 					    } else {
 					        $string .= "'".$row['service_description']."'";
 					    }
 					    break;
 					default : $string .= "'".$row['service_id']."'"; break;
 				}
 				$i++;
 			}
 		}
 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

        /**
         * Get authorized host service ids
         * 
         * @param $db CentreonDB
         * @return string | return id combinations like '14_26' (hostId_serviceId)
         */
        public function getHostServiceIds($db) {
            $this->checkUpdateACL();
            $groupIds = array_keys($this->accessGroups);
            $string = "";
            if (count($groupIds)) {
                $query = "SELECT DISTINCT host_id, service_id
                          FROM centreon_acl
 		    	  WHERE group_id IN (".implode(',', $groupIds).")";
                $res = $db->query($query);
                while ($row = $res->fetchRow()) {
                    if ($string != "") {
                        $string .= ", ";
                    }
                    $string .= "'".$row['host_id']."_".$row['service_id']."'"; 
 		}
            }
            if ($string == "") {
                $string = "''";
            }
            return $string;
        }
        
 	/*
 	 *  Actions Getter
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
 	 *
 	 * Update topologystr value
 	 */
 	public function updateTopologyStr() {
		$this->setTopology();
 		$this->topologyStr = $this->getTopologyString();
 	}


 	public function getTopologyString()
 	{
 		$this->checkUpdateACL();
 		$string = "";
 		$i = 0;

 		foreach ($this->topology as $key => $value) {
 			if ($i) {
 				$string .= ", ";
 			}
 			$string .= "'".$key."'";
 			$i++;
 		}

 		if (!$i) {
 			$string = "''";
 		}
 		return $string;
 	}

 	/*
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
 		$str .= " " . $condition . " " . $field . " IN (".$stringlist.") ";
 		return $str;
 	}


	/*
	 *  Function that returns
	 *  1 : if user is allowed to access the page
	 *  0 : if user is NOT allowed to access the page
	 */
	 public function page($p)
	 {
	 	$this->checkUpdateACL();
	 	if ($this->admin) {
	 		return 1;
	 	} elseif (isset($this->topology[$p])) {
			return $this->topology[$p];
	 	}
	 	return 0;
	 }

	 /*
	  *  Function that checks if the user can execute the action
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

	 /*
	  *  Function that returns the pair host/service by ID if $host_id is NULL
	  *  Otherwise, it returns all the services of a specific host
	  *
	  */
	 public function getHostServices($DB, $host_id = null)
	 {
		global $pearDB;

		$tab = array();
		if (!isset($host_id)) {
			if ($this->admin) {
				$query = "SELECT s.service_id, h.host_id FROM host_service_relation hsr, host h, service s " .
						"WHERE hsr.service_service_id = s.service_id " .
						"AND s.service_activate = '1' " .
						"AND hsr.host_host_id = h.host_id " .
						"AND h.host_activate = '1'";
				$DBRESULT = $pearDB->query($query);
				while ($row = $DBRESULT->fetchRow()) {
					$tab[$row['host_id']][$row['service_id']] = 1;
				}
				$DBRESULT->free();
			} else {
				$query = "SELECT host_id, service_id FROM centreon_acl WHERE group_id IN (".$this->getAccessGroupsString().")";
				$DBRESULT = $DB->query($query);
				while ($row = $DBRESULT->fetchRow()) {
					$tab[$row['host_id']][$row['service_id']] = 1;
				}
				$DBRESULT->free();
			}
		} else {
			if ($this->admin) {
				$query = "SELECT s.service_id, s.service_description, h.host_id FROM host_service_relation hsr, host h, service s " .
						"WHERE hsr.service_service_id = s.service_id " .
						"AND s.service_activate = '1' " .
						"AND hsr.host_host_id = h.host_id " .
						"AND h.host_activate = '1' " .
						"AND h.host_id = '".$host_id."'";
				$DBRESULT = $pearDB->query($query);
				while ($row = $DBRESULT->fetchRow()) {
					$tab[$row['service_id']] = $row['service_description'];
				}
				$DBRESULT->free();

				/*
				 * Get Services attached to hostgroups
				 */
				$DBRESULT = $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
						" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
						" AND service_id = hsr.service_service_id");
				while ($elem = $DBRESULT->fetchRow()){
					$tab[$elem["service_id"]]	= html_entity_decode($elem["service_description"], ENT_QUOTES, "UTF-8");
				}
				$DBRESULT->free();

			} else {
				$query = "SELECT service_id, service_description FROM centreon_acl WHERE host_id = '".$host_id."' AND group_id IN (".$this->getAccessGroupsString().")";
				$DBRESULT = $DB->query($query);
				while ($row = $DBRESULT->fetchRow()) {
					$tab[$row['service_id']] = $row['service_description'];
				}
				$DBRESULT->free();
			}
		}
		return $tab;
	 }

	 /*
	  *  Function that returns the pair host/service by NAME if $host_name is NULL
	  *  Otherwise, it returns all the services of a specific host
	  *
	  */
	 public function getHostServicesName($pearDBndo, $host_name = null)
	 {
		$tab = array();
		if (!isset($host_name)) {
			if ($this->admin) {
				$query = "SELECT DISTINCT host_name, service_description FROM centreon_acl ORDER BY host_name";
			} else {
				$query = "SELECT host_name, service_description FROM centreon_acl WHERE group_id IN (".$this->getAccessGroupsString().") ORDER BY host_name";
			}
			$DBRESULT = $pearDBndo->query($query);
			while ($row = $DBRESULT->fetchRow()) {
				$tab[$row['host_name']][$row['service_description']] = 1;
			}
			$DBRESULT->free();
		} else {
			if ($this->admin) {
				$query = "SELECT service_id, service_description FROM centreon_acl WHERE host_name = '".$host_name."'";
			} else {
				$query = "SELECT service_id, service_description FROM centreon_acl WHERE host_name = '".$host_name."' AND group_id IN (".$this->getAccessGroupsString().")";
			}
			$DBRESULT = $pearDBndo->query($query);
			while ($row = $DBRESULT->fetchRow()) {
				$tab[$row['service_id']] = $row['service_description'];
			}
			$DBRESULT->free();
		}

		return $tab;
	 }


	 /*
	  *  Function  that returns the hosts of a specific hostgroup
	  */
	 public function getHostgroupHosts($hg_id, $pearDBndo)
	 {
	 	global $pearDB;

	 	$tab = array();
	 	$query = "SELECT h.host_id, h.host_name " .
	 			"FROM hostgroup_relation hgr, host h " .
	 			"WHERE hgr.hostgroup_hg_id = '".$hg_id."' " .
	 			"AND hgr.host_host_id = h.host_id " .
	 			$this->queryBuilder("AND", "h.host_id",  $this->getHostsString("ID", $pearDBndo)).
                " ORDER BY h.host_name ";

	 	$DBRESULT = $pearDB->query($query);
	 	while ($row = $DBRESULT->fetchRow()) {
	 		$tab[$row['host_id']] = $row['host_name'];
	 	}
	 	return ($tab);
	 }

	 /*
	  * Function that sets the changed flag to 1 for the cron centAcl.php
	  */
	 public function updateACL()
	 {
		global $pearDB;

		$DBRESULT = $pearDB->query("UPDATE `acl_resources` SET `changed` = '1'");
	}

	/*
	 * Funtion that return only metaservice table
	 */
	public function getMetaServices()
	{
		return $this->metaServices;
	}

	/*
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
}
?>