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
**/
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
	$path = "./include/views/graphs/graphODS/";

	#PHP functions
	require_once "./include/common/common-Func.php";
	require_once("./DBOdsConnect.php");

	# LCA 
	$lcaHostByID = getLcaHostByID($pearDB);
	$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);

	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"50");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
	
	## Database retrieve information for differents elements list we need on the page
	#   Resources comes from DB -> Store in $ppHosts Array
	# Get all host_list
	
	$ppHosts = array( NULL => NULL );
	$rq = "SELECT DISTINCT host_name FROM index_data ".($isRestreint && !$oreon->user->admin ? "WHERE host_id IN ($LcaHostStr) " : "")." ORDER BY `host_name`";
	$DBRESULT =& $pearDBO->query($rq);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($DBRESULT->fetchInto($hostInOreon))
		$ppHosts[$hostInOreon["host_name"]] = $hostInOreon["host_name"];
	$DBRESULT->free();

	if (isset($_GET["host_name"])){
		$ppServices = array( NULL => NULL );
		$rq = "SELECT service_description FROM index_data WHERE host_name = '".$_GET["host_name"]."' ORDER BY `service_description`";
		$DBRESULT =& $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($DBRESULT->fetchInto($svc))
			$ppServices[$svc["service_description"]] = $svc["service_description"];
		$DBRESULT->free();
	}

	$graphTs = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id,name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while($DBRESULT->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
	## Indicator basic information

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);

	$form->addElement('select', 'host_name', $lang["h"], $ppHosts, array("onChange"=>"this.form.submit()"));
	if (isset($ppServices))
		$form->addElement('select', 'service_description', $lang["sv"], $ppServices);
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
		$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_name = '".str_replace(" ", "\ ", $_GET["host_name"])."' AND service_description = '".str_replace(" ", "\ ", $_GET["service_description"])."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$nb_rsp = $DBRESULT->numRows();
	}

	if ($form->validate() && isset($nb_rsp) && $nb_rsp)	{
		$ret =& $form->getsubmitValues();
		$case = NULL;
		if (isset($ret["host_name"]) && $ret["host_name"] && isset($ret["service_description"])){
			$case = str_replace(" ", "\ ",$ret["host_name"])." / ".str_replace(" ", "\ ",$ret["service_description"]);
			isset($_GET["template_id"]) && $_GET["template_id"] ? $graph = array("graph_id" => $_GET["template_id"], "name" => "") : $graph = array("graph_id" => getDefaultGraph($service_id, 2), "name" => "");
		} 
		
		# Create Graphs and database
		if (!$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$host_name])) && $case)	{
			
			# Verify if template exists
			$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			if (!$DBRESULT->numRows())
				print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";
			
			# Init variable in the page
			$label = NULL;
			$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
			$tpl->assign("res", $case);
			if (isset($graph))
				$tpl->assign("graph", $graph["name"]);
			$tpl->assign("lgGraph", $lang['giv_gt_name']);
			$tpl->assign("lgMetric", $lang['giv_ct_metric']);
			$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);
			
			$DBRESULT =& $pearDBO->query("SELECT id, service_id FROM index_data WHERE host_name = '".$_GET["host_name"]."' AND service_description = '".$_GET["service_description"]."' ORDER BY `service_description`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			$DBRESULT->fetchInto($svc_id);
			$service_id = $svc_id["service_id"];
			$index_id = $svc_id["id"];
			$tpl->assign("index_id", $index_id);
			
			$DBRESULT =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$svc_id["id"]."' ORDER BY `metric_name`");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			while ($DBRESULT->fetchInto($metrics_ret)){
				$metrics[$metrics_ret["metric_id"]] = $metrics_ret;
				$form->addElement('checkbox', $metrics_ret["metric_name"], $metrics_ret["metric_name"]);
			}
			foreach ($metrics as $m)
				if (file_exists($oreon->optGen["oreon_path"]."filesGeneration/graphs/graphODS/".$m["metric_id"].".rrd"))
					unlink($oreon->optGen["oreon_path"]."filesGeneration/graphs/graphODS/".$m["metric_id"].".rrd");	
			
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
					$DBRESULT2 =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$graph["graph_id"]."'");
					if (PEAR::isError($DBRESULT2))
						print "Mysql Error : ".$DBRESULT2->getDebugInfo();
					$DBRESULT2->fetchInto($graph);
					$period = $graph["period"];
				}
			} else if ($_GET["period"])
				$period = $_GET["period"];
			
			if (!isset($start) && !isset($end)){
				$tpl->assign('start_daily', $start_daily = time() - 60 * 60 * 24);
				$tpl->assign('end_daily', $end_daily = time());
				$tpl->assign('start_weekly', $start_weekly = time() - 60 * 60 * 24 * 7);
				$tpl->assign('end_weekly', $end_weekly = time());
				$tpl->assign('start_monthly', $start_monthly = time() - 60 * 60 * 24 * 31);
				$tpl->assign('end_monthly', $end_monthly = time());
				$tpl->assign('start_yearly', $start_yearly = time() - 60 * 60 * 24 * 365);
				$tpl->assign('end_yearly', $end_yearly = time());
			}
			
			if (isset($en))
				$tpl->assign('end', $end);
			if (isset($start))
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
	$tpl->assign('p', $p);
	$tpl->assign('pZoom', $p - 1);
	$tpl->assign('isAvl', 1);
	$tpl->assign('lang', $lang);
	$tpl->assign("graphed_values", $lang["giv_sr_gValues"]);
	$tpl->assign('session_id', session_id());
	$tpl->display("graphODS.ihtml");
?>