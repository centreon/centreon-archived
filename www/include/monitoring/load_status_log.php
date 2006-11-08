<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
	
	// Is User Admin ?
	if (!isset($pearDB))
		global $pearDB;
	
	$res1 =& $pearDB->query("SELECT contact_admin FROM contact, session WHERE contact.contact_id = session.user_id AND session_id = '$sid'");
	$res1->fetchInto($user);
	$user_admin = $user["contact_admin"];
	
	
	// Read 
	$res1 =& $pearDB->query("SELECT nagios_version, problem_sort_order, problem_sort_type FROM general_opt");
	$res1->fetchInto($general_opt);
	$version = $general_opt["nagios_version"];
	
	# Init tab
	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	
	
	// Stats
	if (is_object($oreon)){
		$oreon->status_graph_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
		$oreon->status_graph_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => "0");
	}
	
	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	$isRestreint = HadUserLca($pearDB);
	
	$debug = 0;
		
	unset ($host_status);
	unset ($service_status);
	
	# Read File
	if ($version == 1 || $version == 2){
		$res1 =& $pearDB->query("SELECT status_file FROM cfg_nagios WHERE nagios_activate = '1'");
		$res1->fetchInto($nagios_cfg);
		$file = $nagios_cfg["status_file"];
	} else
		$file = "/srv/nagios/var/status_oreon.log";

	// Open File
	if (file_exists($file)){
		$log_file = fopen($file, "r");
	 	$status_proc = 1;
	} else {
	  	$log_file = 0;
	  	$status_proc = 0;
	}
	
	// init table
	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();
		
	$time = time();
	
	if ($version == 1){
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
		  			if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log["1"]])))){
		    			$host_status[$log["1"]] = get_host_data($log);
		    			$tab_host_service[$log["1"]] = array();
		  			}
				} else if (preg_match("/^[\[\]0-9]* PROGRAM[.]*/", $str))
		  			$program_data = get_program_data($log, $status_proc);
		  	}
	      	unset($str);
		}
	} else if ($version == 2){
		if ($log_file)
	    	while ($str = fgets($log_file)) {
	      		$last_update = date("d-m-Y h:i:s");
	      		if (!preg_match("/^\#.*/", $str)){
					###################### SERVICE ######################
					if (preg_match("/^service/", $str)){
				  		$log = array();
				  		while ($str2 = fgets($log_file))
		          			if (!strpos($str2, "}")){      
			      				if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
									$svc_data[$tab[1]] = $tab[2];	
			    			} else
			      				break;
			      		if (strstr("Meta_Module", $svc_data['host_name'])){
			      			$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
			      			$metaService_status[$svc_data["service_description"]] = $svc_data;
			      		} else {
							if (isset($_GET["host_name"]) && strcmp($_GET["host_name"], "OSL_Module") && $_GET["host_name"] == $svc_data["host_name"] 
								&& isset($_GET["service_description"]) && $_GET["service_description"] == $svc_data["service_description"]){
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
			      	##################### HOST #########################
					} else if (preg_match("/^host/", $str)){ // get host stat
						$host_data = array();
			  			while ($str2 = fgets($log_file))
			    		if (!strpos($str2, "}")){
			      			if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
								$host_data[$tab[1]] = $tab[2];
			    		} else
			      			break;
			      		if (isset($host_data['host_name']) && strcmp($host_data['host_name'], "OSL_Module") && strcmp($host_data['host_name'], "Meta_Module") && ($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_data['host_name']])))){
				      		$host_data["current_state"] = $tab_status_host[$host_data['current_state']];
							$host_status[$host_data["host_name"]] = $host_data;
							if (is_object($oreon))
								$oreon->status_graph_host[$host_data['current_state']]++;
			      		}
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
	} else {
		if ($log_file)
		    while ($str = fgets($log_file))	{
		      	// set last update 
		     	$last_update = date("d-m-Y h:i:s");
		      	$log = split("#", $str);
				if (preg_match("/^s/", $str)){
					if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log['1']]))) 
							&& strcmp($log[1], "OSL_Module") && strcmp($log[1], "Meta_Module")){
						$service_status[$log["1"]."_".$log["2"]] = getServiceDataParsed($log);
				   		$tab_host_service[$log["1"]][$log["2"]] = "1";
				   		if (is_object($oreon))
					   		$oreon->status_graph_service[$service_status[$log["1"]."_".$log["2"]]['current_state']]++;
					} else if (!strcmp($log[1], "Meta_Module"))
				  		$metaService_status[$log["2"]] = getServiceDataParsed($log);
				} else if (preg_match("/^h*/", $str) && strcmp($log[1], "OSL_Module")){ // get host stat
			  		if (($user_admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$log["1"]])))){
			    		$tab_host_service[$log["1"]] = array();
			    		$host_status[$log["1"]] = getHostDataParsed($log);
			    		if (is_object($oreon))
				    		$oreon->status_graph_host[$host_status[$log["1"]]['current_state']]++;
			  		}
				} else if (preg_match("/^p/", $str))
			  		$program_data = getProgramDataParsed($log, $status_proc);
		      	unset($str);
			}
	}
	
	$row_data = array();
	if (isset($_GET["o"]) && $_GET["o"] == "svcSch" && !isset($_GET["sort_types"])){
		$_GET["sort_types"] = "next_check";
		$_GET["order"] = "SORT_ASC";
	}
	
	if (isset($_GET["o"]) && $_GET["o"] == "svcpb"){
		if (!isset($_GET["sort_types"])){
			$_GET["sort_types"] = $general_opt["problem_sort_type"];
			if ($_GET["sort_types"] == "last_state_change")
				$_GET["order"] = "SORT_".($$general_opt["problem_sort_order"] == "ASC" ? "DESC" : "ASC");
			else
				$_GET["order"] = "SORT_".$general_opt["problem_sort_order"];
		}
		if (!isset($_GET["order"]))
			$_GET["order"] = "SORT_".$general_opt["problem_sort_order"];
	}
	
	if (isset($_GET["sort_types"]) && $_GET["sort_types"]){
	  foreach ($service_status as $key => $row)
	    $row_data[$key] = $row[$_GET["sort_types"]];
	  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $service_status) : array_multisort($row_data, SORT_DESC, $service_status);
	}

	if (isset($_GET["sort_typeh"]) && $_GET["sort_typeh"]){
	  foreach ($host_status as $key => $row)
	    $row_data[$key] = $row[$_GET["sort_typeh"]];
	  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $host_status) : array_multisort($row_data, SORT_DESC, $host_status);
	}
?> 