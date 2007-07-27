<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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
		include_once("./include/inventory/inventory_oid_library.php");
		include_once("./include/inventory/inventory_library.php");

		$timeout = 100 * 1000;
	    $retries = 10;
		
	  	$community = getMySnmpCommunity($host_id);
		$version = getMySnmpVersion($host_id);	
	    $address = getMyHostAddress($host_id);	

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

		$tpl->assign("host_id", $host_id);


/*
 * sort1", $lang["s_description"]);
 */
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


		$tpl->assign("Statuslabel", $lang["s_status"]);
		$tpl->assign("Outlabel", $lang["s_Out"]);
		$tpl->assign("Inlabel", $lang["s_In"]);
		$tpl->assign("errorlabel", $lang["s_Error"]);
		$tpl->assign("PhysAddresslabel", $lang["s_PhysAddress"]);
		$tpl->assign("Typelabel", $lang["s_Type"]);
    	$tpl->assign("Trafficlabel", $lang["s_traffic"]);
		$tpl->assign("Errorlabel", $lang["s_pkt_error"]);

		$tpl->assign("mntPointlabel",$lang["s_mntPoint"]);
		$tpl->assign("Typelabel",$lang["s_Type"]);
		$tpl->assign("Utilisationlabel",$lang["s_Utilisation"]);
		$tpl->assign("Freelabel",$lang["s_Free"]);
		$tpl->assign("Usedlabel",$lang["s_Used"]);
		$tpl->assign("Sizelabel",$lang["s_Size"]);

		$tpl->assign("Softwarelabel",$lang["s_Software"]);
		$tpl->assign("MermoryUsedlabel",$lang["s_MermoryUsed"]);
		$tpl->assign("Pathlabel",$lang["s_Path"]);
		
		$tpl->assign("Datelabel", $lang['s_Date']);
		$tpl->assign("Objectlabel", $lang['s_Object']);
		$tpl->assign("Beforelabel", $lang['s_Before']);
		$tpl->assign("Afterlabel", $lang['s_After']);
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

	include('AjaxIDCard_serverInfos_js.php');

	if (isset($tpl) && $host_id && $sysUpTime)
		$tpl->display("IDCard_server/infosServer.ihtml");
	else
		print "<div class='msg' align='center'>".$lang["s_notAvl"]."</div>";
?>