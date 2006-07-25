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

For information : contact@oreon.org
*/

	if (!isset($oreon))
		exit();
	
	$hg = array();
	$status_hg = array();
		
	$ret =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
	if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	while ($r =& $ret->fetchRow()){
		$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"], "host" => array());
		$ret_h =& $pearDB->query(	"SELECT host_host_id, host_name, host_alias FROM hostgroup_relation,host,hostgroup ".
									"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
									"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$cpt = 0;
		
		while ($r_h =& $ret_h->fetchRow()){
			$status_hg["OK"] = 0;
			$status_hg["PENDING"] = 0;
			$status_hg["WARNING"] = 0;
			$status_hg["CRITICAL"] = 0;
			$status_hg["UNKNOWN"] = 0;
			
			$hg[$r["hg_name"]]["host"][$cpt] = $r_h["host_name"];
			$service_data_str = NULL;	
			
			$host_data_str = "<a href='./oreon.php?p=201&o=hd&host_name=".$r_h["host_name"]."'>" . $r_h["host_name"] . "</a> (" . $r_h["host_alias"] . ")";
			if(isset($tab_host_service[$r_h["host_name"]]))
			{
					foreach ($tab_host_service[$r_h["host_name"]] as $key => $value)
						$status_hg[$service_status[$r_h["host_name"]. "_" .$key]["status"]]++;
					
					$service_data_str = "";
					if ($status_hg["OK"] != 0)
						$service_data_str = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg["OK"] . " <a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."&status=OK'>OK</a></span> ";
					if ($status_hg["WARNING"] != 0)
						$service_data_str .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg["WARNING"] . " <a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."&status=WARNING'>WARNING</a></span> ";
					if ($status_hg["CRITICAL"] != 0)
						$service_data_str .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg["CRITICAL"] . " <a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."&status=CRITICAL'>CRITICAL</a></span> ";
					if ($status_hg["PENDING"] != 0)
						$service_data_str .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg["PENDING"] . " <a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."&status=PENDING'>PENDING</a></span> ";
					if ($status_hg["UNKNOWN"] != 0)
						$service_data_str .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg["UNKNOWN"] . " <a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."&status=UNKNOWN'>UNKNOWN</a></span> ";
					
					$h_data[$r["hg_name"]][$r_h["host_name"]] = $host_data_str;
					$status = "color_".strtolower($host_status[$r_h["host_name"]]["status"]);
					$h_status_data[$r["hg_name"]][$r_h["host_name"]] = "<td class='ListColCenter' style='background:".$oreon->optGen[$status]."'><a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."'>".$host_status[$r_h["host_name"]]["status"]."</a></td>";
					$svc_data[$r["hg_name"]][$r_h["host_name"]] = $service_data_str;
					$cpt++;
			}
		}
	}
	
	
	if ($debug){
		print "<textarea rows='20' cols='100'>";
		print_r($status_hg);
		print "</textarea>";
		print "<textarea rows='20' cols='100'>";
		print_r($status_hg_h);
		print "</textarea>";
	}
		
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);	
	$tpl->assign("p", $p);
	//$tpl->assign("view", $lang["m_mon_view"]);
	$tpl->assign("hostgroup", $hg);
	if (isset($h_data))
		$tpl->assign("h_data", $h_data);
	if (isset($h_status_data))
		$tpl->assign("h_status_data", $h_status_data);
	if (isset($svc_data))
		$tpl->assign("svc_data", $svc_data);
	$tpl->assign("lang", $lang);
	$tpl->display("serviceSummary.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");
?>