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
 
	if (!isset ($oreon))
		exit ();
		
	function hostExists($name = NULL){
		global $pearDB;
		
		$DBRESULT =& $pearDB->query("SELECT host_host_id FROM ns_host_relation WHERE host_host_id = '".getMyHostID(trim($name))."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows() >= 1)	
			return true;
		return false;
	}
		
	function testHostExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('host_id');;

		$DBRESULT =& $pearDB->query("SELECT host_name, host_id FROM host WHERE host_name = '".htmlentities($name, ENT_QUOTES)."' AND host_register = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$host =& $DBRESULT->fetchRow();
		
		/*
		 * Modif case
		 */
		 
		if ($DBRESULT->numRows() >= 1 && $host["host_id"] == $id)	
			return true;
		/*
		 * Duplicate entry
		 */
		else if ($DBRESULT->numRows() >= 1 && $host["host_id"] != $id)
			return false;
		else
			return true;
	}
	
	function testHostTplExistence ($name = NULL)	{
		global $pearDB, $form;

		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('host_id');;
		$DBRESULT =& $pearDB->query("SELECT host_name, host_id FROM host WHERE host_name = '".htmlentities($name, ENT_QUOTES)."' AND host_register = '0'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$host =& $DBRESULT->fetchRow();
		
		/*
		 * Modif case
		 */
		
		if ($DBRESULT->numRows() >= 1 && $host["host_id"] == $id)	
			return true;
		/*
		 * Duplicate entry
		 */
		else if ($DBRESULT->numRows() >= 1 && $host["host_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableHostInDB ($host_id = null, $host_arr = array())	{
		global $pearDB, $oreon;
		
		if (!$host_id && !count($host_arr)) 
			return;
		
		if ($host_id)
			$host_arr = array($host_id=>"1");
		foreach ($host_arr as $key => $value)	{
			$DBRESULT =& $pearDB->query("UPDATE host SET host_activate = '1' WHERE host_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				
			$DBRESULT2 =& $pearDB->query("SELECT host_name FROM `host` WHERE host_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			$row = $DBRESULT2->fetchRow(); 
			$oreon->CentreonLogAction->insertLog("host", $key, $row['host_name'], "enable");
		}
	}
	
	function disableHostInDB ($host_id = null, $host_arr = array())	{
		global $pearDB, $oreon;
		if (!$host_id && !count($host_arr)) 
			return;
		
		if ($host_id)
			$host_arr = array($host_id=>"1");
		foreach ($host_arr as $key => $value)	{
			$DBRESULT =& $pearDB->query("UPDATE host SET host_activate = '0' WHERE host_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$DBRESULT2 =& $pearDB->query("SELECT host_name FROM `host` WHERE host_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			$row = $DBRESULT2->fetchRow(); 
			$oreon->CentreonLogAction->insertLog("host", $key, $row['host_name'], "disable");
		}
	}
	
	function deleteHostInDB ($hosts = array())	{
		global $pearDB, $oreon;
		
		foreach ($hosts as $key => $value)	{
			$rq = "SELECT @nbr := (SELECT COUNT( * ) FROM host_service_relation WHERE service_service_id = hsr.service_service_id GROUP BY service_service_id ) AS nbr, hsr.service_service_id FROM host_service_relation hsr, host WHERE hsr.host_host_id = '".$key."' AND host.host_id = hsr.host_host_id AND host.host_register = '1'";
			$DBRESULT = & $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			
			$DBRESULT3 =& $pearDB->query("SELECT host_name FROM `host` WHERE `host_id` = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT3))
				print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
			$hostname = $DBRESULT3->fetchRow();
			
			while ($row =& $DBRESULT->fetchRow())
				if ($row["nbr"] == 1)	{
					$DBRESULT4 =& $pearDB->query("SELECT service_description FROM `service` WHERE `service_id` = '".$row["service_service_id"]."' LIMIT 1");
					if (PEAR::isError($DBRESULT4))
						print "DB Error : ".$DBRESULT4->getDebugInfo()."<br />";
					$svcname = $DBRESULT4->fetchRow();
					
					$DBRESULT2 =& $pearDB->query("DELETE FROM service WHERE service_id = '".$row["service_service_id"]."'");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
					$oreon->CentreonLogAction->insertLog("service", $row["service_service_id"], $hostname['host_name']."/".$svcname["service_description"], "d");
				}
			
			
			$DBRESULT =& $pearDB->query("DELETE FROM host WHERE host_id = '".$key."'");
			if ($oreon->user->get_version() >= 3) {
				$DBRESULT =& $pearDB->query("DELETE FROM host_template_relation WHERE host_host_id = '".$key."'");
				$DBRESULT =& $pearDB->query("DELETE FROM on_demand_macro_host WHERE host_host_id = '".$key."'");
				$DBRESULT =& $pearDB->query("DELETE FROM contact_host_relation WHERE host_host_id = '".$key."'");
			}
			$oreon->CentreonLogAction->insertLog("host", $key, $hostname['host_name'], "d");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	/*
	 *  This function is called for duplicating a host
	 */
	function multipleHostInDB ($hosts = array(), $nbrDup = array())	{
		global $pearDB, $path, $oreon, $is_admin;
		
		foreach ($hosts as $key => $value)	{
			$DBRESULT =& $pearDB->query("SELECT * FROM host WHERE host_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row = $DBRESULT->fetchRow();
			$row["host_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "host_name" ? ($host_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "host_id")
						$fields[$key2] = $value2;
					if (isset($host_name))
						$fields["host_name"] = $host_name;
				}
				if (testHostExistence($host_name))	{
					$val ? $rq = "INSERT INTO host VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$DBRESULT =& $pearDB->query("SELECT MAX(host_id) FROM host");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(host_id)"]))	{
						
						/*
						 *  Update LCA
						 */
						if (!$is_admin){
							$group_list = getGroupListofUser($pearDB);
							$resource_list = getResourceACLList($group_list);
							if (count($resource_list)){
								foreach ($resource_list as $res_id)	{			
									$DBRESULT3 =& $pearDB->query("INSERT INTO `acl_resources_host_relations` (acl_res_id, host_host_id) VALUES ('".$res_id."', '".$maxId["MAX(host_id)"]."')");
									if (PEAR::isError($DBRESULT3))
										print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
								}
								unset($resource_list);
							}
						}
						#
						$DBRESULT =& $pearDB->query("SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						$fields["host_parents"] = "";
						while($host =& $DBRESULT->fetchRow()){
							$DBRESULT1 =& $pearDB->query("INSERT INTO host_hostparent_relation VALUES ('', '".$host["host_parent_hp_id"]."', '".$maxId["MAX(host_id)"]."')");	
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
							$fields["host_parents"] .= $host["host_parent_hp_id"] . ",";
						}
						$fields["host_parents"] = trim($fields["host_parents"], ",");
						# We need to duplicate the entire Service and not only create a new relation for it in the DB / Need Service functions
						if (file_exists($path."../service/DB-Func.php"))
							require_once($path."../service/DB-Func.php");
						else if (file_exists($path."../service/DB-Func.php"))
							require_once($path."../configObject/service/DB-Func.php");
						$hostInf = $maxId["MAX(host_id)"];
						$serviceArr = array();
						$serviceNbr = array();
						# Get all Services link to the Host
						$DBRESULT =& $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($service =& $DBRESULT->fetchRow())	{
							# If the Service is link with several Host, we keep this property and don't duplicate it, just create a new relation with the new Host
							$DBRESULT2 =& $pearDB->query("SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = '".$service["service_service_id"]."'");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
							$mulHostSv = $DBRESULT2->fetchrow();
							if ($mulHostSv["COUNT(*)"] > 1)	{
								$DBRESULT3 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$maxId["MAX(host_id)"]."', NULL, '".$service["service_service_id"]."')");
								if (PEAR::isError($DBRESULT3))
									print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
							}
							else	{
								$serviceArr[$service["service_service_id"]] = $service["service_service_id"];
								$serviceNbr[$service["service_service_id"]] = 1;
							}
						}
						# Register Host -> Duplicate the Service list
						if ($row["host_register"])
							multipleServiceInDB($serviceArr, $serviceNbr, $hostInf, 0);
						# Host Template -> Link to the existing Service Template List
						else	{
							$DBRESULT =& $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$key."'");
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
							while($svs =& $DBRESULT->fetchRow()){
								$DBRESULT1 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$maxId["MAX(host_id)"]."', NULL, '".$svs["service_service_id"]."')");
								if (PEAR::isError($DBRESULT1))
									print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
							}
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_host_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						$fields["host_cgs"] = "";
						while($Cg =& $DBRESULT->fetchRow()){
							$DBRESULT1 =& $pearDB->query("INSERT INTO contactgroup_host_relation VALUES ('', '".$maxId["MAX(host_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
							$fields["host_cgs"] .= $Cg["contactgroup_cg_id"] . ",";
						}
						$fields["host_cgs"] = trim($fields["host_cgs"], ",");
						$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($Hg =& $DBRESULT->fetchRow()){
							$DBRESULT1 =& $pearDB->query("INSERT INTO hostgroup_relation VALUES ('', '".$Hg["hostgroup_hg_id"]."', '".$maxId["MAX(host_id)"]."')");
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
						}
						$DBRESULT =& $pearDB->query("SELECT * FROM extended_host_information WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($ehi =& $DBRESULT->fetchRow())	{
							$val = null;
							$ehi["host_host_id"] = $maxId["MAX(host_id)"];
							$ehi["ehi_id"] = NULL;
							foreach ($ehi as $key2=>$value2) {
								$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
								if ($key2 != "ehi_id")
									$fields[$key2] = $value2;
							}
							$val ? $rq = "INSERT INTO extended_host_information VALUES (".$val.")" : $rq = null;
							$DBRESULT2 =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT nagios_server_id FROM ns_host_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						$fields["nagios_server_id"] = "";
						while($Hg =& $DBRESULT->fetchRow()){
							$DBRESULT1 =& $pearDB->query("INSERT INTO ns_host_relation VALUES ('".$Hg["nagios_server_id"]."', '".$maxId["MAX(host_id)"]."')");
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getDebugInfo()."<br />";
							$fields["nagios_server_id"] .= $Hg["nagios_server_id"] . ",";
						}
						$fields["nagios_server_id"] = trim($fields["nagios_server_id"], ",");
						/*
						 *  multiple templates & on demand macros
						 */
						 if ($oreon->user->get_version() >= 3)
						 {
						 	/*
						 	 * multiple templates
						 	 */
						 	$mTpRq1 = "SELECT * FROM `host_template_relation` WHERE `host_host_id` ='".$key."' ORDER BY `order`";
						 	$DBRESULT3 =& $pearDB->query($mTpRq1);
						 	if (PEAR::isError($DBRESULT3))
								print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
							$multiTP_logStr = "";
							while ($hst =& $DBRESULT3->fetchRow()) {
								$mTpRq2 = "INSERT INTO `host_template_relation` (`host_host_id`, `host_tpl_id`, `order`) VALUES" .
											"('".$maxId["MAX(host_id)"]."', '".$hst['host_tpl_id']."', '". $hst['order'] ."')";
						 		$DBRESULT4 =& $pearDB->query($mTpRq2);
						 		$multiTP_logStr .= $hst['host_tpl_id'] . ",";
						 		if (PEAR::isError($DBRESULT4))
									print "DB Error : ".$DBRESULT4->getDebugInfo()."<br />";
							}
							$multiTP_logStr = trim($multiTP_logStr, ",");
							$fields["templates"] = $multiTP_logStr;
							/*
							 * on demand macros
							 */
							$mTpRq1 = "SELECT * FROM `on_demand_macro_host` WHERE `host_host_id` ='".$key."'";
						 	$DBRESULT3 =& $pearDB->query($mTpRq1);
						 	if (PEAR::isError($DBRESULT3))
								print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
							while ($hst =& $DBRESULT3->fetchRow()) {
								$macName = str_replace("\$", "", $hst["host_macro_name"]);
								$mTpRq2 = "INSERT INTO `on_demand_macro_host` (`host_host_id`, `host_macro_name`, `host_macro_value`) VALUES" .
											"('".$maxId["MAX(host_id)"]."', '\$".$macName."\$', '". $hst['host_macro_value'] ."')";
						 		$DBRESULT4 =& $pearDB->query($mTpRq2);
						 		if (PEAR::isError($DBRESULT4))
									print "DB Error : ".$DBRESULT4->getDebugInfo()."<br />";
								$fields["_".strtoupper($macName)."_"] = $hst['host_macro_value'];
							}
						 }
						 $oreon->CentreonLogAction->insertLog("host", $maxId["MAX(host_id)"], $host_name, "a", $fields);
					}
				}
			}
		}
	}
	
	function updateHostInDB ($host_id = NULL, $from_MC = false)	{
		global $form, $oreon;		
		if (!$host_id) 
			return;
			
		$ret = $form->getSubmitValues();
	
		/*
		 *  Global function to use
		 */
		 
		if ($from_MC)
			updateHost_MC($host_id);
		else
			updateHost($host_id, $from_MC);
		
		/*
		 *  Function for updating host parents
		 *  1 - MC with deletion of existing parents
		 *  2 - MC with addition of new parents
		 *  3 - Normal update
		 */
		 
		if (isset($ret["mc_mod_hpar"]["mc_mod_hpar"]) && $ret["mc_mod_hpar"]["mc_mod_hpar"])
			updateHostHostParent($host_id);
		else if (isset($ret["mc_mod_hpar"]["mc_mod_hpar"]) && !$ret["mc_mod_hpar"]["mc_mod_hpar"])
			updateHostHostParent_MC($host_id);
		else
			updateHostHostParent($host_id);
			
		# Function for updating host childs
		# 1 - MC with deletion of existing childs
		# 2 - MC with addition of new childs
		# 3 - Normal update
		if (isset($ret["mc_mod_hch"]["mc_mod_hch"]) && $ret["mc_mod_hch"]["mc_mod_hch"])
			updateHostHostChild($host_id);
		else if (isset($ret["mc_mod_hch"]["mc_mod_hch"]) && !$ret["mc_mod_hch"]["mc_mod_hch"])
			updateHostHostChild_MC($host_id);
		else
			updateHostHostChild($host_id);
			
		# Function for updating host cg
		# 1 - MC with deletion of existing cg
		# 2 - MC with addition of new cg
		# 3 - Normal update
		if (isset($ret["mc_mod_hcg"]["mc_mod_hcg"]) && $ret["mc_mod_hcg"]["mc_mod_hcg"]) {
			updateHostContactGroup($host_id);
			updateHostContact($host_id);
		} else if (isset($ret["mc_mod_hcg"]["mc_mod_hcg"]) && !$ret["mc_mod_hcg"]["mc_mod_hcg"]) {
			updateHostContactGroup_MC($host_id);
			updateHostContact_MC($host_id);
		} else {
			updateHostContactGroup($host_id);
			updateHostContact($host_id);
		}
		# Function for updating host hg
		# 1 - MC with deletion of existing hg
		# 2 - MC with addition of new hg
		# 3 - Normal update
		if (isset($ret["mc_mod_hhg"]["mc_mod_hhg"]) && $ret["mc_mod_hhg"]["mc_mod_hhg"])
			updateHostHostGroup($host_id);
		else if (isset($ret["mc_mod_hhg"]["mc_mod_hhg"]) && !$ret["mc_mod_hhg"]["mc_mod_hhg"])
			updateHostHostGroup_MC($host_id);
		else
			updateHostHostGroup($host_id);
	
		# Function for updating host template
		# 1 - MC with deletion of existing template
		# 2 - MC with addition of new template
		# 3 - Normal update
		if (isset($ret["mc_mod_htpl"]["mc_mod_htpl"]) && $ret["mc_mod_htpl"]["mc_mod_htpl"])
			updateHostTemplateService($host_id);
		else if (isset($ret["mc_mod_htpl"]["mc_mod_htpl"]) && !$ret["mc_mod_htpl"]["mc_mod_htpl"])
			updateHostTemplateService_MC($host_id);
		else
			updateHostTemplateService($host_id);
		if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
			if (isset($ret["host_template_model_htm_id"]))
				createHostTemplateService($host_id, $ret["host_template_model_htm_id"]);
			else if($oreon->user->get_version())
				createHostTemplateService($host_id);
		}
		
		if ($from_MC)
			updateHostExtInfos_MC($host_id);
		else
			updateHostExtInfos($host_id);
			
		# Function for updating host hg
		# 1 - MC with deletion of existing hg
		# 2 - MC with addition of new hg
		# 3 - Normal update
		updateNagiosServerRelation($host_id);
	}	
	
	function insertHostInDB ($ret = array())	{
		global $oreon;
		
		$host_id = insertHost($ret);
		updateHostHostParent($host_id, $ret);
		updateHostHostChild($host_id, $ret);
		updateHostContactGroup($host_id, $ret);
		updateHostContact($host_id, $ret);
		updateHostHostGroup($host_id, $ret);
		updateHostTemplateService($host_id, $ret);
		updateNagiosServerRelation($host_id, $ret);
		updateACL();
		global $form;
		$ret = $form->getSubmitValues();		
		if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"] && $ret["host_template_model_htm_id"] && $oreon->user->get_version() < 3)
			createHostTemplateService($host_id, $ret["host_template_model_htm_id"]);
		elseif(isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"] && $oreon->user->get_version() >= 3) {			
			createHostTemplateService($host_id);
		}
		insertHostExtInfos($host_id, $ret);
		return ($host_id);
	}
	
	function insertHost($ret)	{
		global $form, $pearDB, $oreon, $is_admin;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL)		{
			$ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('/', "#S#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('\\', "#BS#", $ret["command_command_id_arg1"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		
		// For Centreon 2, we no longer need "host_template_model_htm_id" in Nagios 3
		// but we try to keep it compatible with Nagios 2 which needs "host_template_model_htm_id" 
		if (isset($_POST['nbOfSelect']) || $oreon->user->get_version() >= 3) {
			$DBRESULT =& $pearDB->query("SELECT host_id FROM `host` WHERE host_register='0' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$result = $DBRESULT->fetchRow();
			$ret["host_template_model_htm_id"] = $result["host_id"];
			$DBRESULT->free();
		}
		
		$rq = "INSERT INTO host " .
			"(host_template_model_htm_id, command_command_id, command_command_id_arg1, timeperiod_tp_id, timeperiod_tp_id2, command_command_id2, command_command_id_arg2," .
			"host_name, host_alias, host_address, host_max_check_attempts, host_check_interval, host_active_checks_enabled, " .
			"host_passive_checks_enabled, host_checks_enabled, host_obsess_over_host, host_check_freshness, host_freshness_threshold, " .
			"host_event_handler_enabled, host_low_flap_threshold, host_high_flap_threshold, host_flap_detection_enabled, " .
			"host_process_perf_data, host_retain_status_information, host_retain_nonstatus_information, host_notification_interval, " .
			"host_notification_options, host_notifications_enabled, host_first_notification_delay, host_stalking_options, host_snmp_community, " .
			"host_snmp_version, host_location, host_comment, host_register, host_activate) " .
			"VALUES ( ";
			isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != NULL ? $rq .= "'".$ret["host_template_model_htm_id"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL ? $rq .= "'".$ret["command_command_id_arg1"]."', ": $rq .= "NULL, ";
			isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
			isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".$ret["command_command_id_arg2"]."', ": $rq .= "NULL, ";
			isset($ret["host_name"]) && $ret["host_name"] != NULL ? $rq .= "'".htmlentities($ret["host_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_alias"]) && $ret["host_alias"] != NULL ? $rq .= "'".htmlentities($ret["host_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_address"]) && $ret["host_address"] != NULL ? $rq .= "'".htmlentities($ret["host_address"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != NULL ? $rq .= "'".$ret["host_max_check_attempts"]."', " : $rq .= "NULL, ";
			isset($ret["host_check_interval"]) && $ret["host_check_interval"] != NULL ? $rq .= "'".$ret["host_check_interval"]."', ": $rq .= "NULL, ";
			isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) && $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ? $rq .= "'".$ret["host_active_checks_enabled"]["host_active_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) && $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_checks_enabled"]["host_checks_enabled"]) && $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ? $rq .= "'".$ret["host_checks_enabled"]["host_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) && $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ? $rq .= "'".$ret["host_obsess_over_host"]["host_obsess_over_host"]."', ": $rq .= "'2', ";
			isset($ret["host_check_freshness"]["host_check_freshness"]) && $ret["host_check_freshness"]["host_check_freshness"] != 2 ? $rq .= "'".$ret["host_check_freshness"]["host_check_freshness"]."', ": $rq .= "'2', ";
			isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != NULL ? $rq .= "'".$ret["host_freshness_threshold"]."', ": $rq .= "NULL, ";
			isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) && $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ? $rq .= "'".$ret["host_event_handler_enabled"]["host_event_handler_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"] != NULL ? $rq .= "'".$ret["host_low_flap_threshold"]."', " : $rq .= "NULL, ";
			isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != NULL ? $rq .= "'".$ret["host_high_flap_threshold"]."', " : $rq .= "NULL, ";
			isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) && $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]."', " : $rq .= "'2', ";
			isset($ret["host_process_perf_data"]["host_process_perf_data"]) && $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ? $rq .= "'".$ret["host_process_perf_data"]["host_process_perf_data"]."', " : $rq .= "'2', ";
			isset($ret["host_retain_status_information"]["host_retain_status_information"]) && $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ? $rq .= "'".$ret["host_retain_status_information"]["host_retain_status_information"]."', " : $rq .= "'2', ";
			isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) && $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]."', " : $rq .= "'2', ";
			isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != NULL ? $rq .= "'".$ret["host_notification_interval"]."', " : $rq .= "NULL, ";
			isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_notifOpts"]))."', " : $rq .= "NULL, ";
			isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) && $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ? $rq .= "'".$ret["host_notifications_enabled"]["host_notifications_enabled"]."', " : $rq .= "'2', ";
			isset($ret["host_first_notification_delay"]) && $ret["host_first_notification_delay"] ? $rq .= "'".$ret["host_first_notification_delay"]."', " : $rq .= " NULL, ";
			isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_stalOpts"]))."', " : $rq .= "NULL, ";
			isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_community"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_version"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_location"]) && $ret["host_location"] != NULL ? $rq .= "'".htmlentities($ret["host_location"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_comment"]) && $ret["host_comment"] != NULL ? $rq .= "'".htmlentities($ret["host_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_register"]["host_register"]) && $ret["host_register"]["host_register"] != NULL ? $rq .= "'".$ret["host_register"]["host_register"]."', " : $rq .= "NULL, ";
			isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != NULL ? $rq .= "'".$ret["host_activate"]["host_activate"]."'" : $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);		
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(host_id) FROM host");
		$host_id = $DBRESULT->fetchRow();		

		/*
 		**  Insert multiple templates
 		*/
 		$multiTP_logStr = "";
 		if (isset($ret["use"]) && $ret["use"]){
 			$already_stored = array();
 			$tplTab = split(",", $ret["use"]);
 			$j = 0;
 			foreach ($tplTab as $val) {
 				$tplId = getMyHostID($val); 
 					
	 			if (!isset($already_stored[$tplId]) && $tplId) {
		 			$rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) VALUES (". $host_id['MAX(host_id)'] .", ". $tplId .", ". $j .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$multiTP_logStr .= $tplId . ",";
					$j++;
					$already_stored[$tplId] = 1;
	 			}
	 		}
 		} elseif (isset($_POST['nbOfSelect']) || $oreon->user->get_version() >= 3) { 	 		
	 		$already_stored = array();
	 		for ($i=0, $j = 1;$i <= $_POST['nbOfSelect']; $i++)
	 		{ 			
	 			$tpSelect = "tpSelect_" . $i;
	 			if (!isset($already_stored[$_POST[$tpSelect]]) && $_POST[$tpSelect]) {
		 			$rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) VALUES (". $host_id['MAX(host_id)'] .", ". $_POST[$tpSelect] .", ". $j .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$multiTP_logStr .= $_POST[$tpSelect] . ",";
					$j++;
					$already_stored[$_POST[$tpSelect]] = 1;
	 			}	 			
	 		}
 		}
 		if ($multiTP_logStr != "")
	 		$multiTP_logStr = trim($multiTP_logStr, ",");
		
		/*
		 *  Insert on demand macros
		 */
		if (isset($_POST['nbOfMacro']) || $oreon->user->get_version() >= 3) {			
			$already_stored = array(); 		
	 		for ($i=0; $i <= $_POST['nbOfMacro']; $i++)
	 		{ 			
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($_POST[$macInput]) && !isset($already_stored[$_POST[$macInput]]) && $_POST[$macInput]) {
		 			$_POST[$macInput] = str_replace("\$_HOST", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$rq = "INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`, `host_host_id`) VALUES ('\$_HOST". strtoupper($_POST[$macInput]) ."\$', '". $_POST[$macValue] ."', ". $host_id['MAX(host_id)'] .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$fields["_".strtoupper($_POST[$macInput])."_"] = $_POST[$macValue];	
					$already_stored[$_POST[$macInput]] = 1;
	 			}			
	 		}
		}
		
		/*
		 *  Update LCA
		 */
		if (!$is_admin){
			$group_list = getGroupListofUser($pearDB);
			$resource_list = getResourceACLList($group_list);
			if ($ret["host_register"]["host_register"] == 1 && count($resource_list)){
				foreach ($resource_list as $res_id)	{			
					$DBRESULT3 =& $pearDB->query("INSERT INTO `acl_resources_host_relations` (acl_res_id, host_host_id) VALUES ('".$res_id."', '".$host_id["MAX(host_id)"]."')");
					if (PEAR::isError($DBRESULT3))
						print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
				}
				unset($resource_list);
			}
		}
		#
		/*
		 *  Logs
		 */
		$fields["command_command_id"] = $ret["command_command_id"];
		$fields["command_command_id_arg1"] = $ret["command_command_id_arg1"];
		$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		$fields["command_command_id2"] = $ret["command_command_id2"];
		$fields["command_command_id_arg2"] = $ret["command_command_id_arg2"];
		$fields["host_name"] = htmlentities($ret["host_name"], ENT_QUOTES);
		$fields["host_alias"] = htmlentities($ret["host_alias"], ENT_QUOTES);
		$fields["host_address"] = htmlentities($ret["host_address"], ENT_QUOTES);
		$fields["host_max_check_attempts"] = $ret["host_max_check_attempts"];
		$fields["host_check_interval"] = $ret["host_check_interval"];
		$fields["host_active_checks_enabled"] = $ret["host_active_checks_enabled"]["host_active_checks_enabled"];
		$fields["host_passive_checks_enabled"] = $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"];
		$fields["host_checks_enabled"] = $ret["host_checks_enabled"]["host_checks_enabled"];
		$fields["host_obsess_over_host"] = $ret["host_obsess_over_host"]["host_obsess_over_host"];
		$fields["host_check_freshness"] = $ret["host_check_freshness"]["host_check_freshness"];
		$fields["host_freshness_threshold"] = $ret["host_freshness_threshold"];
		$fields["host_event_handler_enabled"] = $ret["host_event_handler_enabled"]["host_event_handler_enabled"];
		$fields["host_low_flap_threshold"] = $ret["host_low_flap_threshold"];
		$fields["host_high_flap_threshold"] = $ret["host_high_flap_threshold"];
		$fields["host_flap_detection_enabled"] = $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"];
		$fields["host_process_perf_data"] = $ret["host_process_perf_data"]["host_process_perf_data"];
		$fields["host_retain_status_information"] = $ret["host_retain_status_information"]["host_retain_status_information"];
		$fields["host_retain_nonstatus_information"] = $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"];
		$fields["host_notification_interval"] = $ret["host_notification_interval"];
		$fields["host_notifOpts"] = "";
		if (isset($ret["host_notifOpts"]))
			$fields["host_notifOpts"] = implode(",", array_keys($ret["host_notifOpts"]));
		$fields["host_notifications_enabled"] = $ret["host_notifications_enabled"]["host_notifications_enabled"];
		$fields["host_first_notification_delay"] = $ret["host_first_notification_delay"];
		$fields["host_stalOpts"] = "";
		if (isset($ret["host_stalOpts"]))
			$fields["host_stalOpts"] = implode(",", array_keys($ret["host_stalOpts"]));
		$fields["host_snmp_community"] = htmlentities($ret["host_snmp_community"], ENT_QUOTES);
		$fields["host_snmp_version"] = htmlentities($ret["host_snmp_version"], ENT_QUOTES);
		$fields["host_location"] = htmlentities($ret["host_location"], ENT_QUOTES);
		$fields["host_comment"] = htmlentities($ret["host_comment"], ENT_QUOTES);
		$fields["host_register"] = $ret["host_register"]["host_register"];
		$fields["host_activate"] = $ret["host_activate"]["host_activate"];
		$fields["templates"] = $multiTP_logStr;
		$fields["ehi_notes"] = htmlentities($ret["ehi_notes"], ENT_QUOTES);
		$fields["ehi_notes_url"] = htmlentities($ret["ehi_notes_url"], ENT_QUOTES);
		$fields["ehi_action_url"] = htmlentities($ret["ehi_action_url"], ENT_QUOTES);
		$fields["ehi_icon_image"] = htmlentities($ret["ehi_icon_image"], ENT_QUOTES);
		$fields["ehi_icon_image_alt"] = htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES);
		$fields["ehi_vrml_image"] = htmlentities($ret["ehi_vrml_image"], ENT_QUOTES);
		$fields["ehi_statusmap_image"] = htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES);
		$fields["ehi_2d_coords"] = htmlentities($ret["ehi_2d_coords"], ENT_QUOTES);
		$fields["ehi_3d_coords"] = htmlentities($ret["ehi_3d_coords"], ENT_QUOTES);
		$fields["host_parents"] = "";
		if (isset($ret["host_parents"]))
			$fields["host_parents"] = implode(",", $ret["host_parents"]);
		$fields["host_childs"] = "";
		if (isset($ret["host_childs"]))
			$fields["host_childs"] = implode(",", $ret["host_childs"]);
		$fields["host_cgs"] = "";
		if (isset($ret["host_cgs"]))
			$fields["host_cgs"] = implode(",", $ret["host_cgs"]);
		$fields["host_cs"] = "";
		if (isset($ret["host_cs"]))
			$fields["host_cs"] = implode(",", $ret["host_cs"]);
		$fields["host_svTpls"] = "";
		if (isset($ret["host_svTpls"]))
			$fields["host_svTpls"] = implode(",", $ret["host_svTpls"]);
		$fields["nagios_server_id"] = "";
		if (isset($ret["nagios_server_id"]))
			$fields["nagios_server_id"] = $ret["nagios_server_id"];
		$oreon->CentreonLogAction->insertLog("host", $host_id["MAX(host_id)"], htmlentities($ret["host_name"], ENT_QUOTES), "a", $fields);
		return ($host_id["MAX(host_id)"]);
	}	
	
	function insertHostExtInfos($host_id = null, $ret)	{
		if (!$host_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		/*
		 * Check if image selected isn't a directory
		 */
		if (isset($ret["ehi_icon_image"]) && strrchr("REP_", $ret["ehi_icon_image"]))
			$ret["ehi_icon_image"] = NULL;
		if (isset($ret["ehi_vrml_image"]) && strrchr("REP_", $ret["ehi_vrml_image"]))
			$ret["ehi_vrml_image"] = NULL;
		if (isset($ret["ehi_statusmap_image"]) && strrchr("REP_", $ret["ehi_statusmap_image"]))
			$ret["ehi_statusmap_image"] = NULL;
		/*
		 * 
		 */
		$rq = 	"INSERT INTO `extended_host_information` " .
				"( `ehi_id` , `host_host_id` , `ehi_notes` , `ehi_notes_url` , " .
				"`ehi_action_url` , `ehi_icon_image` , `ehi_icon_image_alt` , " .
				"`ehi_vrml_image` , `ehi_statusmap_image` , `ehi_2d_coords` , " .
				"`ehi_3d_coords` )" .
				"VALUES ( ";
		$rq .= "NULL, ".$host_id.", ";
		isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_vrml_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_2d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_3d_coords"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	/*
	 * Get list of host templates recursively 
	 */
	 function getHostListInUse ($hst_list, $hst) {
	 	global $pearDB;
	 		 	
	 	$str = $hst_list;
	 	$DBRESULT =& $pearDB->query("SELECT `host_tpl_id` FROM `host_template_relation` WHERE host_host_id ='".$hst."'");
	 	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	 	while ($result =& $DBRESULT->fetchRow()) {
	 		$str .= ",'" . $result['host_tpl_id'] . "'";
	 		$str = getHostListInUse ($str, $result['host_tpl_id']);
	 	}
	 	return $str;
	 }
	 
	/*
	 *  Checks if the service that is gonna be deleted is actually
	 *  associated to another host template
	 *  if yes, we do not delete the service
	 *  Function returns true if it doesn't have to be deleted, otherwise it returns false
	 */
	function serviceIsInUse($svc_id, $host_list) {
		global $pearDB;
		
		$hst_list = "";
		$flag_first = 1;
		foreach ($host_list as $val)
	 	{	 		
	 		if (isset($val)) {
		 		if (!$flag_first)
		 			$hst_list .= ",'" . $val . "'";
		 		else {
		 			$hst_list .= "'".$val."'";
		 			$flag_first = 0;
		 		}		 		
		 		$hst_list = getHostListInUse($hst_list, $val);
	 		}
	 	}
	 	if ($hst_list == "")
	 		$hst_list = "NULL";
		 $rq = "SELECT service_id " .
		 		"FROM service svc, host_service_relation hsr " .
		 		"WHERE hsr.service_service_id = svc.service_template_model_stm_id " .
		 		"AND hsr.service_service_id = '". $svc_id ."' " .
		 		"AND hsr.host_host_id IN (". $hst_list .")";
		 $DBRESULT =& $pearDB->query($rq);
		 if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows() >= 1)
			return true;	 	
	 	return false;
	}
	
	/*
	 * 	this function cleans all the services that were linked to the removed host template  
 	 */
	function deleteHostServiceMultiTemplate($hID, $scndHID, $host_list, $antiLoop = NULL){
		global $pearDB, $path, $oreon;
	
		if (isset($antiLoop[$scndHID]) && $antiLoop[$scndHID]) {
			return 0;
		}
		$DBRESULT3 =& $pearDB->query("SELECT service_service_id " .
	 							"FROM `service` svc, `host_service_relation` hsr " .
	 							"WHERE svc.service_id = hsr.service_service_id " .
	 							"AND svc.service_register = '0' " .
								"AND hsr.host_host_id = '" . $scndHID . "'");
	 	if (PEAR::isError($DBRESULT3))
			print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
		while ($svcID =& $DBRESULT3->fetchRow()) {
			if (!serviceIsInUse($svcID['service_service_id'], $host_list)) {
				$rq2 = "DELETE hsr, svc FROM `host_service_relation` hsr, `service` svc " .
					"WHERE hsr.service_service_id = svc.service_id " .
					"AND svc.service_template_model_stm_id = '".$svcID['service_service_id']."' " .
					"AND svc.service_register = '1' " .
					"AND hsr.host_host_id = '".$hID."'";
				$DBRESULT4 =& $pearDB->query($rq2);
				if (PEAR::isError($DBRESULT4))
					print "DB Error : ".$DBRESULT4->getDebugInfo()."<br />";			
			}			
		}
		
		$rq = "SELECT host_tpl_id " .
				"FROM host_template_relation " .
				"WHERE host_host_id = '".$scndHID."' " .
				"ORDER BY `order`";
				
		$DBRESULT =& $pearDB->query($rq);		
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($result =& $DBRESULT->fetchRow()) {			
			$DBRESULT2 =& $pearDB->query("SELECT service_service_id " .
	 								"FROM `service` svc, `host_service_relation` hsr " .
	 								"WHERE svc.service_id = hsr.service_service_id " .
	 								"AND svc.service_register = '0' " .
									"AND hsr.host_host_id = '" . $result["host_tpl_id"] . "'");
	 		if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($svcID =& $DBRESULT2->fetchRow()) {
				$rq2 = "DELETE hsr, svc FROM `host_service_relation` hsr, `service` svc " .
						"WHERE hsr.service_service_id = svc.service_id " .
						"AND svc.service_template_model_stm_id = '".$svcID['service_service_id']."' " .
						"AND svc.service_register = '1' " .
						"AND hsr.host_host_id = '".$hID."'";
				$DBRESULT4 =& $pearDB->query($rq2);
				if (PEAR::isError($DBRESULT4))
					print "DB Error : ".$DBRESULT4->getDebugInfo()."<br />";
			}
			$antiLoop[$scndHID] = 1;
			deleteHostServiceMultiTemplate($hID, $result["host_tpl_id"], $host_list, $antiLoop);
		}	
	}
	
	function updateHost($host_id = null, $from_MC = false)	{
		if (!$host_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();		
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL)		{
			$ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('/', "#S#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('\\', "#BS#", $ret["command_command_id_arg1"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		
		
		// For Centreon 2, we no longer need "host_template_model_htm_id" in Nagios 3
		// but we try to keep it compatible with Nagios 2 which needs "host_template_model_htm_id" 
		if (isset($_POST['nbOfSelect'])) {
			$DBRESULT =& $pearDB->query("SELECT host_id FROM `host` WHERE host_register='0' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$result = $DBRESULT->fetchRow();
			$ret["host_template_model_htm_id"] = $result["host_id"];
			$DBRESULT->free();
		}
		
		$rq = "UPDATE host SET host_template_model_htm_id = ";
		isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != NULL ? $rq .= "'".$ret["host_template_model_htm_id"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id = ";		
		isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";		
		$rq .= "command_command_id_arg1 = ";		
		isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL ? $rq .= "'".$ret["command_command_id_arg1"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id2 = ";
		isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id_arg2 = ";		
		isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".$ret["command_command_id_arg2"]."', ": $rq .= "NULL, ";
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "host_name = ";
			isset($ret["host_name"]) && $ret["host_name"] != NULL ? $rq .= "'".htmlentities($ret["host_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			$rq .= "host_alias = ";
			isset($ret["host_alias"]) && $ret["host_alias"] != NULL ? $rq .= "'".htmlentities($ret["host_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		}
		$rq .= "host_address = ";
		isset($ret["host_address"]) && $ret["host_address"] != NULL ? $rq .= "'".htmlentities($ret["host_address"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "host_max_check_attempts = ";
		isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != NULL ? $rq .= "'".$ret["host_max_check_attempts"]."', " : $rq .= "NULL, ";
		$rq .= "host_check_interval = ";
		isset($ret["host_check_interval"]) && $ret["host_check_interval"]!= NULL ? $rq .= "'".$ret["host_check_interval"]."', ": $rq .= "NULL, ";
		$rq .= "host_active_checks_enabled = ";
		isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) && $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ? $rq .= "'".$ret["host_active_checks_enabled"]["host_active_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_passive_checks_enabled = ";
		isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) && $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_checks_enabled = ";
		isset($ret["host_checks_enabled"]["host_checks_enabled"]) && $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ? $rq .= "'".$ret["host_checks_enabled"]["host_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_obsess_over_host = ";
		isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) && $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ? $rq .= "'".$ret["host_obsess_over_host"]["host_obsess_over_host"]."', ": $rq .= "'2', ";
		$rq .= "host_check_freshness = ";
		isset($ret["host_check_freshness"]["host_check_freshness"]) && $ret["host_check_freshness"]["host_check_freshness"] != 2 ? $rq .= "'".$ret["host_check_freshness"]["host_check_freshness"]."', ": $rq .= "'2', ";
		$rq .= "host_freshness_threshold = ";
		isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != NULL ? $rq .= "'".$ret["host_freshness_threshold"]."', ": $rq .= "NULL, ";
		$rq .= "host_event_handler_enabled = ";
		isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) && $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ? $rq .= "'".$ret["host_event_handler_enabled"]["host_event_handler_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_low_flap_threshold = ";
		isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"]!= NULL ? $rq .= "'".$ret["host_low_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "host_high_flap_threshold = ";
		isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != NULL ? $rq .= "'".$ret["host_high_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "host_flap_detection_enabled = ";
		isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) && $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]."', " : $rq .= "'2', ";
		$rq .= "host_process_perf_data = ";
		isset($ret["host_process_perf_data"]["host_process_perf_data"]) && $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ? $rq .= "'".$ret["host_process_perf_data"]["host_process_perf_data"]."', " : $rq .= "'2', ";
		$rq .= "host_retain_status_information = ";
		isset($ret["host_retain_status_information"]["host_retain_status_information"]) && $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ? $rq .= "'".$ret["host_retain_status_information"]["host_retain_status_information"]."', " : $rq .= "'2', ";
		$rq .= "host_retain_nonstatus_information = ";
		isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) && $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]."', " : $rq .= "'2', ";
		$rq .= "host_notification_interval = ";
		isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != NULL ? $rq .= "'".$ret["host_notification_interval"]."', " : $rq .= "NULL, ";
		$rq .= "host_notification_options = ";
		isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_notifOpts"]))."', " : $rq .= "NULL, ";
		$rq .= "host_notifications_enabled = ";
		isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) && $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ? $rq .= "'".$ret["host_notifications_enabled"]["host_notifications_enabled"]."', " : $rq .= "'2', ";
		$rq .= "host_first_notification_delay = ";
		isset($ret["host_first_notification_delay"]) && $ret["host_first_notification_delay"] ? $rq .= "'".$ret["host_first_notification_delay"]."', " : $rq .= " NULL, ";
		$rq .= "host_stalking_options = ";
		isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_stalOpts"]))."', " : $rq .= "NULL, ";
		$rq .= "host_snmp_community = ";
		isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_community"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_snmp_version = ";
		isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_version"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_location = ";
		isset($ret["host_location"]) && $ret["host_location"] != NULL ? $rq .= "'".htmlentities($ret["host_location"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_comment = ";
		isset($ret["host_comment"]) && $ret["host_comment"] != NULL ? $rq .= "'".htmlentities($ret["host_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_register = ";
		isset($ret["host_register"]["host_register"]) && $ret["host_register"]["host_register"] != NULL ? $rq .= "'".$ret["host_register"]["host_register"]."', " : $rq .= "NULL, ";
		$rq .= "host_activate = ";
		isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != NULL ? $rq .= "'".$ret["host_activate"]["host_activate"]."' " : $rq .= "NULL ";
		$rq .= "WHERE host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		/*
		 *  Update multiple templates		 
		 */
		if (isset($_POST['nbOfSelect'])) {
	 		$already_stored = array();
	 		
	 		
	 		$oldTp = array();
	 		$newTp = array();	 		
	 		$DBRESULT =& $pearDB->query("SELECT `host_tpl_id` FROM `host_template_relation` WHERE `host_host_id`='".$host_id."'");
	 		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	 		while ($hst =& $DBRESULT->fetchRow())
	 			$oldTp[$hst["host_tpl_id"]] = $hst["host_tpl_id"];			
	 		for ($i=0;$i <= $_POST['nbOfSelect']; $i++){
	 			$tpSelect = "tpSelect_" . $i;
				if (isset($_POST[$tpSelect]))
		 			$newTp[$_POST[$tpSelect]] = $_POST[$tpSelect];
	 		}
	 		
	 		foreach ($oldTp as $val){
	 			/*
  	 			 * if not set, then that means a template was removed
	 			 * we will have to remove the services that were linked to that host template as well  
	 			 */
	 			if (!isset($newTp[$val])) {
	 				deleteHostServiceMultiTemplate($host_id, $val, $newTp);
	 			}
	 		}
	 		
	 		$DBRESULT =& $pearDB->query("DELETE FROM `host_template_relation` WHERE `host_host_id`='".$host_id."'");
	 		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	 		$multiTP_logStr = "";
	 		for ($i=0, $j = 1;$i <= $_POST['nbOfSelect']; $i++){
	 			$tpSelect = "tpSelect_" . $i;
	 			if (isset($_POST[$tpSelect]) && !isset($already_stored[$_POST[$tpSelect]]) && $_POST[$tpSelect]) {
		 			$rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) VALUES (". $host_id .", ". $_POST[$tpSelect] .", ". $j .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$j++;
					$multiTP_logStr .= $_POST[$tpSelect] . ",";
					$already_stored[$_POST[$tpSelect]] = 1;
	 			}			
	 		}
	 		$multiTP_logStr = trim($multiTP_logStr, ",");
		}
 		/*
		 *  Update demand macros
		 */
		if (isset($_POST['nbOfMacro'])) {
			$already_stored = array();
			$DBRESULT =& $pearDB->query("DELETE FROM `on_demand_macro_host` WHERE `host_host_id`='".$host_id."'");
			
	 		for ($i=0; $i <= $_POST['nbOfMacro']; $i++){ 			
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($_POST[$macInput]) && !isset($already_stored[$_POST[$macInput]]) && $_POST[$macInput]) {
		 			$_POST[$macInput] = str_replace("\$_HOST", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$rq = "INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`, `host_host_id`) VALUES ('\$_HOST". strtoupper($_POST[$macInput]) ."\$', '". $_POST[$macValue] ."', ". $host_id .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$fields["_".strtoupper($_POST[$macInput])."_"] = $_POST[$macValue];	
					$already_stored[$_POST[$macInput]] = 1;
	 			}			
	 		}
		}
		
		/*
		 *  Logs
		 */
		$fields["command_command_id"] = $ret["command_command_id"];
		$fields["command_command_id_arg1"] = $ret["command_command_id_arg1"];
		$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		$fields["command_command_id2"] = $ret["command_command_id2"];
		$fields["command_command_id_arg2"] = $ret["command_command_id_arg2"];
		$fields["host_name"] = htmlentities($ret["host_name"], ENT_QUOTES);
		$fields["host_alias"] = htmlentities($ret["host_alias"], ENT_QUOTES);
		$fields["host_address"] = htmlentities($ret["host_address"], ENT_QUOTES);
		$fields["host_max_check_attempts"] = $ret["host_max_check_attempts"];
		$fields["host_check_interval"] = $ret["host_check_interval"];
		$fields["host_active_checks_enabled"] = $ret["host_active_checks_enabled"]["host_active_checks_enabled"];
		$fields["host_passive_checks_enabled"] = $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"];
		if (isset($ret["host_checks_enabled"]))
			$fields["host_checks_enabled"] = $ret["host_checks_enabled"]["host_checks_enabled"];
		$fields["host_obsess_over_host"] = $ret["host_obsess_over_host"]["host_obsess_over_host"];
		$fields["host_check_freshness"] = $ret["host_check_freshness"]["host_check_freshness"];
		$fields["host_freshness_threshold"] = $ret["host_freshness_threshold"];
		$fields["host_event_handler_enabled"] = $ret["host_event_handler_enabled"]["host_event_handler_enabled"];
		$fields["host_low_flap_threshold"] = $ret["host_low_flap_threshold"];
		$fields["host_high_flap_threshold"] = $ret["host_high_flap_threshold"];
		$fields["host_flap_detection_enabled"] = $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"];
		$fields["host_process_perf_data"] = $ret["host_process_perf_data"]["host_process_perf_data"];
		$fields["host_retain_status_information"] = $ret["host_retain_status_information"]["host_retain_status_information"];
		$fields["host_retain_nonstatus_information"] = $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"];
		$fields["host_notification_interval"] = $ret["host_notification_interval"];
		$fields["host_notifOpts"] = "";
		if (isset($ret["host_notifOpts"]))
			$fields["host_notifOpts"] = implode(",", array_keys($ret["host_notifOpts"]));
		$fields["host_notifications_enabled"] = $ret["host_notifications_enabled"]["host_notifications_enabled"];
		if (isset($ret["host_first_notification_delay"]))
			$fields["host_first_notification_delay"] = $ret["host_first_notification_delay"];
		$fields["host_stalOpts"] = "";
		if (isset($ret["host_stalOpts"]))
			$fields["host_stalOpts"] = implode(",", array_keys($ret["host_stalOpts"]));
		$fields["host_snmp_community"] = htmlentities($ret["host_snmp_community"], ENT_QUOTES);
		$fields["host_snmp_version"] = htmlentities($ret["host_snmp_version"], ENT_QUOTES);
		if (isset($ret["host_location"]))
			$fields["host_location"] = htmlentities($ret["host_location"], ENT_QUOTES);
		$fields["host_comment"] = htmlentities($ret["host_comment"], ENT_QUOTES);
		$fields["host_register"] = $ret["host_register"]["host_register"];
		$fields["host_activate"] = $ret["host_activate"]["host_activate"];
		$fields["templates"] = $multiTP_logStr;
		$fields["ehi_notes"] = htmlentities($ret["ehi_notes"], ENT_QUOTES);
		$fields["ehi_notes_url"] = htmlentities($ret["ehi_notes_url"], ENT_QUOTES);
		$fields["ehi_action_url"] = htmlentities($ret["ehi_action_url"], ENT_QUOTES);
		$fields["ehi_icon_image"] = htmlentities($ret["ehi_icon_image"], ENT_QUOTES);
		$fields["ehi_icon_image_alt"] = htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES);
		$fields["ehi_vrml_image"] = htmlentities($ret["ehi_vrml_image"], ENT_QUOTES);
		$fields["ehi_statusmap_image"] = htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES);
		$fields["ehi_2d_coords"] = htmlentities($ret["ehi_2d_coords"], ENT_QUOTES);
		$fields["ehi_3d_coords"] = htmlentities($ret["ehi_3d_coords"], ENT_QUOTES);
		$fields["host_parents"] = "";
		if (isset($ret["host_parents"]))
			$fields["host_parents"] = implode(",", $ret["host_parents"]);
		$fields["host_childs"] = "";
		if (isset($ret["host_childs"]))
			$fields["host_childs"] = implode(",", $ret["host_childs"]);
		$fields["host_cgs"] = "";
		if (isset($ret["host_cgs"]))
			$fields["host_cgs"] = implode(",", $ret["host_cgs"]);
		$fields["host_cs"] = "";
		if (isset($ret["host_cs"]))
			$fields["host_cs"] = implode(",", $ret["host_cs"]);
		$fields["host_svTpls"] = "";
		if (isset($ret["host_svTpls"]))
			$fields["host_svTpls"] = implode(",", $ret["host_svTpls"]);
		$fields["nagios_server_id"] = "";
		if (isset($ret["nagios_server_id"]))
			$fields["nagios_server_id"] = $ret["nagios_server_id"];
		$oreon->CentreonLogAction->insertLog("host", $host_id, htmlentities($ret["host_name"], ENT_QUOTES), "c", $fields);
	}
	
	function updateHost_MC($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();		
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL)		{
			$ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('/', "#S#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('\\', "#BS#", $ret["command_command_id_arg1"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		
		// For Centreon 2, we no longer need "host_template_model_htm_id" in Nagios 3
		// but we try to keep it compatible with Nagios 2 which needs "host_template_model_htm_id" 
		if (isset($_POST['nbOfSelect'])) {
			$DBRESULT =& $pearDB->query("SELECT host_id FROM `host` WHERE host_register='0' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$result = $DBRESULT->fetchRow();
			$ret["host_template_model_htm_id"] = $result["host_id"];
			$DBRESULT->free();
		}
		
		$rq = "UPDATE host SET ";
		if (isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != NULL) {
			$rq .= "host_template_model_htm_id = '".$ret["host_template_model_htm_id"]."', ";
			$fields["host_template_model_htm_id"] = $ret["host_template_model_htm_id"];
		}
		if (isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL) {
			$rq .= "command_command_id = '".$ret["command_command_id"]."', ";
			$fields["command_command_id"] = $ret["command_command_id"];
		}
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL) {
			$rq .= "command_command_id_arg1 = '".$ret["command_command_id_arg1"]."', ";
			$fields["command_command_id_arg1"] = $ret["command_command_id_arg1"];
		}
		if (isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL) {
			$rq .= "timeperiod_tp_id = '".$ret["timeperiod_tp_id"]."', ";
			$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		}
		if (isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL) {
			$rq .= "timeperiod_tp_id2 = '".$ret["timeperiod_tp_id2"]."', ";
			$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		}
		if (isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL) {
			$rq .= "command_command_id2 = '".$ret["command_command_id2"]."', ";
			$fields["command_command_id2"] = $ret["command_command_id2"];
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL) {
			$rq .= "command_command_id_arg2 = '".$ret["command_command_id_arg2"]."', ";
			$fields["command_command_id_arg2"] = $ret["command_command_id_arg2"];
		}
		if (isset($ret["host_address"]) && $ret["host_address"] != NULL) {
			$rq .= "host_address = '".htmlentities($ret["host_address"], ENT_QUOTES)."', ";
			$fields["host_address"] = htmlentities($ret["host_address"], ENT_QUOTES);
		}
		if (isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != NULL) {
			$rq .= "host_max_check_attempts = '".$ret["host_max_check_attempts"]."', " ;
			$fields["host_max_check_attempts"] = $ret["host_max_check_attempts"];
		}
		if (isset($ret["host_check_interval"]) && $ret["host_check_interval"]!= NULL) {
			$rq .= "host_check_interval = '".$ret["host_check_interval"]."', ";
			$fields["host_check_interval"] = $ret["host_check_interval"];
		}
		if (isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"])) {
			$rq .= "host_active_checks_enabled = '".$ret["host_active_checks_enabled"]["host_active_checks_enabled"]."', ";
			$fields["host_active_checks_enabled"] = $ret["host_active_checks_enabled"]["host_active_checks_enabled"];
		}
		if (isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"])) {
			$rq .= "host_passive_checks_enabled = '".$ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]."', ";
			$fields["host_passive_checks_enabled"] = $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"];
		}
		if (isset($ret["host_checks_enabled"]["host_checks_enabled"])) {
			$rq .= "host_checks_enabled = '".$ret["host_checks_enabled"]["host_checks_enabled"]."', ";
			$fields["host_checks_enabled"] = $ret["host_checks_enabled"]["host_checks_enabled"];
		}
		if (isset($ret["host_obsess_over_host"]["host_obsess_over_host"])) {
			$rq .= "host_obsess_over_host = '".$ret["host_obsess_over_host"]["host_obsess_over_host"]."', ";
			$fields["host_obsess_over_host"] = $ret["host_obsess_over_host"]["host_obsess_over_host"];
		}
		if (isset($ret["host_check_freshness"]["host_check_freshness"])) {
			$rq .= "host_check_freshness = '".$ret["host_check_freshness"]["host_check_freshness"]."', ";
			$fields["host_check_freshness"] = $ret["host_check_freshness"]["host_check_freshness"];
		}
		if (isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != NULL) {
			$rq .= "host_freshness_threshold = '".$ret["host_freshness_threshold"]."', ";
			$fields["host_freshness_threshold"] = $ret["host_freshness_threshold"];
		}
		if (isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"])) {
			$rq .= "host_event_handler_enabled = '".$ret["host_event_handler_enabled"]["host_event_handler_enabled"]."', ";
			$fields["host_event_handler_enabled"] = $ret["host_event_handler_enabled"]["host_event_handler_enabled"];
		}
		if (isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"]!= NULL) {
			$rq .= "host_low_flap_threshold = '".$ret["host_low_flap_threshold"]."', ";
			$fields["host_low_flap_threshold"] = $ret["host_low_flap_threshold"];
		}
		if (isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != NULL) {
			$rq .= "host_high_flap_threshold = '".$ret["host_high_flap_threshold"]."', ";
			$fields["host_high_flap_threshold"] = $ret["host_high_flap_threshold"];
		}
		if (isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"])) {
			$rq .= "host_flap_detection_enabled = '".$ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]."', ";
			$fields["host_flap_detection_enabled"] = $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"];
		}
		if (isset($ret["host_process_perf_data"]["host_process_perf_data"])) {
			$rq .= "host_process_perf_data = '".$ret["host_process_perf_data"]["host_process_perf_data"]."', ";
			$fields["host_process_perf_data"] = $ret["host_process_perf_data"]["host_process_perf_data"];
		}
		if (isset($ret["host_retain_status_information"]["host_retain_status_information"])) {
			$rq .= "host_retain_status_information = '".$ret["host_retain_status_information"]["host_retain_status_information"]."', ";
			$fields["host_retain_status_information"] = $ret["host_retain_status_information"]["host_retain_status_information"];
		}
		if (isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"])) {
			$rq .= "host_retain_nonstatus_information = '".$ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]."', ";
			$fields["host_retain_nonstatus_information"] = $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"];
		}
		if (isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != NULL) {
			$rq .= "host_notification_interval = '".$ret["host_notification_interval"]."', ";
			$fields["host_notification_interval"] = $ret["host_notification_interval"];
		}
		if (isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != NULL) {
			$rq .= "host_notification_options = '".implode(",", array_keys($ret["host_notifOpts"]))."', ";
			$fields["host_notifOpts"] = implode(",", array_keys($ret["host_notifOpts"]));
		}
		if (isset($ret["host_notifications_enabled"]["host_notifications_enabled"])) {
			$rq .= "host_notifications_enabled = '".$ret["host_notifications_enabled"]["host_notifications_enabled"]."', ";
			$fields["host_notifications_enabled"] = $ret["host_notifications_enabled"]["host_notifications_enabled"];
		}
		if (isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != NULL) {
			$rq .= "host_stalking_options = '".implode(",", array_keys($ret["host_stalOpts"]))."', ";
			$fields["host_stalking_options"] = implode(",", array_keys($ret["host_stalOpts"]));
		}
		if (isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != NULL) {
			$rq .= "host_snmp_community = '".htmlentities($ret["host_snmp_community"], ENT_QUOTES)."', ";
			$fields["host_snmp_community"] = htmlentities($ret["host_snmp_community"], ENT_QUOTES);
		}
		if (isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != NULL) {
			$rq .= "host_snmp_version = '".htmlentities($ret["host_snmp_version"], ENT_QUOTES)."', ";
			$fields["host_snmp_version"] = htmlentities($ret["host_snmp_version"], ENT_QUOTES);
		}
		if (isset($ret["host_comment"]) && $ret["host_comment"] != NULL) {
			$rq .= "host_comment = '".htmlentities($ret["host_comment"], ENT_QUOTES)."', ";
			$fields["host_comment"] = htmlentities($ret["host_comment"], ENT_QUOTES);
		}
		if (isset($ret["host_register"]["host_register"]) && $ret["host_register"]["host_register"] != NULL) {
			$rq .= "host_register = '".$ret["host_register"]["host_register"]."', ";
			$fields["host_register"] = $ret["host_register"]["host_register"];
		}
		if (isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != NULL) {
			$rq .= "host_activate = '".$ret["host_activate"]["host_activate"]."', ";
			$fields["host_activate"] = $ret["host_activate"]["host_activate"];
		}
		if (strcmp("UPDATE host SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE host_id = '".$host_id."'";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
		
		/*
		 *  update multiple templates		 
		 */	 		
 		if (isset($_POST['nbOfSelect']) && $_POST['nbOfSelect']) {
	 		$already_stored = array();
	 		
	 		$oldTp = array();
	 		$newTp = array();	 		
	 		$DBRESULT =& $pearDB->query("SELECT `host_tpl_id` FROM `host_template_relation` WHERE `host_host_id`='".$host_id."'");
	 		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	 		while ($hst =& $DBRESULT->fetchRow())
	 			$oldTp[$hst["host_tpl_id"]] = $hst["host_tpl_id"];			
	 		$multiTP_logStr = "";
	 		for ($i=0;$i <= $_POST['nbOfSelect']; $i++){
	 			$tpSelect = "tpSelect_" . $i;
	 			if (isset($_POST[$tpSelect])) {
	 				$newTp[$_POST[$tpSelect]] = $_POST[$tpSelect];
	 				$multiTP_logStr .= $_POST[$tpSelect] . ",";
	 			}
	 		}
	 		$multiTP_logStr = trim($multiTP_logStr, ",");
	 		if ($multiTP_logStr != "")
	 			$fields["templates"] = $multiTP_logStr;
	 		foreach ($oldTp as $val){
	 			/*
  	 			 * if not set, then that means a template was removed
	 			 * we will have to remove the services that were linked to that host template as well  
	 			 */
	 			if (!isset($newTp[$val])) {
	 				deleteHostServiceMultiTemplate($host_id, $val, $newTp);
	 			}
	 		}
	 		
	 		$DBRESULT =& $pearDB->query("DELETE FROM `host_template_relation` WHERE `host_host_id`='".$host_id."'");
	 		for ($i=0, $j = 1;$i <= $_POST['nbOfSelect']; $i++)
	 		{ 			
	 			$tpSelect = "tpSelect_" . $i; 		
	 			if (isset($_POST[$tpSelect]) && !isset($already_stored[$_POST[$tpSelect]]) && $_POST[$tpSelect]) {
		 			$rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) VALUES (". $host_id .", ". $_POST[$tpSelect] .", ". $j .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$j++;
					$already_stored[$_POST[$tpSelect]] = 1;
	 			}			
	 		}
 		}
 		
 		/*
		 *  Update on demand macros
		 */
		if (isset($_POST['nbOfMacro']) && $_POST['nbOfMacro']) {
			$already_stored = array();
			$already_stored_in_db = array();
			
			$rq = "SELECT host_macro_name FROM `on_demand_macro_host` WHERE `host_host_id`=" . $host_id;
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while ($mac = $DBRESULT->fetchRow()) {
				$tmp = str_replace("\$_HOST", "", $mac["host_macro_name"]);
				$tmp = str_replace("\$", "", $tmp);
				$tmp = strtolower($tmp);				
				$already_stored_in_db[$tmp] = 1;
			}
			
			
	 		for ($i=0; $i <= $_POST['nbOfMacro']; $i++)
	 		{ 			
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($_POST[$macInput]) && isset($already_stored_in_db[strtolower($_POST[$macInput])])) {	 			 				
	 				$_POST[$macInput] = str_replace("\$_HOST", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
	 				$rq = "UPDATE on_demand_macro_host SET `host_macro_value`='". $_POST[$macValue] . "'".
	 					  " WHERE `host_host_id`=" . $host_id .
	 					  " AND `host_macro_name`='\$_HOST" . $_POST[$macInput] . "\$'";
			 		$DBRESULT =& $pearDB->query($rq);			 		
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	 			}
	 			elseif (isset($_POST[$macInput]) && !isset($already_stored[strtolower($_POST[$macInput])]) && $_POST[$macInput]) {		 			
		 			$_POST[$macInput] = str_replace("\$_HOST", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$rq = "INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`, `host_host_id`) VALUES ('\$_HOST". strtoupper($_POST[$macInput]) ."\$', '". $_POST[$macValue] ."', ". $host_id .")";
			 		$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";				
					$already_stored[$_POST[$macInput]] = 1;
	 			}
	 			$fields["_".strtoupper($_POST[$macInput])."_"] = $_POST[$macValue];
	 		}
		}
		
		if (isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL)
			$fields["ehi_notes"] = htmlentities($ret["ehi_notes"], ENT_QUOTES);
		if (isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL)
			$fields["ehi_notes_url"] = htmlentities($ret["ehi_notes_url"], ENT_QUOTES);
		if (isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL)
			$fields["ehi_action_url"] = htmlentities($ret["ehi_action_url"], ENT_QUOTES);
		if (isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL)
			$fields["ehi_icon_image"] = htmlentities($ret["ehi_icon_image"], ENT_QUOTES);
		if (isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL)
			$fields["ehi_icon_image_alt"] = htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES);
		if (isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL)
			$fields["ehi_vrml_image"] = htmlentities($ret["ehi_vrml_image"], ENT_QUOTES);
		if (isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL)
			$fields["ehi_statusmap_image"] = htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES);
		if (isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL)
			$fields["ehi_2d_coords"] = htmlentities($ret["ehi_2d_coords"], ENT_QUOTES);
		if (isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL)
			$fields["ehi_3d_coords"] = htmlentities($ret["ehi_3d_coords"], ENT_QUOTES);
		if (isset($ret["host_parents"]) && $ret["host_parents"] != NULL)
			$fields["host_parents"] = implode(",", $ret["host_parents"]);
		if (isset($ret["host_childs"]) && $ret["host_childs"] != NULL)
			$fields["host_childs"] = implode(",", $ret["host_childs"]);
		if (isset($ret["host_cgs"]) && $ret["host_cgs"] != NULL)
			$fields["host_cgs"] = implode(",", $ret["host_cgs"]);
		if (isset($ret["host_cs"]) && $ret["host_cs"] != NULL)
			$fields["host_cs"] = implode(",", $ret["host_cs"]);
		if (isset($ret["host_svTpls"]) && $ret["host_svTpls"] != NULL)
			$fields["host_svTpls"] = implode(",", $ret["host_svTpls"]);
		if (isset($ret["nagios_server_id"]) && $ret["nagios_server_id"] != NULL)
			$fields["nagios_server_id"] = $ret["nagios_server_id"];
		
		$DBRESULTX =& $pearDB->query("SELECT host_name FROM `host` WHERE host_id='".$host_id."' LIMIT 1");
		if (PEAR::isError($DBRESULTX))
			print "DB Error : ".$DBRESULTX->getDebugInfo()."<br />";
		$row =& $DBRESULTX->fetchRow();
		$oreon->CentreonLogAction->insertLog("host", $host_id, $row["host_name"], "mc", $fields);
	}
	
	function updateHostHostParent($host_id = null, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM host_hostparent_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["host_parents"]))
			$ret = $ret["host_parents"];
		else
			$ret = $form->getSubmitValue("host_parents");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO host_hostparent_relation ";
			$rq .= "(host_parent_hp_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$host_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateHostHostParent_MC($host_id = null, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM host_hostparent_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$hpars = array();
		while($arr =& $DBRESULT->fetchRow())
			$hpars[$arr["host_parent_hp_id"]] = $arr["host_parent_hp_id"];
		$ret = $form->getSubmitValue("host_parents");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($hpars[$ret[$i]]) && isset($ret[$i]))	{
				$rq = "INSERT INTO host_hostparent_relation ";
				$rq .= "(host_parent_hp_id, host_host_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$host_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}
	
	function updateHostHostChild($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM host_hostparent_relation ";
		$rq .= "WHERE host_parent_hp_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("host_childs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO host_hostparent_relation ";
			$rq .= "(host_parent_hp_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret[$i]."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateHostHostChild_MC($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM host_hostparent_relation ";
		$rq .= "WHERE host_parent_hp_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$hchs = array();
		while($arr =& $DBRESULT->fetchRow())
			$hchs[$arr["host_host_id"]] = $arr["host_host_id"];
		$ret = $form->getSubmitValue("host_childs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($hchs[$ret[$i]]) && isset($ret[$i]))	{
				$rq = "INSERT INTO host_hostparent_relation ";
				$rq .= "(host_parent_hp_id, host_host_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$host_id."', '".$ret[$i]."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function updateHostExtInfos($host_id = null, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		/*
		 * Check if image selected isn't a directory
		 */
		if (isset($ret["ehi_icon_image"]) && strrchr("REP_", $ret["ehi_icon_image"]))
			$ret["ehi_icon_image"] = NULL;		
		if (isset($ret["ehi_vrml_image"]) && strrchr("REP_", $ret["ehi_vrml_image"]))
			$ret["ehi_vrml_image"] = NULL;
		if (isset($ret["ehi_statusmap_image"]) && strrchr("REP_", $ret["ehi_statusmap_image"]))
			$ret["ehi_statusmap_image"] = NULL;
		/*
		 * 
		 */
		$rq = "UPDATE extended_host_information ";		
		$rq .= "SET ehi_notes = ";
		isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_notes_url = ";
		isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_action_url = ";
		isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_icon_image = ";
		isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_icon_image_alt = ";
		isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_vrml_image = ";
		isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_vrml_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_statusmap_image = ";
		isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_2d_coords = ";
		isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_2d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_3d_coords = ";
		isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_3d_coords"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}

	function updateHostExtInfos_MC($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB;
		$ret = $form->getSubmitValues();
		$rq = "UPDATE extended_host_information SET ";
		if (isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL) $rq .= "ehi_notes = '".htmlentities($ret["ehi_notes"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL) $rq .= "ehi_notes_url = '".htmlentities($ret["ehi_notes_url"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL) $rq .= "ehi_action_url = '".htmlentities($ret["ehi_action_url"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL) $rq .= "ehi_icon_image = '".htmlentities($ret["ehi_icon_image"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL) $rq .= "ehi_icon_image_alt = '".htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL) $rq .= "ehi_vrml_image = '".htmlentities($ret["ehi_vrml_image"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL) $rq .= "ehi_statusmap_image = '".htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL) $rq .= "ehi_2d_coords = '".htmlentities($ret["ehi_2d_coords"], ENT_QUOTES)."', ";
		if (isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL) $rq .= "ehi_3d_coords = '".htmlentities($ret["ehi_3d_coords"], ENT_QUOTES)."', ";
		if (strcmp("UPDATE extended_host_information SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE host_host_id = '".$host_id."'";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
		
	function updateHostContactGroup($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM contactgroup_host_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		isset($ret["host_cgs"]) ? $ret = $ret["host_cgs"] : $ret = $form->getSubmitValue("host_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_host_relation ";
			$rq .= "(host_host_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	/*
	 *  Only for Nagios 3
	 */
	function updateHostContact($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM contact_host_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		isset($ret["host_cs"]) ? $ret = $ret["host_cs"] : $ret = $form->getSubmitValue("host_cs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_host_relation ";
			$rq .= "(host_host_id, contact_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateHostContactGroup_MC($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM contactgroup_host_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cgs = array();
		while($arr =& $DBRESULT->fetchRow())
			$cgs[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
		$ret = $form->getSubmitValue("host_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cgs[$ret[$i]]))	{
				$rq = "INSERT INTO contactgroup_host_relation ";
				$rq .= "(host_host_id, contactgroup_cg_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$host_id."', '".$ret[$i]."')";
				$DBRESULT = $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateHostContact_MC($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM contact_host_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cs = array();
		while($arr =& $DBRESULT->fetchRow())
			$cs[$arr["contact_id"]] = $arr["contact_id"];
		$ret = $form->getSubmitValue("host_cs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cs[$ret[$i]]))	{
				$rq = "INSERT INTO contact_host_relation ";
				$rq .= "(host_host_id, contact_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$host_id."', '".$ret[$i]."')";
				$DBRESULT = $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}
	
	
	function updateHostHostGroup($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form;
		global $pearDB;
		# Special Case, delete relation between host/service, when service is linked to hostgroup in escalation, dependencies, osl
		# Get initial Hostgroup list to make a diff after deletion
		$rq = "SELECT hostgroup_hg_id FROM hostgroup_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$hgsOLD = array();
		while ($hg =& $DBRESULT->fetchRow())
			$hgsOLD[$hg["hostgroup_hg_id"]] = $hg["hostgroup_hg_id"];
		# Get service lists linked to hostgroup
		$hgSVS = array();
		foreach ($hgsOLD as $hg)	{
			$rq = "SELECT service_service_id FROM host_service_relation ";
			$rq .= "WHERE hostgroup_hg_id = '".$hg."' AND host_host_id IS NULL";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while ($sv =& $DBRESULT->fetchRow())
				$hgSVS[$hg][$sv["service_service_id"]] = $sv["service_service_id"];
		}
		#
		$rq = "DELETE FROM hostgroup_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		isset($ret["host_hgs"]) ? $ret = $ret["host_hgs"] : $ret = $form->getSubmitValue("host_hgs");
		$hgsNEW = array();
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO hostgroup_relation ";
			$rq .= "(hostgroup_hg_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$host_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$hgsNEW[$ret[$i]] = $ret[$i];
		}
		# Special Case, delete relation between host/service, when service is linked to hostgroup in escalation, dependencies, osl
		if (count($hgSVS))
			foreach ($hgsOLD as $hg)
				if (!isset($hgsNEW[$hg]))	{
					if (isset($hgSVS[$hg]))
						foreach ($hgSVS[$hg] as $sv)	{
							# Delete in escalation
							$rq = "DELETE FROM escalation_service_relation ";
							$rq .= "WHERE host_host_id = '".$host_id."' AND service_service_id = '".$sv."'";
							$DBRESULT =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";						
							# Delete in dependencies
							$rq = "DELETE FROM dependency_serviceChild_relation ";
							$rq .= "WHERE host_host_id = '".$host_id."' AND service_service_id = '".$sv."'";
							$DBRESULT =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
							$rq = "DELETE FROM dependency_serviceParent_relation ";
							$rq .= "WHERE host_host_id = '".$host_id."' AND service_service_id = '".$sv."'";
							$DBRESULT =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
							# Delete in OSL
							$rq = "DELETE FROM osl_indicator ";
							$rq .= "WHERE host_id = '".$host_id."' AND service_id = '".$sv."'";
							$DBRESULT =& $pearDB->query($rq);
							//if (PEAR::isError($DBRESULT))
							//	print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";	
						}
				}
		#
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateHostHostGroup_MC($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM hostgroup_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$hgs = array();
		while($arr =& $DBRESULT->fetchRow())
			$hgs[$arr["hostgroup_hg_id"]] = $arr["hostgroup_hg_id"];
		$ret = $form->getSubmitValue("host_hgs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($hgs[$ret[$i]]))	{
				$rq = "INSERT INTO hostgroup_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$host_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

function generateHostServiceMultiTemplate($hID, $hID2 = NULL, $antiLoop = NULL){
	global $pearDB, $path, $oreon;
	
	if (isset($antiLoop[$hID2]) && $antiLoop[$hID2]) {		
		return 0;	
	}
	if (file_exists($path."../service/DB-Func.php"))
		require_once($path."../service/DB-Func.php");
	else if (file_exists($path."../configObject/service/DB-Func.php"))
		require_once($path."../configObject/service/DB-Func.php");
	$rq = "SELECT host_tpl_id FROM `host_template_relation` WHERE host_host_id = " . $hID2;
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	while ($hTpl = $DBRESULT->fetchRow()) {		
		$rq2 = "SELECT service_service_id FROM `host_service_relation` WHERE host_host_id = " . $hTpl['host_tpl_id'];
		$DBRESULT2 =& $pearDB->query($rq2);
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
		while ($hTpl2 = $DBRESULT2->fetchRow()) {			
			$alias =& getMyServiceAlias($hTpl2["service_service_id"]);			
			if (testServiceExistence ($alias, array(0=>$hID))) {				
				$service = array("service_template_model_stm_id" => $hTpl2["service_service_id"], "service_description"=> $alias, "service_register"=>array("service_register"=> 1), "service_activate"=>array("service_activate" => 1));
				$service_id = insertServiceInDB($service);
				$rq3 = "INSERT INTO host_service_relation (hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) VALUES (NULL, '".$hID."', NULL, '".$service_id."')";
				$DBRESULT3 =& $pearDB->query($rq3);
				if (PEAR::isError($DBRESULT3))
					print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
			}
		}
		$antiLoop[$hID2] = 1;
		generateHostServiceMultiTemplate($hID, $hTpl['host_tpl_id'], $antiLoop);
	}
}

	function createHostTemplateService($host_id = null, $htm_id = NULL)	{
		if (!$host_id) return;
		global $pearDB, $path, $oreon;
		if (file_exists($path."../service/DB-Func.php"))
			require_once($path."../service/DB-Func.php");
		else if (file_exists($path."../configObject/service/DB-Func.php"))
			require_once($path."../configObject/service/DB-Func.php");
		# If we select a host template model, we create the services linked to this host template model		
		if ($oreon->user->get_version() < 3) {
			if ($htm_id)	{
				$DBRESULT =& $pearDB->query("SELECT service_service_id FROM host_service_relation WHERE host_host_id = '".$htm_id."'");
				while ($row =& $DBRESULT->fetchRow())	{
					$alias =& getMyServiceAlias($row["service_service_id"]);
					if (testServiceExistence ($alias, array(0=>$host_id)))	{
						$service = array("service_template_model_stm_id" => $row["service_service_id"], "service_description"=> $alias, "service_register"=>array("service_register"=> 1), "service_activate"=>array("service_activate" => 1));
						$service_id = insertServiceInDB($service);
						$rq = "INSERT INTO host_service_relation ";
						$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
						$rq .= "VALUES ";
						$rq .= "(NULL, '".$host_id."', NULL, '".$service_id."')";
						$DBRESULT2 =& $pearDB->query($rq);
						if (PEAR::isError($DBRESULT2))
							print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
					}
				}
			}
			# Then we create all the services linked to the host template model tree. (n levels services creation)
			while (1)	{
				if (!$htm_id)
					$htm_id = getMyHostTemplateModel($host_id);
				else if ($htm_id)
					$htm_id = getMyHostTemplateModel($htm_id);
				if ($htm_id)	{
					$DBRESULT =& $pearDB->query("SELECT service_service_id FROM host_service_relation WHERE host_host_id = '".$htm_id."'");
					while ($row =& $DBRESULT->fetchRow())	{
						//$desc =& getMyServiceName($row["service_service_id"]);
						$alias =& getMyServiceAlias($row["service_service_id"]);
						if (testServiceExistence ($alias, array(0=>$host_id)))	{
							$service = array("service_template_model_stm_id" => $row["service_service_id"], "service_description"=> $alias, "service_register"=>array("service_register"=> 1), "service_activate"=>array("service_activate" => 1));
							$service_id = insertServiceInDB($service);		
							$rq = "INSERT INTO host_service_relation ";
							$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
							$rq .= "VALUES ";
							$rq .= "(NULL, '".$host_id."', NULL, '".$service_id."')";
							$DBRESULT2 =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
					}
				}
				else if (!$htm_id)
					break;
			}
		}
		else {						
			global $form;
			$ret = $form->getSubmitValues();			
			if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"])
				generateHostServiceMultiTemplate($host_id, $host_id);
		}
	}
	
	function updateHostTemplateService($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB, $oreon, $path;
		if (file_exists($path."../service/DB-Func.php"))
			require_once($path."../service/DB-Func.php");
		else if (file_exists($path."../service/DB-Func.php"))
			require_once($path."../configObject/service/DB-Func.php");
		
		$DBRESULT =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."'");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_register"] == 0) 	{
			$rq = "DELETE FROM host_service_relation ";
			$rq .= "WHERE host_host_id = '".$host_id."'";
			$DBRESULT2 =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			$ret = array();
			$ret = $form->getSubmitValue("host_svTpls");
			for($i = 0; $i < count($ret); $i++)	{
				if (isset($ret[$i]) && $ret[$i] != "") {
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$host_id."', NULL, '".$ret[$i]."')";
					$DBRESULT2 =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				}
			}
		}
		else if ($oreon->user->get_version() >= 3) {
			if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"])
				generateHostServiceMultiTemplate($host_id, $host_id);
		}
	}
	
	function updateHostTemplateService_MC($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB, $oreon, $path;
		if (file_exists($path."../service/DB-Func.php"))
			require_once($path."../service/DB-Func.php");
		else if (file_exists($path."../service/DB-Func.php"))
			require_once($path."../configObject/service/DB-Func.php");
		
		$DBRESULT =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."'");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_register"] == 0) 	{
			$rq = "SELECT * FROM host_service_relation ";
			$rq .= "WHERE host_host_id = '".$host_id."'";
			$DBRESULT2 =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			$svtpls = array();
			while($arr =& $DBRESULT2->fetchRow())
				$svtpls [$arr["service_service_id"]] = $arr["service_service_id"];
			$ret = $form->getSubmitValue("host_svTpls");
			for($i = 0; $i < count($ret); $i++)	{
				if (!isset($svtpls[$ret[$i]]))	{
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$host_id."', NULL, '".$ret[$i]."')";
					$DBRESULT2 =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				}
			}
		}
		else if ($oreon->user->get_version() >= 3){
			if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"])
				generateHostServiceMultiTemplate($host_id, $host_id);
		}
	}

	function updateHostTemplateUsed($useTpls = array())	{
		if(!count($useTpls)) return;
		global $pearDB;
		require_once "./include/common/common-Func.php";
		foreach ($useTpls as $key=>$value){
			$DBRESULT =& $pearDB->query("UPDATE host SET host_template_model_htm_id = '".getMyHostID($value)."' WHERE host_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}			
	}
	
	function updateNagiosServerRelation($host_id, $ret = array())	{
		global $form, $pearDB;
		
		if (!$host_id) 
			return;
			
		isset($ret["nagios_server_id"]) ? $ret = $ret["nagios_server_id"] : $ret = $form->getSubmitValue("nagios_server_id");
		
		if (isset($ret) && $ret != "" && $ret != 0){
			$DBRESULT = $pearDB->query("DELETE FROM `ns_host_relation` WHERE `host_host_id` = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
			$rq = "INSERT INTO `ns_host_relation` ";
			$rq .= "(`host_host_id`, `nagios_server_id`) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret."')";
			
			$DBRESULT = $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateNagiosServerRelation_MC($host_id, $ret = array())	{
		global $form, $pearDB;
		
		if (!$host_id) 
			return;
			
		$cgs = array();
		while ($arr =& $DBRESULT->fetchRow())
			$cgs[$arr["nagios_server_id"]] = $arr["nagios_server_id"];
		
		$ret = $form->getSubmitValue("nagios_server_id");
		if (isset($ret) && $ret != "" && $ret != 0){
			$DBRESULT = $pearDB->query("SELECT * FROM ns_host_relation WHERE host_host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
			$rq = "INSERT INTO `ns_host_relation` ";
			$rq .= "(`host_host_id`, `nagios_server_id`) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret."')";
			$DBRESULT = $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}	
?>