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

	$generatedSG = array();

	$handle = create_file($nagiosCFGPath.$tab['id']."/servicegroups.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM `servicegroup` ORDER BY `sg_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	
	$serviceGroup = array();
	$i = 1;
	$str = NULL;
	while ($serviceGroup = $DBRESULT->fetchRow())	{
		$BP = false;
		$generated = 0;
		$strDef = "";
		array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
		
		if ($BP)	{
			$ret["comment"] ? ($strDef .= "# '" . $serviceGroup["sg_name"] . "' servicegroup definition " . $i . "\n") : NULL;
			if ($ret["comment"] && $serviceGroup["sg_comment"])	{
				$comment = array();
				$comment = explode("\n", $serviceGroup["sg_comment"]);
				foreach ($comment as $cmt)
					$strDef .= "# ".$cmt."\n";
			}
			$strDef .= "define servicegroup{\n";
			$serviceGroup["sg_name"] = str_replace("#S#", "/", $serviceGroup["sg_name"]);
			$serviceGroup["sg_name"] = str_replace("#BS#", "\\", $serviceGroup["sg_name"]);
			
			if ($serviceGroup["sg_name"])  $strDef .= print_line("servicegroup_name", $serviceGroup["sg_name"]);
			if ($serviceGroup["sg_alias"]) $strDef .= print_line("alias", $serviceGroup["sg_alias"]);
			// Service members
			$service = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT service_description, service_id, host_name, host_id " .
									"FROM servicegroup_relation, service, host " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND host.host_id = servicegroup_relation.host_host_id " .
									"AND service.service_activate = '1' " .
									"AND host.host_activate = '1' " .
									"AND  servicegroup_relation.host_host_id IS NOT NULL");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while($service =& $DBRESULT2->fetchRow()){
				isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				
				if ($BP)	{				
					if ($service["host_id"])	{
						$BP = false;
						isset($gbArr[2][$service["host_id"]]) ? $BP = true : NULL;
						
						if ($BP && isHostOnThisInstance($service["host_id"], $tab['id'])){
							$service["service_description"] = str_replace("#S#", "/", $service["service_description"]);
							$service["service_description"] = str_replace("#BS#", "\\", $service["service_description"]);
			
							$strTemp != NULL ? $strTemp .= ", ".$service["host_name"].", ".$service["service_description"] : $strTemp = $service["host_name"].", ".$service["service_description"];
							$generated++;
						}
					}
				}
			}

			$DBRESULT2 =& $pearDB->query("SELECT service_description, service_id, hg_id " .
									"FROM servicegroup_relation, service, hostgroup " .
									"WHERE servicegroup_sg_id = '".$serviceGroup["sg_id"]."' " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND hostgroup.hg_id = servicegroup_relation.hostgroup_hg_id " .
									"AND service.service_activate = '1' " .
									"AND hostgroup.hg_activate = '1' " .
									"AND servicegroup_relation.hostgroup_hg_id IS NOT NULL ");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while($service =& $DBRESULT2->fetchRow()){
				isset($gbArr[4][$service["service_id"]]) ? $BP = true : NULL;
				
				if ($BP)	{				
					if ($service["hg_id"])	{
						$BP = false;
						isset($gbArr[3][$service["hg_id"]]) ? $BP = true : NULL;
						
						if ($BP){
							$DBRESULT3 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$service["hg_id"]."'");
							if (PEAR::isError($DBRESULT3)) 
								print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
							while($host =& $DBRESULT3->fetchRow())	{
								$BP = false;
								isset($gbArr[2][$host["host_host_id"]]) ? $BP = true : NULL;
								
								if ($BP && isHostOnThisInstance($host["host_host_id"],$tab['id'])){
									$strTemp != NULL ? $strTemp .= ", ".getMyHostName($host["host_host_id"]).", ".$service["service_description"] : $strTemp = getMyHostName($host["host_host_id"]).", ".$service["service_description"];
									$generated++;
								}
							}
							$DBRESULT3->free();
						}
					}
				}
			}
			$DBRESULT2->free();
			unset($service);
			if ($strTemp) $strDef .= print_line("members", $strTemp);
			unset($strTemp);
			$strDef .= "}\n\n";
			$i++;
		}
		if ($generated){
			$str .= $strDef;
			$generatedSG[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];	
		}
		unset($serviceGroup);
	}
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/servicegroups.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>