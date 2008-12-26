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
		$lcaHostByID 	= getLcaHostByID($pearDB);
		$lcaHostByName 	= getLcaHostByName($pearDB);
		$LcaHostStr 	= getLcaHostStr($lcaHostByID["LcaHost"]);
	}
	
	$debug 			= 0;
	$msg_error 		= 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"50");
	$tab_class 		= array("1" => "list_one", "0" => "list_two");
	$split 			= 0;
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));
		
	$graphTs = array( NULL => NULL );
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($graphTs =& $DBRESULT->fetchRow())
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
	## Indicator basic information
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);

	$form->addElement('select', 'template_id', _("Template"), $graphTs);
	$subC =& $form->addElement('submit', 'submitC', _("Graph"));
	
	$form->addElement('reset', 'reset', _("Reset"));
  	$form->addElement('button', 'advanced', _("Advanced >>"), array("onclick"=>"DisplayHidden('div1');"));

	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", _("Graph Renderer"));
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", _("Template Name"));
	$tpl->assign("lgMetric", _("Metric"));
	$tpl->assign("lgCompoTmp", _("Template Name"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows()){
		print "<div class='msg' align='center'>"._("There is no graph template : please configure your graph template in order to display graphs correctly.")."</div>";	
	}
	
	$elem = array();
	if (preg_match("/([0-9]*)\_([0-9]*)/", $_GET["index"], $matches)){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_id = '".$matches[1]."' AND service_id = '".$matches[2]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$svc_id =& $DBRESULT2->fetchRow();
	} else if (isset($_GET["index"])){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND id = '".$_GET["index"]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$svc_id =& $DBRESULT2->fetchRow();
	} else if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
		$svc_desc = str_replace("/", "#S#", $_GET["service_description"]);
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_name = '".$_GET["host_name"]."' && service_description = '".$svc_desc."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$svc_id =& $DBRESULT2->fetchRow();
	}

	$template_id = getDefaultGraph($svc_id["service_id"], 1);
	$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
	$GraphTemplate =& $DBRESULT2->fetchRow();
	
	$splitTab[] = &HTML_QuickForm::createElement('radio', 'split', null, _("Yes"), '1');
	$splitTab[] = &HTML_QuickForm::createElement('radio', 'split', null, _("No"), '0');
	$form->addGroup($splitTab, 'split', _("Split Components"), '&nbsp;');

	if (($GraphTemplate["split_component"] == 1 && !isset($_GET["split"])) || (isset($_GET["split"]) && $_GET["split"]["split"] == 1)){
		$split = 1;
		$form->setDefaults(array('split' => '1'));
	} else {
		$form->setDefaults(array('split' => '0'));
	}
	$index = null;	
	if (isset($_GET["index"]))
		$index = $_GET["index"];
	else if (isset($svc_id["id"]))
		$index = $svc_id["id"];

	if ($is_admin || (!$is_admin && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){	
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_description  FROM index_data WHERE `trashed` = '0' AND host_name = '".$svc_id["host_name"]."' ORDER BY service_description");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$other_services = array();
		while ($selected_service =& $DBRESULT2->fetchRow()){
			if (preg_match("/meta_([0-9]*)/", $selected_service["service_description"], $matches)){
				$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
				if (PEAR::isError($DBRESULT_meta))
					print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
				$meta =& $DBRESULT_meta->fetchRow();
				$selected_service["service_description"] = $meta["meta_name"];
			}	
			$selected_service["service_description"] = str_replace("#S#", "/", $selected_service["service_description"]);
			$selected_service["service_description"] = str_replace("#BS#", "\\", $selected_service["service_description"]);
			$other_services[$selected_service["id"]] = $selected_service["service_description"];
		}
		$DBRESULT2->free();
		$form->addElement('select', 'index', 'Others Services', $other_services);
		$form->setDefaults($_GET);
		
		$service_id = $svc_id["service_id"];
		$index_id = $svc_id["id"];
		
		if ($service_id || $svc_id["special"]) {
			$tpl->assign('msg', NULL);
		} else {
			$tpl->assign('msg', _("No graph associated to this service."));
			$msg_error = 1;
		}	
		
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
		
		if (isset($_GET["template_id"]))
			$tpl->assign('template_id', $_GET["template_id"]);				
				
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index."' AND `hidden` = '0' ORDER BY `metric_name`");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		for ($counter = 0;$metrics_ret =& $DBRESULT2->fetchRow(); $counter++){
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace('#S#', "/", $metrics_ret["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace('#BS#', "\\", $metrics[$metrics_ret["metric_id"]]["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_id"] = $metrics_ret["metric_id"];
			$metrics[$metrics_ret["metric_id"]]["class"] = $tab_class[$counter % 2];
		}
			
		# verify if metrics in parameter is for this index
		$metrics_active =& $_GET["metric"];
		$pass = 0;
		if (isset($metrics_active))
			foreach ($metrics_active as $key => $value)
				if (isset($metrics[$key]))
					$pass = 1;
		# 
		
		if ($msg_error == 0){
			if (isset($_GET["metric"]) && $pass){
				$tpl->assign('metric_active', $metrics_active);	
				$DBRESULT =& $pearDB->query("DELETE FROM `ods_view_details` WHERE index_id = '".$index."'");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
				foreach ($metrics_active as $key => $metric){
					if (isset($metrics_active[$key])){
						$DBRESULT =& $pearDB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$key."', '".$oreon->user->user_id."', '0', '".$index."');");
						if (PEAR::isError($DBRESULT))
							print "Mysql Error : ".$DBRESULT->getDebugInfo();
					}
				}
			} else {
				$DBRESULT =& $pearDB->query("SELECT metric_id FROM `ods_view_details` WHERE index_id = '".$index."' AND `contact_id` = '".$oreon->user->user_id."'");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
				$metrics_active = array();
				if ($DBRESULT->numRows())
					while ($metric =& $DBRESULT->fetchRow())
						$metrics_active[$metric["metric_id"]] = 1;		
				else
					foreach ($metrics as $key => $value)
						$metrics_active[$key] = 1;	
			}
			$tpl->assign('metrics', $metrics);
			$tpl->assign('nb_metrics', count($metrics));
			$tpl->assign('metrics_active', $metrics_active);
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
			
		$svc_id["service_description"] = str_replace("#S#", "/", $svc_id["service_description"]);	
			
		if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			if (PEAR::isError($DBRESULT_meta))
				print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
			$meta =& $DBRESULT_meta->fetchRow();
			$svc_id["service_description"] = $meta["meta_name"];
		}	
		
		if ($split){
			$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index."' AND `hidden` = '0' ORDER BY `metric_name`");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			for ($counter = 1;$metrics_ret =& $DBRESULT2->fetchRow(); $counter++){
				if (isset($metrics_active[$metrics_ret["metric_id"]]) && $metrics_active[$metrics_ret["metric_id"]])
					$metrics_list[$metrics_ret["metric_id"]] = $counter;
			}
			$tpl->assign('metrics_list', $metrics_list);
		}
		
		$tips = _("Tips : ");
		$tipsMsg = _("You can disable a data source in order to stop them from appearing on the graph. <br>This could help when two data sources are from the same service and for which the scales are completely different.");
		
		$tpl->assign('host_name', $svc_id);
		$tpl->assign('isAvl', 1);
		$tpl->assign('index', $index_id);
		$tpl->assign('min', $min);	
		$tpl->assign('sid', session_id());	
		$tpl->assign('split', $split);	
		$tpl->assign('session_id', session_id());
		$tpl->assign('tips', $tips);
		$tpl->assign('tipsMsg', $tipsMsg);
		$tpl->assign('properties', _("Properties"));
		$tpl->display("graphODSService.ihtml");
	}
?>