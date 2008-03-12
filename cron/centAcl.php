<?php
	
	$path = "/srv/centreon";
	
	include_once("DB.php");
	include($path."/etc/centreon.conf.php");
	include_once($path."/www/DBconnect.php");
	include_once($path."/www/DBNDOConnect.php");
	include_once($path."/www/include/common/common-Func.php");
	include_once($path."/www/include/common/common-Func-ACL.php");
	
	
	/*
	 * Purge datas
	 */
	$DBRESULT =& $pearDBndo->query("TRUNCATE TABLE `centreon_acl`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	/*
	 * Begin to create topology of hosts/services
	 */
		  	
	$DBRESULT =& $pearDB->query("SELECT acl_group_id FROM `acl_groups` WHERE acl_group_activate = '1'");
	while ($res = $DBRESULT->fetchRow()){
		$group_id = $res["acl_group_id"];
//		print "-----------$group_id-------------\n";
		$DBRESULT2 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations WHERE acl_group_id = '".$group_id."'");			
		$Host = array();
		while ($res2 = $DBRESULT2->fetchRow()){
//			print "###################################\n";
			
			$DBRESULT3 =& $pearDB->query("SELECT host_id, host_name FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id AND host.host_register = '1'");
		  	while ($h = $DBRESULT3->fetchRow()){
				$Host[$h["host_id"]] = $h["host_name"];
		  	}
			$DBRESULT3 =& $pearDB->query("SELECT hg_id FROM `hostgroup`, `acl_resources_hg_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup = $DBRESULT3->fetchRow()){
				$DBRESULT4 =& $pearDB->query("SELECT host_host_id, host_name FROM `hostgroup_relation`, `host` WHERE hostgroup_hg_id = '".$hostgroup["hg_id"]."' AND host.host_id = hostgroup_relation.host_host_id");
	  			while ($host_hostgroup = $DBRESULT4->fetchRow())
					$Host[$host_hostgroup["host_host_id"]] = $host_hostgroup["host_name"];
	  		}
	  		
	  		$DBRESULT3 =& $pearDB->query("SELECT host_id FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_hostex_relations.host_host_id = host.host_id");
			if ($DBRESULT3->numRows())
		  		while ($h = $DBRESULT3->fetchRow())
					if (isset($Host[$h["host_id"]]))
						unset($Host[$h["host_id"]]);

			$strBegin = "INSERT INTO `centreon_acl` ( `host_name` , `service_description` , `group_id` ) VALUES ";

			foreach ($Host as $key => $value){
//				print "Hosts : ".$value." -> $key  ($group_id)\n";
				$tab = getAuthorizedServicesHost($key, $group_id);
				$str = "";
				foreach ($tab as $desc => $id){
					if ($str)
						$str .= ", ";
					$str .= "('".$value."', '".$desc."', ".$group_id.") ";
//					print "SVC : $desc \n";
				}
				if ($str){
					$DBRESULTNDO =& $pearDBndo->query($strBegin.$str);
					if (PEAR::isError($DBRESULTNDO))
						print "DB Error : ".$DBRESULTNDO->getDebugInfo()."<br />";
				}
			}
		}
	}
?>