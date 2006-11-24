<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	isset($_GET["service_id"]) ? $cG = $_GET["service_id"] : $cG = NULL;
	isset($_POST["service_id"]) ? $cP = $_POST["service_id"] : $cP = NULL;
	$cG ? $service_id = $cG : $service_id = $cP;

	isset($_GET["service_description"]) ? $cG = $_GET["service_description"] : $cG = NULL;
	isset($_POST["service_description"]) ? $cP = $_POST["service_description"] : $cP = NULL;
	$cG ? $service_description = $cG : $service_description = $cP;

	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/views/graphs/simpleRenderer/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	require_once("./DBPerfparseConnect.php");

	# LCA 
	$lcaHostByName = getLcaHostByName($pearDB);
	$isRestreint = HadUserLca($pearDB);
	
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#   Resources comes from DB -> Store in $ppHosts Array

	# Get all host_list
	
	$hostsInOreon = array();
	$res =& $pearDB->query("SELECT DISTINCT host_name FROM host WHERE host_register = '1' AND host_activate = '1' ORDER BY host_name");
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	while($res->fetchInto($hostInOreon))
		$hostsInOreon[$hostInOreon["host_name"]] = 1;
	$res->free();
	
	$ppHosts = array(NULL=>NULL);
	$res =& $pearDBpp->query("SELECT DISTINCT host_name FROM perfdata_service_metric ORDER BY host_name");
	if (PEAR::isError($pearDBpp))
		print "Mysql Error : ".$pearDBpp->getMessage();
	while($res->fetchInto($ppHost))
		if (IsHostReadable($lcaHostByName, $ppHost["host_name"]) && isset($hostsInOreon[$ppHost["host_name"]]))
			$ppHosts[$ppHost["host_name"]] = $ppHost["host_name"];
	$res->free();

	$graphTs = array(NULL=>NULL);
	$res =& $pearDB->query("SELECT graph_id,name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($pearDB))
		print "Mysql Error : ".$pearDB->getMessage();
	while($res->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$res->free();

	# Perfparse Host comes from DB -> Store in $ppHosts Array
	$ppServices1 = array();
	$ppServices2 = array();
	if ($host_name && ($oreon->user->admin || !HadUserLca($pearDB) || (HadUserLca($pearDB) && isset($lcaHostByName["LcaHost"][$host_name]))))	{
		$ppServices = array(NULL=>NULL);
		$res =& $pearDBpp->query("SELECT DISTINCT metric_id, service_description, metric, unit FROM perfdata_service_metric WHERE host_name = '".$host_name."' ORDER BY host_name");
		if (PEAR::isError($pearDBpp))
			print "Mysql Error : ".$pearDBpp->getMessage();
		while($res->fetchInto($ppService))
			$ppServices1[$ppService["service_description"]] = $ppService["service_description"];
		$res->free();
	}

	# Perfparse Meta Services comes from DB -> Store in $ppMSs Array
	$ppMSs = array(NULL=>NULL);
	$res =& $pearDBpp->query("SELECT DISTINCT service_description FROM perfdata_service_metric WHERE host_name = 'Meta_Module' ORDER BY service_description");
	if (PEAR::isError($pearDBpp))
		print "Mysql Error : ".$pearDBpp->getMessage();
	while($res->fetchInto($ppMS))	{
		$id = explode("_", $ppMS["service_description"]);
		$res2 =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$id[1]."'");
		if (PEAR::isError($res2))
			print "Mysql Error : ".$res2->getMessage();
		if ($res2->numRows())	{
			$meta =& $res2->fetchRow();
			$ppMSs[$ppMS["service_description"]] = $meta["meta_name"];
		}
		$res2->free();
	}
	$res->free();

	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"50");


	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	#
	## Form begin
	#

	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);

	#
	## Indicator basic information
	#

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);

	$form->addElement('select', 'host_name', $lang["h"], $ppHosts, array("onChange"=>"this.form.submit()"));
	$form->addElement('select', 'service_description', $lang["sv"], $ppServices1);
	$form->addElement('select', 'meta_service', $lang["ms"], $ppMSs);
	$form->addElement('select', 'template_id', $lang["giv_gg_tpl"], $graphTs);

	$form->addElement('text', 'start', $lang['giv_gt_start']);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$form->addElement('text', 'end', $lang['giv_gt_end']);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));

	$periods = array(	""=>"",
						"10800"=>$lang["giv_sr_p3h"],
						"21600"=>$lang["giv_sr_p6h"],
						"43200"=>$lang["giv_sr_p12h"],
						"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);
	$sel =& $form->addElement('select', 'period', $lang["giv_sr_period"], $periods);

	$steps = array(	"0"=>$lang["giv_sr_noStep"],
					"2"=>"2",
					"6"=>"6",
					"10"=>"10",
					"20"=>"20",
					"50"=>"50",
					"100"=>"100");
	$sel =& $form->addElement('select', 'step', $lang["giv_sr_step"], $steps);

	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	$form->addElement('reset', 'reset', $lang["reset"]);
  	$form->addElement('button', 'advanced', $lang["advanced"], array("onclick"=>"DisplayHidden('div1');"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;
	if (isset($_GET["service_description"]) && $_GET["service_description"]){
		$verify =& $pearDBpp->query("SELECT * FROM `perfdata_service` WHERE host_name = '".str_replace(" ", "\ ", $_GET["host_name"])."' AND service_description = '".str_replace(" ", "\ ", $_GET["service_description"])."'");
		if (PEAR::isError($pearDBpp))
			print "Mysql Error : ".$pearDBpp->getMessage();
		$nb_rsp = $verify->numRows();
	} else if (isset($_GET["meta_service"]) && $_GET["meta_service"]){
		$verify =& $pearDBpp->query("SELECT * FROM `perfdata_service` WHERE host_name = 'Meta_Module' AND service_description = '".$_GET["meta_service"]."'");
		if (PEAR::isError($pearDBpp))
			print "Mysql Error : ".$pearDBpp->getMessage();
		$nb_rsp = $verify->numRows();
	}

	if ($form->validate() && isset($nb_rsp) && $nb_rsp)	{
		$ret =& $form->getsubmitValues();
		$case = NULL;
		if (isset($ret["host_name"]) && $ret["host_name"] && isset($ret["service_description"])){
			$case = str_replace(" ", "\ ",$ret["host_name"])." / ".str_replace(" ", "\ ",$ret["service_description"]);
			isset($_GET["template_id"]) && $_GET["template_id"] ? $graph = array("graph_id" => $_GET["template_id"], "name" => "") : $graph = array("graph_id" => getDefaultGraph($service_id, 2), "name" => "");
		} else if ($_GET["meta_service"])	{
			$ret["host_name"] = "Meta_Module";
			$ret["service_description"] = $_GET["meta_service"];
			$id = explode("_", $_GET["meta_service"]);
			$res2 =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$id[1]."'");
			$meta =& $res2->fetchRow();
			$case = $lang["ms"]." : ".$meta["meta_name"];
			$res2->free();
			if (isset($_GET["template_id"]) && $_GET["template_id"])
				$graph = array("graph_id" => $_GET["template_id"], "name" => "");
			else
				$graph = array("graph_id" => getDefaultMetaGraph(1), "name" => "");
		}

		# OK go create Graphs and database
		if (($oreon->user->admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_name])) || isset($_GET["meta_service"])) && $case)	{
			# Init variable in the page
			$label = NULL;
			$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
			$tpl->assign("res", $case);
			if (isset($graph))
				$tpl->assign("graph", $graph["name"]);
			$tpl->assign("lgGraph", $lang['giv_gt_name']);
			$tpl->assign("lgMetric", $lang['giv_ct_metric']);
			$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);

			# Get configuration data for service graphed
			$host_id = getMyHostID($host_name);
			$service_id = getMyServiceID($ret["service_description"], $host_id);
			$service["service_normal_check_interval"] = getMyServiceField($service_id, 'service_normal_check_interval');
			$service["service_active_checks_enabled"] = getMyServiceField($service_id, 'service_active_checks_enabled');

			if (isset($_GET["meta_service"]) && $_GET["meta_service"]){
				$tab_meta = split("\_", $_GET["meta_service"]);
				$res =& $pearDB->query("SELECT normal_check_interval FROM meta_service WHERE meta_id = '".$tab_meta[1]."'");
				$res->fetchInto($meta);
				$len = $meta["normal_check_interval"] * 120;
			} else if (($service["service_active_checks_enabled"] == 0) || (!$service["service_normal_check_interval"] && !isset($service["service_normal_check_interval"])) || $service["service_normal_check_interval"] == ""){
				$service["service_normal_check_interval"] = 5;
				$len = $service["service_normal_check_interval"] * 120;
			} else
				$len = 5 * 120;

			isset($ret["step"]) && $ret["step"] != 0 ? $time_between_two_values = $len * $ret["step"] : $time_between_two_values = $len;

			# Init
			$ppMetrics = array();
			$isAvl = false;
			$cpt_total_values = 0;
			$cpt_total_graphed_values = 0;
			$res =& $pearDBpp->query(	"SELECT DISTINCT metric_id, metric, unit FROM perfdata_service_metric " .
										"WHERE host_name = '".$ret["host_name"]."' AND service_description = '".$ret["service_description"]."'");
			if (PEAR::isError($pearDBpp))
				print "Mysql Error : ".$pearDBpp->getMessage();
			if ($res->numRows()){
				# Delete Old DataBase
				if (file_exists($oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$ret["host_name"])."_".str_replace(" ", "-",$ret["service_description"]).".rrd"))
					unlink($oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$ret["host_name"])."_".str_replace(" ", "-",$ret["service_description"]).".rrd");

				$isAvl = true;
				for ($cpt = 0;$res->fetchInto($ppMetric);$cpt++){
					$form->addElement('checkbox', $ppMetric["metric"], $ppMetric["metric"]);
					$form->setDefaults(array($ppMetric["metric"] => '1'));
					$ppMetrics[$ppMetric["metric_id"]]["metric"] = str_replace(" ", "_", $ppMetric["metric"]);
					$ppMetrics[$ppMetric["metric_id"]]["unit"] = $ppMetric["unit"];
				}

				$res->free();
				$tpl->assign("metrics", $ppMetrics);

				# Create period
				if (isset($_GET["start"]) && isset($_GET["end"]) && $_GET["start"] && $_GET["end"]){
					preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["start"], $matches);
					$start = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3], 1) ;
					preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["end"], $matches);
					$end = mktime("23", "59", "59", $matches[1], $matches[2], $matches[3], 1)  + 10;
				} else if (!isset($_GET["period"]) || (isset($_GET["period"]) && !$_GET["period"])){
					if (!isset($graph["graph_id"]))
						$period = 86400;
					else {
						$res =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$graph["graph_id"]."'");
						if (PEAR::isError($res))
							print "Mysql Error : ".$res->getMessage();
						$res->fetchInto($graph);
						$period = $graph["period"];
					}
				} else if ($_GET["period"])
					$period = $_GET["period"];

				if (!isset($start) && !isset($end)){
					$start = time() - ($period + 30);
					$end = time() + 10;
				}
				$start_create = $start - 200000;
	 			#####################################################################
				# Mise en memoire des valeurs remontees de la base de donnees MySQL
				# Init Lower Value
				$time_start_mysql = microtime_float();
				$GMT = 1;
				$lower = 0;
				$tab_bin = array();
				foreach ($ppMetrics as $key => $value){
					$get = 	"SELECT SQL_BIG_RESULT HIGH_PRIORITY value,ctime FROM `perfdata_service_bin` WHERE `host_name` = '".$ret["host_name"]."' ".
							"AND `service_description` = '".$ret["service_description"]."' AND `metric` = '".$value["metric"]."' ".
							"AND `ctime` >= '".date("Y-m-d G:i:s", $start)."' AND `ctime` <= '".date("Y-m-d G:i:s", $end)."' ORDER BY ctime";
	 				$req =& $pearDBpp->query($get);
	 				if (PEAR::isError($pearDBpp))
	 					print "Mysql Error : ".$pearDBpp->getMessage();
					$r = $str = NULL;
					for ($cpt = 0,$cpt_real = 0;$r =& $req->fetchRow();$cpt++){
						preg_match("/^([0-9]*)-([0-9]*)-([0-9]*) ([0-9]*):([0-9]*):([0-9]*)/", $r["ctime"], $matches);
						$time_temp = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1], 1);
						if ((isset($ret["step"]) && $ret["step"] == "1") || (isset($ret["step"]) && ($cpt % $ret["step"] == 0)) || (!isset($ret["step"]))){
							$tab_bin[$time_temp + (3600 * $GMT)][$value["metric"]] = $r["value"];
							$cpt_real++;
						}
					}
					$cpt_total_values += $cpt;
					$cpt_total_graphed_values += $cpt_real;
				}
				$time_end_mysql = microtime_float();
				# Create RRDTool DB
	 			$cmd = $oreon->optGen["rrdtool_path_bin"] . " create ".$oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$ret["host_name"])."_".str_replace(" ", "-",$ret["service_description"]).".rrd --start $start_create ";
				$nb_ds = 0;
				foreach ($ppMetrics as $key => $metric){
					$cmd .= " DS:".str_replace("/", "", addslashes($metric["metric"])).":GAUGE:$time_between_two_values:U:U";
					$nb_ds++;
				}
				$cpt_total_graphed_values_for_rrd = $cpt_total_values + 100;
				$cmd .=  " RRA:LAST:0.5:1:".$cpt_total_graphed_values_for_rrd . " ";//RRA:MIN:0.5:8:".$cpt_total_graphed_values_for_rrd." RRA:MAX:0.5:8:".$cpt_total_graphed_values_for_rrd;
				system($cmd, $return);

				################################################################
				$time_start_create = microtime_float();
				$cpt_data = 0;
				foreach ($tab_bin as $key => $value){
					$str .= " ".$key;
					$strtemp = NULL;
					$nb = 0;
					foreach ($value as $metric => $tm){
						$strtemp .= ":".$value[$metric];
						$nb++;
					}
					if ($nb < $nb_ds){
						for ($p = $nb; $p != $nb_ds; $p++)
							$strtemp .= ":0";
						$strtemp .= " ";
					}
					$str .= $strtemp;
					$cpt_data++;
					if ($cpt_data % 700 == 0 || $cpt_data == count($tab_bin) || $cpt_data % 700 == count($tab_bin)){
						system($oreon->optGen["rrdtool_path_bin"] . " update ".$oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$ret["host_name"])."_".str_replace(" ", "-",$ret["service_description"]).".rrd ".$str . " 2>&1", $return);
						$str = "";
					}
				}
				$res->free();
				$time_end_create = microtime_float();
			}
			
			$tpl->assign('cpt_total_values', $cpt_total_values);
			$tpl->assign('cpt_total_graphed_values', $cpt_total_graphed_values);
			$tpl->assign('isAvl', $isAvl);
			$tpl->assign('host_name', $ret["host_name"]);
			$tpl->assign('service_description', $ret["service_description"]);
			$tpl->assign('end', $end);
			$tpl->assign('start', $start);
			if (isset($_GET["template_id"]))
				$tpl->assign('template_id', $_GET["template_id"]);
		}
    }

	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('lang', $lang);
	$tpl->assign("graphed_values", $lang["giv_sr_gValues"]);
	$tpl->assign('session_id', session_id());
	$tpl->display("simpleRenderer.ihtml");
?>