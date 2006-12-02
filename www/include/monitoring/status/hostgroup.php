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

	if (!isset($oreon))
		exit();
	
	$TabLca = getLcaHostByID($pearDB);
	$isRestreint = hadUserLca($pearDB);
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
		while ($DBRESULT1->fetchInto($r_h)){
			if (isset($tab_host_service[$r_h["host_name"]]))
				foreach ($tab_host_service[$r_h["host_name"]] as $key => $value){
					$status_hg_h[$host_status[$r_h["host_name"]]["current_state"]]++;	
					$status_hg[$service_status[$r_h["host_name"]. "_" .$key]["current_state"]]++;					
				}						
			$cpt_host++;
		}
		$service_data_str = NULL;	
		$h_data_str = NULL;	
		if ($status_hg["OK"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg["OK"] . " OK</span> ";
		if ($status_hg["WARNING"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg["WARNING"] . " WARNING</span> ";
		if ($status_hg["CRITICAL"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg["CRITICAL"] . " CRITICAL</span> ";
		if ($status_hg["PENDING"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg["PENDING"] . " PENDING</span> ";
		if ($status_hg["UNKNOWN"] != 0)
			$service_data_str .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg["UNKNOWN"] . " UNKNOWN</span> ";
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
	$tpl->assign("hostgroup", $hg);
	if (isset($data))
		$tpl->assign("data", $data);
	$tpl->assign("lang", $lang);
	$tpl->display("hostgroup.ihtml");
?>