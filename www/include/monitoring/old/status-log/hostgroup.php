<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
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

	if (!isset($oreon))
		exit();
	
	$TabLca = getLcaHostByID($pearDB);
	$LcaHGStr = getLCAHostStr($TabLca["LcaHostGroup"]);
	
	$data = array();
	$hg = array();
	$status_hg = array();
			
	if ($oreon->user->admin || !$isRestreint)
		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
	else
		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' AND hg_id IN (".$LcaHGStr.") ORDER BY hg_name");
	if (PEAR::isError($DBRESULT)) 
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($r =& $DBRESULT->fetchRow()){	
		$status_hg = array("OK" => 0, "PENDING" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0);	
		$status_hg_h = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0);	
		$DBRESULT1 =& $pearDB->query(	"SELECT host_host_id, host_name, host_alias FROM hostgroup_relation,host,hostgroup ".
									"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
									"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
		if (PEAR::isError($DBRESULT1)) 
			print "Mysql Error : ".$DBRESULT1->getMessage();
		$cpt_host = 0;
				
		while ($r_h =& $DBRESULT1->fetchRow()){
			if (isset($tab_host_service[$r_h["host_name"]])) {
				$status_hg_h[$host_status[$r_h["host_name"]]["current_state"]]++;
				foreach ($tab_host_service[$r_h["host_name"]] as $key => $value)
					$status_hg[$service_status[$r_h["host_name"]. "_" .$key]["current_state"]]++;					
			}				
			$cpt_host++;
		}
	
		$service_data_str = NULL;	
		$h_data_str = NULL;	
		if ($status_hg["OK"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_ok"]."'><a href='./main.php?p=2020101&o=svc_ok&hg_name=".$r["hg_name"]."'>" . $status_hg["OK"] . " OK</a></span> ";
		if ($status_hg["WARNING"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_warning"]."'><a href='./main.php?p=2020101&o=svc_warning&hg_name=".$r["hg_name"]."'>" . $status_hg["WARNING"] . " WARNING</a></span> ";
		if ($status_hg["CRITICAL"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_critical"]."'><a href='./main.php?p=2020101&o=svc_critical&hg_name=".$r["hg_name"]."'>" . $status_hg["CRITICAL"] . " CRITICAL</a></span> ";
		if ($status_hg["PENDING"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg["PENDING"] . " PENDING</span> ";
		if ($status_hg["UNKNOWN"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_unknown"]."'><a href='./main.php?p=2020101&o=svc_unknown&hg_name=".$r["hg_name"]."'>" . $status_hg["UNKNOWN"] . " UNKNOWN</a></span> ";
		if (!isset($hg[$r["hg_name"]]))
			$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"], "host" => array());			
		$data[$r["hg_name"]]["service"] = $service_data_str;
		$data[$r["hg_name"]]["name"] = $r["hg_name"];
		if ($status_hg_h["UP"] != 0)
			$h_data_str .= "<span style='background:".$oreon->optGen["color_up"]."'>" . $status_hg_h["UP"] . " UP</span> ";
		if ($status_hg_h["DOWN"] != 0)
			$h_data_str .= "<span style='background:".$oreon->optGen["color_down"]."'>" . $status_hg_h["DOWN"] . " DOWN</span> ";
		if ($status_hg_h["UNREACHABLE"] != 0)
			$h_data_str .= "<span style='background:".$oreon->optGen["color_unreachable"]."'>" . $status_hg_h["UNREACHABLE"] . " CRITICAL</span> ";
		if (!isset($hg[$r["hg_name"]]))
			$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"], "host" => array());	
		$data[$r["hg_name"]]["host"] = $h_data_str;
	}
		
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);	
	$tpl->assign("p", $p);
	
	$tpl->assign("mon_hostgroup", _("Hostgroup"));
	$tpl->assign("mon_host_stt_ttl", _("Host status"));
	$tpl->assign("mon_svc_stt_ttl", _("Service status"));
	
	$tpl->assign("hostgroup", $hg);
	if (isset($data))
		$tpl->assign("data", $data);
	
	$tpl->display("hostgroup.ihtml");
?>