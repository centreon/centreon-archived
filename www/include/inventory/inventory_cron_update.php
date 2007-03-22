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


 include("../../oreon.conf.php");
 require_once ("../../$classdir/Session.class.php");
 require_once ("../../$classdir/Oreon.class.php");
 Session::start();

 if (!isset($_SESSION["oreon"])) {
 	// Quick dirty protection
 	header("Location: ./index.php");
 	//exit();
 }else {
 	$oreon =& $_SESSION["oreon"];
 }
 is_file ("../../lang/".$oreon->user->get_lang().".php") ? include_once ("../../lang/".$oreon->user->get_lang().".php") : include_once ("../../lang/en.php");
 is_file ("../../include/configuration/lang/".$oreon->user->get_lang().".php") ? include_once ("../../include/configuration/lang/".$oreon->user->get_lang().".php") : include_once ("../../include/configuration/lang/en.php");
 is_file ("lang/".$oreon->user->get_lang().".php") ? include_once ("lang/".$oreon->user->get_lang().".php") : include_once ("lang/en.php");

  require_once 'DB.php';

  ## Init Microtime
  $begin_time = microtime();

  ## Debug mode activation
  $debug = 0;

  include("./common-Func.php");
  require_once "../../include/common/common-Func.php";

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

  include("inventory_oid_library.php");

 	$res =& $pearDB->query("SELECT debug_path, debug_inventory  FROM general_opt LIMIT 1");
	if (PEAR::isError($res))
		print $res->getDebugInfo()."<br>";

  $debug = $res->fetchRow();

  $debug_inventory = $debug['debug_inventory'];
  $debug_path = $debug['debug_path'];
  if (!isset($debug_inventory))
  	$debug_inventory = 0;

  $optres =& $pearDB->query("SELECT snmp_community,snmp_version FROM general_opt LIMIT 1");
  $optr =& $optres->fetchRow();
  $globalCommunity = $optr["snmp_community"];
  $globalVersion = $optr["snmp_version"];

  if ($debug_inventory == 1)
	error_log("[" . date("d/m/Y H:s") ."] Inventory : Global : SNMP Community : ".  $globalCommunity . ", SNMP Version => ". $globalVersion ."\n", 3, $debug_path."inventory.log");

  	$resHost =& $pearDB->query("SELECT * FROM host WHERE host_register= '1'");
	if (PEAR::isError($resHost))
		print $resHost->getDebugInfo()."<br>";
		
 print "<table id='ListTable'>\n";
 print "	<tr class='ListHeader'>\n";
 print "		<td class='ListColHeaderLeft'>".$lang['name']."</td>\n";
 print "		<td class='ListColHeaderLeft'>".$lang['h_address']."</td>\n";
 print "		<td class='ListColHeaderLeft'>".$lang['s_type']." / ".$lang['s_manufacturer']."</td>\n";
 print "	</tr>\n";

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

  if ($debug_inventory == 1) {
   		error_log("[" . date("d/m/Y H:s") ."] Inventory : Host : Name => ". $r["host_name"] . ", ID => ".  $host_id . ", Address => " . $address ."\n", 3, $debug_path."inventory.log");
		error_log("[" . date("d/m/Y H:s") ."] Inventory : Host : SNMP Community => ".  $community . ", SNMP Version => ". $version ."\n", 3, $debug_path."inventory.log");
	}

  /*  if ($r["host_snmp_community"])
        $community = $r["host_snmp_community"];
      else
        $community = $globalCommunity;*/
    $uptime =  get_snmp_value(".1.3.6.1.2.1.1.3.0", "STRING: ");

    if ($uptime != FALSE){
    	if ($debug_inventory == 1)
   			error_log("[" . date("d/m/Y H:s") ."] Inventory : Host : Great ! we have a SNMP response (uptime)\n", 3, $debug_path."inventory.log");

      $host_inv =& $pearDB->query("SELECT * FROM inventory_index WHERE host_id = '$host_id'");
      if ($host_inv->numRows() == 0){
        $Constructor = get_manufacturer();
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
      }
      /*
         * Get Data
         */

      print "<tr class='list_one'>\n";
      print "		<td class='ListColLeft'>".$r['host_name']."</td>\n";
	  print "		<td class='ListColLeft'>".$address."</td>\n";
      print "		<td class='ListColLeft'>".$Constructor_alias."</td>\n";
      print "	</tr>\n";

	  $sysLocation = get_snmp_value(".1.3.6.1.2.1.1.6.0", "STRING: ");
      $sysDescr =  	get_snmp_value(".1.3.6.1.2.1.1.1.0", "STRING: ");
      $sysName =  	get_snmp_value(".1.3.6.1.2.1.1.5.0", "STRING: ");
      $sysContact = get_snmp_value(".1.3.6.1.2.1.1.4.0", "STRING: ");

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
      } else {
        verify_data($r["host_id"]);
        }
    } else {
        if ($debug_inventory == 1)
   			error_log("[" . date("d/m/Y H:s") ."] Inventory : Host : Don't seems to have SNMP, no uptime retrieved\n", 3, $debug_path."inventory.log");

      //print "host : ".$r["host_name"]." n'a pas snmp;\n";
    }
  }
   print "</table>\n";
  $end_time = microtime();
  $time_len = $end_time - $begin_time;
?>
