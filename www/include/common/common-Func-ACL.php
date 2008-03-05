<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	/*
	 * LCA Generation
	 */
	
	function getLCASG($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "") $uid = $_POST["sid"] ; else $uid = session_id();
		$DBRESULT =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT->fetchInto($user);
		$user_id = $user["user_id"];	
		$lcaServiceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$user_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows())	{
			while($DBRESULT->fetchInto($contactGroup))	{
			 	$DBRESULT1 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				if (PEAR::isError($DBRESULT1))
					print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
				 if ($DBRESULT1->numRows())
					while ($DBRESULT1->fetchInto($lca))	{
						$DBRESULT2 =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup, lca_define_servicegroup_relation WHERE lca_define_lca_id = '".$lca["lca_id"]."' AND sg_id = servicegroup_sg_id");	
						if (PEAR::isError($DBRESULT2))
							print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						while ($DBRESULT2->fetchInto($serviceGroup))
							$lcaServiceGroup[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
						unset($DBRESULT2);
					}
			}
		}
		return $lcaServiceGroup;
	}
		
	function getLCAHostByName($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "")
			$uid = $_POST["sid"];
		else 
			$uid = session_id();
		$lcaHost = array();
		$lcaHostGroup = array();
		$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$res1->fetchInto($user);
		$user_id = $user["user_id"];
		
		$str = "";	
		$res1 =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '".$user_id."'");
  		if ($num = $res1->numRows())
  			while ($group = $res1->fetchRow()) {
				if ($str != "")
					$str .= ", ";
				$str .= $group["acl_group_id"];
			}
		
		$str_topo = "";
		$condition = "";
		if ($str != "")
			$condition = " WHERE acl_group_id IN (".$str.")";		
		$DBRESULT2 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations $condition");
		$host = array();
  		while ($res = $DBRESULT2->fetchRow()){
  			/*
  			 * Hosts
  			 */
  			$DBRESULT3 =& $pearDB->query("SELECT host_name, host_id FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id");
	  		while ($host = $DBRESULT3->fetchRow())
				$lcaHost[$host["host_name"]] = $host["host_id"];
			unset($DBRESULT3);
			/*
			 * Hosts Groups Inclus
			 */
			$DBRESULT3 =& $pearDB->query(	"SELECT hg_id, hg_name " .
											"FROM `hostgroup`, `acl_resources_hg_relations` " .
											"WHERE acl_res_id = '".$res["acl_res_id"]."' " .
											"AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup = $DBRESULT3->fetchRow()){
	  			$DBRESULT4 =& $pearDB->query("SELECT host.host_id, host.host_name FROM `host`, `hostgroup_relation` WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup = $DBRESULT4->fetchRow())
					$lcaHost[$host_hostgroup["host_name"]] = $host_hostgroup["host_id"];
				$LcaHHG[$hostgroup["hg_name"]] = $hostgroup["hg_id"];	
	  		}
			/*
			 * Hosts Exclus
			 */
			$DBRESULT3 =& $pearDB->query("SELECT host_name FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND host.host_id = acl_resources_hostex_relations.host_host_id");
	  		if ($DBRESULT3->numRows())
		  		while ($host = $DBRESULT3->fetchRow())
					if (isset($lcaHost[$host["host_name"]]))
						unset($lcaHost[$host["host_name"]]);
			unset($DBRESULT3);
  		}
		$lcaHost[$host["host_name"]] = $host["host_id"];
		unset($DBRESULT2);
  		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
	}
	
	function getLCAHostByID($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "")
			$uid = $_POST["sid"];
		else 
			$uid = session_id();
		$lcaHost = array();
		$lcaHostGroup = array();
		$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$res1->fetchInto($user);
		
  		$str = "";
		$res1 =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '".$user["user_id"]."'");
  		if ($num = $res1->numRows())
			while ($group = $res1->fetchRow()) {
				if ($str != "")
					$str .= ", ";
				$str .= $group["acl_group_id"];
			}
		
		$str_topo = "";
		$condition = "";
		if ($str != "")
			$condition = " WHERE acl_group_id IN (".$str.")";
		$DBRESULT2 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations $condition");
		$host = array();
  		while ($res = $DBRESULT2->fetchRow()){
			/*
			 * Hosts inclus
			 */
  			$DBRESULT3 =& $pearDB->query("SELECT host_id FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id");
	  		while ($host = $DBRESULT3->fetchRow())
				$lcaHost[$host["host_id"]] = $host["host_id"];
			/*
			 * Hosts Groups Inclus
			 */
			$DBRESULT3 =& $pearDB->query("SELECT hg_id FROM `hostgroup`, `acl_resources_hg_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup = $DBRESULT3->fetchRow()){
				$DBRESULT4 =& $pearDB->query("SELECT host_host_id FROM `hostgroup_relation` WHERE hostgroup_hg_id = '".$hostgroup["hg_id"]."'");
	  			while ($host_hostgroup = $DBRESULT4->fetchRow())
					$lcaHost[$host_hostgroup["host_host_id"]] = $host_hostgroup["host_host_id"];	
				$LcaHHG[$hostgroup["hg_id"]] = $hostgroup["hg_id"];	
	  		}
			/*
			 * Hosts Exclus
			 */
			$DBRESULT3 =& $pearDB->query("SELECT host_id FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res["acl_res_id"]."' AND acl_resources_hostex_relations.host_host_id = host.host_id");
			if ($DBRESULT3->numRows())
		  		while ($host = $DBRESULT3->fetchRow())
					if (isset($lcaHost[$host["host_id"]]))
						unset($lcaHost[$host["host_id"]]);
			unset($DBRESULT3);
  		}
		$lcaHost[$host["host_name"]] = $host["host_id"];
		unset($DBRESULT2);
		
		$LcaHHG = array();
		isset($lcaHost) ? $LcaHHG["LcaHost"] = $lcaHost : $LcaHHG["LcaHost"] = array();
		isset($lcaHostGroup) ? $LcaHHG["LcaHostGroup"] = $lcaHostGroup : $LcaHHG["LcaHostGroup"] = array();
		return $LcaHHG;
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
	
	function HadUserLca($pearDB){
		if (!$pearDB)
			return ;
		if (session_id() == "")
			$uid = $_POST["sid"];
		else 
			$uid = session_id();
		$num = 0;
		
		$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$uid."'");
		$res1->fetchInto($user);
		$res1 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user["user_id"]."'");
		$res1->fetchInto($user_status);
		if ($user_status["contact_admin"]){
			return 0;
		} else {
			$user_id = $user["user_id"];
			$res1 =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '".$user_id."'");
  			$num = $res1->numRows();
			return $num;
		}
	}
	
	function IsHostReadable($lcaHostByName, $host_name){
		global $oreon, $pearDB, $isRestreint;
		if (!isset($isRestreint))
			$isRestreint = HadUserLca($pearDB);
		if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_name])))
			return 1;
		return 0;		
	}

?>