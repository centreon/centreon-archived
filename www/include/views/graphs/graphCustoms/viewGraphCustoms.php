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

	# Smarty template Init

	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	require_once("./DBPerfparseConnect.php");

	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_period"]);
	#
	## Indicator basic information
	#
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);	
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);	
	$gid =& $form->addElement('hidden', 'graph_id');
	$gid->setValue($graph_id);
   
   	# "3600"=>$lang["giv_sr_p1h"],
   
	$periods = array(
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
	$form->setDefaults(array('period' =>'10800'));
	
	$form->addElement('text', 'start', $lang['giv_gt_start']);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$form->addElement('text', 'end', $lang['giv_gt_end']);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	
	$steps = array(
					"0"=>$lang["giv_sr_noStep"],
					"2"=>"2",
					"6"=>"6",
					"10"=>"10",
					"20"=>"20",
					"50"=>"50",
					"100"=>"100"			
	);
	$sel =& $form->addElement('select', 'step', $lang["giv_sr_step"], $steps);
	
	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);

	
	#Different style between each lines
	$style = "one";
	
	$graph_id = $_GET["graph_id"];
	
	# Get graph name
	
	$res_gr =& $pearDB->query("SELECT name FROM giv_graphs WHERE graph_id = '".$graph_id."'");
	$res_gr->fetchInto($graph_data);
	$graph_name = $graph_data["name"];
	
	#
	
	$ppMetrics = array();
	$rq = 	"SELECT pp_metric_id FROM giv_components gc ".
			"WHERE gc.graph_id = '".$graph_id."' ORDER BY ds_order";
	$res =& $pearDB->query($rq);
	$cpt = 0;
	while($res->fetchInto($ppMetric))	{
		$ppMetrics[$ppMetric["pp_metric_id"]] = array();	
		$ppMetrics[$ppMetric["pp_metric_id"]]["metric_id"] = $ppMetric["pp_metric_id"];
	}
	$res->free();
	$tpl->assign("metrics", $ppMetrics);
	
	# Creating rrd DB
	
	$label = NULL;
	isset($ret["step"]) && $ret["step"] != 0 ? $time_between_two_values = 600 * $ret["step"] : $time_between_two_values = 600;
	
	if (isset($_GET["start"]) && $_GET["start"]){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["start"], $matches);
		$start = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3], 1) ;
	}
	if (isset($_GET["end"]) && $_GET["end"]){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["end"], $matches);
		$end = mktime("23", "59", "59", $matches[1], $matches[2], $matches[3], 1)  + 10;
	}
	
	if (!isset($_GET["start"]) && !isset($_GET["end"]) && !isset($_GET["period"]) )
		$_GET["period"] = 21600;
		
	if (!isset($start))
		$start = time() - ($_GET["period"] + 30);
	if (!isset($end))
		$end = time() + 120;
	
	$start_create = $start - 200000;
	
	$tab_metric = array();
	foreach ($ppMetrics as $key => $metric){
		if (file_exists($oreon->optGen["oreon_path"]."filesGeneration/graphs/graphCustoms/".$metric["metric_id"].".rrd"))
			unlink($oreon->optGen["oreon_path"]."filesGeneration/graphs/graphCustoms/".$metric["metric_id"].".rrd");
		$cmd = $oreon->optGen["rrdtool_path_bin"] . " create ".$oreon->optGen["oreon_path"]."filesGeneration/graphs/graphCustoms/".$metric["metric_id"].".rrd --start $start_create "; 	
		$cmd .= " DS:ds".$metric["metric_id"].":GAUGE:$time_between_two_values:U:U";
		$cmd .=  " RRA:AVERAGE:0.5:1:8960";
		$cmd .=  " RRA:LAST:0.5:1:8960";
		system($cmd, $return);
	}
		
	# Mise en memoire des valeurs remont s de la base de donn e MySQL
	# Init Lower Value
	$GMT = $oreon->optGen['gmt'];
	$lower = 0;
	$tab_bin = array();
	$cpt_total_values = 0;
	$cpt_total_graphed_values = 0;
	$i = 1;
	$ret["step"] = 0;
	if ($i)
	foreach ($ppMetrics as $key => $value){
		$res =& $pearDBpp->query("SELECT * FROM `perfdata_service_metric` WHERE metric_id = '".$key."'");
		$res->fetchInto($metric_info);
		$get = 	"SELECT value,ctime FROM `perfdata_service_bin` WHERE `metric` = '".$metric_info["metric"]."' AND `host_name` = '".$metric_info["host_name"]."' AND `service_description` = '".$metric_info["service_description"]."' ".
				"AND `ctime` >= '".date("Y-m-d G:i:s", $start)."' AND `ctime` <= '".date("Y-m-d G:i:s", $end)."' ORDER BY ctime";
	
		$str = NULL;
		$r = NULL;
		$cpt = 0;

		$cpt_real = 0;
		if (!isset($_GET["step"]))
			$_GET["step"] = 0; 

		$req =& $pearDBpp->query($get);
		for ($cpt = 0;$r =& $req->fetchRow();$cpt++){
			preg_match("/^([0-9]*)-([0-9]*)-([0-9]*) ([0-9]*):([0-9]*):([0-9]*)/", $r["ctime"], $matches);
			$time_temp = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1], 1);
			$time_temp2 = $time_temp + (3600 * $GMT);
			$str .= " ".$time_temp2.":".$r["value"];
			if (isset($_GET["step"]) && $_GET["step"] == "1"){
				if ($value < $lower)
					$lower = $r["value"];
				$cpt_real++;
			} else if ($cpt % $_GET["step"] == 0){
				if ($value < $lower)
					$lower = $r["value"];
				$cpt_real++;
			}
		}
		$cpt_total_values += $cpt;
		$cpt_total_graphed_values += $cpt_real;
		$update_cmd = $oreon->optGen["rrdtool_path_bin"]." update ".$oreon->optGen["oreon_path"]."filesGeneration/graphs/graphCustoms/".$key.".rrd " . $str . " 2>&1";
		system($update_cmd, $ret);
		if ($ret)
			print $ret;
	}
	
	
	#
	## Metric List
	#
	
		# start header menu
		$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
		$tpl->assign("headerMenu_name", $lang['name']);
		$tpl->assign("headerMenu_desc", $lang['description']);
		$tpl->assign("headerMenu_metric", $lang['giv_ct_metric']);
		$tpl->assign("headerMenu_compo", $lang["giv_gg_tpl"]);
		$tpl->assign("headerMenu_options", $lang['options']);
		# end header menu
	
		#List
		$rq = "SELECT compo_id, pp_metric_id, compot_compo_id FROM giv_components gc WHERE gc.graph_id = '".$graph_id."' ORDER BY ds_order";
		$res = & $pearDB->query($rq);
		
		$form2 = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
		#Different style between each lines
		$style = "one";
		#Fill a tab with a mutlidimensionnal Array we put in $tpl
		$elemArr = array();
		$nb_entry = $res->numRows();
		for ($i = 0; $res->fetchInto($metric); $i++) {
			$moptions = NULL;
			$res1 =& $pearDBpp->query("SELECT DISTINCT host_name, service_description, metric FROM perfdata_service_metric WHERE metric_id = '".$metric["pp_metric_id"]."'");
			$ppMetric =& $res1->fetchRow();
			$selectedElements =& $form2->addElement('checkbox', "select[".$metric['compo_id']."]");	
			if ($i != 0){
				$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=wup";
				if (isset($_GET["period"]) && $_GET["period"])
					$moptions .= "&period=" . $_GET["period"];
				else
					$moptions .= "&end=".$_GET["end"]."&start=".$_GET["start"];
				$moptions .= "'><img src='img/icones/16x16/arrow_up_green.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
				$moptions .= "";
			}
			if ($i != ($nb_entry - 1)){
				$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=wdown";
				if (isset($_GET["period"]) && $_GET["period"])
					$moptions .= "&period=" . $_GET["period"];
				else
					$moptions .= "&end=".$_GET["end"]."&start=".$_GET["start"];
				$moptions .= "'><img src='img/icones/16x16/arrow_down_green.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
				$moptions .= "";
			} else
				$moptions .= "<img src='./img/icones/1x1/blank.gif' width=16 height=16>&nbsp;&nbsp;";
			//	$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=wdown&end=".$end."&start=".$start."'><img src='img/icones/16x16/arrow_down_green.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
			
			if ($ppMetric["host_name"] == "Meta_Module")	{
				$host_name = $lang["giv_gg_ms"];
				$name = explode("_", $ppMetric["service_description"]);
				$res2 =& $pearDB->query("SELECT DISTINCT meta_name FROM meta_service WHERE meta_id = '".$name[1]."'");
				$name =& $res2->fetchRow();
				$ppMetric["service_description"] = $name["meta_name"];

			}
			else if ($ppMetric["host_name"] == "OSL_Module")	{
				$host_name = $lang["giv_gg_osl"];
				$name = explode("_", $ppMetric["service_description"]);
				$res2 =& $pearDB->query("SELECT DISTINCT name FROM osl WHERE osl_id = '".$name[1]."'");
				$name =& $res2->fetchRow();
				$ppMetric["service_description"] = $name["name"];
			}
			else
				$host_name = $ppMetric["host_name"];
			$elemArr[$i] = array("MenuClass"=>"list_".$style, 
							"RowMenu_select"=>$selectedElements->toHtml(),
							"RowMenu_name"=> $host_name,
							"RowMenu_link"=>"?p=".$p."&o=w&graph_id=".$metric['compo_id'],
							"RowMenu_desc"=>$ppMetric["service_description"],
							"RowMenu_metric"=>$ppMetric["metric"],
							"RowMenu_compo"=>$metric["compot_compo_id"] ? $lang["yes"] : $lang["no"],
							"RowMenu_options"=>$moptions);
			$style != "two" ? $style = "two" : $style = "one";
		}
		$tpl->assign("elemArr", $elemArr);
		#Different messages we put in the template
		$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	
	#Element we need when we reload the page
	$form2->addElement('hidden', 'p');
	$tab1 = array ("p" => $p, "o" => $o, "graph_id"=>$graph_id);
	$tab2 = array ("p" => $p);
	$form2->setDefaults($tab2);	

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form2->accept($renderer);	
	$tpl->assign('form2', $renderer->toArray());
	
	
	
	# RRDTool Data base Created.
	
	#
	## Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('graph_name', $graph_name);
	$tpl->assign('graph_id', $_GET["graph_id"]);
	if (isset($_GET["period"]))
		$tpl->assign('period', $_GET["period"]);
	$tpl->assign('end', $end);
	$tpl->assign('start', $start);
	$tpl->assign('session_id', session_id());
	$tpl->assign('cpt_total_values', $cpt_total_values);
	$tpl->assign('cpt_total_graphed_values', $cpt_total_graphed_values);
	$tpl->assign("graphed_values", $lang["giv_sr_gValues"]);
	$tpl->display("viewGraphCustoms.ihtml");
?>