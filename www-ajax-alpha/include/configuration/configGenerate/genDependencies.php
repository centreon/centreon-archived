<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	if (!isset($oreon))
		exit();

	$handle = create_file($nagiosCFGPath."dependencies.cfg", $oreon->user->get_name());
	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostParent_relation dhpr WHERE dhpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostChild_relation dhcr WHERE dhcr.dependency_dep_id = dep.dep_id) > 0";
	$res =& $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$dependency = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($dependency))	{
		$BP = false;
		$res2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM dependency_hostParent_relation dhpr, host WHERE dhpr.dependency_dep_id = '".$dependency["dep_id"]."' AND host.host_id = dhpr.host_host_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$host = array();
		$strTemp1 = NULL;
		while ($res2->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp1 != NULL ? $strTemp1 .= ", ".$host["host_name"] : $strTemp1 = $host["host_name"];
		}
		$res2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM dependency_hostChild_relation dhcr, host WHERE dhcr.dependency_dep_id = '".$dependency["dep_id"]."' AND host.host_id = dhcr.host_host_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$host = array();
		$strTemp2 = NULL;
		while ($res2->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp2 != NULL ? $strTemp2 .= ", ".$host["host_name"] : $strTemp2 = $host["host_name"];
		}
		$res2->free();			
		if ($strTemp1 && $strTemp2)	{
			$ret["comment"]["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $dependency["dep_comment"])	{
				$comment = array();
				$comment = explode("\n", $dependency["dep_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define hostdependency{\n";
			$str .= print_line("dependent_host_name", $strTemp2);
			$str .= print_line("host_name", $strTemp1);
			if ($oreon->user->get_version() == 2)	{
				if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != NULL) $str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
				if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != NULL) $str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
			}
			if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != NULL) $str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($dependency);
	$res->free();

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupParent_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupChild_relation dhgcr WHERE dhgcr.dependency_dep_id = dep.dep_id) > 0";
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$res =& $pearDB->query($rq);
	$dependency = array();
	while($res->fetchInto($dependency))	{
		$BP = false;
		$res2 =& $pearDB->query("SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name FROM dependency_hostgroupParent_relation dhgpr, hostgroup WHERE dhgpr.dependency_dep_id = '".$dependency["dep_id"]."' AND hostgroup.hg_id = dhgpr.hostgroup_hg_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$hg = array();
		$strTemp1 = NULL;
		while ($res2->fetchInto($hg))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp1 != NULL ? $strTemp1 .= ", ".$hg["hg_name"] : $strTemp1 = $hg["hg_name"];
		}
		$res2 =& $pearDB->query("SELECT DISTINCT hostgroup.hg_id, hostgroup.hg_name FROM dependency_hostgroupChild_relation dhgcr, hostgroup WHERE dhgcr.dependency_dep_id = '".$dependency["dep_id"]."' AND hostgroup.hg_id = dhgcr.hostgroup_hg_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$hg= array();
		$strTemp2 = NULL;
		while ($res2->fetchInto($hg))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp2 != NULL ? $strTemp2 .= ", ".$hg["hg_name"] : $strTemp2 = $hg["hg_name"];
		}
		$res2->free();			
		if ($strTemp1 && $strTemp2)	{
			$ret["comment"]["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $dependency["dep_comment"])	{
				$comment = array();
				$comment = explode("\n", $dependency["dep_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define hostdependency{\n";
			$str .= print_line("dependent_hostgroup_name", $strTemp2);
			$str .= print_line("hostgroup_name", $strTemp1);
			if ($oreon->user->get_version() == 2)	{
				if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != NULL) $str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
				if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != NULL) $str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
			}
			if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != NULL) $str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($dependency);
	$res->free();

	$res2 =& $pearDB->query("SELECT * FROM dependency_serviceParent_relation dspr, dependency WHERE dependency.dep_id = dspr.dependency_dep_id");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while ($res2->fetchInto($svPar))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($svPar["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($svPar["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$res3 =& $pearDB->query("SELECT DISTINCT * FROM host_service_relation hsr WHERE service_service_id = '".$svPar["service_service_id"]."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			while ($res3->fetchInto($rowPar))	{
				$hPar = NULL;
				$hgPar = NULL;
				if ($rowPar["host_host_id"])	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($rowPar["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($rowPar["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						$res4 =& $pearDB->query("SELECT DISTINCT host_name FROM host WHERE host_id = '".$rowPar["host_host_id"]."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						$host =& $res4->fetchRow();						
						$hPar = $host["host_name"];
					}
				}
				else if ($rowPar["hostgroup_hg_id"])	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($rowPar["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($rowPar["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						$res4 =& $pearDB->query("SELECT DISTINCT hg_name FROM hostgroup WHERE hg_id = '".$rowPar["hostgroup_hg_id"]."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						$hg =& $res4->fetchRow();
						$hgPar = $hg["hg_name"];
					}			
				}
				# Service Child
				$res4 =& $pearDB->query("SELECT * FROM dependency_serviceChild_relation WHERE dependency_dep_id = '".$svPar["dependency_dep_id"]."'");
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
				while ($res4->fetchInto($svCh))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($svCh["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($svCh["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						$res5 =& $pearDB->query("SELECT DISTINCT * FROM host_service_relation hsr WHERE service_service_id = '".$svCh["service_service_id"]."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while ($res5->fetchInto($rowCh))	{
							$hCh = NULL;
							$hgCh = NULL;
							if ($rowCh["host_host_id"])	{
								$BP = false;
								if ($ret["level"]["level"] == 1)
									array_key_exists($rowCh["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 2)
									array_key_exists($rowCh["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 3)
									$BP = true;
								if ($BP)	{
									$res6 =& $pearDB->query("SELECT DISTINCT host_name FROM host WHERE host_id = '".$rowCh["host_host_id"]."'");
									if (PEAR::isError($pearDB)) {
										print "Mysql Error : ".$pearDB->getMessage();
									}
									$host =& $res6->fetchRow();						
									$hCh = $host["host_name"];
									$res6->free();
								}
							}
							else if ($rowCh["hostgroup_hg_id"])	{
								$BP = false;
								if ($ret["level"]["level"] == 1)
									array_key_exists($rowCh["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 2)
									array_key_exists($rowCh["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 3)
									$BP = true;
								if ($BP)	{
									$res6 =& $pearDB->query("SELECT DISTINCT hg_name FROM hostgroup WHERE hg_id = '".$rowCh["hostgroup_hg_id"]."'");
									if (PEAR::isError($pearDB)) {
										print "Mysql Error : ".$pearDB->getMessage();
									}
									$hg =& $res6->fetchRow();
									$hgCh = $hg["hg_name"];
									$res6->free();
								}			
							}
							if ($hPar && $hCh)	{
								$ret["comment"]["comment"] ? ($str .= "# '".$svPar["dep_name"]."' host dependency definition ".$i."\n") : NULL;
								if ($ret["comment"]["comment"] && $svPar["dep_comment"])	{
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
								if ($oreon->user->get_version() == 2)
									if (isset($svPar["inherits_parent"]["inherits_parent"]) && $svPar["inherits_parent"]["inherits_parent"] != NULL) $str .= print_line("inherits_parent", $svPar["inherits_parent"]["inherits_parent"]);
								if (isset($svPar["execution_failure_criteria"]) && $svPar["execution_failure_criteria"] != NULL) $str .= print_line("execution_failure_criteria", $svPar["execution_failure_criteria"]);
								if (isset($svPar["notification_failure_criteria"]) && $svPar["notification_failure_criteria"] != NULL) $str .= print_line("notification_failure_criteria", $svPar["notification_failure_criteria"]);
								$str .= "}\n\n";
								$i++;
							}
							else if ($hgPar && $hgCh)	{
								$ret["comment"]["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : NULL;
								if ($ret["comment"]["comment"] && $dependency["dep_comment"])	{
									$comment = array();
									$comment = explode("\n", $dependency["dep_comment"]);
									foreach ($comment as $cmt)
										$str .= "# ".$cmt."\n";
								}
								$str .= "define servicedependency{\n";
								$str .= print_line("dependent_hostgroup_name", $hgCh);
								$str .= print_line("hostgroup_name", $hgPar);
								$str .= print_line("dependent_service_description", getMyServiceName($svCh["service_service_id"]));
								$str .= print_line("service_description", getMyServiceName($svPar["service_service_id"]));
								if ($oreon->user->get_version() == 2)
									if (isset($svPar["inherits_parent"]["inherits_parent"]) && $svPar["inherits_parent"]["inherits_parent"] != NULL) $str .= print_line("inherits_parent", $svPar["inherits_parent"]["inherits_parent"]);
								if (isset($svPar["execution_failure_criteria"]) && $svPar["execution_failure_criteria"] != NULL) $str .= print_line("execution_failure_criteria", $svPar["execution_failure_criteria"]);
								if (isset($svPar["notification_failure_criteria"]) && $svPar["notification_failure_criteria"] != NULL) $str .= print_line("notification_failure_criteria", $svPar["notification_failure_criteria"]);
								$str .= "}\n\n";
								$i++;
							}
						}
						$res5->free();		
					}		
				}
				$res4->free();	
			}		
			$res3->free();	
		}
	}
	$res2->free();
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $path ."dependencies.cfg");
	fclose($handle);
	unset($str);
	unset($i);
?>