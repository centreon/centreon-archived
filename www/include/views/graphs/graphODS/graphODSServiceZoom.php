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

	# include js for zoombox
	include('./include/views/graphs/graphODS/zoombox.php');
	#include('./include/views/graphs/graphODS/javascript.php');
		
		
	# LCA 
	$is_admin = isUserAdmin(session_id());
	if (!$is_admin){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
	}
	
	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"50");
	$tab_class 		= array("1" => "list_one", "0" => "list_two");
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));
		
	$graphTs = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id,name FROM giv_graphs_template ORDER BY name");
	while ($graphT =& $DBRESULT->fetchRow())
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
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
	
	$form->addElement('select', 'template_id', _("Template"), $graphTs);
	$subC =& $form->addElement('submit', 'submitC', _("Graph"));
	
	$periods = array(	""=>"",
						"10800"=>_("Last 3 Hours"),
						"21600"=>_("Last 6 Hours"),
						"43200"=>_("Last 12 Hours"),
						"86400"=>_("Last 24 Hours"),
						"172800"=>_("Last 2 Days"),
						"302400"=>_("Last 4 Days"),
						"604800"=>_("Last 7 Days"),
						"1209600"=>_("Last 14 Days"),
						"2419200"=>_("Last 28 Days"),
						"2592000"=>_("Last 30 Days"),
						"2678400"=>_("Last 31 Days"),
						"5184000"=>_("Last 2 Months"),
						"10368000"=>_("Last 4 Months"),
						"15552000"=>_("Last 6 Months"),
						"31104000"=>_("Last Year"));

	$sel =& $form->addElement('select', 'period', _("Graph Period"), $periods);
	
	$form->addElement('reset', 'reset', _("Reset"));
  	$form->addElement('button', 'advanced', _("Advanced >>"), array("onclick"=>"DisplayHidden('div1');"));
	
	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
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
	
	$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE `trashed` = '0' AND id = '".$_GET["index"]."'");
	$svc_id =& $DBRESULT2->fetchRow();
	$DBRESULT2->free();
	
	if ($is_admin || (!$is_admin && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_description  FROM index_data WHERE `trashed` = '0' AND host_name = '".$svc_id["host_name"]."' ORDER BY service_description");
		$other_services = array();
		while ($selected_service =& $DBRESULT2->fetchRow()){
			if (preg_match("/meta_([0-9]*)/", $selected_service["service_description"], $matches)){
				$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
				$meta =& $DBRESULT_meta->fetchRow();
				$selected_service["service_description"] = $meta["meta_name"];
			}	
			$selected_service["service_description"] = str_replace("#S#", "/", $selected_service["service_description"]);
			$selected_service["service_description"] = str_replace("#BS#", "\\", $selected_service["service_description"]);
			$other_services[$selected_service["id"]] = $selected_service["service_description"];
		}
		$DBRESULT2->free();
		$form->addElement('select', 'index', 'Others Services', $other_services);
		
		$service_id = $svc_id["service_id"];
		$index_id = $svc_id["id"];
		
		if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			$meta =& $DBRESULT_meta->fetchRow();
			$svc_id["service_description"] = $meta["meta_name"];
		}	
		
		$svc_id["service_description"] = str_replace("#S#", "/", str_replace("#BS#", "\\", $svc_id["service_description"]));
		
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY `metric_name`");
		$counter = 0;
		while ($metrics_ret =& $DBRESULT2->fetchRow()){			
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace("#S#", "/", $metrics_ret["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace("#BS#", "\\", $metrics[$metrics_ret["metric_id"]]["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_id"] = $metrics_ret["metric_id"];
			$metrics[$metrics_ret["metric_id"]]["class"] = $tab_class[$counter % 2];
			$counter++;
		}
	
		if (isset($period) && $period){
			$start = time() - ($period + 30);
			$end = time() + 1;
		} else if (!isset($_GET["period"])){
			$start = $_GET["start"];
			$end = $_GET["end"];
		} else {
			$start = $_GET["start"];
			$end = $_GET["end"];	
		}
		
		if (isset($_GET["template_id"]))
			$tpl->assign('template_id', $_GET["template_id"]);				
		
		# verify if metrics in parameter is for this index
		$metrics_active =& $_GET["metric"];
		$pass = 0;
		if (isset($metrics_active))
			foreach ($metrics_active as $key => $value)
				if (isset($metrics[$key]))
					$pass = 1;
		# 
		
		if (isset($_GET["metric"]) && $pass){
			$tpl->assign('metric_active', $metrics_active);	
			$DBRESULT =& $pearDB->query("DELETE FROM `ods_view_details` WHERE index_id = '".$_GET["index"]."'");
			foreach ($metrics_active as $key => $metric){
				if (isset($metrics_active[$metric["metric_id"]])){
					$DBRESULT =& $pearDB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$key."', '".$oreon->user->user_id."', '0', '".$_GET["index"]."');");
				}
			}
		} else {
			$DBRESULT =& $pearDB->query("SELECT metric_id FROM `ods_view_details` WHERE index_id = '".$_GET["index"]."' AND `contact_id` = '".$oreon->user->user_id."'");
			$metrics_active = array();
			if ($DBRESULT->numRows())
				while ($metric =& $DBRESULT->fetchRow())
					$metrics_active[$metric["metric_id"]] = 1;		
			else
				foreach ($metrics as $key => $value)
					$metrics_active[$key] = 1;	
		}
		
		#Apply a template definition
		
		$tips = _("Tips : ");
		$tipsMsg = _("You can disable a data source in order to stop them from appearing on the graph. <br>This could help when two data sources are from the same service and for which the scales are completely different.");		
		
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('p', $p);
		
		if ($svc_id["host_name"] == "_Module_Meta")
			$svc_id["host_name"] = "Meta Services";
		$tpl->assign('host_name', $svc_id);
		
		$tpl->assign('admin', $oreon->user->admin);
		
		$tpl->assign('metrics', $metrics);
		$tpl->assign('nb_metrics', count($metrics));
		$tpl->assign('metrics_active', $metrics_active);
		
		$tpl->assign('start', $start);
		$tpl->assign('end', $end);
		$tpl->assign('isAvl', 1);
		$tpl->assign('index', $_GET["index"]);
		$tpl->assign('session_id', session_id());
		$tpl->assign('tips', $tips);
		$tpl->assign('tipsMsg', $tipsMsg);
		$tpl->display("graphODSServiceZoom.ihtml");
	}
?>