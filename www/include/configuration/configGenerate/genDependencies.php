<?php
/*
 * Copyright 2005-2010 MERETHIS
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
			if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
				$str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
			if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
				$str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
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
	while ($dependency =& $DBRESULT->fetchRow())	{
		$generated = 0;
		$generated2 = 0;
		$strDef = "";
		$query = "SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name ". 
				"FROM dependency_hostgroupParent_relation dhgpr, hostgroup, hostgroup_relation hgr, ns_host_relation ns, host h ". 
				"WHERE dhgpr.dependency_dep_id = '".$dependency["dep_id"]."' ". 
				"AND hostgroup.hg_id = dhgpr.hostgroup_hg_id ".
				"AND dhgpr.hostgroup_hg_id = hgr.hostgroup_hg_id ".
				"AND hgr.host_host_id = ns.host_host_id " .
				"AND ns.host_host_id = h.host_id " . 
				"AND h.host_activate = '1'"; 
					
		$DBRESULT2 =& $pearDB->query($query);
		$hg = array();
		$strTemp1 = "";
		while ($hg =& $DBRESULT2->fetchRow())	{
			if ($gbArr[3][$hg["hg_id"]] && $generatedHG[$hg["hg_id"]]){
				$generated++;
				$strTemp1 != "" ? $strTemp1 .= ", ".$hg["hg_name"] : $strTemp1 = $hg["hg_name"];
			}
		}
		$DBRESULT2->free();
		$query = "SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name ". 
				"FROM dependency_hostgroupChild_relation dhgcr, hostgroup, hostgroup_relation hgr, ns_host_relation ns, host h ".
				"WHERE dhgcr.dependency_dep_id = '".$dependency["dep_id"]."' ".
				"AND hostgroup.hg_id = dhgcr.hostgroup_hg_id " .
				"AND dhgcr.hostgroup_hg_id = hgr.hostgroup_hg_id ".
				"AND hgr.host_host_id = ns.host_host_id " .
				"AND ns.host_host_id = h.host_id " . 
				"AND h.host_activate = '1'";
		
		$DBRESULT2 =& $pearDB->query($query);
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
			if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
				$strDef .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
			if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
				$strDef .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
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
			$hPar = NULL;
			if (isset($gbArr[2][$svPar["host_host_id"]]) && isset($host_instance[$svPar["host_host_id"]])) {						
				$hPar = getMyHostName($svPar["host_host_id"]);
			}
			# Service Child
			$DBRESULT2 =& $pearDB->query("SELECT * FROM dependency_serviceChild_relation WHERE dependency_dep_id = '".$svPar["dependency_dep_id"]."'");
			while ($svCh =& $DBRESULT2->fetchRow())	{
				$hCh = NULL;
				if (isset($gbArr[4][$svCh["service_service_id"]])) {
					if (isset($gbArr[2][$svCh["host_host_id"]]) && isset($gbArr[2][$svCh["host_host_id"]]) && isset($host_instance[$svCh["host_host_id"]]))	{					
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
					$str .= print_line("host_name", $hPar);
					$str .= print_line("service_description", getMyServiceName($svPar["service_service_id"]));

					$str .= print_line("dependent_host_name", $hCh);
					$str .= print_line("dependent_service_description", getMyServiceName($svCh["service_service_id"]));
					
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
		$query = "SELECT DISTINCT servicegroup.sg_id, servicegroup.sg_name ".
				"FROM dependency_servicegroupParent_relation dsgpr, servicegroup, servicegroup_relation sgr, ns_host_relation ns, host h, service s ". 
				"WHERE dsgpr.dependency_dep_id = '".$dependency["dep_id"]."' ". 
				"AND servicegroup.sg_id = dsgpr.servicegroup_sg_id " .
				"AND dsgpr.servicegroup_sg_id = sgr.servicegroup_sg_id " .
				"AND sgr.host_host_id = ns.host_host_id " .
				"AND ns.nagios_server_id = '".$tab['id']."' ".
				"AND ns.host_host_id = h.host_id " . 
				"AND h.host_activate = '1' " .
				"AND sgr.service_service_id = s.service_id " .
				"AND s.service_activate = '1'";
		
		$DBRESULT2 =& $pearDB->query($query);
		$sg = array();
		$strTemp1 = "";
		while ($sg =& $DBRESULT2->fetchRow())	{
			if (isset($gbArr[5][$sg["sg_id"]]))	
				$strTemp1 != "" ? $strTemp1 .= ", ".$sg["sg_name"] : $strTemp1 = $sg["sg_name"];
		}
		$DBRESULT2->free();

		$query = "SELECT DISTINCT servicegroup.sg_id, servicegroup.sg_name ". 
				"FROM dependency_servicegroupChild_relation dsgcr, servicegroup, servicegroup_relation sgr, ns_host_relation ns, host h, service s ". 
				"WHERE dsgcr.dependency_dep_id = '".$dependency["dep_id"]."' ". 
				"AND servicegroup.sg_id = dsgcr.servicegroup_sg_id " .
				"AND dsgcr.servicegroup_sg_id = sgr.servicegroup_sg_id " .
				"AND sgr.host_host_id = ns.host_host_id " .
				"AND ns.nagios_server_id = '".$tab['id']."' " .
				"AND ns.host_host_id = h.host_id " . 
				"AND h.host_activate = '1' " .
				"AND sgr.service_service_id = s.service_id " .
				"AND s.service_activate = '1'";
		
		$DBRESULT2 =& $pearDB->query($query);
		$sg = array();
		$strTemp2 = "";
		while ($sg =& $DBRESULT2->fetchRow()) {
			if (isset($gbArr[5][$sg["sg_id"]]))	
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
			if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != "") 
				$str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
			if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != "") 
				$str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
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