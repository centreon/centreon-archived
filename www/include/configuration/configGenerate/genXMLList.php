<?php
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Drill Down Map » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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
	
	unlink($XMLConfigPath."osm_list.xml");
	$handle = create_file($XMLConfigPath."osm_list.xml", $oreon->user->get_name(), false);
	$str = NULL;
	$str = "<osm_list>\n";
	$str .= "<elements>\n";
	
	#
	##	Listing
	#
	
	# Host List
	foreach($gbArr[2] as $key => $value)	{
		$res =& $pearDB->query("SELECT host_name, host_template_model_htm_id, host_address, host_register, ehi.city_id FROM host, extended_host_information ehi WHERE host_id = '".$key."' AND ehi.host_host_id = host_id LIMIT 1");
		$host = $res->fetchRow();
		if ($host["host_register"])	{
			if (!$host["host_name"])
				$host["host_name"] = getMyHostName($host['host_template_model_htm_id']);
			if (!$host["host_address"])
				$host["host_address"] = getMyHostAddress($host['host_template_model_htm_id']);
			$str .= "<h id='".$key."' name='".html_entity_decode($host["host_name"], ENT_QUOTES)."' address='".$host["host_address"]."'";
			if ($host["city_id"])	{
				$res2 =& $pearDB->query("SELECT city_lat, city_long FROM view_city WHERE city_id = '".$host["city_id"]."' LIMIT 1");
				$gps =& $res2->fetchRow();
				if ($gps["city_lat"] && $gps["city_long"])
					$str .= " gps='true' lat='".$gps["city_lat"]."' long='".$gps["city_long"]."'";
				else
					$str .= " gps='false'";
				$res2->free();
			}
			else
				$str .= " gps='false'";
			$str .= "/>\n";
		}
		else
			unset($gbArr[2][$key]);
	}
	# Host Group List
	foreach($gbArr[3] as $key => $value)	{		
		$res =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$key."'");
		$hostGroup = $res->fetchRow();
		$str .= "<hg id='".$key."' name='".html_entity_decode($hostGroup["hg_name"], ENT_QUOTES)."'";
		if ($hostGroup["city_id"])	{
			$res2 =& $pearDB->query("SELECT city_lat, city_long FROM view_city WHERE city_id = '".$hostGroup["city_id"]."' LIMIT 1");
			$gps =& $res2->fetchRow();
			if ($gps["city_lat"] && $gps["city_long"])
				$str .= " gps='true' lat='".$gps["city_lat"]."' long='".$gps["city_long"]."'";
			else
				$str .= " gps='false'";
			$res2->free();
		}
		else
			$str .= " gps='false'";		
		$str .= "/>\n";
	}
	# Services List
	foreach($gbArr[4] as $key => $value)	{		
		$res =& $pearDB->query("SELECT DISTINCT sv.service_description, sv.service_template_model_stm_id, service_register, hsr.host_host_id, hsr.hostgroup_hg_id FROM service sv, host_service_relation hsr WHERE sv.service_id = '".$key."' AND hsr.service_service_id = sv.service_id");
		while ($res->fetchInto($sv))	{
			if ($sv["service_register"])	{
				if (!$sv["service_description"])
					$sv["service_description"] = getMyServiceName($sv['service_template_model_stm_id']);
				if ($sv["host_host_id"])
					$str .= "<sv id='".$sv["host_host_id"]."_".$key."' name='".$sv["service_description"]."'/>\n";
				else if ($sv["hostgroup_hg_id"])	{
					$res2 =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$sv["hostgroup_hg_id"]."'");
					while ($res2->fetchInto($host))
						if (array_key_exists($host["host_host_id"], $gbArr[2]))
							$str .= "<sv id='".$host["host_host_id"]."_".$key."' name='".$sv["service_description"]."'/>\n";
				}
			}
			else
				unset($gbArr[4][$key]);
		}
	}
	# Service Group List
	foreach($gbArr[5] as $key => $value)	{		
		$res =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$key."'");
		$serviceGroup = $res->fetchRow();
		$str .= "<sg id='".$key."' name='".html_entity_decode($serviceGroup["sg_name"], ENT_QUOTES)."'";
		if ($serviceGroup["city_id"])	{
			$res2 =& $pearDB->query("SELECT city_lat, city_long FROM view_city WHERE city_id = '".$serviceGroup["city_id"]."' LIMIT 1");
			$gps =& $res2->fetchRow();
			if ($gps["city_lat"] && $gps["city_long"])
				$str .= " gps='true' lat='".$gps["city_lat"]."' long='".$gps["city_long"]."'";
			else
				$str .= " gps='false'";
			$res2->free();
		}
		else
			$str .= " gps='false'";	
		$str .= "/>\n";
	}
	# OSL
	foreach($gbArr[6] as $key => $value)	{		
		$res =& $pearDB->query("SELECT name FROM osl WHERE osl_id = '".$key."'");
		$osl = $res->fetchRow();
		$str .= "<osl id='".$key."' name='".html_entity_decode($osl["name"], ENT_QUOTES)."'/>\n";
	}	
	# Meta Service
	foreach($gbArr[7] as $key => $value)	{		
		$res =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$key."'");
		$osm = $res->fetchRow();
		$str .= "<ms id='".$key."' name='".html_entity_decode($osm["meta_name"], ENT_QUOTES)."'/>\n";
	}
	$str .= "</elements>\n";
	
	#
	##	Dependencies
	#
	$str .= "<dependencies>\n";
	
	#	Host
	foreach($gbArr[2] as $key => $value)	{
		$res =& $pearDB->query("SELECT host_template_model_htm_id AS tpl, host_register FROM host WHERE host_id = '".$key."'");
		$host = $res->fetchRow();
		$str .= "<h id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		# Host Groups
		$res =& $pearDB->query("SELECT hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.host_host_id = '".$key."'");
		while($res->fetchInto($hostGroup))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .="<hg id='".$hostGroup["hostgroup_hg_id"]."'/>\n";
		}
		# Hosts
		$res =& $pearDB->query("SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr WHERE hpr.host_host_id = '".$key."'");
		//if (!$res->numRows() && $host["tpl"])
		//	$res =& getMyHostParents($host["tpl"]);
		while($res->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_parent_hp_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_parent_hp_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_parent_hp_id"]."'/>\n";
		}
		$str .= "</prts>\n";
		## Childs
		$str .= "<chds>\n";
		# Hosts
		$res =& $pearDB->query("SELECT host_host_id FROM host_hostparent_relation WHERE host_parent_hp_id = '".$key."'");
		while($res->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_host_id"]."'/>\n";
		}
		# Services from Host
		$res =& $pearDB->query("SELECT hsr.service_service_id FROM host_service_relation hsr WHERE hsr.host_host_id = '".$key."'");
		while($res->fetchInto($service))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<sv id='".$key."_".$service["service_service_id"]."'/>\n";
		}
		# Services from Host Group
		$res =& $pearDB->query("SELECT hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.host_host_id = '".$key."'");
		while($res->fetchInto($hostGroup))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	{
				$res2 =& $pearDB->query("SELECT hsr.service_service_id FROM host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hostGroup["hostgroup_hg_id"]."'");
				while($res2->fetchInto($service))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$str .= "<sv id='".$key."_".$service["service_service_id"]."'/>\n";
				}			
			}
		}		
		$str .= "</chds>\n";
		$str .= "</h>\n";
	}
	# HostGroup
	foreach($gbArr[3] as $key => $value)	{
		$str .= "<hg id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";		
		$res =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$key."'");
		while($res->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_host_id"]."'/>\n";
		}
		$str .= "</chds>\n";
		$str .= "</hg>\n";
	}
	# Service
	foreach($gbArr[4] as $key => $value)	{
		$res =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$key."'");
		while ($res->fetchInto($sv))	{
			if ($sv["host_host_id"])	{
				$str .= "<sv id='".$sv["host_host_id"]."_".$key."'>\n";								
				## Parents
				$str .= "<prts>\n";
				$str .= "<h id='".$sv["host_host_id"]."'/>\n";
				$str .= "</prts>\n";						
				## Childs
				$str .= "<chds>\n";
				$str .= "</chds>\n";
				$str .= "</sv>\n";
			}
			else if ($sv["hostgroup_hg_id"])	{
				$res2 =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$sv["hostgroup_hg_id"]."'");
				while ($res2->fetchInto($host))
					if (array_key_exists($host["host_host_id"], $gbArr[2]))	{
						$str .= "<sv id='".$host["host_host_id"]."_".$key."'>\n";				
						## Parents
						$str .= "<prts>\n";
						$str .= "<h id='".$host["host_host_id"]."'/>\n";
						$str .= "</prts>\n";						
						## Childs
						$str .= "<chds>\n";
						$str .= "</chds>\n";
						$str .= "</sv>\n";
					}
			}			
		}
	}
	# ServiceGroup
	foreach($gbArr[5] as $key => $value)	{
		$str .= "<sg id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";
		$res =& $pearDB->query("SELECT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$key."'");
		while($res->fetchInto($service))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	{
				$res2 =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service["service_service_id"]."'");
				while($res2->fetchInto($service2))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)	{
						array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
						array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					}
					else if ($ret["level"]["level"] == 2)	{
						array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
						array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					}
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						if ($service2["hostgroup_hg_id"])	{
							$res3 =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$service2["hostgroup_hg_id"]."'");
							while($res3->fetchInto($service3))	{
								$BP = false;
								if ($ret["level"]["level"] == 1)
									array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 2)
									array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 3)
									$BP = true;
								if ($BP)
									$str .= "<sv id='".$service3["host_host_id"]."_".$service["service_service_id"]."'/>\n";
							}	
							unset($service3);						
						}
						else
							$str .= "<sv id='".$service2["host_host_id"]."_".$service["service_service_id"]."'/>\n";
					}
				}
				$res2->free();
			}
		}
		$res->free();
		$str .= "</chds>\n";
		$str .= "</sg>\n";
	}
	# OSL
	foreach($gbArr[6] as $key => $value)	{
		$str .= "<osl id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$res =& $pearDB->query("SELECT id_osl FROM osl_indicator WHERE id_indicator_osl = '".$key."'");
		while($res->fetchInto($osl))
			$str .= "<osl id='".$osl["id_osl"]."'/>";
		$res->free();
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";
		$res =& $pearDB->query("SELECT host_id, service_id, id_indicator_osl, meta_id FROM osl_indicator WHERE id_osl = '".$key."' AND activate = '1'");
		while($res->fetchInto($osl))	{
			if ($osl["host_id"] && $osl["service_id"])	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($osl["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($osl["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)
					$str .= "<sv id='".$osl["host_id"]."_".$osl["service_id"]."'/>";
			}
			else if ($osl["id_indicator_osl"])	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($osl["id_indicator_osl"], $gbArr[6]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($osl["id_indicator_osl"], $gbArr[6]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)
					$str .= "<osl id='".$osl["id_indicator_osl"]."'/>";
			}
			else if ($osl["meta_id"])
				$str .= "<oms id='".$osl["meta_id"]."'/>";
		}
		$res->free();
		$str .= "</chds>\n";		
		$str .= "</osl>\n";
	}
	# Meta Service
	foreach($gbArr[7] as $key => $value)	{
		$str .= "<ms id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";
		$res =& $pearDB->query("SELECT meta_select_mode, regexp_str FROM meta_service WHERE meta_id = '".$key."'");
		$meta =& $res->fetchrow();
		$res->free();
		# Regexp mode
		if ($meta["meta_select_mode"] == 2)	{
			$res =& $pearDB->query("SELECT service_id FROM service WHERE service_description LIKE '".$meta["regexp_str"]."'");
			while($res->fetchInto($service))	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$res2 =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service["service_id"]."'");
					while($res2->fetchInto($service2))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)	{
							array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
							array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
						}
						else if ($ret["level"]["level"] == 2)	{
							array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
							array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
						}
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)	{
							if ($service2["hostgroup_hg_id"])	{
								$res3 =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$service2["hostgroup_hg_id"]."'");
								while($res3->fetchInto($service3))	{
									$BP = false;
									if ($ret["level"]["level"] == 1)
										array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
									else if ($ret["level"]["level"] == 2)
										array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
									else if ($ret["level"]["level"] == 3)
										$BP = true;
									if ($BP)
										$str .= "<sv id='".$service3["host_host_id"]."_".$service["service_id"]."'/>\n";
								}	
								unset($service3);						
							}
							else
								$str .= "<sv id='".$service2["host_host_id"]."_".$service["service_id"]."'/>\n";
						}
					}
					$res2->free();
				}
			}
			$res->free();
		}
		else if ($meta["meta_select_mode"] == 1)	{
			require_once("./DBPerfparseConnect.php");
			$res =& $pearDB->query("SELECT meta_id, host_id, metric_id FROM meta_service_relation msr WHERE meta_id = '".$key."' AND activate = '1'");
			while($res->fetchInto($metric))	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($metric["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($metric["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$res2 =& $pearDBpp->query("SELECT service_description FROM perfdata_service_metric WHERE metric_id = '".$metric["metric_id"]."'");
					$ppService =& $res2->fetchRow();
					$sv_id =& getMyServiceID($ppService["service_description"], $metric["host_id"]);
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($sv_id, $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($sv_id, $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$str .= "<sv id='".$metric["host_id"]."_".$sv_id."'/>\n";
				}
			}
			$res->free();
		}
		$str .= "</chds>\n";		
		$str .= "</ms>\n";
	}
	
	$str .= "</dependencies>\n";
	$str .= "</osm_list>";
	write_in_file($handle, $str, $XMLConfigPath."osm_list.xml");
	fclose($handle);
	$res->free();
	unset($str);
?>