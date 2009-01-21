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
	 * Set flag for updating ACL to true
	 *
	 * <code>
	 * updateACL();
	 * </code>
	 *
	 */
	
	function updateACL(){
		global $pearDB;

		$DBRESULT = $pearDB->query("UPDATE `acl_resources` SET `changed` = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	
	/*
	 * get Service group list in array
	 *
	 * <code>
	 * $aclSG = getLCASG($pearDB) 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}pear DB connector
	 * @return{TAB}array{TAB}list SG
	 */
	
	function getLCASG($pearDB){
		if (!$pearDB)
			return ;
		
		/*
		 * Get Groups List
		 */	
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
				
		$condition = "";
		if ($str != "")
			$condition = " WHERE `acl_group_id` IN (".$str.") AND ";			
		else
			$condition = " WHERE ";
		$DBRESULT =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
		$lcaServiceGroup = array();
		while ($res =& $DBRESULT->fetchRow()){

			$DBRESULT2 =& $pearDB->query("SELECT `acl_resources_sg_relations`.`sg_id`, `sg_alias` FROM `servicegroup`, `acl_resources_sg_relations` WHERE `acl_res_id` = '".$res["acl_res_id"]."' AND `acl_resources_sg_relations`.`sg_id` = `servicegroup`.`sg_id`");	
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($serviceGroup =& $DBRESULT2->fetchRow())
				$lcaServiceGroup[$serviceGroup["sg_id"]] = $serviceGroup["sg_alias"];
			$DBRESULT2->free();
		
		}
		$DBRESULT->free();
		return $lcaServiceGroup;
	}
	
	
	/*
	 * Get list by name of host authorized by ACL  
	 *
	 * <code>
	 * $aclHostByName = getLCAHostByName($pearDB);
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}Pear DB connector
	 * @return{TAB}array{TAB}List of hosts
	 */
	
	function getLCAHostByName($pearDB){
		if (!$pearDB)
			return ;
		/*
		 * Get Groups list
		 */	
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
		
		$condition = "";
		if ($str != "")
			$condition = " WHERE `acl_group_id` IN (".$str.") AND ";			
		else
			$condition = " WHERE ";
		$DBRESULT2 =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
		
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
			$DBRESULT3 =& $pearDB->query(	"SELECT hg_id, hg_name " .
											"FROM `hostgroup`, `acl_resources_hg_relations` " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
											"AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT3->fetchRow()){
	  			$DBRESULT4 =& $pearDB->query("SELECT host.host_id, host.host_name FROM `host`, `hostgroup_relation` WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup =& $DBRESULT4->fetchRow())
					$lcaHost[$host_hostgroup["host_name"]] = $host_hostgroup["host_id"];
				$lcaHostGroup[$hostgroup["hg_name"]] = $hostgroup["hg_id"];	
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
	 * Get list by name of host and hostgroup by Alias 
	 *  authorized by ACL  
	 *
	 * <code>
	 * $aclHostByName = getLCAHostByAlias($pearDB);
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}Pear DB connector
	 * @return{TAB}array{TAB}List of hosts
	 */
	
	function getLCAHostByAlias($pearDB){
		if (!$pearDB)
			return ;
		/*
		 * Get Groups list
		 */	
		$groups = getGroupListofUser($pearDB);
		$str 	= groupsListStr($groups);
		
		$condition = "";
		if ($str != "")
			$condition = " WHERE `acl_group_id` IN (".$str.") AND ";			
		else
			$condition = " WHERE ";
		$DBRESULT2 =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
		
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
	 * Get Group list of an user
	 *
	 * <code>
	 * $grouplist = getGroupListofUser($pearDB)
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}pear db connector
	 * @return{TAB}array{TAB}group list
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
		$DBRESULT =& $pearDB->query("SELECT `user_id` FROM `session` WHERE `session_id` = '".$uid."'");
		$user =& $DBRESULT->fetchRow();
		$DBRESULT->free();

		/*
		 * Get Groups
		 */
		$groups = array();
		$DBRESULT =& $pearDB->query("SELECT agcr.`acl_group_id` FROM `acl_group_contacts_relations` agcr, `acl_groups` ag WHERE agcr.`contact_contact_id` = '".$user["user_id"]."' AND agcr.`acl_group_id` = ag.acl_group_id AND ag.`acl_group_activate` = '1'");
  		if ($num = $DBRESULT->numRows()){
			while ($group =& $DBRESULT->fetchRow())
				$groups[$group["acl_group_id"]] = $group["acl_group_id"];
			$DBRESULT->free();
  		}

  		/*
  		 * Free
  		 */
  		unset($user);
		return $groups;
	}

	/*
	 * return group list in str list separated by ","
	 *
	 * <code>
	 * $grouplist = getGroupListStrofUser($pearDB);
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}pear db connector
	 * @return{TAB}str{TAB}group list
	 */
	

	function getGroupListStrofUser($pearDB){
		if (!$pearDB)
			return ;
		getGroupListStrofUser($pearDB);
		return groupsListStr($groups);
	}
	
	/*
	 * return a group list in array to a group list in str
	 *
	 * <code>
	 * $grouplistStr = groupsListStr($groups)
	 * </code>
	 *
	 * @param{TAB}array{TAB}$group{TAB}group list array
	 * @return{TAB}str{TAB}group list
	 */
	
	function groupsListStr($groups){
		$str = '';
		if (count($groups))
			foreach ($groups as $group_id) {
				if ($str != "")
					$str .= ", ";
				$str .= $group_id;
			}
		else
			$str = "'-1'";
		return $str;	
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
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
			$condition = " WHERE `acl_group_id` IN (".$str.") AND ";			
		else
			$condition = " WHERE ";
		$DBRESULT2 =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
		
		while ($res =& $DBRESULT2->fetchRow()){
  			/*
  			 * Hosts
  			 */
  			$flag_all_host = 1;
  			$host = array();
  			$DBRESULT3 =& $pearDB->query("SELECT host_name, host_id FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id");
	  		while ($host =& $DBRESULT3->fetchRow()) 
				if ($host["host_id"] != "") {
					$lcaHost[$host["host_id"]] = $host["host_id"];
					$flag_all_host = 0;
				}
			unset($DBRESULT3);
			/*
			 * Hosts Groups Inclus
			 */
			$hostgroup = array();
			$DBRESULT3 =& $pearDB->query(	"SELECT hg_id, hg_name " .
											"FROM `hostgroup`, `acl_resources_hg_relations` " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
											"AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT3->fetchRow()){
	  			$DBRESULT4 =& $pearDB->query("SELECT host.host_id, host.host_name FROM `host`, `hostgroup_relation` WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup =& $DBRESULT4->fetchRow()) {
					$lcaHost[$host_hostgroup["host_id"]] = $host_hostgroup["host_id"];
	  				$flag_all_host = 0;
	  			}
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
			 *  Service categories			 
			 */
			$host = array();
			if ($flag_all_host) {				
				$DBRESULT3 =& $pearDB->query(	"SELECT service_service_id FROM `acl_resources_sc_relations`,  `service_categories_relation`  " .
												"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
												"AND service_categories_relation.sc_id = acl_resources_sc_relations.sc_id");
				
				$tmpSTR = "";
				while ($svc =& $DBRESULT3->fetchRow()) {
					if ($tmpSTR != "")
						$tmpSTR .= ",";					
					$tmpSTR .= $svc["service_service_id"];
				}
				if ($tmpSTR != "") {
					$DBH =& $pearDB->query ("SELECT host_host_id FROM `host_service_relation` WHERE service_service_id IN (".$tmpSTR.")");
					if ($DBH->numRows())
			  			while ($host =& $DBH->fetchRow()){
							$lcaHost[$host["host_host_id"]] = $host["host_host_id"];
			  			}
					unset($DBRESULT3);
				}
			}
			
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getAuthorizedCategories($groupstr, $res_id = NULL){
		global $pearDB;
		
		if (strlen($groupstr) == 0)
			return array();
			
		$tab_categories = array();		
		if ($res_id == NULL){			
			$request = "SELECT sc_id " .
						"FROM acl_resources_sc_relations, acl_res_group_relations, acl_resources " .
						"WHERE acl_resources_sc_relations.acl_res_id = acl_res_group_relations.acl_res_id " .
						"AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id ".
						"AND acl_resources.acl_res_activate = '1' " .
						"AND acl_res_group_relations.acl_group_id IN (".$groupstr.") ";			
		}
		else {
			$DBRES =& $pearDB->query("SELECT acl_res_id FROM `acl_resources` WHERE acl_res_id = '".$res_id."' AND acl_res_activate = '1'");
			
			if (!$DBRES->numRows())
				return array();
			$request = "SELECT sc_id " .
						"FROM acl_resources_sc_relations, acl_res_group_relations " .
						"WHERE acl_resources_sc_relations.acl_res_id = acl_res_group_relations.acl_res_id " .
						"AND acl_res_group_relations.acl_group_id IN (".$groupstr.") " .
						"AND acl_resources_sc_relations.acl_res_id = '$res_id'";				
		}		
		$DBRESULT =& $pearDB->query($request);
		while ($res =& $DBRESULT->fetchRow())			
			$tab_categories[$res["sc_id"]] = $res["sc_id"];		
	  	unset($res);
	  	unset($DBRESULT);		
	  	return $tab_categories;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getServiceTemplateList2($service_id = NULL)	{
		global $pearDB;

		if (!$service_id) 
			return;
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getServicesCategories($str){
		global $pearDB;
		
		$tab = array();
		$DBRESULT =& $pearDB->query("SELECT `sc_id` FROM `service_categories_relation` WHERE `service_service_id` IN (".$str.")");
		while ($res =& $DBRESULT->fetchRow())
			$tab[$res["sc_id"]] = $res["sc_id"];
		unset($res);		
		unset($DBRESULT);
		return $tab;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getLCASGForHost($pearDB, $host_id = NULL, $groupstr = NULL){
		if (!$pearDB || !isset($host_id))
			return ;

		if ($groupstr == NULL){
			$groups = getGroupListofUser($pearDB);
			$groupstr = groupsListStr($groups);
		}		

		/*
		 * Init Acl Table
		 */
		$svc = array();
		
		$str_topo = "";
		$condition = "";		
		
		$condition = "";
		if ($groupstr != "")
			$condition = " WHERE `acl_group_id` IN (".$groupstr.") AND ";			
		else
			$condition = " WHERE ";
		$DBRESULT =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
				
		while ($res =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDB->query(	"SELECT `service_service_id` " .
											"FROM `servicegroup`, `acl_resources_sg_relations`, `servicegroup_relation` " .
											"WHERE `acl_res_id` = '".$res["acl_res_id"]."' " .
													"AND `acl_resources_sg_relations`.`sg_id` = `servicegroup`.`sg_id` " .
													"AND `servicegroup_relation`.`servicegroup_sg_id` = `servicegroup`.`sg_id` " .
													"AND `servicegroup_relation`.`host_host_id` = '".$host_id."'");	
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($service =& $DBRESULT2->fetchRow())
				$svc[getMyServiceName($service["service_service_id"])] = $service["service_service_id"];
			$DBRESULT2->free();
		}
		$DBRESULT->free();
		return $svc;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getAuthorizedServicesHost($host_id, $groupstr, $res_id = NULL){
		global $pearDB;
		
		$tab_svc 	= getMyHostServicesByName($host_id);

		/*
		 * Get categories
		 */
		if ($res_id == NULL) { 			
			$tab_cat    = getAuthorizedCategories($groupstr);
		} else {
			$tab_cat    = getAuthorizedCategories($groupstr, $res_id);
		}


		/*
		 * Get Service Groups
		 */
		$svc_SG 	= getLCASGForHost($pearDB, $host_id, $groupstr);
		
		$tab_services = array();
		if (count($tab_cat) || count($svc_SG)){
			if ($tab_svc) {
				foreach ($tab_svc as $svc_descr => $svc_id){
					$tmp = getServiceTemplateList2($svc_id);
					$tab = getServicesCategories($tmp);					
					foreach ($tab as $t){
						if (isset($tab_cat[$t])) {							
							$tab_services[$svc_descr] = $svc_id;
						}
					}
				}
			}
			if ($svc_SG)
				foreach ($svc_SG as $key => $value)
					$tab_services[$key] = $value;
		} else {
			$tab_services = $tab_svc;	
		}
	  	return $tab_services;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getLCASVC($lca = NULL){
		global $pearDB;
		
		if (!$lca)
			return array();
		
		$groups 	= getGroupListofUser($pearDB);
		$groupstr 	= groupsListStr($groups);
		
		foreach ($lca["LcaHost"] as $key => $value) {
			$host = array();
			$host["id"] = $value;
			$host["svc"] = getAuthorizedServicesHost($value, $groupstr);
			$lca["LcaHost"][$key] =	$host;	
		}
		$SG = getLCASG($pearDB);
		
		$str = "";
		foreach ($SG as $key => $value){
			if (strlen($str))
				$str .= ", ";
			$str .= "'".$key."'";
		}
		if (strlen($str)){
			$DBRESULT =& $pearDB->query("SELECT host_host_id, service_service_id FROM servicegroup_relation WHERE servicegroup_sg_id IN ($str) ");
			if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				while ($service =& $DBRESULT->fetchRow()){
					//print "TEST : ".getMyHostName($service["host_host_id"]). " _> " .getMyServiceName($service["service_service_id"])."\n"; 
					if (isset($lca["LcaHost"][getMyHostName($service["host_host_id"])])){
						$lca["LcaHost"][getMyHostName($service["host_host_id"])]["svc"][getMyServiceName($service["service_service_id"])] = $service["service_service_id"];
					} else {
						$lca["LcaHost"][getMyHostName($service["host_host_id"])] = array();
						$lca["LcaHost"][getMyHostName($service["host_host_id"])]["id"] = $service["host_host_id"];
						$lca["LcaHost"][getMyHostName($service["host_host_id"])]["svc"][getMyServiceName($service["service_service_id"])] = $service["service_service_id"];
					}
				}
			}
		return $lca;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
		
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
		
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function getLCASGStrByName($lcaServiceGroup){
		$lcaSGStr = "";
	  	foreach ($lcaServiceGroup as $key => $value){
	  		if ($lcaSGStr) 
	  			$lcaSGStr .= ", ";
	  		$lcaSGStr .= "'".$value."'";
	  	}
	  	if (!$lcaSGStr) 
	  		$lcaSGStr = '\'\'';
		return $lcaSGStr;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
	function isUserAdmin($sid = NULL){
		if (!isset($sid))
			return ;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_admin, contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		
		$DBRESULT =& $pearDB->query("SELECT count(*) FROM `acl_group_contacts_relations` WHERE contact_contact_id = '".$admin["contact_id"]."'");
		$admin2 =& $DBRESULT->fetchRow();
		$DBRESULT->free();

		if ($admin["contact_admin"])
			return 1 ;
		else if (!$admin2["count(*)"])
			return 1;
		return 0;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
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
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	
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
		$DBRESULT =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar WHERE argr`acl_group_id` IN ($str) AND argr.acl_res_id = ar.acl_res_id AND ar.acl_res_activate = '1'");
		while ($res =& $DBRESULT->fetchRow())
			$tab_res[$res["acl_res_id"]] = $res["acl_res_id"];
		$DBRESULT->free();
		unset($str);
		if (count($tab_res))
			return $tab_res;
		return array();
	}
	
	/*
	 * function getActionsACLList
	 * This function is designed to return a list of actions allowed for an user
	 *
	 * <code>
	 *		$authorized_actions = array();
	 *		$authorized_actions = getActionsACLList($GroupListofUser);
	 * </code>
	 *
	 * Input is a list of ACL groups of user
	 * @param	array ( [0] => Group1 [1] => Groupx ) 
	 * 
	 * Output is a list of actions allowed for a user like:
	 * @return array ( [0] => service_notifications [1] => service_schedule_check [2] => service_schedule_downtime [3] => ... )
	 */
	 
	function getActionsACLList($GroupListofUser){	
		global $pearDB;		
		
		$i = 0;
		$idsRequest = ""; 
		/*
		 * Formating a variable in order to include on the request
		 */
		foreach ($GroupListofUser as $id) {
			if (strlen($idsRequest))
				$idsRequest .= " OR ";
			$idsRequest .= "`acl_group_id` = ".$id."";
			$i++;
		}
		
		if ($idsRequest != "") {
			/*
			 * Request in order to list the 'acl_action_id' linked with groups of user
			 */
			$request = "SELECT acl_action_id FROM `acl_group_actions_relations` WHERE ".$idsRequest;
			$DBRESULT =& $pearDB->query($request);
			if (PEAR::isError($DBRESULT)) 
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			
			$idsActions = array();
			while ($rule =& $DBRESULT->fetchRow()){
				$idsActions[] = $rule["acl_action_id"];
			}
		
			$i = 0;
			$idsRequest = "";
			# Formating a variable in order to include on the request
			foreach ($idsActions as $id) {
				if (strlen($idsRequest))
					$idsRequest .= " OR ";
				$idsRequest .= "`acl_action_id` = ".$id."";
				$i++;
			}
			unset($idsActions);
			
			# Request in order to list Actions Access enabled
			if ($idsRequest != "") {
				$request = "SELECT acl_action_id FROM `acl_actions` WHERE acl_action_activate = '1' AND ($idsRequest)";			
				$DBRESULT =& $pearDB->query($request);
				if (PEAR::isError($DBRESULT)) 
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				$idsActions = array();
				while ($rule =& $DBRESULT->fetchRow()) {
					$idsActions[] =  $rule["acl_action_id"];
				}
			} else {
				$idsActions = 0;	
			}
						
			$i = 0;
			$idsRequest = "";
			# Formating a variable in order to include on the request
			foreach ($idsActions as $id) {
				if (strlen($idsRequest))
					$idsRequest .= " OR ";
				$idsRequest .= "`acl_action_rule_id` = ".$id."";
				$i++;
			}
	
			if (isset($idsRequest) && $idsRequest != "") {
				# Request in order to list the 'acl_action_name' linked with rules of user		
				$request = "SELECT acl_action_name FROM `acl_actions_rules` WHERE $idsRequest";
				$DBRESULT =& $pearDB->query($request);
				if (PEAR::isError($DBRESULT)) 
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
				$authorized_actions = array();
				while ($actions =& $DBRESULT->fetchRow())
					$authorized_actions[$actions["acl_action_name"]] = $actions["acl_action_name"];
				return $authorized_actions;	
			}
		}
		return NULL;
	}
	
	/*
	 * function verifyActionsACLofUser
	 * This function is designed to verify if user is allowed to perform an action
	 *
	 * <code>
	 * 		// Un exemple with the action "host_comment"
	 *		$authorized_actions = false;
	 *		$authorized_actions = getActionsACLList("host_comment");
	 *		print $authorized_actions; // (print true or false if user is allowed or not)
	 * </code>
	 *
	 * Input is the name of the action, names are in the "DB-Func.php" of the "Actions Access" configuration interface (function listActions()).
	 * @param	string "service_checks" 
	 * 
	 * Output is true or false
	 * @return string true/false
	 */
	 
	function verifyActionsACLofUser($action_name){
		global $pearDB;	
		
		$authorisation = false;
		$authorized_actions = array();
		
		$GroupListofUser = getGroupListofUser($pearDB);
		$authorized_actions = getActionsACLList($GroupListofUser);
		if (count($authorized_actions) == 0) 
			$actions = true;
		
		if (isset($authorized_actions)) {
			foreach ($authorized_actions as $list) {
				if ($list == $action_name) 
					$authorisation = true; 
			}
		}

		return $authorisation;
	}
	
	/*
	 * --- End ---
	 */
?>