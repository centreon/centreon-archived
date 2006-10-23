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

	$buffer = "<reponse>";
	
	require_once("../../../oreon.conf.php");
	require_once("../../../DBconnect.php");
	
	
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
		global $address, $community, $timeout, $retries;
		$str = @snmpget($address, $community, $oid, $timeout , $retries);
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
	
	//$host_id = 37;
	//$_POST["type"] = 3;
	
	$type = isset($_GET["type"]) ? $_GET["type"] : 0;
	$type = isset($_POST["type"]) ? $_POST["type"] : $type;

	$host_id = isset($_GET["host_id"]) ? $_GET["host_id"] : 0;
	$host_id = isset($_POST["host_id"]) ? $_POST["host_id"] : $host_id;
	
	
	
	$community = getMySnmpCommunity($host_id);
	$version = getMySnmpVersion($host_id);	
	$address = getMyHostAddress($host_id);	
	
	$timeout = 100 * 1000;
	$retries = 10;
	
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
				$buffer .= '<Typelabel>'.get_snmp_value("1.3.6.1.2.1.25.3.8.1.4.".$SI, "OID: HOST-RESOURCES-TYPES::")	.'</Typelabel>';
				
				$block = get_snmp_value("1.3.6.1.2.1.25.2.3.1.4.".$SI, "INTEGER: ");
				
				$hrStorageIndex["hsStorageSize"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.5.".$SI, "INTEGER: ");
		    	$hrStorageIndex["hsStorageUsed"] = $block * get_snmp_value("1.3.6.1.2.1.25.2.3.1.6.".$SI, "INTEGER: ");
				$hrStorageIndex["hsStorageFree"] = $hrStorageIndex["hsStorageSize"] - $hrStorageIndex["hsStorageUsed"];
					    		    			    	
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
		    	if (isset($hrStorageIndex["hsStorageSize"])){
		    		if ($hrStorageIndex["hsStorageSize"] != 0)
		    			$buffer .= '<Utilisationlabel>'.round($hrStorageIndex["hsStorageUsed"] / $hrStorageIndex["hsStorageSize"] * 100).'</Utilisationlabel>';	
		    		else
			    		$buffer .= '<Utilisationlabel> </Utilisationlabel>';	
		    	}
		   		$hrStorageIndex["hsStorageFree"] ? $buffer .= '<Freelabel>'.$hrStorageIndex["hsStorageFree"].'</Freelabel>': $buffer .= '<Freelabel> </Freelabel>';
				$hrStorageIndex["hsStorageUsed"] ? $buffer .= '<Usedlabel>'.$hrStorageIndex["hsStorageUsed"].'</Usedlabel>': $buffer .= '<Usedlabel> </Usedlabel>';
				$hrStorageIndex["hsStorageSize"] ? $buffer .= '<Sizelabel>'.$hrStorageIndex["hsStorageSize"].'</Sizelabel>': $buffer .= '<Sizelabel> </Sizelabel>';
				$buffer .= '</storageDevice>';
		    }
		} else 
			$buffer .= '<storageDevice></storageDevice>';
	} else 	if($type == 6 && $host_id){
		$ifTab = walk_snmp_value(".1.3.6.1.2.1.2.2.1.1", "INTEGER: ");
	    if ($ifTab) {
		    foreach ($ifTab as $key => $it){
			   	$ifTab[$key]["ifIndex"] = $it;
//			    $buffer .= '<network>';

				//$buffer .= '<interfaceName>'.get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: ").'</interfaceName>';
				$interfaceName = get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: ");

				$operstatus = get_snmp_value("1.3.6.1.2.1.2.2.1.8.".$it, "INTEGER: ");
				preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $operstatus, $matches);
				$operstatus = $matches[1];



				if($operstatus)
					$status = $operstatus;
					//$buffer .= '<Status>'.$operstatus.'</Status>';
				else
					$status = 'none';
					//$buffer .= '<Status>none</Status>';

				if ($operstatus == "up")
					$class = 'list_three';
//					$buffer .= '<class>list_three</class>';
				else
					$class = 'list_four';
//					$buffer .= '<class>list_four</class>';
				$ifTab["ifPhysAddress"] = get_snmp_value("1.3.6.1.2.1.2.2.1.6.".$it, "STRING: ");
				if ($ifTab["ifPhysAddress"])
					$PhysAddress = $ifTab["ifPhysAddress"];
//					$buffer .= '<PhysAddress>'.$ifTab["ifPhysAddress"].'</PhysAddress>';
				else
					$PhysAddress = 'none';
//					$buffer .= '<PhysAddress> </PhysAddress>';
				# Type
				$iftype = get_snmp_value("1.3.6.1.2.1.2.2.1.3.".$it, "INTEGER: ");
		    	$r = preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $iftype, $matches);
		    	if (isset($ifType[$matches[2]]) && $ifType[$matches[2]])
			    	$ifTab["ifType"] = $ifType[$matches[2]];
		    	else
		    		$ifTab["ifType"] = " ";
//		    	$buffer .= '<Type>'.$ifTab["ifType"].'</Type>';
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
				
//				$buffer .= '<Trafic> In : '.$ifTab["ifInOctets"].' / Out '. $ifTab["ifOutOctets"].'</Trafic>';
				$traffic = ' In : '.$ifTab["ifInOctets"].' / Out '. $ifTab["ifOutOctets"];

				$ifSpeed = get_snmp_value("1.3.6.1.2.1.2.2.1.5.".$it, "Gauge32: ");
		    	for ($cpt = 0,$value = $ifSpeed; $value >= 1000 ; $value /= 1000)
					$cpt++;
//				$buffer .= '<Speed>'.$value.' '.$tab_unit[$cpt].'</Speed>';
				$speed = $value.' '.$tab_unit[$cpt];
//				$buffer .= '<errorPaquet> In : '.get_snmp_value("1.3.6.1.2.1.2.2.1.14.".$it, "Counter32: ") . " Pkts".' / Out : '.get_snmp_value("1.3.6.1.2.1.2.2.1.20.".$it, "Counter32: ") . " Pkts".'</errorPaquet>';
				$errorPaquet =  'In : '.get_snmp_value("1.3.6.1.2.1.2.2.1.14.".$it, "Counter32: ") . " Pkts".' / Out : '.get_snmp_value("1.3.6.1.2.1.2.2.1.20.".$it, "Counter32: ") . " Pkts";
				# IP Interface
				$index = get_snmp_value("1.3.6.1.2.1.4.20.1.2.".$it, "INTEGER: ");
	    		$ipInterface = array();
	    		$ipInterface["ipIP"] = $it;
	    		$ipInterface["ipNetMask"] = get_snmp_value("1.3.6.1.2.1.4.20.1.3.".$it, "IpAddress: ");
				if ($ipInterface["ipIP"] && $ipInterface["ipNetMask"])
					$str = $ipInterface["ipIP"].' / '.$ipInterface["ipNetMask"];
				else
					$str = "Not Defined";
//				$buffer .= '<ipAddress>'.$str.'</ipAddress>';
				$ipAddress = $str;
//				$buffer .= '</network>';


				$buffer .= '<network>';
				$buffer .= '<interfaceName isvalid="1">' .$interfaceName . '</interfaceName>';
				$buffer .= '<PhysAddress isvalid="1">' . $PhysAddress . '</PhysAddress>';
				$buffer .= '<Status isvalid="1">' . $status . '</Status>';
				$buffer .= '<class isvalid="1">' . $class . '</class>';
		    	$buffer .= '<Type isvalid="1">' . $type . '</Type>';
				$buffer .= '<Trafic isvalid="1">' . $traffic . '</Trafic>';
				$buffer .= '<Speed isvalid="1">' . $speed . '</Speed>';
				$buffer .= '<errorPaquet isvalid="1">' . $errorPaquet . '</errorPaquet>';
				$buffer .= '<ipAddress isvalid="1">' . $ipAddress . '</ipAddress>';
				$buffer .= '</network>';
		    }
	    } else 
	    	$buffer .= '<network>none</network>';
	} else if($type == 4 && $host_id){
		$hrSWInstalled = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.1", "INTEGER: ");
	   	$hrSWInstalledName = walk_snmp_value("1.3.6.1.2.1.25.6.3.1.2", "STRING: ");
	   	if ($hrSWInstalled)
			foreach ($hrSWInstalled as $key => $SWI){
		    	$buffer .= '<software>';	
				if (isset($hrSWInstalledName[$key]) && !strstr($hrSWInstalledName[$key], "Hex-"))
		    		$hrSWInstalled["hrSWInstalledName"] = str_replace("\"", "", $hrSWInstalledName[$key]);
		    	$buffer.= '<name>'.$hrSWInstalled["hrSWInstalledName"].'</name>';
				$buffer .= '</software>';
		    }
	} else if($type == 5 && $host_id){
		$hrSWRun = walk_snmp_value("1.3.6.1.2.1.25.4.2.1.1", "INTEGER: ");
	    if ($hrSWRun){
		    foreach ($hrSWRun as $key => $SWR){
		    	$buffer .= '<runningprocessus>';
				$buffer .= '<application>'.str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.2.".$SWR, "STRING: ")).'</application>';

				$path = str_replace("\"", "", get_snmp_value("1.3.6.1.2.1.25.4.2.1.4.".$SWR, "STRING: "));
		    	$path = str_replace("\\\\", "\\", $path);

		    	$buffer .= '<path> '.$path.'</path>';
		    	$buffer .= '<mem> '.get_snmp_value("1.3.6.1.2.1.25.5.1.1.2.".$SWR, "INTEGER: ").'</mem>';
				$buffer .= '</runningprocessus>';	
		    }
	    } else
	    	$buffer .= '</runningprocessus><runningprocessus>';
	}
	

	$buffer .= '</reponse>';	
	header('Content-Type: text/xml');
	echo $buffer;

?>