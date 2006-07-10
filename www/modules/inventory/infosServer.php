<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Inventory » is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to thequality,
safety, contents, performance, merchantability, non-infringement or
suitability for any particular or intended purpose of the Software found on the OREON web
site. In no event will OREON be liable for any direct, indirect, punitive,
special, incidental or consequential damages however they may arise and even if OREON
has been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!$oreon)
		exit();
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;

	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	!isset ($_GET["limit"]) ? $limit = 20 : $limit = $_GET["limit"];
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	isset($type) ? $type = $type : $type = "Server";

	$t = microtime();

	$enable = array("1" => $lang["yes"], "2" => $lang["no"]);

	if (!$min)	{
		# start quickSearch form
		include_once("./include/common/quickSearch.php");
		# end quickSearch form
	}

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	if (isset($host_id))	{
		include_once("./modules/inventory/inventory_oid_library.php");
		include_once("./modules/inventory/inventory_library.php");

		$timeout = 100 * 1000;
	    $retries = 10;
	    $ret =& $pearDB->query("SELECT host_id,host_address,host_snmp_community,host_snmp_version,host_template_model_htm_id FROM host WHERE host_id = '".$host_id."'");
	    $r =& $ret->fetchRow();

		if (!$r["host_snmp_community"]){
			$community = getMySnmpCommunity($r["host_id"]);
			if ($community == "")
				$community = $oreon->optGen["snmp_community"];
		} else
			$community = $r["host_snmp_community"];

		if (!$r["host_snmp_version"]){
			$version = getMySnmpVersion($r["host_id"]);
			if ($version == "")
				$version = $oreon->optGen["snmp_version"];
		} else 
			$version = $r["host_snmp_version"];

	    $address = $r["host_address"];

	  	//$community = $r["host_snmp_community"];
		//$version = $r["host_snmp_version"];

	    $resData =& $pearDB->query("SELECT * FROM `inventory_index` WHERE host_id = '".$host_id."'");
	    $rD =& $resData->fetchRow();

		$tpl->assign("sort1", $lang["s_description"]);
		$tpl->assign("sort2", $lang["s_network"]);
		$tpl->assign("sort3", $lang["s_storageDevice"]);
		$tpl->assign("sort4", $lang["s_softwareInstalled"]);
		$tpl->assign("sort5", $lang["s_runningProcessus"]);
		$tpl->assign("sort6", $lang["s_changeLog"]);


		$tpl->assign("sysName", $rD["name"]);
		$tpl->assign("sysNamelabel", $lang["s_name"]);

		$tpl->assign("sysDescr", $rD["description"]);
		$tpl->assign("sysDescrlabel", $lang["s_description"]);

		$tpl->assign("sysContact", $rD["contact"]);
		$tpl->assign("sysContactlabel", $lang["s_contact"]);

		$tpl->assign("sysLocation", $rD["location"]);
		$tpl->assign("sysLocationlabel", $lang["s_location"]);

		$sysUpTime =  get_snmp_value(".1.3.6.1.2.1.1.3.0", "STRING: ");
		$tpl->assign("sysUpTime", $sysUpTime);
		$tpl->assign("sysUpTimelabel", $lang["s_uptime"]);

	    if ($sysUpTime /*|| $rD["description"]*/) {

	    	//$tpl->assign("Type", $change_value[$type]);
	    	$tab_unit = array("0"=>"bits", "1"=>"Kbits","2"=>"Mbits","3"=>"Gbits");

	    	$ifTab = walk_snmp_value(".1.3.6.1.2.1.2.2.1.1", "INTEGER: ");
		    if ($ifTab)
			    foreach ($ifTab as $key => $it){
			    	$ifTab[$key] = array();
			    	$ifTab[$key]["ifIndex"] = $it;
			    	$ifTab[$key]["ifDescr"] = htmlentities($lang["s_interface"]." : ".get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: "));
			    	$iftype = get_snmp_value("1.3.6.1.2.1.2.2.1.3.".$it, "INTEGER: ");
			    	$r = preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $iftype, $matches);
			    	if (isset($ifType[$matches[2]]) && $ifType[$matches[2]])
				    	$ifTab[$key]["ifType"] = $ifType[$matches[2]];
			    	else
			    		$ifTab[$key]["ifType"] = "";
			    	$ifTab[$key]["ifMtu"] = get_snmp_value("1.3.6.1.2.1.2.2.1.4.".$it, "INTEGER: ");

					$tpl->assign("Statuslabel", $lang["s_status"]);
					$tpl->assign("Outlabel", $lang["s_Out"]);
					$tpl->assign("Inlabel", $lang["s_In"]);
					$tpl->assign("errorlabel", $lang["s_Error"]);
					$tpl->assign("PhysAddresslabel", $lang["s_PhysAddress"]);
					$tpl->assign("Typelabel", $lang["s_Type"]);
			    	$tpl->assign("Trafficlabel", $lang["s_traffic"]);
					$tpl->assign("Errorlabel", $lang["s_pkt_error"]);
			    	/*
			    	 * Speed
			    	 */

			    	$ifSpeed = get_snmp_value("1.3.6.1.2.1.2.2.1.5.".$it, "Gauge32: ");
			    	for ($cpt = 0,$value = $ifSpeed; $value >= 1000 ; $value /= 1000)
						$cpt++;
					$ifTab[$key]["ifSpeed"] = $value;
					$ifTab[$key]["ifSpeedUnit"] = $tab_unit[$cpt];
			    	$ifTab[$key]["ifPhysAddress"] = get_snmp_value("1.3.6.1.2.1.2.2.1.6.".$it, "STRING: ");

			    	/*
			    	 * In Octets
			    	 */

			    	$ifinoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.10.".$it, "Counter32: ");
			    	for ($cpt = 0,$value = $ifinoctets; $value >= 1024 ; $value /= 1024)
						$cpt++;
					$ifTab[$key]["ifInOctets"] = round($value,2) . " " . $tab_unit[$cpt];

			    	/*
			    	 * Out Octets
			    	 */

			    	$ifoutoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.16.".$it, "Counter32: ");
			    	for ($cpt = 0,$value = $ifoutoctets; $value >= 1024 ; $value /= 1024)
						$cpt++;
					$ifTab[$key]["ifOutOctets"] = round($value,2) . " " . $tab_unit[$cpt];

					/*
					 * Packets Errors
					 */

					$ifTab[$key]["ifInError"] = get_snmp_value("1.3.6.1.2.1.2.2.1.14.".$it, "Counter32: ") . " Pkts";
			    	$ifTab[$key]["ifOutError"] = get_snmp_value("1.3.6.1.2.1.2.2.1.20.".$it, "Counter32: ") . " Pkts";

					$ifTab[$key]["ifAdminStatus"] = get_snmp_value("1.3.6.1.2.1.2.2.1.7.".$it, "INTEGER: ");
			    	preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $ifTab[$key]["ifAdminStatus"], $matches);
			    	$ifTab[$key]["ifAdminStatus"] = $ifAdminStatus[$matches[2]];
			    	$operstatus = get_snmp_value("1.3.6.1.2.1.2.2.1.8.".$it, "INTEGER: ");
			    	preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $operstatus, $matches);
			    	$ifTab[$key]["ifOperStatus"] = $ifOperStatus[$matches[2]];
//			    	strstr($operstatus, "up") ? $ifTab[$key]["Color"] = "list_three" : $ifTab[$key]["Color"] = "list_four";
					($matches[2] == 1 ) ? $ifTab[$key]["Color"] = "list_three" : $ifTab[$key]["Color"] = "list_four";
			    	$ifTab[$key]["ifLastChange"] = get_snmp_value("1.3.6.1.2.1.2.2.1.6.".$it, "STRING: ");

			    }

			    $ipInterface_data = walk_snmp_value("1.3.6.1.2.1.4.20.1.1", "IpAddress: ");
			  	if ($ipInterface_data){
			    	foreach ($ipInterface_data as $iI){
			    		$index = get_snmp_value("1.3.6.1.2.1.4.20.1.2.".$iI, "INTEGER: ");
			    		$ipInterface[$index] = array();
			    		$ipInterface[$index]["ipIP"] = $iI;
			    		$ipInterface[$index]["ipIndex"] = $index;
			    		$ipInterface[$index]["ipNetMask"] = get_snmp_value("1.3.6.1.2.1.4.20.1.3.".$iI, "IpAddress: ");
			    	}
			    }

	    		if (isset($ifTab))
		    		foreach ($ifTab as $key => $it){
		    			if (isset($ipInterface[$it["ifIndex"]]) && $ipInterface[$it["ifIndex"]]["ipIP"])
				    		$ifTab[$key]["ipInterface"] = $ipInterface[$it["ifIndex"]]["ipIP"]."&nbsp;/&nbsp;".$ipInterface[$it["ifIndex"]]["ipNetMask"];
				    	else
				    		$ifTab[$key]["ipInterface"] = "Not Defined";
		    		}

			    $tpl->assign("ifTab", $ifTab);

				$tab_unit = array("0" => "o", "1" => "Ko","2" => "Mo","3" => "Go","4" => "To");

			    $hrStorageIndex = walk_snmp_value(".1.3.6.1.2.1.25.2.3.1.1", "INTEGER: ");


				$tpl->assign("mntPointlabel",$lang["s_mntPoint"]);
				$tpl->assign("Typelabel",$lang["s_Type"]);
				$tpl->assign("Utilisationlabel",$lang["s_Utilisation"]);
				$tpl->assign("Freelabel",$lang["s_Free"]);
				$tpl->assign("Usedlabel",$lang["s_Used"]);
				$tpl->assign("Sizelabel",$lang["s_Size"]);



			    if ($hrStorageIndex)
				    foreach ($hrStorageIndex as $key => $SI){
				    	$hrStorageIndex[$key] = array();
				    	$hrStorageIndex[$key]["Index"] = $SI;
				    	$hrStorageIndex[$key]["hsStorageSize"] = get_snmp_value("1.3.6.1.2.1.25.2.3.1.4.".$SI, "INTEGER: ");
				    	if ($hrStorageIndex[$key]["hsStorageSize"] != 256){
				    		$hrStorageIndex[$key]["hsStorageType"] = get_snmp_value("1.3.6.1.2.1.25.2.3.1.2.".$SI, "OID: HOST-RESOURCES-TYPES::");
				    		if (!strcmp($hrStorageIndex[$key]["hsStorageType"], "hrStorageFixedDisk")){
			    				$hrStorageIndex[$key]["hrFSAccess"] = get_snmp_value("1.3.6.1.2.1.25.3.8.1.5.".$SI, "INTEGER: ");
			    				$hrStorageIndex[$key]["hrFSBootable"] = get_snmp_value("1.3.6.1.2.1.25.3.8.1.6.".$SI, "INTEGER: ");
				    			$hrStorageIndex[$key]["hsFSType"] = get_snmp_value("1.3.6.1.2.1.25.3.8.1.4.".$SI, "OID: HOST-RESOURCES-TYPES::");
					    		$hrStorageIndex[$key]["hsFSBootable"] = get_snmp_value("1.3.6.1.2.1.25.3.8.1.6.".$SI, "INTEGER: ");
				    		}
				    		$hrStorageIndex[$key]["hsStorageDescr"] = get_snmp_value("1.3.6.1.2.1.25.2.3.1.3.".$SI, "STRING: ");
				    		$block = get_snmp_value("1.3.6.1.2.1.25.2.3.1.4.".$SI, "INTEGER: ");
					    	$hrStorageIndex[$key]["hsStorageSize"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.5.".$SI, "INTEGER: ");
					    	$hrStorageIndex[$key]["hsStorageUsed"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.6.".$SI, "INTEGER: ");
					    	$hrStorageIndex[$key]["hsStorageFree"] = $hrStorageIndex[$key]["hsStorageSize"] - $hrStorageIndex[$key]["hsStorageUsed"];
					    	if ($hrStorageIndex[$key]["hsStorageSize"]){
						    	$hrStorageIndex[$key]["hsStorageUsedPercent"] =  round($hrStorageIndex[$key]["hsStorageUsed"] / $hrStorageIndex[$key]["hsStorageSize"] * 100);
						    	$hrStorageIndex[$key]["hsStorageUsedlen"] = $hrStorageIndex[$key]["hsStorageUsedPercent"] * 2;
						    	$hrStorageIndex[$key]["hsStorageNotUsedlen"] = 200 - ($hrStorageIndex[$key]["hsStorageUsedPercent"] * 2);
					    	}
					    	if 	($hrStorageIndex[$key]["hsStorageSize"]){
						    	for ($cpt = 0; $hrStorageIndex[$key]["hsStorageSize"] >= 1024; $cpt++)
						    		$hrStorageIndex[$key]["hsStorageSize"] /= 1024;
						    	$hrStorageIndex[$key]["hsStorageSize"] = round($hrStorageIndex[$key]["hsStorageSize"], 2) . " " . $tab_unit[$cpt];
					    	}
					    	if 	($hrStorageIndex[$key]["hsStorageUsed"]){
						    	for ($cpt = 0; $hrStorageIndex[$key]["hsStorageUsed"] >= 1024; $cpt++)
						    		$hrStorageIndex[$key]["hsStorageUsed"] /= 1024;
						    	$hrStorageIndex[$key]["hsStorageUsed"] = round($hrStorageIndex[$key]["hsStorageUsed"], 2) ." " . $tab_unit[$cpt];
				    		}
					    	if 	($hrStorageIndex[$key]["hsStorageFree"]){
						    	for ($cpt = 0; $hrStorageIndex[$key]["hsStorageFree"] >= 1024; $cpt++)
						    		$hrStorageIndex[$key]["hsStorageFree"] /= 1024;
						    	$hrStorageIndex[$key]["hsStorageFree"] = round($hrStorageIndex[$key]["hsStorageFree"], 2) ." " . $tab_unit[$cpt];
					    	}
					    }
				    }

			    $tpl->assign("hrStorageIndex", $hrStorageIndex);

			    $hrDeviceIndex = walk_snmp_value("1.3.6.1.2.1.25.3.2.1.1", "INTEGER: ");
			    if ($hrDeviceIndex)
				    foreach ($hrDeviceIndex as $key => $SD){
				    	$hrDeviceIndex[$key] = array();
				    	$hrDeviceIndex[$key]["Index"] = $SD;
				    	$hrDeviceIndex[$key]["hsDeviceType"] = get_snmp_value("1.3.6.1.2.1.25.3.2.1.2.".$SD, "OID: HOST-RESOURCES-TYPES::");
			    		if (!strcmp($hrDeviceIndex[$key]["hsDeviceType"], "hrDeviceDiskStorage")){
			    			// Disk de Stockage
			    			$hrDeviceIndex[$key]["hrDiskStorageAccess"] = get_snmp_value("1.3.6.1.2.1.25.3.6.1.1.".$SD, "INTEGER: ");
			    			$hrDeviceIndex[$key]["hrDiskStorageMedia"] = get_snmp_value("1.3.6.1.2.1.25.3.6.1.2.".$SD, "INTEGER: ");
			    			$hrDeviceIndex[$key]["hrDiskStorageRemovable"] = get_snmp_value("1.3.6.1.2.1.25.3.6.1.3.".$SD, "INTEGER: ");
			    		}
				    }
			    $tpl->assign("hrDeviceIndex", $hrDeviceIndex);

				$tpl->assign("Softwarelabel",$lang["s_Software"]);
				$tpl->assign("MermoryUsedlabel",$lang["s_MermoryUsed"]);
				$tpl->assign("Pathlabel",$lang["s_Path"]);



			    $hrSWRun = walk_snmp_value("1.3.6.1.2.1.25.4.2.1.1", "INTEGER: ");
			    if ($hrSWRun)
				    foreach ($hrSWRun as $key => $SWR){
				    	$hrSWRun[$key] = array();
				    	$hrSWRun[$key]["Index"] = $SWR;
				    	$hrSWRun[$key]["hrSWRunName"] =  str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.2.".$SWR, "STRING: "));
				    	$hrSWRun[$key]["hrSWRunPath"] =  str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.4.".$SWR, "STRING: "));
				    	//$hrSWRun[$key]["hrSWRunParameters"] = get_snmp_value("1.3.6.1.2.1.25.4.2.1.5.".$SWR, "STRING: ");
				    	//$hrSWRun[$key]["hrSWRunType"] = get_snmp_value("1.3.6.1.2.1.25.4.2.1.6.".$SWR, "INTEGER: ");
				    	//$hrSWRun[$key]["hrSWRunStatus"] = get_snmp_value("1.3.6.1.2.1.25.4.2.1.7.".$SWR, "INTEGER: ");
				    	//$hrSWRun[$key]["hrSWRunPerfCPU"] = get_snmp_value("1.3.6.1.2.1.25.5.1.1.1.".$SWR, "INTEGER: ");
				    	$hrSWRun[$key]["hrSWRunPerfMem"] = get_snmp_value("1.3.6.1.2.1.25.5.1.1.2.".$SWR, "INTEGER: ");
				    }
				$tpl->assign("hrSWRun", $hrSWRun);

			   	$hrSWInstalled = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.1", "INTEGER: ");
			   	$hrSWInstalledName = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.2", "STRING: ");
			   	$hrSWInstalledType = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.4", "INTEGER: ");
			   	if ($hrSWInstalled)
					foreach ($hrSWInstalled as $key => $SWI){
				    	$hrSWInstalled[$key] = array();
				    	$hrSWInstalled[$key]["Index"] = $SWI;
				    	if (isset($hrSWInstalledName[$key]) && !strstr($hrSWInstalledName[$key], "Hex-")){
				    		$hrSWInstalled[$key]["hrSWInstalledName"] = str_replace("\"", "", $hrSWInstalledName[$key]);
				    	}
				    	isset($hrSWInstalled[$key]["hrSWInstalledType"]) ? $hrSWInstalled[$key]["hrSWInstalledType"] = $hrSWInstalledType[$key] : NULL;
					}
				$tpl->assign("hrSWInstalled", $hrSWInstalled);

				$tpl->assign("Datelabel",$lang["s_Date"]);
				$tpl->assign("Objectlabel",$lang["s_Object"]);
				$tpl->assign("Beforelabel",$lang["s_Before"]);
				$tpl->assign("Afterlabel",$lang["s_After"]);


	    }

	}
	if ($host_id){
		$res =& $pearDB->query("SELECT ctime,replaced_value,value,type FROM inventory_log WHERE host_id = '".$host_id."' ORDER BY ctime DESC");
		$log_array = array();
		for ($cpt = 0; $r =& $res->fetchRow(); $cpt++){
			$r["ctime"] = date("d/m/Y", $r["ctime"]);
			$log_array[$cpt] = $r;
		}
		$tpl->assign("log_array", $log_array);
	}

	function get_snmp_value($oid, $replace_string){
		global $address, $community, $timeout, $retries;
		return str_replace($replace_string, '', @snmpget($address, $community, $oid, $timeout , $retries));
	}

	function walk_snmp_value($oid, $replace_string){
		$tab_ret = array();
		global $address, $community, $timeout, $retries;
		$tab = @snmpwalk($address, $community, $oid, $timeout , $retries);
		$cpt = 0;
		if ($tab)
			foreach ($tab as $t){
				$tab_ret[$cpt] = str_replace($replace_string, '', $t);
				$cpt++;
			}
		return $tab_ret;
	}

	if (isset($tpl) && $host_id && $sysUpTime)
		$tpl->display("infosServer.ihtml");
	else
		print $lang['profile_error_snmp'];
?>