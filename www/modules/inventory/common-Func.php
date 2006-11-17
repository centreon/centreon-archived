<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Inventory » is developped by Merethis company for Lafarge Group,
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

	/*
	 * Include function For SNMPWALK and SNMPGET
	 */

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

	/*
	 * Get manufacturer ID with mac address
	 */

	function get_manufacturer(){
		global $address;
		global $pearDB;
		$str = walk_snmp_value("1.3.6.1.2.1.2.2.1.6", "STRING: ");
		if (isset($str) && $str)
			foreach ($str as $ifTab_address){
				if (isset($ifTab_address) && strcmp("", $ifTab_address) && strcmp("0:1", $ifTab_address)){
					preg_match("/^([0-9A-Fa-f]*\:[0-9A-Fa-f]*\:[0-9A-Fa-f]*).*/", $ifTab_address, $matches);
					$tab = split(":", substr($matches[1], 0, 8));
					foreach ($tab as $key => $t)
						if (strlen($t) == 1)
							$tab[$key] = strtoupper("0" . $t);
					$res =& $pearDB->query("SELECT manufacturer,mac_address_begin FROM inventory_mac_address WHERE mac_address_begin LIKE '".$tab[0].":".$tab[1].":".$tab[2]."' ");
					while ($r =& $res->fetchRow())
						if ($r["manufacturer"])
							return $r["manufacturer"];
					break;
				}
			}
		return 0;
	}

	function get_hwaddress(){
		global $address;
		global $pearDB;
		$str = walk_snmp_value("1.3.6.1.2.1.2.2.1.6", "STRING: ");
		if (isset($str) && $str)
			foreach ($str as $ifTab_address){
				if (isset($ifTab_address) && strcmp("", $ifTab_address)){
					preg_match("/^([0-9A-Fa-f]*\:[0-9A-Fa-f]*\:[0-9A-Fa-f]*\:[0-9A-Fa-f]*\:[0-9A-Fa-f]*\:[0-9A-Fa-f]*).*/", $ifTab_address, $matches);
					//print "-" . $address . "->" . substr($matches[1], 0, 8) ."|". $ifTab_address ."\n";
					$tab = split(":", $matches[1]);
					foreach ($tab as $key => $t) {
						if (strlen($t) == 1)
							$tab[$key] = strtoupper("0" . $t);
					}
					$hwaddress = $tab[0].":".$tab[1].":".$tab[2].":".$tab[3].":".$tab[4].":".$tab[5];
					return $hwaddress ;
					break;
				}
			}
		return 0;
	}


	function verify_data($host_id){
		global $pearDB, $debug;
		global $sysName,$sysContact,$sysDescr,$sysLocation,$Manufacturer,$SerialNumber,$SwitchVersion,$RomVersion;

		/*
    	 * Recuprer les donnes pour faire un diff de donnes qui ont change.
    	 */

    	$resLogCurrent =& $pearDB->query("SELECT * FROM `inventory_index` WHERE `host_id` = '".$host_id."' LIMIT 1");
    	$rLC =& $resLogCurrent->fetchRow();
    	/*
    	if ($rLC["type_ressources"] == NULL){
	    	//$manufacturer = get_manufacturer();
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `type_ressources` = '".$manufacturer."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
			if (PEAR::isError($res)) print ("type_ressources : " . $res->getMessage());
    	}
    	*/
    	/*
    	 * Selectionner les infos qui different et mettre un log en place pour l'historique de modifs
    	 */

    	if (strcmp($rLC["name"], $sysName)) {
    		$res =& $pearDB->query("UPDATE `inventory_index` SET `name` = '".$sysName."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'name', '".$rLC["name"]."', '".$sysName."', '".time()."')");
       	}
    	if (strcmp($rLC["contact"], $sysContact))  {
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `contact` = '".$sysContact."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'contact', '".$rLC["contact"]."', '".$sysContact."', '".time()."')");
    	}
    	if (strcmp($rLC["description"], $sysDescr)) {
   	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `description` = '".$sysDescr."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'description', '".$rLC["description"]."', '".$sysDescr."', '".time()."')");
    	}
    	if (strcmp($rLC["location"], $sysLocation)) {
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `location` = '".$sysLocation."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'location', '".$rLC["location"]."', '".$sysLocation."', '".time()."')");
    	}
    	if (strcmp($rLC["manufacturer"], $Manufacturer)) {
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `manufacturer` = '".$Manufacturer."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'manufacturer', '".$rLC["manufacturer"]."', '".$Manufacturer."', '".time()."')");
    	}
    	$SerialNumber = str_replace(" ", "", $SerialNumber);
    	if (strcmp($rLC["serial_number"], $SerialNumber)) {
    		$res =& $pearDB->query("UPDATE `inventory_index` SET `serial_number` = '".$SerialNumber."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'servial_number', '".$rLC["serial_number"]."', '".$SerialNumber."', '".time()."')");
    	}
    	$SwitchVersion = str_replace(" ", "", $SwitchVersion);
    	if (strcmp($rLC["os"], $SwitchVersion)) {
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `os` = '".$SwitchVersion."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'os', '".$rLC["os"]."', '".$SwitchVersion."', '".time()."')");
    	}
    	$RomVersion = str_replace(" ", "", $RomVersion);
    	if (strcmp($rLC["os_revision"], $RomVersion)) {
	    	$res =& $pearDB->query("UPDATE `inventory_index` SET `os_revision` = '".$RomVersion."' WHERE `host_id` = '".$host_id."' LIMIT 1 ;") ;
    		$res =& $pearDB->query("INSERT INTO `inventory_log` (`id`, `host_id`, `type`, `replaced_value`, `value`, `ctime`) VALUES ('', '".$host_id."', 'os_revision', '".$rLC["os_revision"]."', '".$RomVersion."', '".time()."')");
    	}

    }


?>