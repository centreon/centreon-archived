<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	$debugXML = 0;
	$buffer = '';

	require_once 'DB.php';

	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/DBconnect.php";
	include_once $centreon_path . "www/DBOdsConnect.php";
	include_once $centreon_path . "www/DBNDOConnect.php";
	
	/* PHP functions */
	include_once $centreon_path . "www/include/common/common-Func-ACL.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Include Access Class
	 */
	include_once $centreon_path . "www/class/centreonACL.class.php";

	if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) { 	
		header("Content-type: application/xhtml+xml"); 
	} else {
		header("Content-type: text/xml"); 
	} 
	echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n"); 

	function getMyHostGraphs($host_id = NULL)	{
		global $pearDBO;
		if (!isset($host_id))
			return NULL;
		$tab_svc = array();

		$DBRESULT =& $pearDBO->query("SELECT `service_id` FROM `index_data` WHERE `host_id` = '".$host_id."' AND `hidden` = '0' AND `trashed` = '0' ORDER BY `service_description`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($row =& $DBRESULT->fetchRow())
			$tab_svc[$row["service_id"]] = 1;
		return $tab_svc;
	}
	
	function getHostGraphedList()	{
		global $pearDBO;
		$tab = array();
		$DBRESULT =& $pearDBO->query("SELECT `host_id` FROM `index_data` WHERE `hidden` = '0' AND `trashed` = '0' ORDER BY `host_name`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($row =& $DBRESULT->fetchRow())
			$tab[$row["host_id"]] = 1;
		return $tab;
	}
	
	function checkIfServiceSgIsEn($host_id = NULL, $service_id = NULL)	{
		global $pearDBO;
		if (!isset($host_id) || !isset($service_id))
			return NULL;
		$tab_svc = array();

		$DBRESULT =& $pearDBO->query("SELECT `service_id` FROM `index_data` WHERE `host_id` = '".$host_id."' AND `service_id` = '".$service_id."' AND `hidden` = '0' AND `trashed` = '0'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$num_row =& $DBRESULT->numRows();
		$DBRESULT->free();
		return $num_row;
	}

	/* 
	 * if debug == 0 => Normal, debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml) 
	 */
	
	/* Connect to oreon DB */
	$dsn = array('phptype'  => 'mysql',
			     'username' => $conf_centreon['user'],
			     'password' => $conf_centreon['password'],
			     'hostspec' => $conf_centreon['hostCentreon'],
			     'database' => $conf_centreon['db']);
	$options = array('debug' => 2,'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
	
	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) 
		die("Connecting problems with oreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	global $is_admin, $user_id;

	$is_admin = isUserAdmin($_GET["sid"]);
	if (isset($_GET["sid"]) && $_GET["sid"]){
		$DBRESULT =& $pearDB->query("SELECT user_id FROM session where session_id = '".$_GET["sid"]."'");
		$session =& $DBRESULT->fetchRow();
		$access = new CentreonAcl($session["user_id"], $is_admin);
		$lca = array("LcaHost" => $access->getHostServices($pearDBndo), "LcaHostGroup" => $access->getHostGroups(), "LcaSG" => $access->getServiceGroups());
		
		$hoststr = $access->getHostsString("ID", $pearDBndo);
		$servicestr = $access->getServicesString("ID", $pearDBndo);
	} else 
		exit();
		
	$normal_mode = 1;
	(isset($_GET["mode"])) ? $normal_mode = $_GET["mode"] : $normal_mode = 1;
	(isset($_GET["id"])) ? $url_var = $_GET["id"] : $url_var = 0;

	$type = "root";
	$id = "0";
	if (strlen($url_var) > 1){
		$id = "42";
		$type = substr($url_var, 0, 2);
		$id = substr($url_var, 3, strlen($url_var));
	}

	if ($normal_mode){
		$i = 0;
		print("<tree id='".$url_var."' >");	
		
		if ($type == "HG") {
			/*
			 * Get Hosts
			 */
			$hosts = getMyHostGroupHosts($id);
			foreach ($hosts as $host){
				if ($is_admin){
					if (host_has_one_or_more_GraphService($host))
				        print("<item child='1' id='HH_".$host."_".$id."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host]) && host_has_one_or_more_GraphService($host))
			        	print("<item child='1' id='HH_".$host."_".$id."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
				}
			}
		} else if ($type == "ST") {
			/*
			 * Send Service/host list for a SG 
			 */
			$data = getMyServiceGroupActivateServices($id);
			foreach ($data as $key => $value){
				$tab_value = split("_", $key);
				$host_name = getMyHostName($tab_value[0]);
				$service_description = getMyServiceName($tab_value[1], $tab_value[0]);
				if (checkIfServiceSgIsEn($tab_value[0], $tab_value[1]))
					print("<item child='0' id='HS_".$tab_value[1]."_".$tab_value[0]."' text='".$host_name." - ".$service_description."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif' ></item>");
			}
		} else if ($type == "HH") {
			/*
			 * get services for host
			 */
			$tab_value = split("_", $id);
			$id = $tab_value[0];
			$services = getMyHostActiveServices($id);
			$graphList = getMyHostGraphs($id);
		    $host_name = getMyHostName($id);
		    foreach ($services as $svc_id => $svc_name){
				if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$id]) && isset($lca["LcaHost"][$id][$svc_id])))
			        print("<item child='0' id='HS_".$svc_id."_".$id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>");			
			}
		} else if ($type == "HS") {	
			;
		} else if ($type == "HO") {
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' order by host_name");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
		        if ($is_admin)
			        print("<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
				else if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]]))
			       print("<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");			
			}
			$DBRESULT2->free();
		} else if ($type == "RS") {
			/*
			 * Send Service Group list
			 */
			$lcaSG = getLCASG($pearDB);
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($SG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin){
					if (SGIsNotEmpty($SG["sg_id"]))
			        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
				} else {
					if (SGIsNotEmpty($SG["sg_id"]) && isset($lcaSG) && isset($lcaSG[$SG["sg_id"]]))
			        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
				}
			}
			$DBRESULT->free();
		} else if ($type == "MT") {
			/*
			 * Send Meta Service list
			 */
			$cpt = 0;
			$str = 0;
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM meta_service ORDER BY `meta_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($MS =& $DBRESULT->fetchRow()){
				$i++;
				print("<item child='0' id='MS_".$MS["meta_id"]."' text='".$MS["meta_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
			}
			$DBRESULT->free();
		} else if ($type == "RR") {
			/*
			 * Send Host Group list
			 */
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($HG =& $DBRESULT->fetchRow()) {
				$i++;
				if ($is_admin){
					if (HG_has_one_or_more_host($HG["hg_id"])){
			        	print("<item child='1' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				} else {
					if (HG_has_one_or_more_host($HG["hg_id"]) && isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_id"]])){
			        	print("<item child='1' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				}
			}
			$DBRESULT->free();
			/*
			 * Hosts Alone
			 */
			
			$cpt = 0;
			$str = "";
			$hostWithGraph = getHostGraphedList();
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' ORDER BY host_name");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
				if (isset($hostWithGraph[$host["host_id"]])){
					if ($is_admin){
			           	$cpt++;
			           	$str .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>";
					} else {
						if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]])){
							$str .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>";	
							$cpt++;
						}
					}
				}
			}
			$DBRESULT2->free();
			if ($cpt){
				print ("<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");
				print $str ;
				print("</item>");	
			}
			
			/*
			 * Meta Services
			 */
			$cpt = 0;
			$str = 0;
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM meta_service ORDER BY `meta_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($MS =& $DBRESULT->fetchRow()){
				$i++;
				$cpt++;
			}
			if ($cpt && $is_admin){
				print("<item child='1' id='MT_0' text='Meta services' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");	
				print $str ;
				print("</item>");
			}
			
		} else {
			print("<item nocheckbox='1' open='1' call='1' select='1' child='1' id='RR_0' text='HostGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
			print("<itemtext>label</itemtext>");
			print("</item>");
			print("<item nocheckbox='1' open='1' call='1' select='1' child='1' id='RS_0' text='ServiceGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
			print("<itemtext>label</itemtext>");
			print("</item>");
		}
	} else {
		/* 
		 * direct to ressource (ex: pre-selected by GET)
		 */
		$selected = array();
		$selected_host = array();
		
		$tab_id = split(",", $url_var);
		foreach ($tab_id as $openid) {
			$tabTMP = split("_", $openid);
			if (count($tabTMP) == 3){
				$type = $tabTMP[0];
				$selected[$tabTMP[1]."_".$tabTMP[2]] = 1;
				$selected_host[$tabTMP[2]] = 1;
			}
		}
		
		print("<tree id='1'>");
		print("<item nocheckbox='1' call='0' open='1' select='1' child='0' id='RR_0' text='HostGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif'>");
	   	
	   	$hostgroups = getAllHostgroups();
	   	$i = 0;
		foreach ($hostgroups as $hg_id => $hg_name){
			$i++;
			if (HG_has_one_or_more_host($hg_id)){
	
				$hg_open = $hg_checked = "";
				if (isset($hgs_selected[$hg_id]))
					$hg_checked = " checked='1' ";
				if (isset($hgs_open[$hg_id]))
					$hg_open = " open='1' ";
	    		if (HG_has_one_or_more_host($hg_id) && ($is_admin || isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$hg_name]))){
		    		print("<item child='1' $hg_open $hg_checked id='HG_".$hg_id."_$i' text='".$hg_name."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
					$hosts = getMyHostGroupHosts($hg_id);
					foreach ($hosts as $host_id => $host_name){
						$i++;
						$services = getMyHostActiveServices($host_id);
						$graphList = getMyHostGraphs($host_id);
						$host_name = getMyHostName($host_id);
						if (($is_admin &&  host_has_one_or_more_GraphService($host_id)) || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host_name]) && host_has_one_or_more_GraphService($host_id))) {
							print("<item child='1' call='0' id='HH_".$host_id."_$i' text='$host_name' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");	
						    foreach ($services as $svc_id => $svc_name){
								if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))){
							    	$checked = "";
									if (isset($selected[$svc_id."_".$host_id]))
										$checked = " open='1' checked='1' ";
									if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]])))
								        print("<item child='0' $checked call='0' id='HS_".$svc_id."_".$host_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>");			
								}
							}
							print("</item>");
						}
					}
					print("</item>");
	    		}
			}
		}
		/*
		 * Hosts Alone
		 */
		$cpt = 0;
		$str = "";
		
		$hostWithGraph = getHostGraphedList();
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' ORDER BY host_name");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		while ($host =& $DBRESULT2->fetchRow()){
			$i++;
			if (isset($hostWithGraph[$host["host_id"]])){
				if ($is_admin){
		           	$cpt++;
		           	$checked = "";
					if (isset($selected_host[$host["host_id"]]))
						$checked = " open='1' ";
		           	$str .= "<item child='1' $checked call='0' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>";
		           	$host_id = $host["host_id"];
		           	$services = getMyHostActiveServices($host_id);
					$graphList = getMyHostGraphs($host_id);
					$host_name = getMyHostName($host_id);
					if (($is_admin &&  host_has_one_or_more_GraphService($host_id)) || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host_name]) && host_has_one_or_more_GraphService($host_id))) {
						foreach ($services as $svc_id => $svc_name){
							if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))){
						    	$checked = "";
								if (isset($selected[$svc_id."_".$host_id]))
									$checked = " open='1' checked='1' ";
								if (isset($graphList[$svc_id]))
							        $str .= "<item child='0' $checked call='0' id='HS_".$svc_id."_".$host_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>";			
							}
						}
					}
		           	$str .= "</item>";
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]])) {
						$checked = "";
						if (isset($selected_host[$host["host_id"]]))
							$checked = " open='1' ";
						$str .= "<item child='1' $checked call='0' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>";
						$host_id = $host["host_id"];
			           	$services = getMyHostActiveServices($host_id);
						$graphList = getMyHostGraphs($host_id);
						$host_name = getMyHostName($host_id);
						if (($is_admin &&  host_has_one_or_more_GraphService($host_id)) || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host_name]) && host_has_one_or_more_GraphService($host_id))) {
							foreach ($services as $svc_id => $svc_name){
								if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))){
							    	$checked = "";
									if (isset($selected[$svc_id."_".$host_id]))
										$checked = " open='1' checked='1' ";
									if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]])))
								        $str .= "<item child='0' $checked call='0' id='HS_".$svc_id."_".$host_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>";			
								}
							}
						}
						$str .= "</item>";
						$cpt++;
					}
				}
			}
		}
		$DBRESULT2->free();
		if ($cpt){
			print ("<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");
			print $str ;
			print("</item>");	
		}
		/*
		 * Meta Services
		 */
		$cpt = 0;
		$str = 0;
		$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM meta_service ORDER BY `meta_name`");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($MS =& $DBRESULT->fetchRow()){
			$i++;
			$cpt++;
		}
		if ($cpt && $is_admin){
			print("<item child='1' id='MT_0' text='Meta services' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");	
			print $str ;
			print("</item>");
		}
		print("</item>");
		print("<item nocheckbox='1' open='1' call='0' select='1' child='0' id='RS_0' text='ServiceGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
		/*
		 * Send Service Group list
		 */
		$lcaSG = getLCASG($pearDB);
		$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($SG =& $DBRESULT->fetchRow()){
		    $i++;
			if ($is_admin) {
				if (SGIsNotEmpty($SG["sg_id"]))
		        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
			} else {
				if (SGIsNotEmpty($SG["sg_id"]) && isset($lcaSG) && isset($lcaSG[$SG["sg_id"]]))
		        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
			}
		}
		$DBRESULT->free();
		
		print("<itemtext>label</itemtext>");
		print("</item>");
	}
	print("</tree>");
?>