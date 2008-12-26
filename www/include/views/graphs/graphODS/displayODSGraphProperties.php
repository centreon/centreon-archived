<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();

	# LCA 
	$is_admin = isUserAdmin(session_id());
	if (!$is_admin){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
	}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));
	
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
		print "<div class='msg' align='center'>"._("There is no graph template : please configure your graph template in order to display graphs correctly.")."</div>";
	
	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", _("Graph Renderer"));
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", _("Template Name"));
	$tpl->assign("lgMetric", _("Metric"));
	$tpl->assign("lgCompoTmp", _("Template Name"));
		
	$elem = array();	
	$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE id = '".$_GET["index"]."'");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	$svc_id =& $DBRESULT2->fetchRow();
	
	$service_id = $svc_id["service_id"];
	$index_id = $svc_id["id"];
	$indexF =& $form->addElement('hidden', 'index');
	$indexF->setValue($index_id);
	
	$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$service_id."' ORDER BY `metric_name`");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	while ($metrics_ret =& $DBRESULT2->fetchRow()){
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
	
	if ($svc_id["host_name"] == "_Module_Meta")
		$svc_id["host_name"] = "Meta Services";
	$tpl->assign('host_name', $svc_id["host_name"]);
	if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
		$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
		if (PEAR::isError($DBRESULT_meta))
			print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
		$meta =& $DBRESULT_meta->fetchRow();
		$svc_id["service_description"] = $meta["meta_name"];
	}
	$tpl->assign('service_description', str_replace("#BS#", "\\", str_replace("#S#", "/", $svc_id["service_description"])));

	if ($is_admin || (!$is_admin && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){	
		$DBRESULT =& $pearDBO->query("SELECT * FROM config");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$config =& $DBRESULT->fetchRow();
		$tpl->assign('config', $config);
		
		$metrics = array();
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY metric_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
	
		$counter = 1;	
		$metrics = array();	
		while ($metric =& $DBRESULT->fetchRow()){
			$metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
			$metrics[$metric["metric_id"]]["metric"] = str_replace("/", "", $metric["metric_name"]);
			$metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];		
			if ($tab_stat = stat($config["RRDdatabase_path"].$metric["metric_id"].".rrd")){
				$metrics[$metric["metric_id"]]["last_update"] = date(_("d/m/Y H:i:s"),$tab_stat[9]);
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
		$conf =& $DBRESULT_data->fetchRow();
		$DBRESULT_data->free();
		
		$storage_type = array(0 => "RRDTool", 1 => "MySQL", 2 => "RRDTool & MySQL");	
		$tpl->assign('storage_type_possibility', $storage_type);
		$tpl->assign('storage_type', $conf["storage_type"]);
		
		$tpl->assign('admin', $oreon->user->admin);
		
		$tpl->assign('start', $_GET["start"]);
		$tpl->assign('end', $_GET["end"]);
		$tpl->assign('isAvl', 1);
		$tpl->assign('index', $_GET["index"]);
		$tpl->assign('session_id', session_id());
		$tpl->assign('metrics', $metrics);
		$tpl->assign('ods_on', _("on "));
		$tpl->display("displayODSGraphProperties.ihtml");
	}
?>