<?php
/*
 * Copyright 2005-2009 MERETHIS
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
	
	global $generatedSG;
	$generatedSG = array();

	$handle = create_file($nagiosCFGPath.$tab['id']."/servicegroups.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM `servicegroup` ORDER BY `sg_name`");
	
	$serviceGroup = array();
	$i = 1;
	$str = NULL;
	while ($serviceGroup = $DBRESULT->fetchRow())	{
		$generated = 0;
		$strDef = "";

		if (isset($gbArr[5][$serviceGroup["sg_id"]])) {
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
			
			if ($serviceGroup["sg_name"])  
				$strDef .= print_line("servicegroup_name", $serviceGroup["sg_name"]);
			if ($serviceGroup["sg_alias"]) 
				$strDef .= print_line("alias", $serviceGroup["sg_alias"]);
			
			/*
			 * Service members
			 */
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
			while ($service =& $DBRESULT2->fetchRow()){
				if (isset($gbArr[4][$service["service_id"]]))	{				
					if ($service["host_id"])	{
						if (isset($gbArr[2][$service["host_id"]]) && isset($host_instance[$service["host_id"]])){
							
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
			while($service =& $DBRESULT2->fetchRow()){
				if (isset($gbArr[4][$service["service_id"]]))	{				
					if ($service["hg_id"])	{
						if (isset($gbArr[3][$service["hg_id"]])){
							$DBRESULT3 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$service["hg_id"]."'");
							while($host =& $DBRESULT3->fetchRow())	{
								if (isset($gbArr[2][$host["host_host_id"]]) && isset($host_instance[$host["host_host_id"]])){
									$service["service_description"] = str_replace("#S#", "/", $service["service_description"]);
									$service["service_description"] = str_replace("#BS#", "\\", $service["service_description"]);
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
			if ($strTemp) 
				$strDef .= print_line("members", $strTemp);
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