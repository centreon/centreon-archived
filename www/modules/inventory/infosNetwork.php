<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called � Oreon Inventory � is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the
quality,safety, contents, performance, merchantability, non-infringement or
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

		$timeout = 30 * 1000;
	    $retries = 5;
	    $ret =& $pearDB->query("SELECT host_address,host_snmp_community,host_snmp_version,host_template_model_htm_id FROM host WHERE host_id = '".$host_id."'");
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

	    $resData =& $pearDB->query("SELECT * FROM `inventory_index` WHERE host_id = '".$host_id."'");
	    $rD =& $resData->fetchRow();

		$tpl->assign("sort1", $lang["s_description"]);
		$tpl->assign("sort2", $lang["s_network"]);
		$tpl->assign("sort3", $lang["s_vlan"]);
		$tpl->assign("sort4", $lang["s_changeLog"]);


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

	    if ($sysUpTime || $rD["description"]) {

	    	$change_value = array("NetRessource" => "1", "Server" => "2");
	    	$tpl->assign("Type", $change_value[$type]);
	    	$hp_status = array("1" => "unknown", "2" => "information", "3" => "ok", "4" => "warning", "5" => "critical", "6" => "nonrecoverable");

	    	$constr =& $pearDB->query("SELECT name FROM inventory_manufacturer WHERE id = '".$rD["type_ressources"]."'");
	    	$manufacturer =& $constr->fetchRow();
	    	if (isset($oid[$manufacturer["name"]]["CPUStat"]))
	    		$tpl->assign("CPUStat", get_snmp_value($oid[$manufacturer["name"]]["CPUStat"], "INTEGER: "));
			$tpl->assign("sysCPUStatlabel", $lang["s_CPUStat"]);
	    	if (isset($oid[$manufacturer["name"]]["TelnetEnabled"]))
	    		$tpl->assign("TelnetEnable", $enable[get_snmp_value($oid[$manufacturer["name"]]["TelnetEnabled"], "INTEGER: ")]);
			$tpl->assign("sysTelnetEnablelabel", $lang["s_TelnetEnable"]);
	    	if (isset($oid[$manufacturer["name"]]["SSH"]))
	    		$tpl->assign("SSHEnable", get_snmp_value($oid[$manufacturer["name"]]["SSH"], "INTEGER: "));
			$tpl->assign("sysSSHEnablelabel", $lang["s_SSHEnable"]);
	    	if (isset($oid[$manufacturer["name"]]["SSHPort"]))
	    		$tpl->assign("SSHPort", get_snmp_value($oid[$manufacturer["name"]]["SSHPort"], "INTEGER: "));
			$tpl->assign("sysSSHPortlabel", $lang["s_SSHPort"]);
	    	if (isset($oid[$manufacturer["name"]]["SwitchVersion"]))
	    		$tpl->assign("SwitchVersion", str_replace("\"", "", get_snmp_value($oid[$manufacturer["name"]]["SwitchVersion"], "STRING: ")));
			$tpl->assign("sysSwitchVersionlabel", $lang["s_SwitchVersion"]);
	    	if (isset($oid[$manufacturer["name"]]["RomVersion"]))
	    		$tpl->assign("RomVersion", str_replace("\"", "", get_snmp_value($oid[$manufacturer["name"]]["RomVersion"], "STRING: ")));
			$tpl->assign("sysRomVersionlabel", $lang["s_RomVersion"]);
	    	if (isset($oid[$manufacturer["name"]]["SerialNumber"]))
	    		$tpl->assign("SerialNumber", str_replace("\"", "", get_snmp_value($oid[$manufacturer["name"]]["SerialNumber"], "STRING: ")));
			$tpl->assign("sysSerialNumberlabel", $lang["s_SerialNumber"]);
	    	if (isset($oid[$manufacturer["name"]]["manufacturer"]))
	    		$tpl->assign("Manufacturer", str_replace("\"", "", get_snmp_value($oid[$manufacturer["name"]]["manufacturer"], "STRING: ")));
			$tpl->assign("sysManufacturerlabel", $lang["s_Manufacturer"]);
	    	if (isset($oid[$manufacturer["name"]]["manufacturer"]))
	    		$manufacturer_temp = str_replace("\"", "", get_snmp_value($oid[$manufacturer["name"]]["manufacturer"], "STRING: "));
	    	if (!$manufacturer_temp)
	    		$manufacturer_temp = $manufacturer["name"];
		    $tpl->assign("Manufacturer", $manufacturer_temp);

	    	$tab_unitO = array("0"=>"o", "1"=>"Ko","2"=>"Mo","3"=>"Go");
	    	$tab_unitB = array("0"=>"bits", "1"=>"Kbits","2"=>"Mbits","3"=>"Gbits");

	    	$ifTab = walk_snmp_value(".1.3.6.1.2.1.2.2.1.1", "INTEGER: ");
		    if ($ifTab)
			    foreach ($ifTab as $key => $it){
			    	$ifTab[$key] = array();
			    	$ifTab[$key]["ifIndex"] = $it;
			    	$ifTab[$key]["ifDescr"] = get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: ");
			    	$iftype = get_snmp_value("1.3.6.1.2.1.2.2.1.3.".$it, "INTEGER: ");
			    	$r = preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $iftype, $matches);
			    	if (isset($ifType[$matches[2]]))
				    	$ifTab[$key]["ifType"] = $ifType[$matches[2]];
					else
						$ifTab[$key]["ifType"] = "";
			    	if (strstr(strtolower($ifTab[$key]["ifDescr"]), "vlan") || strstr(strtolower($ifTab[$key]["ifType"]), 'virtual'))
			    		$ifTab[$key]["type_interface"] = 2;
			    	else
			    		$ifTab[$key]["type_interface"] = 1;
			    	$ifTab[$key]["ifMtu"] = get_snmp_value("1.3.6.1.2.1.2.2.1.4.".$it, "INTEGER: ");

			    	$tpl->assign("Statuslabel", $lang["s_status"]);
					$tpl->assign("Outlabel", $lang["s_Out"]);
					$tpl->assign("Inlabel", $lang["s_In"]);
					$tpl->assign("errorlabel", $lang["s_Error"]);
					$tpl->assign("PhysAddresslabel", $lang["s_PhysAddress"]);
					$tpl->assign("Typelabel", $lang["s_Type"]);
					$tpl->assign("Trafficlabel", $lang["s_traffic"]);
					$tpl->assign("Errorlabel", $lang["s_pkt_error"]);

			    	#
			    	# Speed
			    	#

			    	$ifSpeed = get_snmp_value("1.3.6.1.2.1.2.2.1.5.".$it, "Gauge32: ");
			    	for ($cpt = 0,$value = $ifSpeed; $value >= 1000 ; $value /= 1000)
						$cpt++;
					$ifTab[$key]["ifSpeed"] = $value;
					$ifTab[$key]["ifSpeedUnit"] = $tab_unitB[$cpt];
			    	$ifTab[$key]["ifPhysAddress"] = get_snmp_value("1.3.6.1.2.1.2.2.1.6.".$it, "STRING: ");

			    	#
			    	# In Octets
			    	#

			    	$ifinoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.10.".$it, "Counter32: ");
			    	for ($cpt = 0,$value = $ifinoctets; $value >= 1024 ; $value /= 1024)
						$cpt++;
					$ifTab[$key]["ifInOctets"] = round($value,2) . " " . $tab_unitO[$cpt];

			    	#
			    	# Out Octets
			    	#

			    	$ifoutoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.16.".$it, "Counter32: ");
			    	for ($cpt = 0,$value = $ifoutoctets; $value >= 1024 ; $value /= 1024)
						$cpt++;
					$ifTab[$key]["ifOutOctets"] = round($value,2) . " " . $tab_unitO[$cpt];

					#
					# Packets Errors
					#

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


			    	#
			    	# If Cisco regarder le port vers quel VLAN est rattach
			    	# 1.3.6.1.4.1.9.9.68.1.2.2.1.2.
			    	#
			    	# pour HP 1.3.6.1.4.1.11.2.14.11.5.1.9.16.1.1.1. + id VLAN + id interface
			    	# Mais id VLAN est different de celui de Iftable.
			    	#
			    }

			    $tab_array_index = array();

