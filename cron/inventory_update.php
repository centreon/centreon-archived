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

	$path = '@OREON_PATH@';

 	include($path."/www/oreon.conf.php");
 	require_once ($path."/www/class/Session.class.php");
 	require_once ($path."/www/class/Oreon.class.php");

 	require_once 'DB.php';

  	## Init Microtime
  	$begin_time = microtime();

  	## Debug mode activation
 	 $debug = 0;

  	include($path."/www/include/inventory/common-Func.php");
  	require_once $path."/www/include/common/common-Func.php";
  	$dsn = array(
      'phptype'  => 'mysql',
      'username' => $conf_oreon['user'],
      'password' => $conf_oreon['password'],
      'hostspec' => $conf_oreon['host'],
      'database' => $conf_oreon['db'],
  	);
  	$options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
  	$pearDB =& DB::connect($dsn, $options);
  	if (PEAR::isError($pearDB))
      	die("pb connexion : ".$pearDB->getMessage());

  	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

  	$timeout = 30 * 1000;
  	$retries = 5;

  	if (!isset($oreon))
   	 	$oreon = 1;

 	$msg = '';

  	include($path."/www/include/inventory/inventory_oid_library.php");

  	$optres =& $pearDB->query("SELECT snmp_community,snmp_version FROM general_opt LIMIT 1");
  	$optr =& $optres->fetchRow();
  	$globalCommunity = $optr["snmp_community"];
  	$globalVersion = $optr["snmp_version"];

  	$resHost =& $pearDB->query("SELECT * FROM host WHERE host_register= '1'");

while ($r =& $resHost->fetchRow()){
	$host_id = $r["host_id"];
    $address = $r["host_address"];

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

   $uptime =  get_snmp_value(".1.3.6.1.2.1.1.3.0", "STRING: ");

    if ($uptime != FALSE){
		$host_inv =& $pearDB->query("SELECT * FROM inventory_index WHERE host_id = '$host_id'");
      	if ($host_inv->numRows() == 0){
        	$Constructor = get_manufacturer();
        	//$hwaddress = get_hwaddress();
        	$constr =& $pearDB->query(	"SELECT inventory_manufacturer.name,inventory_manufacturer.alias FROM inventory_manufacturer,inventory_index ".
                      "WHERE inventory_manufacturer.id = '".$Constructor."'");
          	$m =& $constr->fetchRow();
          	$Constructor_name = $m["name"];
          	$Constructor_alias = $m["alias"];
      	} else {
        	$constr =& $pearDB->query(	"SELECT inventory_manufacturer.name,inventory_manufacturer.alias FROM inventory_manufacturer,inventory_index ".
                      "WHERE inventory_manufacturer.id = inventory_index.type_ressources ".
                      "AND inventory_index.host_id = '".$host_id."'");
          	$m =& $constr->fetchRow();
          	$Constructor_name = $m["name"];
          	$Constructor_alias = $m["alias"];
          	//$hwaddress = get_hwaddress();
      	}
	      /*
	       * Get Data
	       */
		$sysLocation 	= get_snmp_value(".1.3.6.1.2.1.1.6.0", "STRING: ");
		$sysDescr 	= get_snmp_value(".1.3.6.1.2.1.1.1.0", "STRING: ");
		$sysName 		= get_snmp_value(".1.3.6.1.2.1.1.5.0", "STRING: ");
		$sysContact 	= get_snmp_value(".1.3.6.1.2.1.1.4.0", "STRING: ");

    	if ( isset($Constructor_name)&& ($Constructor_name)) {
			$str = get_snmp_value($oid[$Constructor_name]["SwitchVersion"], "STRING: ");
      		if ($str)
        		$SwitchVersion = str_replace("\"", "", $str);
        	$str = get_snmp_value($oid[$Constructor_name]["RomVersion"], "STRING: ");
	        if ($str)
	        	$RomVersion = str_replace("\"", "", $str);
	        $str = get_snmp_value($oid[$Constructor_name]["SerialNumber"], "STRING: ");
	        if ($str)
	        	$SerialNumber = str_replace("\"", "", $str);
	        $str = get_snmp_value($oid[$Constructor_name]["manufacturer"], "STRING: ");
	        if ($str)
	        	$Manufacturer = str_replace("\"", "", $str);
    		} else {
				$SwitchVersion = '';
		        $RomVersion = '';
		        $SerialNumber = '';
		        $Manufacturer = '';
    		}
	    	$res =& $pearDB->query("SELECT * FROM inventory_index WHERE host_id = '".$r["host_id"]."'");
			if (!$res->numRows()){
				if (!isset($Constructor) || ($Constructor == 0 ))
	        		$Constructor = "NULL";
	        	else
	            	$Constructor = "'".$Constructor."'";
		        $res =& $pearDB->query(	"INSERT INTO `inventory_index` (`id`, `host_id`, `name`, `contact`, `description`, `location`, `manufacturer`, `serial_number`, `os`, `os_revision`, `type_ressources`) " .
		                      			"VALUES ('', '".$r["host_id"]."', '".$sysName."', '".$sysContact."', '".$sysDescr."', '".$sysLocation."', '".$Manufacturer."', '".$SerialNumber."', '".$SwitchVersion."', '".$RomVersion."', ".$Constructor.")");
		        if (PEAR::isError($res))
		        	print ($res->getMessage());
	    	} else
	      		verify_data($r["host_id"]);
    	} else {
      		;
    	}
  	}
	$end_time = microtime();
	$time_len = $end_time - $begin_time;
?>
