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
	function deleteAllConfCFG()	{
		global $pearDB;
		global $oreon;
		//echo "delete all cfg conf<br>";
		$rq = "DELETE FROM command";
		$pearDB->query($rq);
		$rq = "DELETE FROM timeperiod";
		$pearDB->query($rq);
		$rq = "DELETE FROM contact WHERE contact_id != '".$oreon->user->get_id()."'";
		$pearDB->query($rq);
		$rq = "DELETE FROM contactgroup";
		$pearDB->query($rq);
		$rq = "DELETE FROM host";
		$pearDB->query($rq);
		$rq = "DELETE FROM service";
		$pearDB->query($rq);
		$rq = "DELETE FROM hostgroup";
		$pearDB->query($rq);
		$rq = "DELETE FROM servicegroup";
		$pearDB->query($rq);
		$rq = "DELETE FROM dependency";
		$pearDB->query($rq);
		$rq = "DELETE FROM escalation";
		$pearDB->query($rq);
	}
	
	function insertResourceCFG(& $buf)	{
		$i = 0;
		foreach ($buf as $str)	{
			$regs = array();
			$resCFG = array();
			# Fill with buffer value
			if (preg_match("/^[ \t]*([0-9a-zA-Z\_ \$#]+)[ \t]*=[ \t]*(.+)/", $str, $regs))	{
				$resCFG["resource_activate"]["resource_activate"] = "1";
				$resCFG["resource_name"] = trim($regs[1]);
				$resCFG["resource_line"] = trim($regs[1])."=".trim($regs[2]);
				$resCFG["resource_comment"] = trim($regs[1])." ".date("d/m/Y - H:i:s", time());
				# Add in db
				require_once("./include/configuration/configResources/DB-Func.php");
				if (testExistence($resCFG["resource_name"]) && insertResource($resCFG))
					$i++;
			}
			unset($regs);
		}
		return $i;
	}
		
	function deleteResourceCFG()	{
		global $pearDB;
		//echo "delete all resources<br>";
		$rq = "DELETE * FROM cfg_resource; ";
		$pearDB->query($rq);
	}
	
	function insertNagiosCFG(& $buf)	{
		$nagiosCFG = array();
		$flag = false;	
		# Fill with buffer value
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/^[ \t]*([0-9a-zA-Z\_]+)[ \t]*=[ \t]*(.+)/", $str, $regs))	{
				switch($regs[1])	{
					case "cfg_file" :
						if (!$flag)	{
							$path = explode("/", $regs[2]);
							array_pop($path);
							$regs[2] = implode("/", $path);
							if (!trim($regs[2]))
								$nagiosCFG["cfg_pwd"] = "/";
							else
								$nagiosCFG["cfg_pwd"] = trim($regs[2])."/";
							$flag = true;
						}
						break;
					case "global_host_event_handler" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "global_service_event_handler" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "ocsp_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "ochp_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "host_perfdata_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "service_perfdata_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "host_perfdata_file_processing_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					case "service_perfdata_file_processing_command" : $nagiosCFG[trim($regs[1])] = getMyCommandID(trim($regs[2])); break;
					default : $nagiosCFG[trim($regs[1])] = trim($regs[2]); break;
				}
			}
			unset($regs);
		}
		# Add Oreon comment
		if ($nagiosCFG)	{
			$nagiosCFG["nagios_activate"]["nagios_activate"] = "0";
			$nagiosCFG["nagios_name"] = "nagios.cfg ".date("d m Y - H:i:s", time());
			$nagiosCFG["nagios_comment"] = "nagios.cfg ".date("d/m/Y - H:i:s", time());
		}
		# Add in db
		require_once("./include/configuration/configNagios/DB-Func.php");
		if (insertNagios($nagiosCFG))
			return true;
		else
			return false;
	}

	function deleteNagiosCFG()	{
		global $pearDB;
		//echo "delete all nagios<br>";
		$rq = "DELETE FROM cfg_nagios; "; 	
		$pearDB->query($rq);
	}
		
	function insertCgiCFG(& $buf)	{
		$cgiCFG = array();
		$flag = false;	
		# Fill with buffer value
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/^[ \t]*([0-9a-zA-Z\_]+)[ \t]*=[ \t]*(.+)/", $str, $regs))
				$cgiCFG[trim($regs[1])] = trim($regs[2]);
			unset($regs);
		}
		# Add Oreon comment
		if ($cgiCFG)	{
			$cgiCFG["cgi_activate"]["cgi_activate"] = "0";
			$cgiCFG["cgi_name"] = "cgi.cfg ".date("d m Y - H:i:s", time());
			$cgiCFG["cgi_comment"] = "cgi.cfg ".date("d/m/Y - H:i:s", time());
		}
		# Add in db
		require_once("./include/configuration/configCGI/DB-Func.php");
		if (insertCGIInDB($cgiCFG))
			return true;
		else
			return false;
	}
	
	function deleteCgiCFG()	{
		global $pearDB;
		$rq = "DELETE FROM cfg_cgi; "; 	
		$pearDB->query($rq);
	}
	
	function deletePerfparseCFG()	{
		global $pearDB;
		//echo "delete all perpar<br>";
		$rq = "DELETE FROM cfg_perfparse; "; 	
		$pearDB->query($rq);
	}
		
	function insertCFG(& $buf, & $ret)	{
		$typeDef = NULL;
		global $nbr;
		global $oreon;
		$nbr = array("cmd"=>0, "tp"=>0, "cct"=>0, "cg"=>0, "h"=>0, "hg"=>0, "hd"=>0, "sv"=>0, "svd"=>0, "sg"=>0, "sgd"=>0, "hei"=>0);
		$tmpConf = array();
		$get = false;
		$regexp = "/^[ \t]*(.[^ \t#]+)[ \t]+(.[^;]+)/";
		# Fill with buffer value
		# Turn 1 -> Time Period, Commands
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				switch ($typeDef)	{
					case "command": insertCommandCFG($tmpConf, $ret); break;
					case "timeperiod": insertTimePeriodCFG($tmpConf); break;						
					default :; break;
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			# Limit only to cfg conf we need
			if (preg_match("/^[ \t]*define (timeperiod|command)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Turn 2 -> contacts
		reset($buf);
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				insertContactCFG($tmpConf);
				$get = false;
				$tmpConf = array();
			}
			if (preg_match("/^[ \t]*define contact[ \t]*{/", $str, $def))
				$get = true;
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Turn 3 -> Contact Groups
		reset($buf);
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				insertContactGroupCFG($tmpConf);
				$get = false;
				$tmpConf = array();
			}
			if (preg_match("/^[ \t]*define contactgroup[ \t]*{/", $str, $def))
				$get = true;
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Turn 4 -> Hosts
		reset($buf);
		$useTpl = array();
		$useTpls = array();
		$parentsTMP = array();
		require_once("./include/configuration/configObject/host/DB-Func.php");
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				$useTpl = insertHostCFG($tmpConf);
				$useTpls[$useTpl[0]] = $useTpl[1];
				isset($useTpl[2]) ? $parentsTMP[$useTpl[0]] = $useTpl[2] : NULL;
				$get = false;
				$tmpConf = array();
			}
			if (preg_match("/^[ \t]*define host[ \t]*{/", $str, $def))
				$get = true;
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Update Host Parents relation when we have record all host definition
		foreach($parentsTMP as $key=>$value)	{
			$tmpConf["host_parents"] = explode(",", $value);
			foreach ($tmpConf["host_parents"] as $key2=>$value2)
				$tmpConf["host_parents"][$key2] = getMyHostID(trim($value2));
			updateHostHostParent($key, $tmpConf);
		}
		# Update Host Template relation when we have record all host definition
		updateHostTemplateUsed($useTpls);
		# Turn 5 -> Host Groups
		reset($buf);
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				switch ($typeDef)	{
					case "hostgroup": insertHostGroupCFG($tmpConf); break;
					case "hostextinfo": insertHostExtInfoCFG($tmpConf); break;					
					default :; break;
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			if (preg_match("/^[ \t]*define (hostgroup|hostextinfo)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Turn 6 -> Services
		reset($buf);
		$useTpl = array();
		$useTpls = array();
		require_once("./include/configuration/configObject/service/DB-Func.php");
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				switch ($typeDef)	{
					case "service": $useTpl = insertServiceCFG($tmpConf); $useTpls[$useTpl[0]] = $useTpl[1]; break;
					case "hostdependency": insertHostDependencyCFG($tmpConf); break;	
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			if (preg_match("/^[ \t]*define (service|hostdependency)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Update Service Template relation when we have record all service definition
		updateServiceTemplateUsed($useTpls);
		# Turn 7 -> Service Groups
		reset($buf);
		
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				switch ($typeDef)	{
					case "servicegroup": insertServiceGroupCFG($tmpConf);  break;					
					default :; break;
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			if (preg_match("/^[ \t]*define (servicegroup)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		# Turn 8 -> Service Dependencies
		reset($buf);
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str) && $get)	{
				switch ($typeDef)	{
					case "servicedependency": insertServiceDependencyCFG($tmpConf);  break;					
					default :; break;
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			if (preg_match("/^[ \t]*define (servicedependency)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		/*
		reset($buf);
		foreach ($buf as $str)	{
			$regs = array();
			if (preg_match("/}/", $str))	{
				switch ($typeDef)	{
					case "hostgroupescalation": print "hge:"; print_r ($tmpConf);  break;
					case "hostescalation": print "host esc:"; print_r ($tmpConf);  break;
					case "serviceescalation": print "sv esc:"; print_r ($tmpConf);  break;
					default :; break;
				}
				$get = false;
				$tmpConf = array();
				$typeDef = NULL;
			}
			if (preg_match("/^[ \t]*define ([a-zA-Z0-9\_\-]+)[ \t]*{/", $str, $def))	{
				$typeDef = $def[1];
				$get = true;
			}
			else if ($get)	{
				if (preg_match($regexp, $str, $regs))
					$tmpConf[$regs[1]] = trim($regs[2]);
			}
			unset($regs);
		}
		*/
		return $nbr;
	}
	
	function insertContactCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/contact/DB-Func.php");
		if (isset($tmpConf["contact_name"]) && testContactExistence($tmpConf["contact_name"]))	{
			foreach ($tmpConf as $key=>$value)
				switch($key)	{
					case "alias" : $tmpConf["contact_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "host_notification_options" : $tmpConf["contact_hostNotifOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "service_notification_options" : $tmpConf["contact_svNotifOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "host_notification_period" : $tmpConf["timeperiod_tp_id"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "service_notification_period" : $tmpConf["timeperiod_tp_id2"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "email" : $tmpConf["contact_email"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "pager" : $tmpConf["contact_pager"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "host_notification_commands" : 
						$tmpConf["contact_hostNotifCmds"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["contact_hostNotifCmds"] as $key2=>$value2)
							$tmpConf["contact_hostNotifCmds"][$key2] = getMyCommandID(trim($value2));
						unset ($tmpConf[$key]); 
						break;
					case "service_notification_commands" : 
						$tmpConf["contact_svNotifCmds"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["contact_svNotifCmds"] as $key2=>$value2)
							$tmpConf["contact_svNotifCmds"][$key2] = getMyCommandID(trim($value2));
						unset ($tmpConf[$key]); 
						break;
					case "contactgroups" :
						$tmpConf["contact_cgNotif"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["contact_cgNotif"] as $key2=>$value2)
							$tmpConf["contact_cgNotif"][$key2] = getMyContactGroupID(trim($value2));
						unset ($tmpConf[$key]); 
						break;
				}
			$tmpConf["contact_oreon"]["contact_oreon"] = "0";
			$tmpConf["contact_admin"]["contact_admin"] = "0";
			$tmpConf["contact_type_msg"] = "txt";
			$tmpConf["contact_lang"] = "en";
			$tmpConf["contact_activate"]["contact_activate"] = "1";
			$tmpConf["contact_comment"] = date("d/m/Y - H:i:s", time());
			insertContactInDB($tmpConf);
			$nbr["cct"] += 1;
			return true;
		}
		return false;
	}
	
	function insertContactGroupCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/contactgroup/DB-Func.php");
		if (isset($tmpConf["contactgroup_name"]) && testContactGroupExistence($tmpConf["contactgroup_name"]))	{
			foreach ($tmpConf as $key=>$value)
				switch($key)	{
					case "contactgroup_name" : $tmpConf["cg_name"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "alias" : $tmpConf["cg_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "members" :
						$tmpConf["cg_contacts"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["cg_contacts"] as $key2=>$value2)
							$tmpConf["cg_contacts"][$key2] = getMyContactID(trim($value2));
						unset ($tmpConf[$key]);
						break;
				}
			$tmpConf["cg_activate"]["cg_activate"] = "1";
			$tmpConf["cg_comment"] = date("d/m/Y - H:i:s", time());
			insertContactGroupInDB($tmpConf);
			$nbr["cg"] += 1;
			return true;
		}
		return false;
	}
	
	function insertHostCFG($tmpConf = array())	{
		$use = NULL;
		$useTpl = array();
		global $nbr;
		if (isset($tmpConf["host_name"]) && testHostExistence($tmpConf["host_name"]) || isset($tmpConf["name"]) && testHostExistence($tmpConf["name"]))	{
			foreach ($tmpConf as $key=>$value)	{
				switch($key)	{
					case "use" : $use = trim($tmpConf[$key]); unset ($tmpConf[$key]); break;
					case "name" : $tmpConf["host_name"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "alias" : $tmpConf["host_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "address" : $tmpConf["host_address"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "max_check_attempts" : $tmpConf["host_max_check_attempts"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "check_interval" : $tmpConf["host_check_interval"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "freshness_threshold" : $tmpConf["host_freshness_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "low_flap_threshold" : $tmpConf["host_low_flap_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "high_flap_threshold" : $tmpConf["host_high_flap_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "notification_interval" : $tmpConf["host_notification_interval"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;

					case "active_checks_enabled" : $tmpConf["host_active_checks_enabled"]["host_active_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "checks_enabled" : $tmpConf["host_checks_enabled"]["host_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "passive_checks_enabled" : $tmpConf["host_passive_checks_enabled"]["host_passive_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "obsess_over_host" : $tmpConf["host_obsess_over_host"]["host_obsess_over_host"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "check_freshness" : $tmpConf["host_check_freshness"]["host_check_freshness"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "event_handler_enabled" : $tmpConf["host_event_handler_enabled"]["host_event_handler_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "flap_detection_enabled" : $tmpConf["host_flap_detection_enabled"]["host_flap_detection_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "process_perf_data" : $tmpConf["host_process_perf_data"]["host_process_perf_data"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "retain_status_information" : $tmpConf["host_retain_status_information"]["host_retain_status_information"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "retain_nonstatus_information" : $tmpConf["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "notifications_enabled" : $tmpConf["host_notifications_enabled"]["host_notifications_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "register" : $tmpConf["host_register"]["host_register"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;

					case "notification_options" : $tmpConf["host_notifOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "stalking_options" : $tmpConf["host_stalOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;

					case "check_command" : 
						$cmd =& explode("!", trim($tmpConf[$key]));
						$tmpConf["command_command_id"] = getMyCommandID(array_shift($cmd));
						if (count($cmd)) 
							$tmpConf["command_command_id_arg"] = "!".implode("!", $cmd);
						unset ($tmpConf[$key]);
						break;
					case "event_handler" : 
						$cmd =& explode("!", trim($tmpConf[$key]));
						$tmpConf["command_command_id2"] = getMyCommandID(array_shift($cmd));
						if (count($cmd)) 
							$tmpConf["command_command_id2_arg"] = "!".implode("!", $cmd);
						unset ($tmpConf[$key]);
						break;
					case "parents" : $tmpConf["host_parentsTMP"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "check_period" : $tmpConf["timeperiod_tp_id"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
					case "notification_period" : $tmpConf["timeperiod_tp_id2"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
					
					case "contact_groups" :
						$tmpConf["host_cgs"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["host_cgs"] as $key2=>$value2)
							$tmpConf["host_cgs"][$key2] = getMyContactGroupID(trim($value2));
						unset ($tmpConf[$key]);
						break;					
					case "hostgroups" :
						$tmpConf["host_hgs"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["host_hgs"] as $key2=>$value2)
							$tmpConf["host_hgs"][$key2] = getMyHostGroupID(trim($value2));
						unset ($tmpConf[$key]);
						break;
				}
			}
			if (isset($tmpConf["host_register"]["host_register"]))	{
				if ($tmpConf["host_register"]["host_register"] == '1')
					$tmpConf["host_register"]["host_register"] = '1';
				else
					$tmpConf["host_register"]["host_register"] = '0';
			}
			else
				$tmpConf["host_register"]["host_register"] = '1';
			$tmpConf["host_activate"]["host_activate"] = "1";
			$tmpConf["host_comment"] = date("d/m/Y - H:i:s", time());
			$tmpConf["ehi_notes"] = NULL;
			$tmpConf["ehi_notes_url"] = NULL;
			$tmpConf["ehi_action_url"] = NULL;
			$tmpConf["ehi_icon_image"] = NULL;
			$tmpConf["ehi_icon_image_alt"] = NULL;
			$tmpConf["ehi_vrml_image"] = NULL;
			$tmpConf["ehi_statusmap_image"] = NULL;
			$tmpConf["ehi_2d_coords"] = NULL;
			$tmpConf["ehi_3d_coords"] = NULL;
			$tmpConf["country_id"] = NULL;
			$tmpConf["city_name"] = NULL;
			$useTpl[0] = insertHostInDB($tmpConf);
			$useTpl[1] = $use;
			isset($tmpConf["host_parentsTMP"]) ? $useTpl[2] = $tmpConf["host_parentsTMP"] : NULL;
			$nbr["h"] += 1;
			return $useTpl;
		}
	}
	
	function insertHostExtInfoCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/host/DB-Func.php");
		foreach ($tmpConf as $key=>$value)
			switch($key)	{
				case "notes" : $tmpConf["ehi_notes"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "notes_url" : $tmpConf["ehi_notes_url"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "action_url" : $tmpConf["ehi_action_url"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "icon_image" : $tmpConf["ehi_icon_image"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "icon_image_alt" : $tmpConf["ehi_icon_image_alt"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "vrml_image" : $tmpConf["ehi_vrml_image"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "statusmap_image" : $tmpConf["ehi_statusmap_image"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "2d_coords" : $tmpConf["ehi_2d_coords"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "3d_coords" : $tmpConf["ehi_3d_coords"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;

				case "host_name" : 
					$tmpConf["host_names"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["host_names"] as $key2=>$value2)
						$tmpConf["host_names"][$key2] = getMyHostID(trim($value2));
					unset ($tmpConf[$key]);
					break;
			}
		foreach($tmpConf["host_names"] as $key=>$value)	{
			updateHostExtInfos($value, $tmpConf);
			$nbr["hei"] += 1;
		}
		return true;
	}
	
	function insertHostGroupCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/hostgroup/DB-Func.php");
		if (isset($tmpConf["hostgroup_name"]) && testHostGroupExistence($tmpConf["hostgroup_name"]))	{
			foreach ($tmpConf as $key=>$value)
				switch($key)	{
					case "hostgroup_name" : $tmpConf["hg_name"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "alias" : $tmpConf["hg_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "members" :
						$tmpConf["hg_hosts"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["hg_hosts"] as $key2=>$value2)
							$tmpConf["hg_hosts"][$key2] = getMyHostID(trim($value2));
						unset ($tmpConf[$key]);
						break;
					case "contact_groups" :
						$tmpConf["hg_cgs"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["hg_cgs"] as $key2=>$value2)
							$tmpConf["hg_cgs"][$key2] = getMyContactGroupID(trim($value2));
						unset ($tmpConf[$key]);
						break;
				}
			$tmpConf["hg_activate"]["hg_activate"] = "1";
			$tmpConf["hg_comment"] = date("d/m/Y - H:i:s", time());
			insertHostGroupInDB($tmpConf);
			$nbr["hg"] += 1;
			return true;
		}
		return false;
	}
	
	function insertHostDependencyCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/host_dependency/DB-Func.php");
		require_once("./include/configuration/configObject/hostgroup_dependency/DB-Func.php");
		foreach ($tmpConf as $key=>$value)
			switch($key)	{
				case "inherits_parent" : $tmpConf["inherits_parent"]["inherits_parent"] = $tmpConf[$key]; break;
				case "execution_failure_criteria" : $tmpConf["execution_failure_criteria"] = array_flip(explode(",", $tmpConf[$key])); break;
				case "notification_failure_criteria" : $tmpConf["notification_failure_criteria"] = array_flip(explode(",", $tmpConf[$key])); break;
				case "dependent_host_name" :
					$tmpConf["dep_hostChilds"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hostChilds"] as $key2=>$value2)
						$tmpConf["dep_hostChilds"][$key2] = getMyHostID(trim($value2));
					unset ($tmpConf[$key]);
					break;				
				case "host_name" :
					$tmpConf["dep_hostParents"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hostParents"] as $key2=>$value2)
						$tmpConf["dep_hostParents"][$key2] = getMyHostID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "dependent_hostgroup_name" :
					$tmpConf["dep_hgChilds"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hgChilds"] as $key2=>$value2)
						$tmpConf["dep_hgChilds"][$key2] = getMyHostGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "hostgroup_name" :
					$tmpConf["dep_hgParents"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hgParents"] as $key2=>$value2)
						$tmpConf["dep_hgParents"][$key2] = getMyHostGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
			}
		if (isset($tmpConf["dep_hgParents"]) && isset($tmpConf["dep_hgChilds"]))	{
			$nbr["hd"] += 1;
			$tmpConf["dep_name"] = "Host Dependency ".$nbr["hd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_description"] = "Host Dependency ".$nbr["hd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_comment"] = date("d/m/Y - H:i:s", time());
			insertHostGroupDependencyInDB($tmpConf);
		}
		else if (isset($tmpConf["dep_hostParents"]) && isset($tmpConf["dep_hostChilds"]))	{
			$nbr["hd"] += 1;
			$tmpConf["dep_name"] = "Host Dependency ".$nbr["hd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_description"] = "Host Dependency ".$nbr["hd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_comment"] = date("d/m/Y - H:i:s", time());
			insertHostDependencyInDB($tmpConf);	
		}
		return true;
	}
	
	function insertServiceDependencyCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/service_dependency/DB-Func.php");
		require_once("./include/configuration/configObject/servicegroup_dependency/DB-Func.php");
		foreach ($tmpConf as $key=>$value)
			switch($key)	{
				case "inherits_parent" : $tmpConf["inherits_parent"]["inherits_parent"] = $tmpConf[$key]; break;
				case "execution_failure_criteria" : $tmpConf["execution_failure_criteria"] = array_flip(explode(",", $tmpConf[$key])); break;
				case "notification_failure_criteria" : $tmpConf["notification_failure_criteria"] = array_flip(explode(",", $tmpConf[$key])); break;
				case "dependent_host_name" :
					$tmpConf["dep_hChi"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hChi"] as $key2=>$value2)
						$tmpConf["dep_hChi"][$key2] = getMyHostID(trim($value2));
					unset ($tmpConf[$key]);
					break;				
				case "host_name" :
					$tmpConf["dep_hPar"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hPar"] as $key2=>$value2)
						$tmpConf["dep_hPar"][$key2] = getMyHostID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "dependent_hostgroup_name" :
					$tmpConf["dep_hgChi"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hgChi"] as $key2=>$value2)
						$tmpConf["dep_hgChi"][$key2] = getMyHostGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "hostgroup_name" :
					$tmpConf["dep_hgPar"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_hgPar"] as $key2=>$value2)
						$tmpConf["dep_hgPar"][$key2] = getMyHostGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "dependent_servicegroup_name" :
					$tmpConf["dep_sgChilds"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_sgChilds"] as $key2=>$value2)
						$tmpConf["dep_sgChilds"][$key2] = getMyServiceGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
				case "servicegroup_name" :
					$tmpConf["dep_sgParents"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["dep_sgParents"] as $key2=>$value2)
						$tmpConf["dep_sgParents"][$key2] = getMyServiceGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;

				case "dependent_service_description" :
					if (isset($tmpConf["dep_hChi"]))	{
						$tmpConf["dep_hSvChi"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["dep_hSvChi"] as $key2=>$value2)						
							foreach ($tmpConf["dep_hChi"] as $key3=>$value3)	{
								if (array_key_exists($key2, $tmpConf["dep_hSvChi"]) && ($tmpConf["dep_hSvChi"][$key2] != getMyServiceID(trim($value2), $value3)))
									$tmpConf["dep_hSvChi"][count($tmpConf["dep_hSvChi"])] = getMyServiceID(trim($value2), $value3);
								else
									$tmpConf["dep_hSvChi"][$key2] = getMyServiceID(trim($value2), $value3);
							}
					}
					else if (isset($tmpConf["dep_hgChi"]))	{
						$tmpConf["dep_hgSvChi"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["dep_hgSvChi"] as $key2=>$value2)
							foreach ($tmpConf["dep_hgChi"] as $key3=>$value3)	{
								if (array_key_exists($key2, $tmpConf["dep_hgSvChi"]) && ($tmpConf["dep_hgSvChi"][$key2] != getMyServiceID(trim($value2), NULL, $value3)))
									$tmpConf["dep_hgSvChi"][count($tmpConf["dep_hgSvChi"])] = getMyServiceID(trim($value2), NULL, $value3);
								else
									$tmpConf["dep_hgSvChi"][$key2] = getMyServiceID(trim($value2), NULL, $value3);
							}
					}
					unset ($tmpConf[$key]);
					break;				
				case "service_description" :
					if (isset($tmpConf["dep_hPar"]))	{
						$tmpConf["dep_hSvPar"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["dep_hSvPar"] as $key2=>$value2)
							foreach ($tmpConf["dep_hPar"] as $key3=>$value3)	{
								if (array_key_exists($key2, $tmpConf["dep_hSvPar"]) && ($tmpConf["dep_hSvPar"][$key2] != getMyServiceID(trim($value2), $value3)))
									$tmpConf["dep_hSvPar"][count($tmpConf["dep_hSvPar"])] = getMyServiceID(trim($value2), $value3);
								else
									$tmpConf["dep_hSvPar"][$key2] = getMyServiceID(trim($value2), $value3);
							}
					}
					else if (isset($tmpConf["dep_hgPar"]))	{
						$tmpConf["dep_hgSvPar"] = explode(",", $tmpConf[$key]);
						foreach ($tmpConf["dep_hgSvPar"] as $key2=>$value2)
							foreach ($tmpConf["dep_hgPar"] as $key3=>$value3)	{
								if (array_key_exists($key2, $tmpConf["dep_hgSvPar"]) && ($tmpConf["dep_hgSvPar"][$key2] != getMyServiceID(trim($value2), NULL, $value3)))
									$tmpConf["dep_hgSvPar"][count($tmpConf["dep_hgSvPar"])] = getMyServiceID(trim($value2), NULL, $value3);
								else
									$tmpConf["dep_hgSvPar"][$key2] = getMyServiceID(trim($value2), NULL, $value3);
							}
					}
					unset ($tmpConf[$key]);
					break;
			}
		if ((isset($tmpConf["dep_hSvPar"]) && isset($tmpConf["dep_hSvChi"])) || (isset($tmpConf["dep_hgSvPar"]) && isset($tmpConf["dep_hgSvChi"])))	{
			$nbr["svd"] += 1;
			$tmpConf["dep_name"] = "Service Dependency ".$nbr["svd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_description"] = "Service Dependency ".$nbr["svd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_comment"] = date("d/m/Y - H:i:s", time());
			insertServiceDependencyInDB($tmpConf);
		}
		else if (isset($tmpConf["dep_sgParents"]) && isset($tmpConf["dep_sgChilds"]))	{
			$nbr["sgd"] += 1;
			$tmpConf["dep_name"] = "SG Dependency ".$nbr["sgd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_description"] = "SG Dependency ".$nbr["sgd"]." - ".date("d/m/Y - H:i:s", time());
			$tmpConf["dep_comment"] = date("d/m/Y - H:i:s", time());
			insertServiceGroupDependencyInDB($tmpConf);	
		}
		return true;
	}
	
	function insertServiceCFG($tmpConf = array())	{
		$use = NULL;
		$rrd_host = NULL;
		$rrd_service = NULL;
		$useTpl = array();
		global $nbr;
		foreach ($tmpConf as $key=>$value)
			switch($key)	{
				case "use" : $use = trim($tmpConf[$key]); unset ($tmpConf[$key]); break;
				case "name" : $tmpConf["service_description"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "description" : $tmpConf["service_description"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "max_check_attempts" : $tmpConf["service_max_check_attempts"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "normal_check_interval" : $tmpConf["service_normal_check_interval"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "retry_check_interval" : $tmpConf["service_retry_check_interval"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "freshness_threshold" : $tmpConf["service_freshness_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "low_flap_threshold" : $tmpConf["service_low_flap_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "high_flap_threshold" : $tmpConf["service_high_flap_threshold"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "notification_interval" : $tmpConf["service_notification_interval"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;

				case "is_volatile" : $tmpConf["service_is_volatile"]["service_is_volatile"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "active_checks_enabled" : $tmpConf["service_active_checks_enabled"]["service_active_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "checks_enabled" : $tmpConf["service_checks_enabled"]["service_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "passive_checks_enabled" : $tmpConf["service_passive_checks_enabled"]["service_passive_checks_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "parallelize_check" : $tmpConf["service_parallelize_check"]["service_parallelize_check"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "obsess_over_service" : $tmpConf["service_obsess_over_service"]["service_obsess_over_service"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "check_freshness" : $tmpConf["service_check_freshness"]["service_check_freshness"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "event_handler_enabled" : $tmpConf["service_event_handler_enabled"]["service_event_handler_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "flap_detection_enabled" : $tmpConf["service_flap_detection_enabled"]["service_flap_detection_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "process_perf_data" : $tmpConf["service_process_perf_data"]["service_process_perf_data"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "retain_status_information" : $tmpConf["service_retain_status_information"]["service_retain_status_information"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "retain_nonstatus_information" : $tmpConf["service_retain_nonstatus_information"]["service_retain_nonstatus_information"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "notifications_enabled" : $tmpConf["service_notifications_enabled"]["service_notifications_enabled"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				case "register" : $tmpConf["service_register"]["service_register"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;

				case "notification_options" : $tmpConf["service_notifOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;
				case "stalking_options" : $tmpConf["service_stalOpts"] = array_flip(explode(",", $tmpConf[$key])); unset ($tmpConf[$key]); break;

				case "check_command" : 
					$cmd =& explode("!", trim($tmpConf[$key]));
					$cmd_name =& array_shift($cmd);
					$tmpConf["command_command_id"] = getMyCommandID($cmd_name);
					if (strstr($cmd_name, "check_graph"))
						$rrd_service = array_pop($cmd);
					if (count($cmd))
						$tmpConf["command_command_id_arg"] = "!".implode("!", $cmd);
					unset ($tmpConf[$key]);
					break;
				case "event_handler" : 
					$cmd =& explode("!", trim($tmpConf[$key]));
					$cmd_name =& array_shift($cmd);
					$tmpConf["command_command_id2"] = getMyCommandID($cmd_name);
					if (strstr($cmd_name, "check_graph"))
						$cmd = array_pop($cmd);
					if (count($cmd)) 
						$tmpConf["command_command_id2_arg"] = "!".implode("!", $cmd);
					unset ($tmpConf[$key]);
					break;
				case "check_period" : $tmpConf["timeperiod_tp_id"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
				case "notification_period" : $tmpConf["timeperiod_tp_id2"] = getMyTPID(trim($tmpConf[$key])); unset ($tmpConf[$key]); break;
				
				case "contact_groups" :
					$tmpConf["service_cgs"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["service_cgs"] as $key2=>$value2)
						$tmpConf["service_cgs"][$key2] = getMyContactGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;				
				case "host_name" :
					$tmpConf["service_hPars"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["service_hPars"] as $key2=>$value2)	{
						$tmpConf["service_hPars"][$key2] = getMyHostID(trim($value2));
						$rrd_host = getMyHostID(trim($value2));
					}
					unset ($tmpConf[$key]);
					break;				
				case "hostgroup_name" :
					$tmpConf["service_hgPars"] = explode(",", $tmpConf[$key]);
					foreach ($tmpConf["service_hgPars"] as $key2=>$value2)
						$tmpConf["service_hgPars"][$key2] = getMyHostGroupID(trim($value2));
					unset ($tmpConf[$key]);
					break;
			}
		if (isset($tmpConf["service_register"]["service_register"]))	{
			if ($tmpConf["service_register"]["service_register"] == '1')
				$tmpConf["service_register"]["service_register"] = '1';
			else
				$tmpConf["service_register"]["service_register"] = '0';
		}
		else
			$tmpConf["service_register"]["service_register"] = '1';
		$tmpConf["service_activate"]["service_activate"] = "1";
		$tmpConf["service_comment"] = date("d/m/Y - H:i:s", time());
		$useTpl[0] = insertServiceInDB($tmpConf);
		$useTpl[1] = $use;
		if ($rrd_service)
			copyRrdDB($rrd_service, $useTpl[0], $rrd_host);
		$nbr["sv"] += 1;
		return $useTpl;
	}	
	
	function insertServiceGroupCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/servicegroup/DB-Func.php");
		if (isset($tmpConf["servicegroup_name"]) && testServiceGroupExistence($tmpConf["servicegroup_name"]))	{
			foreach ($tmpConf as $key=>$value)
				switch($key)	{
					case "servicegroup_name" : $tmpConf["sg_name"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "alias" : $tmpConf["sg_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "members" :
						$sg_servicesTMP = explode(",", $tmpConf[$key]);
						for ($i = 0, $j = 0; $i < count($sg_servicesTMP); $i += 2)	{
							$tmpConf["sg_hServices"][$j] = getMyServiceID(trim($sg_servicesTMP[$i+1]), getMyHostID(trim($sg_servicesTMP[$i])));
							$j++;
						}
						unset ($tmpConf[$key]);
						break;
				}
			$tmpConf["sg_activate"]["sg_activate"] = "1";
			$tmpConf["sg_comment"] = date("d/m/Y - H:i:s", time());
			insertServiceGroupInDB($tmpConf);
			$nbr["sg"] += 1;
			return true;
		}
		return false;
	}
	
	function insertTimePeriodCFG($tmpConf = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/timeperiod/DB-Func.php");
		if (isset($tmpConf["timeperiod_name"]) && testTPExistence($tmpConf["timeperiod_name"]))	{			
			foreach ($tmpConf as $key=>$value)
				switch($key)	{
					case "timeperiod_name" : $tmpConf["tp_name"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "alias" : $tmpConf["tp_alias"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "sunday" : $tmpConf["tp_sunday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "monday" : $tmpConf["tp_monday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "tuesday" : $tmpConf["tp_tuesday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "wednesday" : $tmpConf["tp_wednesday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "thursday" : $tmpConf["tp_thursday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "friday" : $tmpConf["tp_friday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
					case "saturday" : $tmpConf["tp_saturday"] = $tmpConf[$key]; unset ($tmpConf[$key]); break;
				}
			insertTimeperiodInDB($tmpConf);
			$nbr["tp"] += 1;
			return true;
		}
		return false;
	}
	
	function insertCommandCFG($tmpConf = array(), $ret = array())	{
		global $nbr;
		require_once("./include/configuration/configObject/command/DB-Func.php");
		if (isset($tmpConf["command_name"]) && testCmdExistence($tmpConf["command_name"]))	{
			$tmpConf["command_type"]["command_type"] = $ret["cmdType"]["cmdType"];
			$tmpConf["command_example"] = NULL;
			insertCommandInDB($tmpConf);
			$nbr["cmd"] += 1;
			return true;
		}
		return false;
	}
	
	function deleteAll()	{
		deleteAllConfCFG();
		deleteResourceCFG();
		deleteNagiosCFG();
		deleteCgiCFG();
		deletePerfparseCFG();
	}
	
?>