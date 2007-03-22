<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	$buffer = "<reponse>";

	require_once("../../../oreon.conf.php");
	require_once("../../../DBconnect.php");
	require_once("../inventory_library.php");

	#Path to the configuration dir
	#PHP functions

	function getMyHostGroups($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hgs = array();
		$res =& $pearDB->query("SELECT hg.hg_name, hgr.hostgroup_hg_id FROM hostgroup hg, hostgroup_relation hgr WHERE hgr.host_host_id = '".$host_id."' AND hgr.hostgroup_hg_id = hg.hg_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		while ($res->fetchInto($hg))
			$hgs[$hg["hostgroup_hg_id"]] = $hg["hg_name"];
		return $hgs;
	}

	function getMyHostGroupCommunity($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$row =& $res->fetchRow();
		if ($row["hg_snmp_community"])
			return $row["hg_snmp_community"];
		return NULL;
	}

	function getMyHostGroupVersion($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$row =& $res->fetchRow();
		if ($row["hg_snmp_version"])
			return $row["hg_snmp_version"];
		return NULL;
	}

	function get_snmp_value($oid, $replace_string){
		global $address, $community, $timeout, $retries,$debug_inventory,$debug_path;
		$str = @snmpget($address, $community, $oid, $timeout , $retries);

//		if ($debug_inventory == 1)
//			error_log("[" . date("d/m/Y H:s") ."] Inventory : OID => " . $oid . ", Value => " . $str ."\n", 3, $debug_path."inventory.log");

		//print "[".$str."]";
		if ($str == FALSE)
			return FALSE;
		else
			return str_replace($replace_string, '', $str);
	}

	function walk_snmp_value($oid, $replace_string){
		global $address, $community, $timeout, $retries;
		$tab = @snmpwalk($address, $community, $oid, $timeout , $retries);
		if ($tab){
			$cpt = 0;
			if ($tab)
				foreach ($tab as $t){
					$tab_ret[$cpt] = str_replace($replace_string, '', $t);
					$cpt++;
				}
			return $tab_ret;
		} else
			return FALSE;
	}

	function getMyHostName($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_name, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row =& $res->fetchRow();
			if ($row["host_name"])
				return $row["host_name"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostAddress($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row =& $res->fetchRow();
			if ($row["host_address"])
				return $row["host_address"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMySnmpVersion($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$res =& $pearDB->query("SELECT host_snmp_version, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row =& $res->fetchRow();
			if ($row["host_snmp_version"])
				return $row["host_snmp_version"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$res =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$row =& $res->fetchRow();
					if ($row["hg_snmp_version"])
						return $row["hg_snmp_version"];
				}
				$res =& $pearDB->query("SELECT snmp_version FROM general_opt LIMIT 1");
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
				$row =& $res->fetchRow();
				if (isset($row["snmp_version"]))
					return $row["snmp_version"];
				else
					break;
				break;
			}
		}
		return NULL;
	}

	function getMySnmpCommunity($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$res =& $pearDB->query("SELECT host_snmp_community, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row =& $res->fetchRow();
			if ($row["host_snmp_community"])
				return $row["host_snmp_community"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$res =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$row =& $res->fetchRow();
					if ($row["hg_snmp_community"])
						return $row["hg_snmp_community"];
				}
				$res =& $pearDB->query("SELECT snmp_community FROM general_opt LIMIT 1");
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
				$row =& $res->fetchRow();
				if (isset($row["snmp_community"]))
					return $row["snmp_community"];
				return NULL;
			}
		}
		return NULL;
	}

	 global $pearDB;
     $res =& $pearDB->query("SELECT debug_path, debug_inventory  FROM general_opt LIMIT 1");
	 if (PEAR::isError($res))
		die($res->getMessage());

	  $debug = $res->fetchRow();

	  $debug_inventory = $debug['debug_inventory'];
	  $debug_path = $debug['debug_path'];
	  if (!isset($debug_inventory))
	  	$debug_inventory = 0;

	$type = isset($_GET["type"]) ? $_GET["type"] : 0;
	$type = isset($_POST["type"]) ? $_POST["type"] : $type;

	$host_id = isset($_GET["host_id"]) ? $_GET["host_id"] : 0;
	$host_id = isset($_POST["host_id"]) ? $_POST["host_id"] : $host_id;

	$community = getMySnmpCommunity($host_id);
	$version = getMySnmpVersion($host_id);
	$address = getMyHostAddress($host_id);

	if ($debug_inventory == 1)
		error_log("[" . date("d/m/Y H:s") ."] Inventory : Host Server '".  $address . "' : SNMP Community : ".  $community . ", SNMP Version => ". $version ."\n", 3, $debug_path."inventory.log");

	$timeout = 100 * 1000;
	$retries = 4;

	$tab_unit = array("0"=>"bits", "1"=>"Kbits","2"=>"Mbits","3"=>"Gbits");
	$tab_unit_o = array("0"=>"o", "1"=>"Ko","2"=>"Mo","3"=>"Go");


	if($type == 3 && $host_id)
	{
		$hrStorageIndex = walk_snmp_value(".1.3.6.1.2.1.25.2.3.1.1", "INTEGER: ");
		if ($hrStorageIndex){
		    foreach ($hrStorageIndex as $key => $SI){
		    	$hrStorageIndex = array();
		    	$buffer .= '<storageDevice>';
				$buffer .= '<mntPointlabel>' . get_snmp_value("1.3.6.1.2.1.25.2.3.1.3.".$SI, "STRING: "). '</mntPointlabel>';

				$typelabel = get_snmp_value("1.3.6.1.2.1.25.3.8.1.4.".$SI, "OID: HOST-RESOURCES-TYPES::");
				if ($typelabel)
					$buffer .= '<Typelabel>'.$typelabel.'</Typelabel>';
				else
					$buffer .= '<Typelabel>none</Typelabel>';

				$block = get_snmp_value("1.3.6.1.2.1.25.2.3.1.4.".$SI, "INTEGER: ");

				$hrStorageIndex["hsStorageSize"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.5.".$SI, "INTEGER: ");
				$hrStorageIndex["hsStorageUsed"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.6.".$SI, "INTEGER: ");
				$hrStorageIndex["hsStorageFree"] = $hrStorageIndex["hsStorageSize"] - $hrStorageIndex["hsStorageUsed"];


		    	if (isset($hrStorageIndex["hsStorageSize"])){
		    		if ($hrStorageIndex["hsStorageSize"] != 0)
						$buffer .= '<Utilisationlabel>'.round($hrStorageIndex["hsStorageUsed"] / $hrStorageIndex["hsStorageSize"] * 100).'</Utilisationlabel>';
		    		else
						$buffer .= '<Utilisationlabel>none</Utilisationlabel>';
		    	}
		   		if 	(isset($hrStorageIndex["hsStorageSize"]) && $hrStorageIndex["hsStorageSize"]){
		   			for ($cpt = 0; $hrStorageIndex["hsStorageSize"] >= 1024; $cpt++)
			    		$hrStorageIndex["hsStorageSize"] /= 1024;
			    	$hrStorageIndex["hsStorageSize"] = round($hrStorageIndex["hsStorageSize"], 2) . " " . $tab_unit_o[$cpt];
		    	}
		    	if 	(isset($hrStorageIndex["hsStorageUsed"]) && $hrStorageIndex["hsStorageUsed"]){
			    	for ($cpt = 0; $hrStorageIndex["hsStorageUsed"] >= 1024; $cpt++)
			    		$hrStorageIndex["hsStorageUsed"] /= 1024;
			    	$hrStorageIndex["hsStorageUsed"] = round($hrStorageIndex["hsStorageUsed"], 2) ." " . $tab_unit_o[$cpt];
	    		}
		    	if 	(isset($hrStorageIndex["hsStorageFree"]) && $hrStorageIndex["hsStorageFree"]){
			    	for ($cpt = 0; $hrStorageIndex["hsStorageFree"] >= 1024; $cpt++)
			    		$hrStorageIndex["hsStorageFree"] /= 1024;
			    	$hrStorageIndex["hsStorageFree"] = round($hrStorageIndex["hsStorageFree"], 2) ." " . $tab_unit_o[$cpt];
		    	}

		   		$hrStorageIndex["hsStorageFree"] ? $buffer .= '<Freelabel>'.$hrStorageIndex["hsStorageFree"].'</Freelabel>': $buffer .= '<Freelabel>none</Freelabel>';
				$hrStorageIndex["hsStorageUsed"] ? $buffer .= '<Usedlabel>'.$hrStorageIndex["hsStorageUsed"].'</Usedlabel>': $buffer .= '<Usedlabel>none</Usedlabel>';
				$hrStorageIndex["hsStorageSize"] ? $buffer .= '<Sizelabel>'.$hrStorageIndex["hsStorageSize"].'</Sizelabel>': $buffer .= '<Sizelabel>none</Sizelabel>';
				$buffer .= '</storageDevice>';

			    if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host Server '".  $address . "' : Type => " . $typelabel . " : Block => ". $block . " : Size => " . $hrStorageIndex["hsStorageSize"] . " : Used => " . $hrStorageIndex["hsStorageUsed"] . " : Free => " . $hrStorageIndex["hsStorageFree"] . " \n", 3, $debug_path."inventory.log");
		    }
		} else
			$buffer .= '<storageDevice>none</storageDevice>';
	} else 	if($type == 6 && $host_id){
		$ifTab = walk_snmp_value(".1.3.6.1.2.1.2.2.1.1", "INTEGER: ");
	    if ($ifTab) {
		    foreach ($ifTab as $key => $it){
			   	$ifTab[$key]["ifIndex"] = $it;
				$interfaceName = get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: ");

				$operstatus = get_snmp_value("1.3.6.1.2.1.2.2.1.8.".$it, "INTEGER: ");
				if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : Status => " . $operstatus ."\n", 3, $debug_path."inventory.log");

				preg_match("/[A-Za-z\-]*\(?([0-9]+)\)?/", $operstatus, $matches);
				$operstatus = $matches[1];

				if(isset($operstatus) && $operstatus)
					$status = $ifOperStatus[$operstatus];
				else
					$status = 'none';

				if ($ifOperStatus[$operstatus] == "Up")
					$class = 'list_three';
				else
					$class = 'list_four';

				$ifTab["ifPhysAddress"] = get_snmp_value("1.3.6.1.2.1.2.2.1.6.".$it, "STRING: ");
				if ($ifTab["ifPhysAddress"])
					$PhysAddress = $ifTab["ifPhysAddress"];
				else
					$PhysAddress = 'N/A';

				# Type
				$iftype = get_snmp_value("1.3.6.1.2.1.2.2.1.3.".$it, "INTEGER: ");
		    	$r = preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $iftype, $matches);
		    	if (isset($ifType[$matches[2]]) && $ifType[$matches[2]])
			    	$ifTab["ifType"] = $ifType[$matches[2]];
		    	else
		    		$ifTab["ifType"] = " ";
				$type = $ifTab["ifType"];

				# In Octets
				$ifinoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.10.".$it, "Counter32: ");
		    	for ($cpt = 0,$value = $ifinoctets; $value >= 1024 ; $value /= 1024)
					$cpt++;
				$ifTab["ifInOctets"] = round($value,2) . " " . $tab_unit[$cpt];

		    	# Out Octets
		    	$ifoutoctets = get_snmp_value("1.3.6.1.2.1.2.2.1.16.".$it, "Counter32: ");
		    	for ($cpt = 0,$value = $ifoutoctets; $value >= 1024 ; $value /= 1024)
					$cpt++;
				$ifTab["ifOutOctets"] = round($value,2) . " " . $tab_unit[$cpt];

				$traffic = ' In : '.$ifTab["ifInOctets"].' / Out '. $ifTab["ifOutOctets"];


				$ifSpeed = get_snmp_value("1.3.6.1.2.1.2.2.1.5.".$it, "Gauge32: ");
		    	for ($cpt = 0,$value = $ifSpeed; $value >= 1000 ; $value /= 1000)
					$cpt++;
				$speed = $value.' '.$tab_unit[$cpt];
				$errorPaquet =  'In : '.get_snmp_value("1.3.6.1.2.1.2.2.1.14.".$it, "Counter32: ") . " Pkts".' / Out : '.get_snmp_value("1.3.6.1.2.1.2.2.1.20.".$it, "Counter32: ") . " Pkts";

				# IP Interface
				$ipAddress = NULL;
				$ipInterface_data = walk_snmp_value("1.3.6.1.2.1.4.20.1.1", "IpAddress: ");
				if ($ipInterface_data){
			    	foreach ($ipInterface_data as $it2){
			    		$index = get_snmp_value("1.3.6.1.2.1.4.20.1.2.".$it2, "INTEGER: ");
			    		if ($index == $it)			    
				    		$ipAddress   = $it2." / ".get_snmp_value("1.3.6.1.2.1.4.20.1.3.".$it2, "IpAddress: ");
			    		if ($debug_inventory == 1)
							error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' Index => " . $ipInterface[$index]["ipIndex"] ." : Ip => " .$ipInterface[$index]["ipIP"] . " : NetMask => " .  $ipInterface[$index]["ipNetMask"]  ."\n", 3, $debug_path."inventory.log");
			    		if (isset($ipAddress))
			    			break;
			    	}
			    }

		    	if (!isset($ipAddress))
					$ipAddress   = "Not Defined";


				$buffer .= '<network>';
				$buffer .= '<interfaceName isvalid="1">' .htmlentities($interfaceName, ENT_QUOTES) . '</interfaceName>';
				$buffer .= '<PhysAddress isvalid="1">' . $PhysAddress . '</PhysAddress>';
				$buffer .= '<Status isvalid="1">' . $status . '</Status>';
				$buffer .= '<class isvalid="1">' . $class . '</class>';
		    	$buffer .= '<Type isvalid="1">' . $type . '</Type>';
				$buffer .= '<Trafic isvalid="1">' . $traffic . '</Trafic>';
				$buffer .= '<Speed isvalid="1">' . $speed . '</Speed>';
				$buffer .= '<errorPaquet isvalid="1">' . $errorPaquet . '</errorPaquet>';
				$buffer .= '<ipAddress isvalid="1">' . $ipAddress . '</ipAddress>';
				$buffer .= '</network>';

				if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : Type => " . $ifTab["ifType"]. " :  Status => " . $operstatus . " : Speed => " .  $value.' '.$tab_unit[$cpt] . " : PhysAddress => " .  $ifTab["ifPhysAddress"]  . ": IP Address => " . $ipAddress   ."\n", 3, $debug_path."inventory.log");
		    }
	    } else
	    	$buffer .= '<network>none</network>';
	} else if($type == 4 && $host_id){
		$hrSWInstalled = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.1", "INTEGER: ");
	   	$hrSWInstalledName = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.2", "STRING: ");
	   	if ($hrSWInstalled)
			foreach ($hrSWInstalled as $key => $SWI){
		    	$buffer .= '<software>';
				if (isset($hrSWInstalledName[$key]) && !strstr($hrSWInstalledName[$key], "Hex-")) {
		    		$hrSWInstalled["hrSWInstalledName"] = str_replace("\"", "", $hrSWInstalledName[$key]);
		    		$hrSWInstalled["hrSWInstalledName"] = str_replace("\n", "", $hrSWInstalledName[$key]);
		    		$hrSWInstalled["hrSWInstalledName"] = str_replace("\r", "", $hrSWInstalledName[$key]);
		    		}

		    	if ($hrSWInstalled["hrSWInstalledName"])
			    	$buffer.= '<name>'.htmlentities($hrSWInstalled["hrSWInstalledName"], ENT_QUOTES) .'</name>';
			    else
			    	$buffer.= '<name>N/A</name>';

				$buffer .= '</software>';
				if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : Software => ". $hrSWInstalled["hrSWInstalledName"] ."\n", 3, $debug_path."inventory.log");
		    }
	} else if($type == 5 && $host_id){
		$hrSWRun = walk_snmp_value("1.3.6.1.2.1.25.4.2.1.1", "INTEGER: ");
	    if ($hrSWRun){
		    foreach ($hrSWRun as $key => $SWR){
		    	$buffer .= '<runningprocessus>';
		    	$app = str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.2.".$SWR, "STRING: "));
				$buffer .= '<application>'.htmlentities($app, ENT_QUOTES).'</application>';

				$path = str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.4.".$SWR, "STRING: "));
		    	$path = str_replace("\\\\", "\\", $path);

				if ($path)
					$buffer .= '<path> '.htmlentities($path, ENT_QUOTES).'</path>';
				else
					$buffer .= '<path>N/A</path>';

				$mem =	 get_snmp_value("1.3.6.1.2.1.25.5.1.1.2.".$SWR, "INTEGER: ");
		    	$buffer .= '<mem> '.$mem.'</mem>';
				$buffer .= '</runningprocessus>';

				if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : Running Processus  : Application => ". $app . ": Path => ". $path . " : Memory =>  " . $mem  ."\n", 3, $debug_path."inventory.log");
		    }
	    } else
	    	$buffer .= '<runningprocessus></runningprocessus>';
	}


	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	echo $buffer;

	if ($debug_inventory == 1)
		error_log("[" . date("d/m/Y H:s") ."] Inventory : XML Response : " .$buffer ."\n", 3, $debug_path."inventory.log");

?>