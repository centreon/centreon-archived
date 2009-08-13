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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
	$debugXML = 0;
	$buffer = '';

	/* 
	 * pearDB init 
	 */	
	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");

	/* 
	 * PHP functions 
	 */
	include_once $centreon_path . "www/include/common/common-Func.php";
	
	require_once ($centreon_path . "www/class/Session.class.php");
	require_once ($centreon_path . "www/class/Oreon.class.php");
	
	Session::start();
	$oreon =& $_SESSION["oreon"];
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", $centreon_path . "/www/locale/");
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
	
	/*
	 * Include Access Class
	 */
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	
	
	if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) { 	
		header("Content-type: application/xhtml+xml"); 
	} else {
		header("Content-type: text/xml"); 
	} 
	 
	function getServiceGroupCount($search = NULL)	{
		global $pearDB;

		if ($search != "")
			$DBRESULT =& $pearDB->query("SELECT count(sg_id) FROM `servicegroup` WHERE sg_name LIKE '%$search%'");
		else
			$DBRESULT =& $pearDB->query("SELECT count(sg_id) FROM `servicegroup`");
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
	(isset($_GET["search_host"])) ? $search_host = $_GET["search_host"] : $search_host = "";

	$type = "root";
	$id = "0";
	if (strlen($url_var) > 1){
		$id = "42";
		$type = substr($url_var, 0, 2);
		$id = substr($url_var, 3, strlen($url_var));
	}
	
	$buffer = new CentreonXML();
	if ($normal_mode){
		$i = 0;
		$buffer->startElement("tree");
		$buffer->writeAttribute("id", $url_var);			
		if ($type == "HG") {
			/*
			 * Get Hosts
			 */
			$hosts = getMyHostGroupHosts($id, $search_host);
			foreach ($hosts as $host){
				if ($is_admin || ((isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host])))){
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("id", "HH_".$host."_".$id);
					$buffer->writeAttribute("text", getMyHostName($host));
					$buffer->writeAttribute("im0", "../16x16/server_network.gif");
					$buffer->writeAttribute("im1", "../16x16/server_network.gif");
					$buffer->writeAttribute("im2", "../16x16/server_network.gif");
					$buffer->endElement();					
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
				$buffer->startElement("item");
				$buffer->writeAttribute("child", "0");
				$buffer->writeAttribute("id", "HS_".$tab_value[1]."_".$tab_value[0]);
				$buffer->writeAttribute("text", $host_name." - ".$service_description);
				$buffer->writeAttribute("im0", "../16x16/gear.gif");
				$buffer->writeAttribute("im1", "../16x16/gear.gif");
				$buffer->writeAttribute("im2", "../16x16/gear.gif");
				$buffer->endElement();
			}
		} else if ($type == "HH") {
			/*
			 * get services for host
			 */
			$tab_id = split('_', $id);
			$id = $tab_id[0];
			$services = getMyHostActiveServices($id, htmlentities($_GET["search_service"], ENT_QUOTES));
			foreach ($services as $svc_id => $svc_name) {
				if ($is_admin || (isset($lca["LcaHost"][$id]) && isset($lca["LcaHost"][$id][$svc_id]))) {
			    	$buffer->startElement("item");
					$buffer->writeAttribute("child", "0");
					$buffer->writeAttribute("id", "HS_".$svc_id."_".$id);
					$buffer->writeAttribute("text", $svc_name);
					$buffer->writeAttribute("im0", "../16x16/gear.gif");
					$buffer->writeAttribute("im1", "../16x16/gear.gif");
					$buffer->writeAttribute("im2", "../16x16/gear.gif");
					$buffer->endElement();			    	
				}			
			}
		} else if ($type == "HS") {	
			;
		} else if ($type == "HO") {
			$searchSTR = "";
			if ($search_host != "")
				$searchSTR = " AND (`host_name` LIKE '%$search_host%' OR `host_alias` LIKE '%$search_host%') ";
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' $searchSTR ORDER BY host_name");
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
				if ($is_admin || ((isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]])))){
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("id", "HO_0");
					$buffer->writeAttribute("text", _("Orphan hosts"));
					$buffer->writeAttribute("im0", "../16x16/clients.gif");				
					$buffer->writeAttribute("im1", "../16x16/clients.gif");
					$buffer->writeAttribute("im2", "../16x16/clients.gif");
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("id", "HH_".$host["host_id"]);
					$buffer->writeAttribute("text", $host["host_name"]);
					$buffer->writeAttribute("im0", "../16x16/server_network.gif");
					$buffer->writeAttribute("im1", "../16x16/server_network.gif");
					$buffer->writeAttribute("im2", "../16x16/server_network.gif");
					$buffer->endElement();
					$buffer->endElement();
				}
			}
		} else if ($type == "RS") {
			/*
			 * Send Service Group list
			 */
			$lcaSG = $access->getServiceGroups();
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
			while ($SG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin || (isset($lca["LcaSG"]) && isset($lca["LcaSG"][$SG["sg_id"]]))){ 					
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("text", $SG["sg_name"]);
					$buffer->writeAttribute("id", "ST_".$SG["sg_id"]);
					$buffer->writeAttribute("im0", "../16x16/clients.gif");
					$buffer->writeAttribute("im1", "../16x16/clients.gif");
					$buffer->writeAttribute("im2", "../16x16/clients.gif");
					$buffer->endElement();					
				}
			}
			$DBRESULT->free();
		} else if ($type == "RR") {
			
			/*
			 * Request build
			 */
			if ($search_host != "")
				$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation, host WHERE hostgroup_relation.host_host_id = host.host_id AND (host.host_name LIKE '%$search_host%' OR `host_alias` LIKE '%$search_host%') ".$access->queryBuilder("AND", "host_host_id", $hoststr).") ORDER BY `hg_name`");			
			else
				$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation ".$access->queryBuilder("WHERE", "host_host_id", $hoststr).") ORDER BY `hg_name`");
			
			while ($HG =& $DBRESULT->fetchRow()){
			    $i++;
				if ($is_admin || (isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_id"]]))){
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("test", $is_admin);
					$buffer->writeAttribute("text", $HG["hg_name"]);
					$buffer->writeAttribute("id", "HG_".$HG["hg_id"]);
					$buffer->writeAttribute("im0", "../16x16/clients.gif");
					$buffer->writeAttribute("im1", "../16x16/clients.gif");
					$buffer->writeAttribute("im2", "../16x16/clients.gif");
					$buffer->endElement();					
				} 
			}
		
			/*
			 * Hosts Alone
			 */
			$searchSTR = "";
			if ($search_host != "")
				$searchSTR = " AND (`host_name` LIKE '%$search_host%' OR `host_alias` LIKE '%$search_host%') ";
			
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (SELECT host_host_id FROM hostgroup_relation) AND host_register = '1' $searchSTR ORDER BY host_name");
			$cpt = 0;
			$hostaloneSTR2 = "";
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
				if ($is_admin || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_id"]]))){
		           	if (!$cpt) {
		           		$buffer->startElement("item");
						$buffer->writeAttribute("child", "1");
						$buffer->writeAttribute("id", "HO_0");
						$buffer->writeAttribute("text", _("Orphan hosts"));
						$buffer->writeAttribute("im0", "../16x16/clients.gif");				
						$buffer->writeAttribute("im1", "../16x16/clients.gif");
						$buffer->writeAttribute("im2", "../16x16/clients.gif");
		           	}
		           	$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("id", "HH_".$host["host_id"]);
					$buffer->writeAttribute("text", $host["host_name"]);
					$buffer->writeAttribute("im0", "../16x16/server_network.gif");
					$buffer->writeAttribute("im1", "../16x16/server_network.gif");
					$buffer->writeAttribute("im2", "../16x16/server_network.gif");
					$buffer->endElement();					
					$cpt++;
				}				
			}
			if ($cpt)
				$buffer->endElement();		
				
			/*
			 * Meta Services
			 */
			$str = "";
			$cpt = 0;
			
			/*
			 * Request build
			 */
			if ($is_admin) {
				if ($search_host != "") 
					$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE `meta_name` LIKE '%$search_host%' ORDER BY `meta_name`");
				else
					$DBRESULT =& $pearDB->query("SELECT * FROM meta_service ORDER BY `meta_name`");
			} else {
				if ($search_host != "") 
					$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE `meta_name` LIKE '%$search_host%' AND `meta_id` IN (".$access->getMetaServiceString().") ORDER BY `meta_name`");
				else
					$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE `meta_id` IN (".$access->getMetaServiceString().") ORDER BY `meta_name`");
			}
			while ($MS =& $DBRESULT->fetchRow()){
				if (!$cpt) {
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					$buffer->writeAttribute("id", "MT_0");
					$buffer->writeAttribute("text", _("Meta services"));
					$buffer->writeAttribute("im0", "../16x16/server_network.gif");
					$buffer->writeAttribute("im1", "../16x16/server_network.gif");
					$buffer->writeAttribute("im2", "../16x16/server_network.gif");									
				}
				$i++;				
		        $buffer->startElement("item");
				$buffer->writeAttribute("child", "0");
				$buffer->writeAttribute("id", "MS_".$MS["meta_id"]);
				$buffer->writeAttribute("text", $MS["meta_name"]);
				$buffer->writeAttribute("im0", "../16x16/server_network.gif");
				$buffer->writeAttribute("im1", "../16x16/server_network.gif");
				$buffer->writeAttribute("im2", "../16x16/server_network.gif");	
		        $buffer->endElement();
		       	$cpt++;
			}
			if ($cpt)
				$buffer->endElement();						
		} else {
			/*
			 * Display HG
			 */
			$buffer->startElement("item");
			$buffer->writeAttribute("nocheckbox", "1");
			$buffer->writeAttribute("open", "1");
			$buffer->writeAttribute("call", "1");
			$buffer->writeAttribute("select", "1");
			$buffer->writeAttribute("child", "1");
			$buffer->writeAttribute("id", "RR_0");
			$buffer->writeAttribute("text", _("Host Groups"));
			$buffer->writeAttribute("im0", "../16x16/clients.gif");
			$buffer->writeAttribute("im1", "../16x16/clients.gif");
			$buffer->writeAttribute("im2", "../16x16/clients.gif");	
			$buffer->writeElement("itemtext", "label");		
			$buffer->endElement();			
			
			/*
			 * Display SG
			 */
			if (getServiceGroupCount($search_host)) {
				$buffer->startElement("item");
				$buffer->writeAttribute("nocheckbox", "1");
				$buffer->writeAttribute("open", "1");
				$buffer->writeAttribute("call", "1");
				$buffer->writeAttribute("select", "1");
				$buffer->writeAttribute("child", "1");
				$buffer->writeAttribute("id", "RS_0");
				$buffer->writeAttribute("text", _("Service Groups"));
				$buffer->writeAttribute("im0", "../16x16/clients.gif");
				$buffer->writeAttribute("im1", "../16x16/clients.gif");
				$buffer->writeAttribute("im2", "../16x16/clients.gif");	
				$buffer->writeElement("itemtext", "label");		
				$buffer->endElement();				
				$buffer->writeElement("itemtext", "label");	
				$buffer->endElement();				
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
		
		$buffer->startElement("tree");
		$buffer->writeAttribute("id", "1");		
		
		$tab_id = split(",",$url_var);
		foreach ($tab_id as $openid) {
			$type = substr($openid, 0, 2);
			$id = substr($openid, 3, strlen($openid));
			
			$id_full = split('_', $id);
			$id = $id_full[0];
			
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
			} else if ($type == "HS"){ // svc + host_parent + hg_parent
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
					if (isset($hgs)) {
						foreach($hgs as $hg_id => $hg_name)
							$hgs_open[$hg_id] = $hg_name;
					}
				}			
			} else if ($type == "HG"){ // HG + hostS_child + svcS_child
				
				$hgs_selected[$id] = getMyHostGroupName($id);
				$hgs_open[$id] = getMyHostGroupName($id);
	
				$hosts = getMyHostGroupHosts($id);
				foreach ($hosts as $host_id) {
					$host_name = getMyHostName($host_id);
					$hosts_open[$host_id] = $host_name;
					$hosts_selected[$host_id] = $host_name;
	
					/* + all svc*/
					$services = getMyHostActiveServices($host_id);
					foreach($services as $svc_id => $svc_name)
						$svcs_selected[$svc_id] = $svc_name;
				}
			} else if ($type == "MS"){ // Meta Services
				/*
				 * Init Table
				 */
				$meta_checked[$id] = $id;				
			}
		}
		
		$buffer->startElement("item");
		$buffer->writeAttribute("nocheckbox", "1");
		$buffer->writeAttribute("open", "1");
		$buffer->writeAttribute("call", "1");
		$buffer->writeAttribute("select", "1");
		$buffer->writeAttribute("child", "1");
		$buffer->writeAttribute("id", "RR_0");
		$buffer->writeAttribute("text", _("Host Groups"));
		$buffer->writeAttribute("im0", "../16x16/clients.gif");
		$buffer->writeAttribute("im1", "../16x16/clients.gif");
		$buffer->writeAttribute("im2", "../16x16/clients.gif");
		
		$hostgroups = getAllHostgroups();
		foreach ($hostgroups as $hg_id => $hg_name){
			/*
			 * Hostgroups
			 */
			
			if (HG_has_one_or_more_host($hg_id)) {
	    		$buffer->startElement("item");
	    		if (isset($hgs_open[$hg_id]))
	    			$buffer->writeAttribute("open", "1");
	    		if (isset($hgs_selected[$hg_id]))
	    			$buffer->writeAttribute("checked", "1");
	    		$buffer->writeAttribute("child", "1");
	    		$buffer->writeAttribute("nocheckbox", "1");
	    		$buffer->writeAttribute("id", "HG_".$hg_id);
	    		$buffer->writeAttribute("text", $hg_name);
	    		$buffer->writeAttribute("im0", "../16x16/clients.gif");
	    		$buffer->writeAttribute("im1", "../16x16/clients.gif");
	    		$buffer->writeAttribute("im2", "../16x16/clients.gif");	    		
	
				/*
				 * Hosts
				 */
				if (isset($hgs_open) && isset($hgs_open[$hg_id]) && $hgs_open[$hg_id]) {
					$hosts = getMyHostGroupHosts($hg_id);
					foreach ($hosts as $host_id => $host_name){
						
						$buffer->startElement("item");
			    		
			    		if (isset($hosts_open[$host_id]))
			    			$buffer->writeAttribute("open", "1");
			    		
			    		if (isset($hosts_selected[$host_id]))
			    			$buffer->writeAttribute("checked", "1");
			    		
			    		$buffer->writeAttribute("child", "1");
			    		$buffer->writeAttribute("id", "HH_".$host_id."_".$hg_id);
			    		$buffer->writeAttribute("text", getMyHostName($host_id));
			    		$buffer->writeAttribute("im0", "../16x16/server_network.gif");
			    		$buffer->writeAttribute("im1", "../16x16/server_network.gif");
			    		$buffer->writeAttribute("im2", "../16x16/server_network.gif");		        		
	
						/*
						 * Services
						 */
						if ((isset($hosts_open[$host_id]) && $hosts_open[$host_id]) || (isset($hosts_selected[$host_id]) && $hosts_selected[$host_id]) ) {
							$services = getMyHostServices($host_id);
							foreach($services as $svc_id => $svc_name)	{
					           	$buffer->startElement("item");					    		
					    		if (isset($svcs_selected[$svc_id]))
					    			$buffer->writeAttribute("checked", "1");
					    		$buffer->writeAttribute("child", "0");
					    		$buffer->writeAttribute("id", "HS_".$svc_id."_".$host_id."_".$hg_id);
					    		$buffer->writeAttribute("text", $svc_name);
					    		$buffer->writeAttribute("im0", "../16x16/gear.gif");
					    		$buffer->writeAttribute("im1", "../16x16/gear.gif");
					    		$buffer->writeAttribute("im2", "../16x16/gear.gif");
					        	$buffer->endElement();
							}
						}
						$buffer->endElement();						
					}
				}
				$buffer->endElement();
			}
		}
		
		/*
		 * Orphan Hosts
		 */
		$buffer->startElement("item");
		$buffer->writeAttribute("child", "1");
		$buffer->writeAttribute("id", "HO_0");
		$buffer->writeAttribute("nocheckbox", "1");
		$buffer->writeAttribute("text", _("Orphan Hosts"));
		$buffer->writeAttribute("im0", "../16x16/server_network.gif");
		$buffer->writeAttribute("im1", "../16x16/server_network.gif");
		$buffer->writeAttribute("im2", "../16x16/server_network.gif");
		$buffer->endElement();
		
		/*
		 * Meta Services
		 */
		$buffer->startElement("item");
		$buffer->writeAttribute("child", "1");
		if (isset($meta_checked))
			$buffer->writeAttribute("open", "1");
		$buffer->writeAttribute("id", "MT_0");
		$buffer->writeAttribute("text", _("Meta Services"));
		$buffer->writeAttribute("nocheckbox", "1");
		$buffer->writeAttribute("im0", "../16x16/server_network.gif");
		$buffer->writeAttribute("im1", "../16x16/server_network.gif");
		$buffer->writeAttribute("im2", "../16x16/server_network.gif");
		
		if (isset($meta_checked) && count($meta_checked)) {
			if ($is_admin)
				$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE `meta_activate` = '1' ORDER BY `meta_name`");
			else
				$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE `meta_activate` = '1' AND `meta_id` IN (".$access->getMetaServiceString().") ORDER BY `meta_name`");
			while ($MS =& $DBRESULT->fetchRow()){
				$buffer->startElement("item");
				$buffer->writeAttribute("child", "0");
				if (isset($meta_checked[$MS["meta_id"]]))
					$buffer->writeAttribute("checked", "1");
				$buffer->writeAttribute("id", "MS_".$MS["meta_id"]);
				$buffer->writeAttribute("text", $MS["meta_name"]);
				$buffer->writeAttribute("im0", "../16x16/server_network.gif");
				$buffer->writeAttribute("im1", "../16x16/server_network.gif");
				$buffer->writeAttribute("im2", "../16x16/server_network.gif");
				$buffer->endElement();				
			}
			$DBRESULT->free();
			unset($MS);
		}		
		$buffer->endElement();
		
		$buffer->endElement();
		
		/*
		 * Display SG
		 */
		$buffer->startElement("item");
		$buffer->writeAttribute("nocheckbox", "1");
		$buffer->writeAttribute("open", "1");
		$buffer->writeAttribute("call", "1");
		$buffer->writeAttribute("select", "1");
		$buffer->writeAttribute("child", "1");
		$buffer->writeAttribute("id", "RS_0");
		$buffer->writeAttribute("text", _("Service Groups"));
		$buffer->writeAttribute("im0", "../16x16/clients.gif");
		$buffer->writeAttribute("im1", "../16x16/clients.gif");
		$buffer->writeAttribute("im2", "../16x16/clients.gif");	
		$buffer->writeElement("itemtext", "label");		
		$buffer->endElement();
		$buffer->endElement();
	}
	$buffer->endElement();
	$buffer->output();
?>