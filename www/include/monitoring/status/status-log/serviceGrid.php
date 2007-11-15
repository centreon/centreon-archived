<?php
/**
Centreon is developped with GPL Licence 2.0 :
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

	unset($TabLca);;
	$TabLca = getLcaHostByName($pearDB);

	$h_data = array();
	$h_class = array();
	$svc_data = array();
	$svc_data_ack = array();
	$tab_color = array(0=>"list_one", 1=>"list_two");
	
	$counter_host = 0;	
	foreach ($host_status as $key => $data){
		if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($TabLca["LcaHost"][$data["host_name"]]))){
			$service_data_str = NULL;
			$service_data_str_ack = NULL;
			$h_data[$data["host_name"]] = "<a href='./oreon.php?p=201&o=hd&host_name=".$data["host_name"]."'>".$data["host_name"]."</a>";
			# define class
			isset($host_status[$data["host_name"]]) && $host_status[$data["host_name"]]["current_state"] == "DOWN" ? $h_class[$data["host_name"]] = "list_down" : $h_class[$data["host_name"]] = $tab_color[++$counter_host % 2];
			if (isset($tab_host_service[$data["host_name"]]))
				foreach ($tab_host_service[$data["host_name"]] as $key_svc => $data_svc){
					if ((isset($_GET["problem"]) && $service_status[$data["host_name"]."_".$key_svc]["current_state"] != "OK") 
						|| 	(!isset($_GET["problem"]) && !isset($_GET["acknowledge"])) 
						|| 	(!isset($_GET["problem"]) && isset($_GET["acknowledge"]) && $_GET["acknowledge"] == 1 && $service_status[$data["host_name"]."_".$key_svc]["problem_has_been_acknowledged"] == 1)
						|| 	(!isset($_GET["problem"]) && isset($_GET["acknowledge"]) && $_GET["acknowledge"] == 0 && $service_status[$data["host_name"]."_".$key_svc]["problem_has_been_acknowledged"] == 0 && $service_status[$data["host_name"]."_".$key_svc]["current_state"] != "OK" )){
						if (isset($_GET["problem"])){
							if ($service_status[$data["host_name"]."_".$key_svc]["problem_has_been_acknowledged"] == 0)
								$service_data_str .= " <span style='background:".$oreon->optGen["color_".strtolower($service_status[$data["host_name"]."_".$key_svc]["current_state"])]."'><a href='./oreon.php?p=202&o=svcd&host_name=".$data["host_name"]."&service_description=".$key_svc."'>".$key_svc."</a></span>&nbsp; \n";
							else
								$service_data_str_ack .= " <span style='background:".$oreon->optGen["color_".strtolower($service_status[$data["host_name"]."_".$key_svc]["current_state"])]."'><a href='./oreon.php?p=202&o=svcd&host_name=".$data["host_name"]."&service_description=".$key_svc."'>".$key_svc."</a></span>&nbsp; \n";						
						} else {
							$service_data_str .= " <span style='background:".$oreon->optGen["color_".strtolower($service_status[$data["host_name"]."_".$key_svc]["current_state"])]."'><a href='./oreon.php?p=202&o=svcd&host_name=".$data["host_name"]."&service_description=".$key_svc."'>".$key_svc."</a></span>&nbsp; \n";
						}
					}
				}
			if (isset($_GET["problem"]) && $service_data_str_ack)
				$svc_data_ack[$data["host_name"]] = $service_data_str_ack;
			if ($service_data_str)
				$svc_data[$data["host_name"]] = $service_data_str;
		}
	}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);
	$tpl->assign("p", $p);
	$tpl->assign("h_data", $h_data);
	$tpl->assign("h_class", $h_class);
	$tpl->assign("lang", $lang);
	$tpl->assign("svc_data", $svc_data);
	if (isset($_GET["problem"]))
		$tpl->assign("svc_data_ack", $svc_data_ack);
	$tpl->display("serviceGrid.ihtml");
?>