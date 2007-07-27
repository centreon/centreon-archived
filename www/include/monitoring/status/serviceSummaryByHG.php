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

	if (!isset($oreon))
		exit();
	
	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	unset($TabLca);	
	$TabLca = getLcaHostByName($pearDB);
	
	$hg = array();
	$status_hg = array();
	$h_class = array();
	$status_hg_ack = array();
	$tab_color = array(0=>"list_one", 1=>"list_two");
	
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
	if (PEAR::isError($DBRESULT)) 
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($r)){
		if ($oreon->user->admin || !hadUserLca($pearDB) || (hadUserLca($pearDB) && isset($TabLca["LcaHostGroup"][$r["hg_name"]]))){		
			$DBRESULT1 =& $pearDB->query(	"SELECT host_host_id, host_name, host_alias FROM hostgroup_relation,host,hostgroup ".
										"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
										"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
			if (PEAR::isError($DBRESULT1)) 
				print "Mysql Error : ".$DBRESULT1->getMessage();
			$cpt_host = 0;
			$counter_host = 0;	
			while ($DBRESULT1->fetchInto($r_h)){
				$status_hg = array("OK" => 0, "PENDING" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0);
				$status_hg_ack = array("OK" => 0, "PENDING" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0);
				$service_data_str = NULL;
				$service_data_str_ack = NULL;
				isset($host_status[$r_h["host_name"]]) && $host_status[$r_h["host_name"]]["current_state"] == "DOWN" ? $h_class[$r["hg_name"]][$r_h["host_name"]] = "list_down" : $h_class[$r["hg_name"]][$r_h["host_name"]] = $tab_color[++$counter_host % 2];
				if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($TabLca["LcaHost"][$r_h["host_name"]]))){
					if (isset($tab_host_service[$r_h["host_name"]])){
						foreach ($tab_host_service[$r_h["host_name"]] as $key => $value){
							if ((isset($_GET["problem"]) && $service_status[$r_h["host_name"]."_".$key]["current_state"] != "OK") 
							|| 	(!isset($_GET["problem"]) && !isset($_GET["acknowledge"])) 
							|| 	(!isset($_GET["problem"]) && isset($_GET["acknowledge"]) && $_GET["acknowledge"] == 1 && $service_status[$r_h["host_name"]."_".$key]["problem_has_been_acknowledged"] == 1)
							|| 	(!isset($_GET["problem"]) && isset($_GET["acknowledge"]) && $_GET["acknowledge"] == 0 && $service_status[$r_h["host_name"]."_".$key]["problem_has_been_acknowledged"] == 0 && $service_status[$r_h["host_name"]."_".$key]["current_state"] != "OK" )){
								if (isset($_GET["problem"])){
									if ($service_status[$r_h["host_name"]."_".$key]["problem_has_been_acknowledged"] == 1)
										$status_hg_ack[$service_status[$r_h["host_name"]. "_" .$key]["current_state"]]++;
									else
										$status_hg[$service_status[$r_h["host_name"]. "_" .$key]["current_state"]]++;	
								} else {
									$status_hg[$service_status[$r_h["host_name"]. "_" .$key]["current_state"]]++;					
								}
							}
						}
						$service_data_str = "";
						$service_data_str_ack = "";
						if (isset($_GET["problem"])){
							if (isset($status_hg["OK"]) && $status_hg["OK"] != 0)
								$service_data_str = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg["OK"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_ok'>OK</a></span> ";
							if (isset($status_hg["WARNING"]) && $status_hg["WARNING"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg["WARNING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_warning'>WARNING</a></span> ";
							if (isset($status_hg["CRITICAL"]) && $status_hg["CRITICAL"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg["CRITICAL"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_critical'>CRITICAL</a></span> ";
							if (isset($status_hg["PENDING"]) && $status_hg["PENDING"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg["PENDING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=PENDING'>PENDING</a></span> ";
							if (isset($status_hg["UNKNOWN"]) && $status_hg["UNKNOWN"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg["UNKNOWN"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_unknown'>UNKNOWN</a></span> ";
							
							if (isset($status_hg_ack["OK"]) && $status_hg_ack["OK"] != 0)
								$service_data_str_ack = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg_ack["OK"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_ok'>OK</a></span> ";
							if (isset($status_hg_ack["WARNING"]) && $status_hg_ack["WARNING"] != 0)
								$service_data_str_ack .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg_ack["WARNING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_warning'>WARNING</a></span> ";
							if (isset($status_hg_ack["CRITICAL"]) && $status_hg_ack["CRITICAL"] != 0)
								$service_data_str_ack .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg_ack["CRITICAL"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_critical'>CRITICAL</a></span> ";
							if (isset($status_hg_ack["PENDING"]) && $status_hg_ack["PENDING"] != 0)
								$service_data_str_ack .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg_ack["PENDING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=PENDING'>PENDING</a></span> ";
							if (isset($status_hg_ack["UNKNOWN"]) && $status_hg_ack["UNKNOWN"] != 0)
								$service_data_str_ack .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg_ack["UNKNOWN"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_unknown'>UNKNOWN</a></span> ";
							$svc_data[$r["hg_name"]][$r_h["host_name"]] = $service_data_str;
							$svc_data_ack[$r["hg_name"]][$r_h["host_name"]] = $service_data_str_ack;
						} else {
							
							if (isset($status_hg["OK"]) && $status_hg["OK"] != 0)
								$service_data_str = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg["OK"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_ok'>OK</a></span> ";
							if (isset($status_hg["WARNING"]) && $status_hg["WARNING"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg["WARNING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_warning'>WARNING</a></span> ";
							if (isset($status_hg["CRITICAL"]) && $status_hg["CRITICAL"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg["CRITICAL"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_critical'>CRITICAL</a></span> ";
							if (isset($status_hg["PENDING"]) && $status_hg["PENDING"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg["PENDING"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=PENDING'>PENDING</a></span> ";
							if (isset($status_hg["UNKNOWN"]) && $status_hg["UNKNOWN"] != 0)
								$service_data_str .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg["UNKNOWN"] . " <a href='./oreon.php?p=2020202&host_name=".$r_h["host_name"]."&o=svc_unknown'>UNKNOWN</a></span> ";
							$svc_data[$r["hg_name"]][$r_h["host_name"]] = $service_data_str;
						}
						if (!isset($hg[$r["hg_name"]]))
							$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"], "host" => array());
						$hg[$r["hg_name"]]["host"][$cpt_host] = $r_h["host_name"];
						$host_data_str = "<a href='./oreon.php?p=201&o=hd&host_name=".$r_h["host_name"]."'>" . $r_h["host_name"] . "</a>";
						$h_data[$r["hg_name"]][$r_h["host_name"]] = $host_data_str;
						$status = "color_".strtolower($host_status[$r_h["host_name"]]["current_state"]);
						$h_status_data[$r["hg_name"]][$r_h["host_name"]] = "<td class='ListColCenter' width='70' style='background:".$oreon->optGen[$status]."'><a href='./oreon.php?p=".$p."&host_name=".$r_h["host_name"]."'>".$host_status[$r_h["host_name"]]["current_state"]."</a></td>";
					}
				}
				$cpt_host++;
			}
		}
	}
	
	foreach ($hg as $key => $value){
		$flag = 0;
		foreach ($value['host'] as $h)
			if ((isset($svc_data[$key][$h]) && $svc_data[$key][$h]) || (isset($svc_data_ack[$key][$h]) && $svc_data_ack[$key][$h]))
				$flag = 1;
		if (!$flag)
			unset($hg[$key]);
	}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);	
	$tpl->assign("p", $p);
	$tpl->assign("hostgroup", $hg);
	if (isset($h_data))
		$tpl->assign("h_data", $h_data);
	if (isset($h_status_data))
		$tpl->assign("h_status_data", $h_status_data);
	if (isset($svc_data))
		$tpl->assign("svc_data", $svc_data);
	if (isset($svc_data_ack))
		$tpl->assign("svc_data_ack", $svc_data_ack);
	if (isset($h_class))
		$tpl->assign("h_class", $h_class);
	$tpl->assign("lang", $lang);
	$tpl->display("serviceSummaryByHG.ihtml");
?>