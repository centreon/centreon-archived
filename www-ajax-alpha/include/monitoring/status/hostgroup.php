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

	$tab = array("1"=>'list_one', "0" => "list_two"); 

	$ret =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
	if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	while ($r =& $ret->fetchRow()){
		$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"]);
		$status_hg_h[$r["hg_name"]] = array();
		$status_hg_h[$r["hg_name"]]["UP"] = 0;
		$status_hg_h[$r["hg_name"]]["DOWN"] = 0;
		$status_hg_h[$r["hg_name"]]["UNREACHABLE"] = 0;
		$status_hg_h[$r["hg_name"]]["PENDING"] = 0;
		$status_hg_h[$r["hg_name"]]["UNKNOWN"] = 0;
		$status_hg[$r["hg_name"]] = array();
		$status_hg[$r["hg_name"]]["OK"] = 0;
		$status_hg[$r["hg_name"]]["PENDING"] = 0;
		$status_hg[$r["hg_name"]]["WARNING"] = 0;
		$status_hg[$r["hg_name"]]["CRITICAL"] = 0;
		$status_hg[$r["hg_name"]]["UNKNOWN"] = 0;
		
		$ret_h =& $pearDB->query(	"SELECT host_host_id,host_name FROM hostgroup_relation,host,hostgroup ".
									"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
									"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		while ($r_h =& $ret_h->fetchRow()){
			!$r_h["host_name"] ? $hostname = getMyHostName($r_h["host_id"]) : $hostname = $r_h["host_name"];
			//print $r["hg_name"]. " : " . $hostname ."-".$host_status[$hostname]["status"] . "<br>";
			if (isset($host_status[$hostname]["status"])){
				$status_hg_h[$r["hg_name"]][$host_status[$hostname]["status"]]++;
				foreach ($tab_host_service[$hostname] as $key => $s){
					$status_hg[$r["hg_name"]][$service_status[$hostname. "_" .$key]["status"]]++;
				} 		
			}
		}
	}
	
	$cpt = 0;
	foreach ($hg as $hgs){
		$hg[$hgs["name"]]["host_stats"] = "";
		if ($status_hg_h[$hgs["name"]]["UP"] != 0)
			$hg[$hgs["name"]]["host_stats"] = "<span style='background:".$oreon->optGen["color_up"]."'>" . $status_hg_h[$hgs["name"]]["UP"] . " UP</span> ";
		if ($status_hg_h[$hgs["name"]]["DOWN"] != 0)
			$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_down"]."'>" . $status_hg_h[$hgs["name"]]["DOWN"] . " DOWN</span> ";
		if ($status_hg_h[$hgs["name"]]["UNREACHABLE"] != 0)
			$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_unreachable"]."'>" . $status_hg_h[$hgs["name"]]["UNREACHABLE"] . " UNREACHABLE</span> ";
		if ($status_hg_h[$hgs["name"]]["PENDING"] != 0)
			$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg_h[$hgs["name"]]["PENDING"] . " PENDING</span> ";
		if ($status_hg_h[$hgs["name"]]["UNKNOWN"] != 0)
			$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg_h[$hgs["name"]]["UNKNOWN"] . " UNKNOWN</span> ";
		
		$hg[$hgs["name"]]["svc_stats"] = "";
		if ($status_hg[$hgs["name"]]["OK"] != 0)
			$hg[$hgs["name"]]["svc_stats"] = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg[$hgs["name"]]["OK"] . " OK</span> ";
		if ($status_hg[$hgs["name"]]["WARNING"] != 0)
			$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg[$hgs["name"]]["WARNING"] . " WARNING</span> ";
		if ($status_hg[$hgs["name"]]["CRITICAL"] != 0)
			$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg[$hgs["name"]]["CRITICAL"] . " CRITICAL</span> ";
		if ($status_hg[$hgs["name"]]["PENDING"] != 0)
			$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg[$hgs["name"]]["PENDING"] . " PENDING</span> ";
		if ($status_hg[$hgs["name"]]["UNKNOWN"] != 0)
			$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg[$hgs["name"]]["UNKNOWN"] . " UNKNOWN</span> ";
		$hg[$hgs["name"]]["class"] = $tab[$cpt % 2];
		$cpt++;
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


/*
    $ajax = "<script type='text/javascript'>" .
    "window.onload = function () {" .
    "setTimeout('init()', 2000);" .
    "};" .
    "</script>";
    $tpl->assign('ajax', $ajax);
    $tpl->assign('time', time());
    $tpl->assign('fileStatus',  $oreon->Nagioscfg["status_file"]);
	$tpl->assign('fileOreonConf', $oreon->optGen["oreon_path"]);
    $tpl->assign('color_OK', $oreon->optGen["color_ok"]);
    $tpl->assign('color_CRITICAL', $oreon->optGen["color_critical"]);
    $tpl->assign('color_WARNING', $oreon->optGen["color_warning"]);
    $tpl->assign('color_UNKNOWN', $oreon->optGen["color_unknown"]);
    $tpl->assign('color_PENDING', $oreon->optGen["color_pending"]);
    $tpl->assign('color_UP', $oreon->optGen["color_up"]);
    $tpl->assign('color_DOWN', $oreon->optGen["color_down"]);
    $tpl->assign('color_UNREACHABLE', $oreon->optGen["color_unreachable"]);

    $lca =& $oreon->user->lcaHStrName;
	$version = $oreon->user->get_version();
	$tpl->assign("lca", $lca);
	$tpl->assign("version", $version);
*/
	
		

	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);
	
	$tpl->assign("p", $p);
	$tpl->assign("hg", $hg);
	$tpl->assign("lang", $lang);
	$tpl->display("hostgroup.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");
?>