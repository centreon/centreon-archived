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

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBOdsConnect.php");
	
	/* PHP functions */
	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) { 	
		header("Content-type: application/xhtml+xml"); 
	} else {
		header("Content-type: text/xml"); 
	} 
	echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n"); 

	function getMyHostGraphs($host_id = NULL)	{
		global $pearDBO;
		if (!$host_id)
			return NULL;
		$tab_svc = array();
		$DBRESULT =& $pearDBO->query("SELECT `service_id` FROM `index_data` WHERE `host_id` = '".$host_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($row =& $DBRESULT->fetchRow())
			$tab_svc[$row["service_id"]] = 1;
		return $tab_svc;
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
		if (!isUserAdmin($_GET["sid"]))
			$lca = getLCASVC(getLcaHostByName($pearDB));
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
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][getMyHostName($host)]) && host_has_one_or_more_GraphService($host))
			        	print("<item child='1' id='HH_".$host."_".$id."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
				}
			}
		} else if ($type == "ST") {
			/*
			 * Send Service/host list for a SG 
			 */
			$data = getMyServiceGroupServices($id);
			foreach ($data as $key => $value){
				$tab_value = split("_", $key);
				$host_name = getMyHostName($tab_value[0]);
				$service_description = getMyServiceName($tab_value[1], $tab_value[0]);
				if ($is_admin) {
					print("<item child='0' id='HS_".$tab_value[1]."_".$tab_value[0]."' text='".$host_name." - ".$service_description."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif' ></item>");					
				} else {
					//if (HG_has_one_or_more_host($HG["hg_id"]) && isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_alias"]]))
			    	print("<item child='0' id='HS_".$tab_value[1]."_".$tab_value[0]."' text='".$host_name." - ".$service_description."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif' ></item>");					
				}
			}
		} else if ($type == "HH") {
			/*
			 * get services for host
			 */
			$services = getMyHostServices($id);
			$graphList = getMyHostGraphs($id);
		    $host_name = getMyHostName($id);
		        
		    foreach ($services as $svc_id => $svc_name){
				if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]])))
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
		        print("<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
			}
			$DBRESULT2->free();
		} else if ($type == "RS") {
			/*
			 * Send Service Group list
			 */
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($SG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin){
					if (SGIsNotEmpty($SG["sg_id"])){
			        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				} else {
					if (SGIsNotEmpty($SG["sg_id"]) /*&& isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_alias"]])*/){
			        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				}
			}
			$DBRESULT->free();
		} else if ($type == "RR") {
			/*
			 * Send Host Group list
			 */
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($HG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin){
					if (HG_has_one_or_more_host($HG["hg_id"])){
			        	print("<item child='1' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				} else {
					if (HG_has_one_or_more_host($HG["hg_id"]) && isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_alias"]])){
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
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' order by host_name");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			while ($host $DBRESULT2->fetchRow()){
				$i++;
				$cpt++;
				if ($is_admin){
		           	$str .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>";
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]]))
						$str .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>";	
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
		        print("<item child='0' id='MS_".$MS["meta_id"]."' text='".$MS["meta_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
			}
			if ($cpt){
				print("<item child='1' id='MS_0' text='Meta services' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");	
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
		$hgs_selected = array();
		$hosts_selected = array();
		$svcs_selected = array();
	
		$hgs_open = array();
		$hosts_open = array();
		
		print("<tree id='1' >");
		$tab_id = split(",",$url_var);
		foreach ($tab_id as $openid) {
			$type = substr($openid, 0, 2);
			$id = substr($openid, 3, strlen($openid));
	
			echo "<id>".$id."</id>";
	
			$id_full = split('_', $id);
			$id = $id_full[0];
			echo "<idfull>";
			print_r($id_full);
			echo "</idfull>";
			
			if ($type == "HH") {
				/*
				 * host + hg_parent
				 */	
				$hosts_selected[$id] = getMyHostName($id);
				$hosts_open[$id] = getMyHostName($id);	
				/* + all svc*/
				$services = getMyHostServices($id);
				foreach($services as $svc_id => $svc_name)
					$svcs_selected[$svc_id] = $svc_name;
				// 	hg_parent
				if (isset($id_full[2]))
					$hgs_open[$id_full[2]] = getMyHostGroupName($id_full[2]);
				else {
					$hgs = getMyHostGroups($id);
					foreach($hgs as $hg_id => $hg_name)
						$hgs_open[$hg_id] = $hg_name;
				}				
			} else if($type == "HS"){ // svc + host_parent + hg_parent
				/*
				 * svc
				 */
				$svcs_selected[$id] = getMyServiceName($id);
				$svcs_selected[$id] = getMyServiceName($id);
	
				/*
				 * host_parent
				 */
				if (isset($id_full[1])) {
					$host_id = $id_full[1];
					$hosts_open[$host_id] = getMyHostName($host_id);
				} else {
					$host_id = getMyHostServiceID($id);
					$hosts_open[$host_id] = getMyHostName($host_id);				
				}

				/*
				 * hg_parent
				 */
				if (isset($id_full[2]))
					$hgs_open[$id_full[2]] = getMyHostGroupName($id_full[2]);
				else {
					$hgs = getMyHostGroups($host_id);
					foreach($hgs as $hg_id => $hg_name)
						$hgs_open[$hg_id] = $hg_name;
				}			
			} else if($type == "HG"){ // HG + hostS_child + svcS_child
				
				$hgs_selected[$id] = getMyHostGroupName($id);
				$hgs_open[$id] = getMyHostGroupName($id);
	
				$hosts = getMyHostGroupHosts($id);
				foreach($hosts as $host_id) {
					$host_name = getMyHostName($host_id);
					$hosts_open[$host_id] = $host_name;
					$hosts_selected[$host_id] = $host_name;
	
					/* 
					 * + all svc
					 */
					$services = getMyHostServices($host_id);
					foreach($services as $svc_id => $svc_name)
						$svcs_selected[$svc_id] = $svc_name;
				}
			}
		}
	
		$hostgroups = getAllHostgroups();
		foreach($hostgroups as $hg_id => $hg_name){
			/*
			 * Hostgroups
			 */
			if (HG_has_one_or_more_host($hg_id)){
	
				$hg_open = $hg_checked = "";
				if (isset($hgs_selected[$hg_id]))
					$hg_checked = " checked='1' ";
				if (isset($hgs_open[$hg_id]))
					$hg_open = " open='1' ";
	    		print("<item ".$hg_open." ".$hg_checked." child='1' id='HG_".$hg_id."' text='".$hg_name."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
	
				/*
				 * Hosts
				 */
				if ($hg_open){
					$hosts = getMyHostGroupHosts($hg_id);
					foreach ($hosts as $host_id => $host_name){
						$host_checked = "";
						$host_open = "";
						if (isset($hosts_selected[$host_id]))
							$host_checked = " checked='1' ";
						if (isset($hosts_open[$host_id]))
							$host_open = " open='1' ";
		        		print("<item  ".$host_open." ".$host_checked." child='1' id='HH_".$host_id."_".$hg_id."' text='".getMyHostName($host_id)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
	
						/*
						 * Services
						 */
						if($host_open){
							$services = getMyHostServices($host_id);
							foreach ($services as $svc_id => $svc_name)	{
								$svc_checked = "";
								if (isset($svcs_selected[$svc_id]))
									$svc_checked = " checked='1' ";
					        	print("<item ".$svc_checked." child='0' id='HS_".$svc_id."_".$host_id."_".$hg_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>");			
							}
						}
						print("</item>");
					}
				}
				print("</item>");
			}
		}
		print("<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");
		print("</item>");
	}
	print("</tree>");
?>