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

	unset($TabLca);
	$TabLca = getLcaHostByName($pearDB);
	$isRestreint = hadUserLca($pearDB);
	
	$hg = array();
	
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($r)){
		$cpt_host = 0;
		if ($oreon->user->admin || !hadUserLca($pearDB) || (hadUserLca($pearDB) && isset($TabLca["LcaHostGroup"][$r["hg_name"]]))){		
			$DBRESULT1 =& $pearDB->query(	"SELECT host_host_id, host_name, host_alias FROM hostgroup_relation,host,hostgroup ".
										"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
										"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
			if (PEAR::isError($DBRESULT1))
				print "Mysql Error : ".$DBRESULT1->getMessage();
			$cpt_host = 0;
			while ($DBRESULT1->fetchInto($r_h)){
				if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($TabLca["LcaHost"][$r_h["host_name"]]))){
					$service_data_str = NULL;	
					$host_data_str = "<a href='./oreon.php?p=201&o=hd&host_name=".$r_h["host_name"]."'>" . $r_h["host_name"] . "</a> (" . $r_h["host_alias"] . ")";
					if(isset($tab_host_service[$r_h["host_name"]])){
						foreach ($tab_host_service[$r_h["host_name"]] as $key => $value){
							$service_data_str .= 	"<span style='background:".
													$oreon->optGen["color_".strtolower($service_status[$r_h["host_name"]."_".$key]["current_state"])]."'>".
													"<a href='./oreon.php?p=202&o=svcd&host_name=".$r_h["host_name"]."&service_description=".$key."'>".$key.
													"</a></span> &nbsp;&nbsp;";
							if (!isset($hg[$r["hg_name"]]))
								$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"], "host" => array());
							$hg[$r["hg_name"]]["host"][$cpt_host] = $r_h["host_name"];
							$h_data[$r["hg_name"]][$r_h["host_name"]] = $host_data_str;
							$status = "color_".strtolower($host_status[$r_h["host_name"]]["current_state"]);
							$h_status_data[$r["hg_name"]][$r_h["host_name"]] = "<td class='ListColCenter' style='background:".$oreon->optGen[$status]."'>".$host_status[$r_h["host_name"]]["current_state"]."</td>";
							$svc_data[$r["hg_name"]][$r_h["host_name"]] = $service_data_str;
						}
					}
					$cpt_host++;
				}
			}
		}
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
	$tpl->assign("lang", $lang);
	if(isset($svc_data))
		$tpl->assign("svc_data", $svc_data);
	$tpl->display("serviceOverview.ihtml");
?>