<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL
 * SVN : $Id: GetXmlTree.php 7140 2008-11-24 19:48:50Z jmathis $
 * 
 */
 
	$debugXML = 0;
	$buffer = '';

	/* 
	 * pearDB init 
	 */
	require_once 'DB.php';

	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/DBconnect.php";
	include_once $centreon_path . "www/DBOdsConnect.php";
	include_once $centreon_path . "www/DBNDOConnect.php";

	/* 
	 * PHP functions 
	 */
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

	function getServiceGroupCount()	{
		global $pearDB;

		$DBRESULT =& $pearDB->query("SELECT count(sg_id) FROM `servicegroup`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$num_row =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		return $num_row["count(sg_id)"];
	}


	/* 
	 * if debug == 0 => Normal, 
	 * debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml) 
	 */
	
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
					print("<item child='1' id='HH_".$host."_".$id."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>");
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host]))
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
				print("<item child='0' id='HS_".$tab_value[1]."_".$tab_value[0]."' text='".$host_name." - ".$service_description."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif' ></item>");					
			}
		} else if ($type == "HH") {
			/*
			 * get services for host
			 */
			$tab_id = split('_', $id);
			$id = $tab_id[0];
			$services = getMyHostActiveServices($id);
			foreach ($services as $svc_id => $svc_name) {
				if ($is_admin || (!$is_admin && isset($lca["LcaHost"][$id]) && isset($lca["LcaHost"][$id][$svc_id])))
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
				if ($is_admin){
					$hostaloneSTR2 .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>\n";
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]]))
					 	$hostaloneSTR2 .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>\n";	
				}
			}
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
					print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
				} else {
					if (isset($lca["LcaSG"]) && isset($lca["LcaSG"][$SG["sg_id"]]))
			        	print("<item child='1' id='ST_".$SG["sg_id"]."' text='".$SG["sg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
				}
			}
			$DBRESULT->free();
		} else if ($type == "RR") {
			$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation ".$access->queryBuilder("WHERE", "host_host_id", $hoststr).") ORDER BY `hg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($HG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin){
					print("<item child='1' test='$is_admin' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");					
				} else {
					if (isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_id"]])){
			        	print("<item child='1' test='$is_admin' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' ></item>");
					}					
				}
			}
		
			/*
			 * Hosts Alone
			 */
			
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (SELECT host_host_id FROM hostgroup_relation) AND host_register = '1' order by host_name");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			$cpt = 0;
			$hostaloneSTR2 = "";
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
				if ($is_admin){
		           	$hostaloneSTR2 .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>\n";
					$cpt++;
				} else {
					if (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_id"]])){
						$hostaloneSTR2 .= "<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>\n";	
						$cpt++;
					}
				}
			}
			if ($cpt){
				print "<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >";
				print $hostaloneSTR2;
				print("</item>");
			}
				
			/*
			 * Meta Services
			 */
			$str = "";
			$cpt = 0;
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM meta_service ORDER BY `meta_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($MS =& $DBRESULT->fetchRow()){
				$i++;
				$cpt++;
		        $str .= "<item child='0' id='MS_".$MS["meta_id"]."' text='".$MS["meta_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'></item>";
			}
			if ($cpt) {
				print("<item child='1' id='MT_0' text='Meta services' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");	
				print $str;
				print("</item>");
			}
		} else {
			/*
			 * Display HG
			 */
			print("<item nocheckbox='1' open='1' call='1' select='1' child='1' id='RR_0' text='HostGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
			print("<itemtext>label</itemtext>");
			print("</item>");
			
			/*
			 * Display SG
			 */
			if (getServiceGroupCount()) {
				print("<item nocheckbox='1' open='1' call='1' select='1' child='1' id='RS_0' text='ServiceGroups' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
				print("<itemtext>label</itemtext>");
				print("</item>");
			}
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
				$services = getMyHostActiveServices($id);
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
				// svc
				$svcs_selected[$id] = getMyServiceName($id);
				$svcs_selected[$id] = getMyServiceName($id);
	
				//host_parent
				if (isset($id_full[1])) {
					$host_id = $id_full[1];
					$hosts_open[$host_id] = getMyHostName($host_id);
				} else {
					$host_id = getMyHostServiceID($id);
					$hosts_open[$host_id] = getMyHostName($host_id);				
				}

				// 	hg_parent
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
	
					/* + all svc*/
					$services = getMyHostActiveServices($host_id);
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
							foreach($services as $svc_id => $svc_name)	{//$tab_id = split(",",$openid);
								$svc_checked = "";
								if (isset($svcs_selected[$svc_id]))
									$svc_checked = " checked='1' ";
					        	print("<item ".$svc_checked."  child='0' id='HS_".$svc_id."_".$host_id."_".$hg_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'></item>");			
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