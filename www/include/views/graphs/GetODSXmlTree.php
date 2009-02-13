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

	include_once "@CENTREON_ETC@/centreon.conf.php";	
	include_once $centreon_path . "www/class/centreonDB.class.php";
	
	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");
	$pearDBO = new CentreonDB("centstorage");
	
	/* PHP functions */
	
	include_once $centreon_path . "www/include/common/common-Func.php";

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
	$buffer = new CentreonXML();
	if ($normal_mode){
		$i = 0;
		$buffer->startElement("tree");
		$buffer->writeAttribute("id", $url_var);		
		
		if ($type == "HG") {
			/*
			 * Get Hosts
			 */
			$hosts = getMyHostGroupHosts($id);
			foreach ($hosts as $host){				
				if (host_has_one_or_more_GraphService($host)) {
					if ($is_admin || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host]))) { 
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
				if (checkIfServiceSgIsEn($tab_value[0], $tab_value[1])) {
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "0");
					$buffer->writeAttribute("id", "HS_".$tab_value[1]."_".$tab_value[0]);
					$buffer->writeAttribute("text", $host_name." - ".$service_description);
					$buffer->writeAttribute("im0", "../16x16/gear.gif");
					$buffer->writeAttribute("im1", "../16x16/gear.gif");
					$buffer->writeAttribute("im2", "../16x16/gear.gif");
					$buffer->endElement();
				}
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
				if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$id]) && isset($lca["LcaHost"][$id][$svc_id]))) {
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
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' order by host_name");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			while ($host =& $DBRESULT2->fetchRow()){
				$i++;
		        if ($is_admin || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]]))) {
			        $buffer->startElement("item");
			        $buffer->writeAttribute("child", "1");
			        $buffer->writeAttribute("id", "HH_".$host["host_id"]);
			        $buffer->writeAttribute("text", $host["host_name"]);
			        $buffer->writeAttribute("im0", "../16x16/server_network.gif");
			        $buffer->writeAttribute("im1", "../16x16/server_network.gif");
			        $buffer->writeAttribute("im2", "../16x16/server_network.gif");
			        $buffer->endElement();
		        }
			}
			$DBRESULT2->free();
		} else if ($type == "RS") {
			/*
			 * Send Service Group list
			 */
			$lcaSG = $access->getServiceGroups();
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($SG =& $DBRESULT->fetchRow()){
			    $i++;
				if (SGIsNotEmpty($SG["sg_id"])) {
			        if ($is_admin || (isset($lcaSG) && isset($lcaSG[$SG["sg_id"]]))) {
			        	$buffer->startElement("item");
				        $buffer->writeAttribute("child", "1");
				        $buffer->writeAttribute("id", "ST_".$SG["sg_id"]);
				        $buffer->writeAttribute("text", $SG["sg_name"]);
				        $buffer->writeAttribute("im0", "../16x16/clients.gif");
				        $buffer->writeAttribute("im1", "../16x16/clients.gif");
				        $buffer->writeAttribute("im2", "../16x16/clients.gif");
				        $buffer->endElement();
					}
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
				$buffer->startElement("item");
				$buffer->writeAttribute("child", "0");
				$buffer->writeAttribute("id", "MS_".$MS["meta_id"]);
				$buffer->writeAttribute("text", $MS["meta_name"]);
				$buffer->writeAttribute("im0", "../16x16/server_network.gif");
				$buffer->writeAttribute("im1", "../16x16/server_network.gif");
				$buffer->writeAttribute("im2", "../16x16/server_network.gif");
				$buffer->endElement();				
			}
			$DBRESULT->free();
		} else if ($type == "RR") {
			/*
			 * Send Host Group list
			 */
			$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM hostgroup WHERE hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation ".$access->queryBuilder("WHERE", "host_host_id", $hoststr).") ORDER BY `hg_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($HG =& $DBRESULT->fetchRow()) {
				$i++;				
				if (HG_has_one_or_more_host($HG["hg_id"])){
			       	if ($is_admin || (isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$HG["hg_id"]]))) {
				       	$buffer->startElement("item");
						$buffer->writeAttribute("child", "1");
						$buffer->writeAttribute("id", "HG_".$HG["hg_id"]);
						$buffer->writeAttribute("text", $HG["hg_name"]);
						$buffer->writeAttribute("im0", "../16x16/clients.gif");
						$buffer->writeAttribute("im1", "../16x16/clients.gif");
						$buffer->writeAttribute("im2", "../16x16/clients.gif");						
						$buffer->endElement();
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
					if ($is_admin || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host["host_name"]]))){			           	
			           	if (!$cpt) {
			    			$buffer->startElement("item");
							$buffer->writeAttribute("child", "1");
							$buffer->writeAttribute("id", "HO_0");
							$buffer->writeAttribute("text", _("Orphan hosts"));
							$buffer->writeAttribute("im0", "../16x16/server_network.gif");
							$buffer->writeAttribute("im1", "../16x16/server_network.gif");
							$buffer->writeAttribute("im2", "../16x16/server_network.gif");       		
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
			}
			if ($cpt)
				$buffer->endElement();
			$DBRESULT2->free();			
			
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
				$buffer->startElement("item");
				$buffer->writeAttribute("child", "1");
				$buffer->writeAttribute("id", "MT_0");
				$buffer->writeAttribute("text", _("Meta services"));
				$buffer->writeAttribute("im0", "../16x16/server_network.gif");
				$buffer->writeAttribute("im1", "../16x16/server_network.gif");
				$buffer->writeAttribute("im2", "../16x16/server_network.gif");
				$buffer->text($str);
				$buffer->endElement();				
			}
			
		} else {
			$buffer->startElement("item");
			$buffer->writeAttribute("nocheckbox", "1");
			$buffer->writeAttribute("open", "1");
			$buffer->writeAttribute("call", "1");
			$buffer->writeAttribute("select", "1");
			$buffer->writeAttribute("child", "1");
			$buffer->writeAttribute("id", "RR_0");
			$buffer->writeAttribute("text", _("HostGroups"));
			$buffer->writeAttribute("im0", "../16x16/clients.gif");
			$buffer->writeAttribute("im1", "../16x16/clients.gif");
			$buffer->writeAttribute("im2", "../16x16/clients.gif");
			$buffer->writeElement("itemtext", "label");
			$buffer->endElement();
			$buffer->startElement("item");
			$buffer->writeAttribute("nocheckbox", "1");
			$buffer->writeAttribute("open", "1");
			$buffer->writeAttribute("call", "1");
			$buffer->writeAttribute("id", "RS_0");
			$buffer->writeAttribute("select", "1");
			$buffer->writeAttribute("child", "1");
			$buffer->writeAttribute("text", _("ServiceGroups"));
			$buffer->writeAttribute("im0", "../16x16/clients.gif");
			$buffer->writeAttribute("im1", "../16x16/clients.gif");
			$buffer->writeAttribute("im2", "../16x16/clients.gif");
			$buffer->writeElement("itemtext", "label");
			$buffer->endElement();			
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
	    		if ($is_admin || isset($lca["LcaHostGroup"]) && isset($lca["LcaHostGroup"][$hg_name])){
		    		$buffer->startElement("item");
		    		$buffer->writeAttribute("child", "1");
		    		if (isset($hgs_selected[$hg_id]))
		    			$buffer->writeAttribute("checked", "1");
		    		if (isset($hgs_open[$hg_id]))
		    			$buffer->writeAttribute("open", "1");
		    		$buffer->writeAttribute("id", "HG_".$hg_id."_".$i);
		    		$buffer->writeAttribute("text", $hg_name);
		    		$buffer->writeAttribute("im0", "../16x16/clients.gif");
		    		$buffer->writeAttribute("im1", "../16x16/clients.gif");
		    		$buffer->writeAttribute("im2", "../16x16/clients.gif");		    		
					$hosts = getMyHostGroupHosts($hg_id);
					foreach ($hosts as $host_id => $host_name){
						$i++;
						$services = getMyHostActiveServices($host_id);
						$graphList = getMyHostGraphs($host_id);
						$host_name = getMyHostName($host_id);
						if (host_has_one_or_more_GraphService($host_id) && ($is_admin || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host_name])))) {
							$buffer->startElement("item");
							$buffer->writeAttribute("child", "1");
							$buffer->writeAttribute("call", "0");
							$buffer->writeAttribute("id", "HH_".$host_id."_".$i);
							$buffer->writeAttribute("text", $host_name);
							$buffer->writeAttribute("im0", "../16x16/server_network.gif");
							$buffer->writeAttribute("im1", "../16x16/server_network.gif");
							$buffer->writeAttribute("im2", "../16x16/server_network.gif");
						    foreach ($services as $svc_id => $svc_name){
								if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))){
							    	$checked = "";
									if (isset($selected[$svc_id."_".$host_id])) {										
										$checked = " open='1' checked='1' ";
									}
									if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))) {
								     	$buffer->startElement("item");
								     	$buffer->writeAttribute("child", "0");
								     	if (isset($selected[$svc_id."_".$host_id])) {
								     		$buffer->writeAttribute("open", "1");
								     		$buffer->writeAttribute("checked", "1");
								     	}
								     	$buffer->writeAttribute("call", "0");
								     	$buffer->writeAttribute("id", "HS_".$svc_id."_".$host_id);
								     	$buffer->writeAttribute("text", $svc_name);
								     	$buffer->writeAttribute("im0", "../16x16/gear.gif");
								     	$buffer->writeAttribute("im1", "../16x16/gear.gif");
								     	$buffer->writeAttribute("im2", "../16x16/gear.gif");
								        $buffer->endElement();
									}
								}
							}
							$buffer->endElement();							
						}
					}
					$buffer->endElement();					
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
		           	if (!$cpt) {
		           		$buffer->startElement("item");
		           		$buffer->writeAttribute("child", "1");
		           		$buffer->writeAttribute("id", "HO_0");
		           		$buffer->writeAttribute("text", _("Orphan hosts"));
		           		$buffer->writeAttribute("im0", "../16x16/server_network.gif");
		           		$buffer->writeAttribute("im1", "../16x16/server_network.gif");
		           		$buffer->writeAttribute("im2", "../16x16/server_network.gif");
		           	}		           	
					$buffer->startElement("item");
					$buffer->writeAttribute("child", "1");
					if (isset($selected_host[$host["host_id"]]))
						$buffer->writeAttribute("open", "1");
					$buffer->writeAttribute("call", "0");
					$buffer->writeAttribute("id", "HH_".$host["host_id"]);
					$buffer->writeAttribute("text", $host["host_name"]);
					$buffer->writeAttribute("im0", "../16x16/server_network.gif");
					$buffer->writeAttribute("im1", "../16x16/server_network.gif");
					$buffer->writeAttribute("im2", "../16x16/server_network.gif");
		           	
		           	$host_id = $host["host_id"];
		           	$services = getMyHostActiveServices($host_id);
					$graphList = getMyHostGraphs($host_id);
					$host_name = getMyHostName($host_id);
					if (host_has_one_or_more_GraphService($host_id) && (($is_admin) || (isset($lca["LcaHost"]) && isset($lca["LcaHost"][$host_name])))) {
						foreach ($services as $svc_id => $svc_name){
							if ((isset($graphList[$svc_id]) && $is_admin) || (!$is_admin && isset($graphList[$svc_id]) && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name]["svc"][$services[$svc_id]]))){						    	
								if (isset($graphList[$svc_id])) {
							        $buffer->startElement("item");
							        $buffer->writeAttribute("child", "0");
							        if (isset($selected[$svc_id."_".$host_id])) {
							        	$buffer->writeAttribute("open", "1");
							        	$buffer->writeAttribute("checked", "1");
							        }
							        $buffer->writeAttribute("call", "0");
							        $buffer->writeAttribute("id", "HS_".$svc_id."_".$host_id);
							        $buffer->writeAttribute("text", $svc_name);
							        $buffer->writeAttribute("im0", "../16x16/gear.gif");
							        $buffer->writeAttribute("im1", "../16x16/gear.gif");
							        $buffer->writeAttribute("im2", "../16x16/gear.gif");
							        $buffer->endElement();							        
								}			
							}
						}
					}
		           	$buffer->endElement();		           	
		           	$cpt++;
			}
		}
		if ($cpt)
			$buffer->endElement();
		$DBRESULT2->free();
		
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
			$buffer->startElement("item");
			$buffer->writeAttribute("child", "1");
			$buffer->writeAttribute("id", "MT_0");
			$buffer->writeAttribute("text", _("Meta services"));
			$buffer->writeAttribute("im0", "../16x16/server_network.gif");
			$buffer->writeAttribute("im1", "../16x16/server_network.gif");
			$buffer->writeAttribute("im2", "../16x16/server_network.gif");
			$buffer->text($str);
			$buffer->endElement();			
		}
		$buffer->endElement();
		$buffer->startElement("item");
		$buffer->writeAttribute("nocheckbox", "1");
		$buffer->writeAttribute("open", "1");
		$buffer->writeAttribute("call", "0");
		$buffer->writeAttribute("select", "1");
		$buffer->writeAttribute("child", "1");
		$buffer->writeAttribute("id", "RS_0");
		$buffer->writeAttribute("text", _("ServiceGroups"));
		$buffer->writeAttribute("im0", "../16x16/clients.gif");
		$buffer->writeAttribute("im1", "../16x16/clients.gif");
		$buffer->writeAttribute("im2", "../16x16/clients.gif");
		
		/*
		 * Send Service Group list
		 */
		$lcaSG = getLCASG($pearDB);
		$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM servicegroup ORDER BY `sg_name`");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($SG =& $DBRESULT->fetchRow()){
		    $i++;			
			if (SGIsNotEmpty($SG["sg_id"]) && (($is_admin) || ((isset($lcaSG) && isset($lcaSG[$SG["sg_id"]])))))
		       	$buffer->startElement("item");
		       	$buffer->writeAttribute("child", "1");
		       	$buffer->writeAttribute("id", "ST_".$SG["sg_id"]);
		       	$buffer->writeAttribute("text", $SG["sg_name"]);
		       	$buffer->writeAttribute("im0", "../16x16/clients.gif");
		       	$buffer->writeAttribute("im1", "../16x16/clients.gif");
		       	$buffer->writeAttribute("im2", "../16x16/clients.gif");
		       	$buffer->endElement();		       				
		}
		$DBRESULT->free();
		$buffer->writeElement("itemtext", "label");
		$buffer->endElement();		
	}
	$buffer->endElement();
	$buffer->output();	
?>