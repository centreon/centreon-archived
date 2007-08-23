<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
	
	if (file_exists("./include/monitoring/functions.php"))
		include_once("./include/monitoring/functions.php");
	else if (file_exists("../functions.php"))
		include_once("../functions.php");
	
	if (!isset($pearDB))
		global $pearDB;
	if (!isset($pearDBO))
		global $pearDBO;
	
	// Is User Admin ?
	$DBRESULT1 =& $pearDB->query("SELECT contact_admin FROM contact, session WHERE contact.contact_id = session.user_id AND session_id = '$sid'");
	if (PEAR::isError($DBRESULT1))
		print "DB Error : ".$DBRESULT1->getDebugInfo()."<br>";
	$DBRESULT1->fetchInto($user);
	$user_admin = $user["contact_admin"];
	
	// Read 
	$DBRESULT1 =& $pearDB->query("SELECT * FROM general_opt");
	if (PEAR::isError($DBRESULT1))
		print "DB Error : ".$DBRESULT1->getDebugInfo()."<br>";	
	$DBRESULT1->fetchInto($general_opt);
	$version = $general_opt["nagios_version"];
	
	// reload Session
	$session_id_cache = session_id(); 
	if (isset($_POST["sid"]) && $_POST["sid"])
		$session_id = $_POST["sid"];
	else if (isset($session_id_cache))
		$session_id = session_id();
	else 
		$session_id = NULL;
	
	if (isset($session_id) && $session_id){
		$DBRESULT =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$session_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if($session =& $DBRESULT->fetchRow()){
			$time_limit = time() - $general_opt["session_expire"] * 60;
			if ($session["last_reload"] >= $time_limit){
				$DBRESULT2 =& $pearDB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".$session_id."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			}
		}
	}
	
	# Init tab
	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	
	# Stats
	if (is_object($oreon)){
		$oreon->status_graph_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
		$oreon->status_graph_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => "0");
	}
	
	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	if (!isset($isRestreint))
		$isRestreint = HadUserLca($pearDB);
	
	$debug = 0;
		
	unset ($host_status);
	unset ($service_status);
	
	if (isset($pearDBO))
		$DBRESULT =& $pearDBO->query("SELECT fast_parsing FROM config");
	if (PEAR::isError($DBRESULT)){
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$fast_parsing = 0;
	} else {
		$DBRESULT->fetchInto($config);
		if (isset($config['fast_parsing']))
			$fast_parsing = $config['fast_parsing'];		
		else 
			$fast_parsing = 0;
	}
	# Read File
	$DBRESULT1 =& $pearDB->query("SELECT status_file FROM cfg_nagios WHERE nagios_activate = '1'");
	if (PEAR::isError($DBRESULT1))
		print "DB Error : ".$DBRESULT1->getDebugInfo()."<br>";
	$DBRESULT1->fetchInto($nagios_cfg);
	
	if (($version == 1 || $version == 2) && $fast_parsing == 0)
		$file = $nagios_cfg["status_file"];
 	else if ($version == 2 && $fast_parsing == 1)
		$file = $nagios_cfg["status_file"]."_light";
	
	if ((isset($_GET["hg_name"]) && $_GET["hg_name"])|| (isset($_POST["hg_name"]) && $_POST["hg_name"])){
		if (isset($_GET["hg_name"]) && $_GET["hg_name"])
			$hg_name = $_GET["hg_name"];
		if (isset($_POST["hg_name"]) && $_POST["hg_name"])
			$hg_name = $_POST["hg_name"];	
		
		$hostgroup = array();
		$DBRESULT1 =& $pearDB->query("SELECT host_name FROM host, hostgroup, hostgroup_relation WHERE hostgroup.hg_name = '".$hg_name."' AND hostgroup_relation.hostgroup_hg_id = hostgroup.hg_id AND host.host_id = hostgroup_relation.host_host_id");
		if (PEAR::isError($DBRESULT1))
			print "DB Error : ".$DBRESULT1->getDebugInfo()."<br>";
		while ($DBRESULT1->fetchInto($h))
			$hostgroup[$h["host_name"]] = 1;
		unset($h);
	}
	
	// Open File
	if (file_exists($file)){
		$log_file = fopen($file, "r");
	 	$status_proc = 1;
	} else {
	  	$log_file = 0;
	  	$status_proc = 0;
	}
	
	if (isset($oreon) && is_object($oreon)){
		if (isset ($_GET["search"]))
			$search = $_GET["search"];
		else if (isset($oreon->historySearch[$url]))
			$search = $oreon->historySearch[$url];
		else 
			$search = NULL;
		
		if (isset($_GET["search_type_service"])){
			$search_type_service = $_GET["search_type_service"];
			$oreon->search_type_service = $_GET["search_type_service"];
		} else if (isset($oreon->search_type_service))
			 $search_type_service = $oreon->search_type_service;
		else 
			$search_type_service = NULL;
			
		if (isset($_GET["search_type_host"])){
			$search_type_host = $_GET["search_type_host"];
			$oreon->search_type_host = $_GET["search_type_host"];
		} else if (isset($oreon->search_type_host))
			 $search_type_host = $oreon->search_type_host;
		else 
			$search_type_host = NULL;
	}
		
	// init table
	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();
		
	$time = time();	
	if ($version == 1 && !$fast_parsing){
	  if ($log_file)
	    while ($str = fgets($log_file))	{
      	  	// set last update 
	     	$last_update = date("d-m-Y h:i:s");
	      	if (!preg_match("/^\#.*/", $str)){		// get service stat
				$log = split(";", $str);
				if (preg_match("/^[\[\]0-9]* SERVICE[.]*/", $str)){
		  			if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log['1']]))) && strcmp($log[1], "OSL_Module")){
						$service_status[$log["1"]."_".$log["2"]] = get_service_data($log);
			    		$tab_host_service[$log["1"]][$log["2"]] = "1";
			 		} else if (!strcmp($log[1], "Meta_Module")){
			    		$metaService_status[$log["2"]] = get_service_data($log);
		  			}
				} else if (preg_match("/^[\[\]0-9]* HOST[.]*/", $str) && strcmp($log[1], "OSL_Module")){ // get host stat
		  			if ((!isset($hostgroup) && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log["1"]])))) || (isset($hostgroup) && isset($hostgroup[$log["1"]]) && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log["1"]]))))){
		    			$host_status[$log["1"]] = get_host_data($log);
		    			$tab_host_service[$log["1"]] = array();
		  			}
				} else if (preg_match("/^[\[\]0-9]* PROGRAM[.]*/", $str))
		  			$program_data = get_program_data($log, $status_proc);
		  	}
	      	unset($str);
		}
	} else if ($version == 2 && !$fast_parsing){
		if ($log_file)
	    	while ($str = fgets($log_file)) {
	      		$last_update = date("d-m-Y h:i:s");
	      		if (!preg_match("/^\#.*/", $str)){
					###################### SERVICE ######################
					if (preg_match("/^service/", $str)){
				  		$log = array();
				  		while ($str2 = fgets($log_file))
		          			if (!strpos($str2, "}")){      
			      				if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)/", $str2, $tab))
									$svc_data[$tab[1]] = $tab[2];	
			    			} else
			      				break;
			      		if (strstr("Meta_Module", $svc_data['host_name'])){
			      			$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
			      			$metaService_status[$svc_data["service_description"]] = $svc_data;
			      		} else {
			      			if ((isset($hostgroup) && isset($hostgroup[$svc_data['host_name']])) || (!isset($hostgroup))){
								if (isset($svc_data['plugin_output']) && $svc_data['plugin_output'])
									$svc_data['plugin_output'] =  str_replace("&", "", $svc_data['plugin_output']);
								else
									$svc_data['plugin_output'] = "";
								if (isset($_GET["host_name"]) && strcmp($_GET["host_name"], "OSL_Module") && $_GET["host_name"] == $svc_data["host_name"] && isset($_GET["service_description"]) && $_GET["service_description"] == $svc_data["service_description"]){
									$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
							      	$service_status[$svc_data["host_name"] . "_" . $svc_data["service_description"]] = $svc_data;
							      	$tab_host_service[$svc_data["host_name"]][$svc_data["service_description"]] = "1";
							      	if (is_object($oreon))
							      		$oreon->status_graph_service[$svc_data['current_state']]++;	
									break;
								} else {		
									if (isset($svc_data['host_name']) && strcmp($svc_data['host_name'], "OSL_Module") && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$svc_data['host_name']])))
										&& (($search && $search_type_host == 1 &&  strpos(strtolower($svc_data['host_name']), strtolower($search)) !== false)||($search &&$search_type_service == 1 && strpos(strtolower($svc_data['service_description']), strtolower($search)) !== false) 
										||($search_type_service == NULL && $search_type_host == NULL)|| !$search)){
						      			
						      			$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
						      			$service_status[$svc_data["host_name"] . "_" . $svc_data["service_description"]] = $svc_data;
						      			$tab_host_service[$svc_data["host_name"]][$svc_data["service_description"]] = "1";
						      			if (is_object($oreon))
						      				$oreon->status_graph_service[$svc_data['current_state']]++;
						  			}
								}
			      			}
			      		}
			      		unset($svc_data);
			      	##################### HOST #########################
					} else if (preg_match("/^host/", $str)){ // get host stat
						$host_data = array();
			  			while ($str2 = fgets($log_file))
				    		if (!strpos($str2, "}")){
				      			if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
									$host_data[$tab[1]] = $tab[2];
				    		} else
				      			break;
			      		if ((!isset($hostgroup) && isset($host_data['host_name']) && strcmp($host_data['host_name'], "OSL_Module") && strcmp($host_data['host_name'], "Meta_Module") && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_data['host_name']]))))||( isset($hostgroup) && isset($hostgroup[$host_data['host_name']]) && isset($host_data['host_name']) && strcmp($host_data['host_name'], "OSL_Module") && strcmp($host_data['host_name'], "Meta_Module") && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_data['host_name']]))))){
			      				if ((isset($hostgroup) && isset($hostgroup[$host_data['host_name']])) || (!isset($hostgroup))){						      			
						      		$host_data["current_state"] = $tab_status_host[$host_data['current_state']];
									$host_status[$host_data["host_name"]] = $host_data;
									if (is_object($oreon))
										$oreon->status_graph_host[$host_data['current_state']]++;
			      				}
			      		}
			      		unset($host_data);
			      	################## PROGRAM ############################
					} else if (preg_match("/^program/", $str)){
		          		while ($str2 = fgets($log_file))
		            		if (!strpos($str2, "}")){
		              			if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab))
		                			$pgr_nagios_stat[$tab[1]] = $tab[2];
		            		} else
		              			break;
		          		unset($log);
		        	} else if (preg_match("/^info/", $str)){
		          		while ($str2 = fgets($log_file))
		            		if (!strpos($str2, "}")){
		            	  		if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab))
		                			$pgr_nagios_stat[$tab[1]] = $tab[2];
		            		} else
		              			break;
		        	}
					unset($str);	
	      		}
	    	}
	} else if ($fast_parsing){
		if ($version == 1)
			print "Can't enable fast parsing status log for nagios 1";
		if ($log_file && $version == 2)
		    while ($str = fgets($log_file))	{
		      	// set last update 
		     	$last_update = date("d-m-Y h:i:s");
		      	$log = split("#", $str);
				if (preg_match("/^p\#/", $str)){
			  		$program_data = getProgramDataParsed($log, $status_proc);
			  	} else if (preg_match("/^s/", $str)){
					if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log['1']]))) && strcmp($log[1], "OSL_Module") && strcmp($log[1], "Meta_Module")){
						$svc_data = getServiceDataParsed($log);
						# Check search					
						if  (	($search && $search_type_host == 1 && strpos(strtolower($svc_data['host_name']), strtolower($search)) !== false)
							||	($search && $search_type_service == 1 && strpos(strtolower($svc_data['service_description']), strtolower($search)) !== false) 
							||	($search_type_service == NULL && $search_type_host == NULL)
							|| 	!$search){
							$svc_data['plugin_output']=  str_replace("&", "", $svc_data['plugin_output']);
							$service_status[$log["1"]."_".$log["2"]] = $svc_data;
					   		$tab_host_service[$log["1"]][$log["2"]] = "1";
					   		if (is_object($oreon))
						   		$oreon->status_graph_service[$service_status[$log["1"]."_".$log["2"]]['current_state']]++;
						}
					} else if (!strcmp($log[1], "Meta_Module"))
				  		$metaService_status[$log["2"]] = getServiceDataParsed($log);
				} else if (preg_match("/^h*/", $str) && strcmp($log[1], "OSL_Module")){ // get host stat
			  		if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log["1"]])))){
			    		$tab_host_service[$log["1"]] = array();
			    		$host_data = getHostDataParsed($log);
			    		$host_status[$log["1"]] = $host_data;
			    		unset($host_data);
			    		if (is_object($oreon))
				    		$oreon->status_graph_host[$host_status[$log["1"]]['current_state']]++;
			  		}
				}
		      	unset($str);
			}
	}
	
	$row_data = array();
	if (isset($_GET["o"]) && $_GET["o"] == "svcSch" && !isset($_GET["sort_types"])){
		$_GET["sort_types"] = "next_check";
		$_GET["order"] = "SORT_ASC";
	}
	
	if ((!isset($_GET["o"]) || !$_GET["o"]) && isset($o))
		$_GET["o"] = $o;
	
	if (isset($_GET["o"]) && ($_GET["o"] == "svcpb" || $_GET["o"] == "svc_warning" || $_GET["o"] == "svc_critical" || $_GET["o"] == "svc_unknown")){
		if (!isset($_GET["sort_types"])){
			$_GET["sort_types"] = $general_opt["problem_sort_type"];
			if ($_GET["sort_types"] == "last_state_change")
				$_GET["order"] = "SORT_".($general_opt["problem_sort_order"] == "ASC" ? "DESC" : "ASC");
			else
				$_GET["order"] = "SORT_".$general_opt["problem_sort_order"];
		}
		if (!isset($_GET["order"]))
			$_GET["order"] = "SORT_".$general_opt["problem_sort_order"];
	} else {
		 $_GET["sort_types"] = (isset($_GET["sort_types"])) ? $_GET["sort_types"] :"host_name"; 
		 $_GET["order"] = (isset($_GET["order"])) ? $_GET["order"] :"SORT_ASC"; 
		//$_GET["sort_types"] = "host_name";
		//$_GET["order"] = "SORT_ASC";
	}
	
	if (isset($_GET["sort_types"]) && $_GET["sort_types"]){
	  	foreach ($service_status as $key => $row)
	  		$row_data[$key] = $row[$_GET["sort_types"]];
	  	 if (count($service_status) > 1)
	  	 	!strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $service_status) : array_multisort($row_data, SORT_DESC, $service_status);
	}

	if (isset($_GET["sort_typeh"]) && $_GET["sort_typeh"] && count($host_status) > 2){
		$row_data = array();
		foreach ($host_status as $key => $row)
	    	$row_data[$key] = $row[$_GET["sort_typeh"]];

	    !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $host_status) : array_multisort($row_data, SORT_DESC, $host_status);
	}
?>