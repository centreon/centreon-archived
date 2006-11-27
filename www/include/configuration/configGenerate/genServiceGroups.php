<?
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

	$handle = create_file($nagiosCFGPath."servicegroups.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT * FROM servicegroup ORDER BY `sg_name`");
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	
	$serviceGroup = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($serviceGroup))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$ret["comment"]["comment"] ? ($str .= "# '" . $serviceGroup["sg_name"] . "' servicegroup definition " . $i . "\n") : NULL;
			if ($ret["comment"]["comment"] && $serviceGroup["sg_comment"])	{
				$comment = array();
				$comment = explode("\n", $serviceGroup["sg_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define servicegroup{\n";
			if ($serviceGroup["sg_name"])  $str .= print_line("servicegroup_name", $serviceGroup["sg_name"]);
			if ($serviceGroup["sg_alias"]) $str .= print_line("alias", $serviceGroup["sg_alias"]);
			// Service members
			$service = array();
			$strTemp = NULL;
			$res2 =& $pearDB->query("SELECT service_description, service_id, host_name, host_id " .
									"FROM servicegroup_relation, service, host " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND host.host_id = servicegroup_relation.host_host_id " .
									"AND service.service_activate = '1' " .
									"AND host.host_activate = '1' " .
									"AND  servicegroup_relation.host_host_id IS NOT NULL");
			if (PEAR::isError($res2))
				print "Mysql Error : ".$res2->getMessage();
			while($res2->fetchInto($service)){
				if ($ret["level"]["level"] == 1)
					isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				else if ($ret["level"]["level"]	 == 3)
					$BP = true;
				if ($BP)	{				
					if ($service["host_id"])	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							isset($gbArr[2][$service["host_id"]]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							isset($gbArr[2][$service["host_id"]]) ? $BP = true : NULL;
						else if ($ret["level"]["level"]	 == 3)
							$BP = true;
						if ($BP)
							$strTemp != NULL ? $strTemp .= ", ".$service["host_name"].", ".$service["service_description"] : $strTemp = $service["host_name"].", ".$service["service_description"];
					}
				}
			}

			$res2 =& $pearDB->query("SELECT service_description, service_id, hg_id " .
									"FROM servicegroup_relation, service, hostgroup " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND hostgroup.hg_id = servicegroup_relation.hostgroup_hg_id " .
									"AND service.service_activate = '1' " .
									"AND hostgroup.hg_activate = '1' " .
									"AND servicegroup_relation.hostgroup_hg_id IS NOT NULL ");
			if (PEAR::isError($res2))
				print "Mysql Error : ".$res2->getMessage();
			while($res2->fetchInto($service)){
				if ($ret["level"]["level"] == 1)
					isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				else if ($ret["level"]["level"]	 == 3)
					$BP = true;
				if ($BP)	{				
					if ($service["hg_id"])	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							isset($gbArr[3][$service["hg_id"]]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							isset($gbArr[3][$service["hg_id"]]) ? $BP = true : NULL;
						else if ($ret["level"]["level"]	 == 3)
							$BP = true;
						if ($BP){
							$res3 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$service["hg_id"]."'");
							if (PEAR::isError($res3)) 
								print "Mysql Error : ".$res3->getMessage();
							while($res3->fetchInto($host))	{
								$BP = false;
								if ($ret["level"]["level"] == 1)
									isset($gbArr[2][$host["host_host_id"]]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 2)
									isset($gbArr[2][$host["host_host_id"]]) ? $BP = true : NULL;
								else if ($ret["level"]["level"]	 == 3)
									$BP = true;
								if ($BP)
									$strTemp != NULL ? $strTemp .= ", ".getMyHostName($host["host_host_id"]).", ".$service["service_description"] : $strTemp = getMyHostName($host["host_host_id"]).", ".$service["service_description"];
							}
						}
						$res3->free();
					}
				}
			}
			$res2->free();
			unset($service);
			if ($strTemp) $str .= print_line("members", $strTemp);
			unset($strTemp);
			$str .= "}\n\n";
			$i++;
		}
		unset($serviceGroup);
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."servicegroups.cfg");
	fclose($handle);
	$res->free();
	unset($str);
	unset($i);
?>