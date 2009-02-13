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

	if (!isset($oreon))
		exit();
	
	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");

	$handle = create_file($nagiosCFGPath.$tab['id']."/dependencies.cfg", $oreon->user->get_name());
	
	/* 
	 * Host Dependancies 
	 */
	
	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostParent_relation dhpr WHERE dhpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostChild_relation dhcr WHERE dhcr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT =& $pearDB->query($rq);
	$dependency = array();
	$i = 1;
	$str = "";
	while ($dependency =& $DBRESULT->fetchRow()) {
		
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM dependency_hostParent_relation dhpr, host, ns_host_relation nhr WHERE host.host_id = nhr.host_host_id AND nhr.nagios_server_id = '".$tab["id"]."' AND dhpr.dependency_dep_id = '".$dependency["dep_id"]."' AND host.host_id = dhpr.host_host_id");
		$host = array();
		$strTemp1 = "";
		while ($host =& $DBRESULT2->fetchRow())	{
			if (isset($host_instance[$host["host_id"]]) && isset($gbArr[2][$host["host_id"]]))	
				$strTemp1 != "" ? $strTemp1 .= ", ".$host["host_name"] : $strTemp1 = $host["host_name"];
		}
		
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM dependency_hostChild_relation dhcr, host, ns_host_relation nhr WHERE host.host_id = nhr.host_host_id AND nhr.nagios_server_id = '".$tab["id"]."' AND dhcr.dependency_dep_id = '".$dependency["dep_id"]."' AND host.host_id = dhcr.host_host_id");
		$host = array();
		$strTemp2 = "";
		while ($host =& $DBRESULT2->fetchRow())	{
			if (isset($host_instance[$host["host_id"]]) && isset($gbArr[2][$host["host_id"]]))	
				$strTemp2 != "" ? $strTemp2 .= ", ".$host["host_name"] : $strTemp2 = $host["host_name"];
		}
		$DBRESULT2->free();			
		if ($strTemp1 && $strTemp2)	{
			$ret["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : "";
			if ($ret["comment"] && $dependency["dep_comment"])	{
				$comment = array();
				$comment = explode("\n", $dependency["dep_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define hostdependency{\n";
			$str .= print_line("dependent_host_name", $strTemp2);
			$str .= print_line("host_name", $strTemp1);
			if ($oreon->user->get_version() >= 2)	{
				if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
					$str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
				if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
					$str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
			}
			if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != "") 
				$str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($dependency);
	$DBRESULT->free();

	/*
	 * HostGroup Dependancies
	 */

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupParent_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupChild_relation dhgcr WHERE dhgcr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT =& $pearDB->query($rq);
	$dependency = array();
	while($dependency =& $DBRESULT->fetchRow())	{
		$BP = false;
		$generated = 0;
		$generated2 = 0;
		$strDef = "";
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name FROM dependency_hostgroupParent_relation dhgpr, hostgroup WHERE dhgpr.dependency_dep_id = '".$dependency["dep_id"]."' AND hostgroup.hg_id = dhgpr.hostgroup_hg_id");
		$hg = array();
		$strTemp1 = "";
		while ($hg =& $DBRESULT2->fetchRow())	{
			if ($gbArr[3][$hg["hg_id"]] && $generatedHG[$hg["hg_id"]]){
				$generated++;
				$strTemp1 != "" ? $strTemp1 .= ", ".$hg["hg_name"] : $strTemp1 = $hg["hg_name"];
			}
		}
		$DBRESULT2->free();
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name FROM dependency_hostgroupChild_relation dhgcr, hostgroup WHERE dhgcr.dependency_dep_id = '".$dependency["dep_id"]."' AND hostgroup.hg_id = dhgcr.hostgroup_hg_id");
		$hg = array();
		$strTemp2 = "";
		while ($hg =& $DBRESULT2->fetchRow())	{
			if ($gbArr[3][$hg["hg_id"]] && $generatedHG[$hg["hg_id"]])	{
				$strTemp2 != "" ? $strTemp2 .= ", ".$hg["hg_name"] : $strTemp2 = $hg["hg_name"];
				$generated2++;
			}
		}
		$DBRESULT2->free();			
		if ($strTemp1 && $strTemp2)	{
			$ret["comment"] ? ($str .= "# '".$dependency["dep_name"]."' hostgroup dependency definition ".$i."\n") : "";
			if ($ret["comment"] && $dependency["dep_comment"])	{
				$comment = array();
				$comment = explode("\n", $dependency["dep_comment"]);
				foreach ($comment as $cmt)
					$strDef .= "# ".$cmt."\n";
			}
			$strDef .= "define hostdependency{\n";
			$strDef .= print_line("dependent_hostgroup_name", $strTemp2);
			$strDef .= print_line("hostgroup_name", $strTemp1);
			if ($oreon->user->get_version() >= 2)	{
				if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
					$strDef .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
				if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
					$strDef .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
			}
			if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != "") 
				$strDef .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
			$strDef .= "}\n\n";
			$i++;
		}
		if ($generated && $generated2){
			$str .= $strDef;	
		}
	}
	unset($dependency);
	$DBRESULT->free();

	/*
	 * Services Dependancies
	 */

	$DBRESULT =& $pearDB->query("SELECT * FROM dependency_serviceParent_relation dspr, dependency WHERE dependency.dep_id = dspr.dependency_dep_id");
	while ($svPar =& $DBRESULT->fetchRow())	{
		if (isset($gbArr[4][$svPar["service_service_id"]]))	{
			if (isset($gbArr[2][$svPar["host_host_id"]]) && isset($host_instance[$svPar["host_host_id"]])) {						
				$hPar = getMyHostName($svPar["host_host_id"]);
			}
			# Service Child
			$DBRESULT2 =& $pearDB->query("SELECT * FROM dependency_serviceChild_relation WHERE dependency_dep_id = '".$svPar["dependency_dep_id"]."'");
			while ($svCh =& $DBRESULT2->fetchRow())	{
				if (isset($gbArr[4][$svCh["service_service_id"]])) {
					if (isset($gbArr[2][$svCh["host_host_id"]]) && isset($gbArr[2][$svCh["host_host_id"]]))	{					
						$hCh = getMyHostName($svCh["host_host_id"]);
					}
				}
				if (isset($hPar) && isset($hCh))	{
					$ret["comment"] ? ($str .= "# '".$svPar["dep_name"]."' host dependency definition ".$i."\n") : "";
					if ($ret["comment"] && $svPar["dep_comment"])	{
						$comment = array();
						$comment = explode("\n", $svPar["dep_comment"]);
						foreach ($comment as $cmt)
							$str .= "# ".$cmt."\n";
					}
					$str .= "define servicedependency{\n";
					$str .= print_line("dependent_host_name", $hCh);
					$str .= print_line("host_name", $hPar);
					$str .= print_line("dependent_service_description", getMyServiceName($svCh["service_service_id"]));
					$str .= print_line("service_description", getMyServiceName($svPar["service_service_id"]));
					if ($oreon->user->get_version() >= 2)
						if (isset($svPar["inherits_parent"]["inherits_parent"]) && $svPar["inherits_parent"]["inherits_parent"] != "") 
							$str .= print_line("inherits_parent", $svPar["inherits_parent"]["inherits_parent"]);
					if (isset($svPar["execution_failure_criteria"]) && $svPar["execution_failure_criteria"] != "") 
						$str .= print_line("execution_failure_criteria", $svPar["execution_failure_criteria"]);
					if (isset($svPar["notification_failure_criteria"]) && $svPar["notification_failure_criteria"] != "") 
						$str .= print_line("notification_failure_criteria", $svPar["notification_failure_criteria"]);
					$str .= "}\n\n";
					$i++;
					unset($hCh);					
				}				
			}
			$DBRESULT2->free();
			unset($hPar);
		}		
	}
	$DBRESULT->free();
	
	/*
	 * ServiceGroup Dependancies
	 */

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_servicegroupParent_relation dsgpr WHERE dsgpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_servicegroupChild_relation dsgcr WHERE dsgcr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT =& $pearDB->query($rq);
	$dependency = array();
	while($dependency =& $DBRESULT->fetchRow())	{
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT servicegroup.sg_id, servicegroup.sg_name FROM dependency_servicegroupParent_relation dsgpr, servicegroup WHERE dsgpr.dependency_dep_id = '".$dependency["dep_id"]."' AND servicegroup.sg_id = dsgpr.servicegroup_sg_id");
		$sg = array();
		$strTemp1 = "";
		while ($sg =& $DBRESULT2->fetchRow())	{
			$BP = false;
			array_key_exists($sg["sg_id"], $gbArr[5]) ? $BP = true : "";
			
			if ($BP)	
				$strTemp1 != "" ? $strTemp1 .= ", ".$sg["sg_name"] : $strTemp1 = $sg["sg_name"];
		}
		$DBRESULT2->free();
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT servicegroup.sg_id, servicegroup.sg_name FROM dependency_servicegroupChild_relation dsgcr, servicegroup WHERE dsgcr.dependency_dep_id = '".$dependency["dep_id"]."' AND servicegroup.sg_id = dsgcr.servicegroup_sg_id");
		$sg = array();
		$strTemp2 = "";
		while ($sg =& $DBRESULT2->fetchRow())	{
			$BP = false;
			array_key_exists($sg["sg_id"], $gbArr[5]) ? $BP = true : "";
			if ($BP)	
				$strTemp2 != "" ? $strTemp2 .= ", ".$sg["sg_name"] : $strTemp2 = $sg["sg_name"];
		}
		$DBRESULT2->free();			
		if ($strTemp1 && $strTemp2)	{
			$ret["comment"] ? ($str .= "# '".$dependency["dep_name"]."' servicegroup dependency definition ".$i."\n") : "";
			if ($ret["comment"] && $dependency["dep_comment"])	{
				$comment = array();
				$comment = explode("\n", $dependency["dep_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define servicedependency{\n";
			$str .= print_line("dependent_servicegroup_name", $strTemp2);
			$str .= print_line("servicegroup_name", $strTemp1);
			if ($oreon->user->get_version() >= 2)	{
				if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
					$str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
				if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
					$str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
			}
			if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != "") 
				$str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($dependency);
	$DBRESULT->free();
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $path .$tab['id']."/dependencies.cfg");
	fclose($handle);
	unset($str);
	unset($i);
?>