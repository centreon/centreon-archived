<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
		global $address, $community, $timeout, $retries, $debug_inventory, $debug_path ;
		$str = @snmpget($address, $community, $oid, $timeout , $retries);

		if ($debug_inventory == 1)
			error_log("[" . date("d/m/Y H:s") ."] Inventory : OID => " . $oid . ", Value => " . $str ."\n", 3, $debug_path."inventory.log");

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

	//$_POST["host_id"] = 53;
	//$_POST["type"] = 2;
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

	if (($type == 3 || $type == 2) && $host_id){
		$ifTab = walk_snmp_value(".1.3.6.1.2.1.2.2.1.1", "INTEGER: ");
	    if ($ifTab){
		    foreach ($ifTab as $key => $it){
			   	$ifTab[$key]["ifIndex"] = $it;
			   	$description = get_snmp_value("1.3.6.1.2.1.2.2.1.2.".$it, "STRING: ");
			   	$iftype = get_snmp_value("1.3.6.1.2.1.2.2.1.3.".$it, "INTEGER: ");
			   	(strstr(strtolower($description), "vlan") || strstr(strtolower($iftype), 'virtual')) ? $type = 2 : $type = 1;
			    $type == 1 ? $buffer .= '<network>': $buffer .= '<vlan>';
				$buffer .= '<interfaceName>'. htmlentities($description, ENT_QUOTES) .'</interfaceName>';
				$operstatus = get_snmp_value("1.3.6.1.2.1.2.2.1.8.".$it, "INTEGER: ");
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
					$buffer .= '<PhysAddress>'.$ifTab["ifPhysAddress"].'</PhysAddress>';
				else
					$buffer .= '<PhysAddress> </PhysAddress>';
				# Type
				$r = preg_match("/([A-Za-z\-]*)\(?([0-9]+)\)?/", $iftype, $matches);
		    	if (isset($ifType[$matches[2]]) && $ifType[$matches[2]])
			    	$ifTab["ifType"] = $ifType[$matches[2]];
		    	else
		    		$ifTab["ifType"] = " ";
		    	$buffer .= '<Type>'.$ifTab["ifType"].'</Type>';

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

				$buffer .= '<Trafic> In : '.$ifTab["ifInOctets"].' / Out '. $ifTab["ifOutOctets"].'</Trafic>';

				$ifSpeed = get_snmp_value("1.3.6.1.2.1.2.2.1.5.".$it, "Gauge32: ");
		    	for ($cpt = 0,$value = $ifSpeed; $value >= 1000 ; $value /= 1000)
					$cpt++;
				$buffer .= '<Speed>'.$value.' '.$tab_unit[$cpt].'</Speed>';

				$buffer .= '<errorPaquet> In : '.get_snmp_value("1.3.6.1.2.1.2.2.1.14.".$it, "Counter32: ") . " Pkts".' / Out : '.get_snmp_value("1.3.6.1.2.1.2.2.1.20.".$it, "Counter32: ") . " Pkts".'</errorPaquet>';

				# IP Interface
	/*			$index = get_snmp_value("1.3.6.1.2.1.4.20.1.2.".$it, "INTEGER: ");
	    		$ipInterface = array();
	    		//$ipInterface["ipIP"] = $it;
	    		$ipInterface["ipIP"] = get_snmp_value("1.3.6.1.2.1.4.20.1.1.".$it, "IpAddress: ");
	    		$ipInterface["ipNetMask"] = get_snmp_value("1.3.6.1.2.1.4.20.1.3.".$it, "IpAddress: ");

	    		if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : IP Address => " . $ipInterface["ipIP"] . " : NetMask => " .  $ipInterface["ipNetMask"]  ."\n", 3, $debug_path."inventory.log");


				if ($ipInterface["ipIP"] && $ipInterface["ipNetMask"])
					$str = $ipInterface["ipIP"].' / '.$ipInterface["ipNetMask"];
				else
					$str = "Not Defined";
				$buffer .= '<ipAddress>'.$str.'</ipAddress>';
				$type == 1 ? $buffer .= '</network>': $buffer .= '</vlan>';

				if ($debug_inventory == 1)
					error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' : Description => ". $description  . " : Type => " . $ifTab["ifType"]. " :  Status => " . $operstatus . " : Speed => " .  $value.' '.$tab_unit[$cpt] . " : PhysAddress => " .  $ifTab["ifPhysAddress"]  . ": IP Address => " . $str  ."\n", 3, $debug_path."inventory.log");
					*/

				$ipInterface_data = walk_snmp_value("1.3.6.1.2.1.4.20.1.1", "IpAddress: ");
			  	if ($ipInterface_data){
			    	foreach ($ipInterface_data as $it){
			    		$index = get_snmp_value("1.3.6.1.2.1.4.20.1.2.".$it, "INTEGER: ");
			    		$ipInterface[$index] = array();
			    		$ipInterface[$index]["ipIP"] = $it;
			    		$ipInterface[$index]["ipIndex"] = $index;
			    		$ipInterface[$index]["ipNetMask"] = get_snmp_value("1.3.6.1.2.1.4.20.1.3.".$it, "IpAddress: ");

			    		if ($debug_inventory == 1)
							error_log("[" . date("d/m/Y H:s") ."] Inventory : Host '".  $address . "' Index => " . $ipInterface[$index]["ipIndex"] ." : Ip => " .$ipInterface[$index]["ipIP"] . " : NetMask => " .  $ipInterface[$index]["ipNetMask"]  ."\n", 3, $debug_path."inventory.log");
			    	}
			    }

		    	if (isset($ipInterface[$ifTab[$key]["ifIndex"]]) && $ipInterface[$ifTab[$key]["ifIndex"]]["ipIP"])
					$str   = $ipInterface[$ifTab[$key]["ifIndex"]]["ipIP"]." / ".$ipInterface[$ifTab[$key]["ifIndex"]]["ipNetMask"];
				else
					$str   = "Not Defined";

				$buffer .= '<ipAddress>'.$str.'</ipAddress>';
				$type == 1 ? $buffer .= '</network>': $buffer .= '</vlan>';

		    }
	    } else {
	    	$buffer .= "<network></network>\n<vlan></vlan>";
	    }
	}

	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	echo $buffer;

	if ($debug_inventory == 1)
		error_log("[" . date("d/m/Y H:s") ."] Inventory : XML Response : " .$buffer ."\n", 3, $debug_path."inventory.log");

?>