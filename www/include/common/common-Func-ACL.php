<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	
	/*
	 * LCA Generation
	 */
	
	function getLCASG($pearDB){
		if (!$pearDB)
			return ;
			
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
		
		$lcaServiceGroup = array();
		
		$str_topo = "";
		$condition = "";
		if ($str != "")
			$condition = " WHERE acl_group_id IN (".$str.")";		
		$DBRESULT =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations $condition");
		while ($res =& $DBRESULT->fetchRow()){

			$DBRESULT2 =& $pearDB->query("SELECT acl_resources_sg_relations.sg_id, sg_name FROM servicegroup, acl_resources_sg_relations WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_sg_relations.sg_id = servicegroup.sg_id");	
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($serviceGroup =& $DBRESULT2->fetchRow())
				$lcaServiceGroup[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
			$DBRESULT2->free();
		
		}
		$DBRESULT->free();
		return $lcaServiceGroup;
	}
	
	/*
	 * Return the table of host and hostgroups that user as access 
	 */
	function getLCAHostByName($pearDB){
		if (!$pearDB)
			return ;
		/*
		 * Get Groups list
		 */	
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
		
		$str_topo = "";
		$condition = "";
		if ($str != "")
			$condition = " WHERE acl_group_id IN (".$str.")";		
		$DBRESULT2 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations $condition");
		
		while ($res =& $DBRESULT2->fetchRow()){
  			/*
  			 * Hosts
  			 */
  			$host = array();
  			$DBRESULT3 =& $pearDB->query("SELECT host_name, host_id FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id");
	  		while ($host =& $DBRESULT3->fetchRow())
				if ($host["host_name"] != "")
					$lcaHost[$host["host_name"]] = $host["host_id"];
			unset($DBRESULT3);
			/*
			 * Hosts Groups Inclus
			 */
			$hostgroup =  array();
			$DBRESULT3 =& $pearDB->query(	"SELECT hg_id, hg_alias " .
											"FROM `hostgroup`, `acl_resources_hg_relations` " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
											"AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT3->fetchRow()){
	  			$DBRESULT4 =& $pearDB->query("SELECT host.host_id, host.host_name FROM `host`, `hostgroup_relation` WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup =& $DBRESULT4->fetchRow())
					$lcaHost[$host_hostgroup["host_name"]] = $host_hostgroup["host_id"];
				$lcaHostGroup[$hostgroup["hg_alias"]] = $hostgroup["hg_id"];	
	  		}
			/*
			 * Hosts Exclus
			 */
			$host = array();
			$DBRESULT3 =& $pearDB->query("SELECT host_name FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND host.host_id = acl_resources_hostex_relations.host_host_id");
	  		if ($DBRESULT3->numRows())
		  		while ($host =& $DBRESULT3->fetchRow())
					if (isset($lcaHost[$host["host_name"]]))
						unset($lcaHost[$host["host_name"]]);
			unset($DBRESULT3);
  		}
  		if (isset($host) && isset($host["host_name"]))
			$lcaHost[$host["host_name"]] = $host["host_id"];
		unset($DBRESULT2);
  		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
	}
	
	/*
	 * Return the list of groups that user is attached
	 */
	function getGroupListofUser($pearDB){
		if (!$pearDB)
			return ;
		/*
		 * Get session ID
		 */
		if (session_id() != "")
			$uid = session_id();
		if (isset($_GET["sid"])) 
			$uid = $_GET["sid"];
		else if (isset($_POST["sid"]))
			$uid = $_POST["sid"];
		else if (isset($_GET["uid"])) 
			$uid = $_GET["uid"];
		else if (isset($_POST["uid"]))
			$uid = $_POST["uid"]; 
		/*
		 * Get User
		 */			
		$DBRESULT =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$user =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		/*
		 * Get Groups
		 */
		$groups = array();
		$DBRESULT =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '".$user["user_id"]."'");
  		if ($num = $DBRESULT->numRows()){
			while ($group =& $DBRESULT->fetchRow())
				$groups[$group["acl_group_id"]] = $group["acl_group_id"];
			$DBRESULT->free();
  		}
  		/*
  		 * Free
  		 */
  		unset($user);
  		unset($res1);
		return $groups;
	}

	function getGroupListStrofUser($pearDB){
		if (!$pearDB)
			return ;
		getGroupListStrofUser($pearDB);
		return groupsListStr($groups);
	}
	
	function groupsListStr($groups){
		$str = '';
		if (count($groups))
			foreach ($groups as $group_id){
				if ($str != "")
					$str .= ", ";
				$str .= $group_id;
			}
		else
			$str = "'-1'";
		return $str;	
	}
	
	function getLCAHostByID($pearDB){
		if (!$pearDB)
			return ;
		/*
		 * Get Groups list
		 */	
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
		
		$str_topo = "";
		$condition = "";
		if ($str != "")
			$condition = " WHERE acl_group_id IN (".$str.")";		
		$DBRESULT2 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations $condition");
		
		while ($res =& $DBRESULT2->fetchRow()){
  			/*
  			 * Hosts
  			 */
  			$host = array();
  			$DBRESULT3 =& $pearDB->query("SELECT host_name, host_id FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id");
	  		while ($host =& $DBRESULT3->fetchRow())
				if ($host["host_id"] != "")
					$lcaHost[$host["host_id"]] = $host["host_id"];
			unset($DBRESULT3);
			/*
			 * Hosts Groups Inclus
			 */
			$hostgroup = array();
			$DBRESULT3 =& $pearDB->query(	"SELECT hg_id, hg_alias " .
											"FROM `hostgroup`, `acl_resources_hg_relations` " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
											"AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT3->fetchRow()){
	  			$DBRESULT4 =& $pearDB->query("SELECT host.host_id, host.host_name FROM `host`, `hostgroup_relation` WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup =& $DBRESULT4->fetchRow())
					$lcaHost[$host_hostgroup["host_id"]] = $host_hostgroup["host_id"];
				$lcaHostGroup[$hostgroup["hg_id"]] = $hostgroup["hg_id"];	
	  		}
			/*
			 * Hosts Exclus
			 */
			$host = array();
			$DBRESULT3 =& $pearDB->query("SELECT host_id FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND host.host_id = acl_resources_hostex_relations.host_host_id");
	  		if ($DBRESULT3->numRows())
		  		while ($host =& $DBRESULT3->fetchRow())
					if (isset($lcaHost[$host["host_id"]]))
						unset($lcaHost[$host["host_id"]]);
			unset($DBRESULT3);
			/*
			 * Service group hosts
			 */
			$DBRESULT3 =& $pearDB->query(	"SELECT host_host_id FROM `acl_resources_sg_relations`,  `servicegroup_relation`  " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
													"AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id");
	  		if ($DBRESULT3->numRows())
		  		while ($host =& $DBRESULT3->fetchRow()){
					$lcaHost[$host["host_host_id"]] = $host["host_host_id"];
		  		}
			unset($DBRESULT3);

  		}
  		if (isset($host) && isset($host["host_name"]))
			$lcaHost[$host["host_name"]] = $host["host_id"];
		unset($DBRESULT2);
  		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
	}
	
	function getAuthorizedCategories($groupstr){
		global $pearDB;
		
		$tab_categories = array();
		$DBRESULT =& $pearDB->query("SELECT sc_id " .
									"FROM acl_resources_sc_relations, acl_res_group_relations " .
									"WHERE acl_resources_sc_relations.acl_res_id = acl_res_group_relations.acl_res_id " .
									"AND acl_res_group_relations.acl_group_id = '".$groupstr."'");
		while ($res =& $DBRESULT->fetchRow())
			$tab_categories[$res["sc_id"]] = $res["sc_id"];
	  	unset($res);
	  	unset($DBRESULT);
	  	return $tab_categories;
	}
	
	function getServiceTemplateList2($service_id = NULL)	{
		if (!$service_id) 
			return;
		global $pearDB;
		/*
		 * Init Table of template
		 */
		$strTemplate = "'$service_id'";
		while (1)	{
			/*
			 * Get template Informations
			 */
			$DBRESULT =& $pearDB->query("SELECT service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row =& $DBRESULT->fetchRow();
			if (isset($row["service_template_model_stm_id"]) && $row["service_template_model_stm_id"]){
				if ($strTemplate)
					$strTemplate .= ', ';
				$strTemplate .= "'".$row["service_template_model_stm_id"]."'";
				$service_id = $row["service_template_model_stm_id"];
			} else
				return $strTemplate;
		}
	}
	
	function getServicesCategories($str){
		global $pearDB;
		
		$tab = array();
		$DBRESULT =& $pearDB->query("SELECT sc_id FROM `service_categories_relation` WHERE service_service_id IN (".$str.")");
		while ($res =& $DBRESULT->fetchRow())
			$tab[$res["sc_id"]] = $res["sc_id"];
		unset($res);		
		unset($DBRESULT);
		return $tab;
	}
	
	function getAuthorizedServicesHost($host_id, $groupstr){
		global $pearDB;
		
		$tab_svc 	= getMyHostServicesByName($host_id);
		$tab_cat    = getAuthorizedCategories($groupstr); 
		
		$nb = 0;
		foreach ($tab_cat as $i)
			$nb++;
			
		$tab_services = array();
		if ($nb){
			if ($tab_svc)
				foreach ($tab_svc as $svc_descr => $svc_id){
					$tmp = getServiceTemplateList2($svc_id);
					$tab = getServicesCategories($tmp);
					foreach ($tab as $t){
						if (isset($tab_cat[$t]))
							$tab_services[$svc_descr] = $svc_id;
					}
				}
		} else {
			$tab_services = $tab_svc;	
		}
	  	return $tab_services;
	}
	
	function getLCASVC($lca = NULL){
		global $pearDB;
		
		if (!$lca)
			return array();
		
		$groups 	= getGroupListofUser($pearDB);
		$groupstr 	= groupsListStr($groups);
		
		foreach ($lca["LcaHost"] as $key => $value){
			$host = array();
			$host["id"] = $value;
			$host["svc"] = getAuthorizedServicesHost($value, $groupstr);
			$lca["LcaHost"][$key] =	$host;	
		}
		return $lca;
	}
	
	function getLCASVCStr($lca = NULL){
		global $pearDB;
		
		if (!$lca)
			return array();
		
		$groups 	= getGroupListofUser($pearDB);
		$groupstr 	= groupsListStr($groups);
		$str = "";
		foreach ($lca["LcaHost"] as $key => $value){
			$host = array();
			$host["id"] = $value;
			$svc_list = getAuthorizedServicesHost($value, $groupstr);
			if (count($svc_list))
				foreach ($svc_list as $service_id){
					if ($str)
						$str .= ", ";
					$str .= $service_id;
				}			
		}
		return $str;
	}
	
	function getLCAHostStr($lcaHost){
		$lcaHStr = "";
	  	foreach ($lcaHost as $key => $value){
	  		if ($lcaHStr)
	  			$lcaHStr .= ", ";
	  		$lcaHStr .= "'".$key."'";
	  	}
	  	if (!$lcaHStr) 
	  		$lcaHStr = '\'\'';
  	  	return $lcaHStr;
	}
		
	function getLCAHGStr($lcaHostGroup){
		$lcaHGStr = "";
		foreach ($lcaHostGroup as $key => $value){
	  		if ($lcaHGStr) 
	  			$lcaHGStr .= ", ";
	  		$lcaHGStr .= "'".$key."'";
		}
	  	if (!$lcaHGStr) 
	  		$lcaHGStr = '\'\'';
	  	return $lcaHGStr;
	}
		
	function getLCASGStr($lcaServiceGroup){
		$lcaSGStr = "";
	  	foreach ($lcaServiceGroup as $key => $value){
	  		if ($lcaSGStr) 
	  			$lcaSGStr .= ", ";
	  		$lcaSGStr .= "'".$key."'";
	  	}
	  	if (!$lcaSGStr) 
	  		$lcaSGStr = '\'\'';
		return $lcaSGStr;
	}
	
	function isUserAdmin($sid = NULL){
		if (!isset($sid))
			return ;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_admin FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["contact_admin"]))
			return $admin["contact_admin"];
		return 0;
	}
	
	function getUserIdFromSID($sid = NULL){
		if (!isset($sid))
			return ;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["contact_id"]))
			return $admin["contact_id"];
		return 0;
	}
	
	function getResourceACLList($group_list){
		if (!isset($group_list))
			return ;
		global $pearDB;
		$str = "";
		foreach ($group_list as $gl){
			if ($str)
				$str .= ", ";
			$str .= $gl; 
		}	
		$tab_res = array();
		$DBRESULT =& $pearDB->query("SELECT `acl_res_id` FROM `acl_res_group_relations` WHERE `acl_group_id` IN ($str)");
		while ($res =& $DBRESULT->fetchRow())
			$tab_res[$res["acl_res_id"]] = $res["acl_res_id"];
		$DBRESULT->free();
		unset($str);
		if (count($tab_res))
			return $tab_res;
		return array();
	}
	
?>