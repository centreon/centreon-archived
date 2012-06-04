<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}
	
	require_once ("@CENTREON_ETC@/centreon.conf.php");
	require_once ($centreon_path . "/www/class/centreonHost.class.php");
	require_once ($centreon_path . "/www/class/centreonService.class.php");
	
	/*
	 * Host Extended Information
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/hostextinfo.cfg", $oreon->user->get_name());
	$DBRESULT = $pearDB->query(	"SELECT host_id, host_name FROM host " .
									"WHERE host_activate = '1' AND host_register = '1' " .
									"ORDER BY `host_name`");
									
	$ehi = array();
	$i = 1;
	$str = NULL;
	
	$host_method = new CentreonHost($pearDB);
	
	while ($ehi = $DBRESULT->fetchRow())	{
		if (isset($host_instance[$ehi["host_id"]]) && isset($ehi["host_id"][$gbArr[2]])) {
			$flag = 0;
			$strTmp = "";
			$ret["comment"] ? ($strTmp .= "# '" . $ehi["host_name"] . "' Host Extended Information definition " . $i . "\n") : NULL ;
			$strTmp .= "define hostextinfo{\n";
			if ($ehi["host_name"])
				$strTmp .= print_line("host_name", $ehi["host_name"]);
			
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_notes")){
				$strTmp .= print_line("notes", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_notes_url")){
				$strTmp .= print_line("notes_url", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_action_url")){
				$strTmp .= print_line("action_url", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoImage($ehi["host_id"], "ehi_icon_image", 1)){
				$strTmp .= print_line("icon_image", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_icon_image_alt")){
				$strTmp .= print_line("icon_image_alt", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoImage($ehi["host_id"], "ehi_vrml_image", 1)){
				$strTmp .= print_line("vrml_image", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoImage($ehi["host_id"], "ehi_statusmap_image", 1)){
				$strTmp .= print_line("statusmap_image", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_2d_coords")){
				$strTmp .= print_line("2d_coords", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_3d_coords")){
				$strTmp .= print_line("3d_coords", $host_method->replaceMacroInString($ehi['host_id'], $field));
				$flag++;
			}
			$strTmp .= "}\n\n";
			if ($flag != 0)
				$str .= $strTmp;
			$i++;
			unset($strTmp);
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/hostextinfo.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($ehi);
	unset($str);
	unset($i);

	/*
	 * Service Extended Information
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/serviceextinfo.cfg", $oreon->user->get_name());
	$esi = array();
	$i = 1;
	$str = NULL;

	$svc_method = new CentreonService($pearDB);

	$DBRESULT = $pearDB->query("SELECT service_id, service_description, esi_notes, esi_notes_url, esi_action_url, esi_icon_image, esi_icon_image_alt FROM service, extended_service_information WHERE service_service_id = service_id AND service_register = '1' AND service_activate = '1'");
	while ($esi = $DBRESULT->fetchRow())	{	
		if (isset($esi["service_id"]) && ($esi["esi_notes"] || $esi["esi_notes_url"] || $esi["esi_action_url"] || $esi["esi_icon_image"] || $esi["esi_icon_image_alt"]))	{			
			$hosts = getMyServiceHosts($esi["service_id"]);
			foreach ($hosts as $key=>$value)	{				
				if (isset($gbArr[2][$value]) && isAHostTpl($value))	{										
					$host_name = getMyHostName($value);
					if (isset($host_instance[$value])) {						
						$flag = 0;
						$strTMP = "";
						$service_description = getMyServiceName($esi["service_id"]);
						$service_description = str_replace('#S#', "/", $service_description);
						$service_description = str_replace('#BS#', "\\", $service_description);
						$strTMP .= "# '" . $host_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
						$strTMP .= "define serviceextinfo{\n";
						if ($host_name)
							$strTMP .= print_line("host_name", $host_name);
						if ($service_description){
							$strTMP .= print_line("service_description", $service_description);		
							$flag++;
						}							
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes")){
							$strTMP .= print_line("notes", $svc_method->replaceMacroInString($esi['service_id'], $field));
							$flag++;
						}
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes_url")){
							$strTMP .= print_line("notes_url", $svc_method->replaceMacroInString($esi['service_id'], $field));
							$flag++;
						}
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_action_url")){
							$strTMP .= print_line("action_url", $svc_method->replaceMacroInString($esi['service_id'], $field));
							$flag++;
						}
						if ($field = getMyServiceExtendedInfoImage($esi["service_id"], "esi_icon_image")){
							$strTMP .= print_line("icon_image", $svc_method->replaceMacroInString($esi['service_id'], $field));
							$flag++;
						}
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image_alt")){
							$strTMP .= print_line("icon_image_alt", $svc_method->replaceMacroInString($esi['service_id'], $field));
							$flag++;
						}
						if ($flag != 0)
							$str .= $strTMP."}\n\n";
						$i++;
					}
				}
			}
			$hgs = getMyServiceHostGroups($esi["service_id"]);
			foreach ($hgs as $key => $value)	{
				if (isset($value[$gbArr[3]]))	{
					$flag = 0;
					$strTMP = "";
					$hostgroup_name = getMyHostGroupName($value);
					$service_description = getMyServiceName($esi["service_id"]);
					$service_description = str_replace('#S#', "/", $service_description);
					$service_description = str_replace('#BS#', "\\", $service_description);
					$strTMP .= "# '" . $hostgroup_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
					$strTMP .= "define serviceextinfo{\n";
					if ($hostgroup_name)
						$strTMP .= print_line("hostgroup_name", $hostgroup_name);
					if ($service_description){
						$strTMP .= print_line("service_description", $service_description);		
						$flag++;
					}							
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes")){
						$strTMP .= print_line("notes", $field);
						$flag++;
					}
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes_url")){
						$strTMP .= print_line("notes_url", $field);
						$flag++;
					}
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_action_url")){
						$strTMP .= print_line("action_url", $field);
						$flag++;
					}
					if ($field = getMyServiceExtendedInfoImage($esi["service_id"], "esi_icon_image")){
						$strTMP .= print_line("icon_image", $field);
						$flag++;
					}
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image_alt")){
						$strTMP .= print_line("icon_image_alt", $field);
						$flag++;
					}
					if ($flag != 0)
						$str .= $strTMP."}\n\n";
					$i++;
				}
			}
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/serviceextinfo.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/serviceextinfo.cfg");
	
	$DBRESULT->free();
	unset($esi);
	unset($str);
	unset($i);
?>