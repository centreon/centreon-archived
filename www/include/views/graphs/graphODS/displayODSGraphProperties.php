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
**/

	if (!isset($oreon))
		exit();

	# LCA 
	if ($isRestreint){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
	}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
	
	## Indicator basic information
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$page =& $form->addElement('hidden', 'min');
	$page->setValue($min);
	
	if (isset($_GET["start"]) && !isset($_GET["period"])){
		$startF =& $form->addElement('hidden', 'start');
		$startF->setValue($_GET["start"]);
	}
	if (isset($_GET["end"]) && !isset($_GET["period"])){
		$endF =& $form->addElement('hidden', 'end');
		$endF->setValue($_GET["end"]);
	}
	
	if (isset($_GET["period"]))
		$period =  $_GET["period"];
	if (isset($_POST["period"]))
		$period =  $_POST["period"];

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows())
		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";
	
	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", $lang['giv_gt_name']);
	$tpl->assign("lgMetric", $lang['giv_ct_metric']);
	$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);
		
	$elem = array();	
	$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE id = '".$_GET["index"]."'");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	$DBRESULT2->fetchInto($svc_id);
	
	$service_id = $svc_id["service_id"];
	$index_id = $svc_id["id"];
	$indexF =& $form->addElement('hidden', 'index');
	$indexF->setValue($index_id);
	
	$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$service_id."' ORDER BY `metric_name`");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	while ($DBRESULT2->fetchInto($metrics_ret)){
		$metrics[$metrics_ret["metric_id"]] = $metrics_ret;
		$form->addElement('checkbox', $metrics_ret["metric_name"], $metrics_ret["metric_name"]);
	}
		
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	
	if ($svc_id["host_name"] == "Meta_Module")
		$svc_id["host_name"] = "Meta Services";
	$tpl->assign('host_name', $svc_id["host_name"]);
	if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
		$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
		if (PEAR::isError($DBRESULT_meta))
			print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
		$DBRESULT_meta->fetchInto($meta);
		$svc_id["service_description"] = $meta["meta_name"];
	}
	$tpl->assign('service_description', str_replace("#BS#", "\\", str_replace("#S#", "/", $svc_id["service_description"])));

	if (!$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){	
		$DBRESULT =& $pearDBO->query("SELECT * FROM config");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT->fetchInto($config);
		$tpl->assign('config', $config);
		
		$metrics = array();
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY metric_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
	
		$counter = 1;	
		$metrics = array();	
		while ($DBRESULT->fetchInto($metric)){
			$metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
			$metrics[$metric["metric_id"]]["metric"] = str_replace("/", "", $metric["metric_name"]);
			$metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];		
			if ($tab_stat = stat($config["RRDdatabase_path"].$metric["metric_id"].".rrd")){
				$metrics[$metric["metric_id"]]["last_update"] = date($lang["date_time_format_status"],$tab_stat[9]);
				$metrics[$metric["metric_id"]]["size"] = round($tab_stat[7] / 1024 / 1024, 2);	
				$metrics[$metric["metric_id"]]["db_name"] = $config["RRDdatabase_path"].$metric["metric_id"].".rrd";
			}
			$metrics[$metric["metric_id"]]["order"] = $counter;
			$counter++;
		}
		$DBRESULT->free();
		
		$DBRESULT_data =& $pearDBO->query("SELECT storage_type FROM index_data WHERE id = '".$_GET["index"]."' LIMIT 1");
		if (PEAR::isError($DBRESULT_data))
			print "DB Error : ".$DBRESULT_data->getDebugInfo();
		$DBRESULT_data->fetchInto($conf);
		$DBRESULT_data->free();
		
		$storage_type = array(0 => "RRDTool", 1 => "MySQL", 2 => "RRDTool & MySQL");	
		$tpl->assign('storage_type_possibility', $storage_type);
		$tpl->assign('storage_type', $conf["storage_type"]);
		
		$tpl->assign('admin', $oreon->user->admin);
		
		$tpl->assign('start', $_GET["start"]);
		$tpl->assign('end', $_GET["end"]);
		$tpl->assign('isAvl', 1);
		$tpl->assign('lang', $lang);
		$tpl->assign('index', $_GET["index"]);
		$tpl->assign('session_id', session_id());
		$tpl->assign('metrics', $metrics);
		$tpl->display("displayODSGraphProperties.ihtml");
	}
?>