if(isset($iftab) && is_array($iftab))
{
			    foreach ($ifTab as $key => $it)
			    	$tab_array_index[$it["ifIndex"]] = $key;

			    foreach ($ifTab as $key => $it){
			    	if (isset($oid[$manufacturer["name"]]) && isset($oid[$manufacturer["name"]]["VlanAssign"]) &&  $oid[$manufacturer["name"]]["VlanAssign"]){
			    		$returned_value = get_snmp_value($oid[$manufacturer["name"]]["VlanAssign"].$it["ifIndex"], "INTEGER: ");
			    		$ifTab[$key]["ifVLan"] = $ifTab[$tab_array_index[$returned_value]]["ifDescr"];
			    	}
	   			}
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
				$tpl->assign("Datelabel",$lang["s_Date"]);
				$tpl->assign("Objectlabel",$lang["s_Object"]);
				$tpl->assign("Beforelabel",$lang["s_Before"]);
				$tpl->assign("Afterlabel",$lang["s_After"]);			    
	    }
	}

	if (isset($host_id)){
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
		global $address, $community, $timeout, $retries;
		$tab = @snmpwalk($address, $community, $oid, $timeout , $retries);
		$cpt = 0;
		$tab_ret = array();
		if ($tab)
			foreach ($tab as $t){
				$tab_ret[$cpt] = str_replace($replace_string, '', $t);
				$cpt++;
			}
		return $tab_ret;
	}

	if (isset($tpl) && $host_id && $sysUpTime)
		$tpl->display('infosNetwork.ihtml');
	else
		print $lang['profile_error_snmp'];

?>
