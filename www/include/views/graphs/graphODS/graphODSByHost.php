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
	
	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"50");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));
	
	## Database retrieve information for differents elements list we need on the page
	#   Resources comes from DB -> Store in $ppHosts Array
	# Get all host_list
	
	$ppHosts = array( NULL => NULL );
	$rq = "SELECT DISTINCT host_name FROM index_data ".(!$is_admin ? "WHERE host_id IN ($LcaHostStr) AND " : "WHERE ")."  `trashed` = '0' ORDER BY `host_name`";
	$DBRESULT =& $pearDBO->query($rq);
	while ($hostInOreon =& $DBRESULT->fetchRow()){
		if ($hostInOreon["host_name"] == "_Module_Meta")
			$ppHosts[$hostInOreon["host_name"]] = "Meta Services";
		else if (!ereg("^_Module_", $hostInOreon["host_name"]))
			$ppHosts[$hostInOreon["host_name"]] = $hostInOreon["host_name"];
	}
	$DBRESULT->free();

	if (isset($_GET["host_name"])){
		$ppServices = array( NULL => NULL );
		$rq = "SELECT service_description,host_name FROM index_data WHERE `trashed` = '0' AND host_name = '".$_GET["host_name"]."' ORDER BY `service_description`";
		$DBRESULT =& $pearDBO->query($rq);
		while ($svc =& $DBRESULT->fetchRow()){
			$ppServices[$svc["service_description"]] = $svc["service_description"];
		}
		$DBRESULT->free();
	} else if (isset($_GET["host_id"])) {
		$ppServices = array( NULL => NULL );
		$rq = "SELECT service_description,host_name FROM index_data WHERE `trashed` = '0' AND host_id = '".$_GET["host_id"]."' ORDER BY `service_description`";
		$DBRESULT =& $pearDBO->query($rq);
		while ($svc =& $DBRESULT->fetchRow()){
			$ppServices[$svc["service_description"]] = $svc["service_description"];
		}
		$DBRESULT->free();
	}

	$graphTs = array(NULL => NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id,name FROM giv_graphs_template ORDER BY name");
	while ($graphT =& $DBRESULT->fetchRow())
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
	## Indicator basic information

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);
	
	if (isset($_GET["host_id"])){
		$minF =& $form->addElement('hidden', 'host_id');
		$minF->setValue($_GET["host_id"]);
	}
	
	$host_nameForm =& $form->addElement('select', 'host_name', _("Host"), $ppHosts, array("onChange"=>"this.form.submit()"));
	$form->addElement('select', 'template_id', _("Template"), $graphTs);

	$form->addElement('text', 'start', _("Begin Date"));
	$form->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));

	$form->addElement('text', 'end', _("End Date"));
	$form->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));

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
	$subC =& $form->addElement('submit', 'submitC', _("Graph"));
	
	$form->addElement('reset', 'reset', _("Reset"));
  	$form->addElement('button', 'advanced', _("Advanced >>"), array("onclick"=>"DisplayHidden('div1');"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (!$DBRESULT->numRows())
		print "<div class='msg' align='center'>"._("There is no graph template : please configure your graph template in order to display graphs correctly.")."</div>";	
		
	if ($form->validate() && (isset($_GET["host_name"]) || isset($_GET["host_id"]))){
		if ($is_admin || (!$is_admin && ((isset($_GET["host_name"]) && isset($lcaHostByName["LcaHost"][$_GET["host_name"]]))||(isset($_GET["host_id"]) && isset($lcaHostByName["LcaHost"][$_GET["host_id"]]))))) {
			# Init variable in the page
			$label = NULL;
			$tpl->assign("title2", _("Graph Renderer"));
			if (isset($graph))
				$tpl->assign("graph", $graph["name"]);
			$tpl->assign("lgGraph", _("Template Name"));
			$tpl->assign("lgMetric", _("Metric"));
			$tpl->assign("lgCompoTmp", _("Template Name"));
			
			$elem = array();
			if (isset($_GET["host_name"])){
				$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_name = '".str_replace(" ", "\ ", $_GET["host_name"])."' AND `trashed` = '0' ORDER BY service_description");
			} else if (isset($_GET["host_id"])){
				$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_id = '".$_GET["host_id"]."' AND `trashed` = '0' ORDER BY service_description");
			}
			
			while ($index_data =& $DBRESULT->fetchRow()){
				
				$template_id = getDefaultGraph($index_data["service_id"], 1);
				$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
				$GraphTemplate =& $DBRESULT2->fetchRow();
						
				if (isset($_GET["host_name"])){
					$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description FROM index_data WHERE host_name = '".$_GET["host_name"]."' AND service_description = '".$index_data["service_description"]."' ORDER BY `service_description`");	
				} else if (isset($_GET["host_id"])){
					$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE host_id = '".$_GET["host_id"]."' AND service_description = '".$index_data["service_description"]."' ORDER BY `service_description`");	
				}
				$svc_id =& $DBRESULT2->fetchrow();
				$DBRESULT->free();
				
				$service_id = $svc_id["service_id"];
				$index_id = $svc_id["id"];
				if (isset($_GET["host_name"]))
					$host_name = $_GET["host_name"];
				else
					$host_name = $svc_id["host_name"];
				
				if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
					$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
					$meta =& $DBRESULT_meta->fetchrow();
					$svc_id["service_description"] = $meta["meta_name"];
				}
				
				$elem[$index_id] = array("index_id" => $index_id, "service_description" => str_replace("#S#", "/", str_replace("#BS#", "\\", $svc_id["service_description"])));
				
				$DBRESULT_view =& $pearDB->query("SELECT `metric_id` FROM `ods_view_details` WHERE `index_id` = '".$index_id."' AND `contact_id` = '".$oreon->user->user_id."'");
				while ($metric_activate =& $DBRESULT_view->fetchRow())
					$metrics_activate[$metric_activate["metric_id"]] = $metric_activate["metric_id"];
				$DBRESULT_view->free();
				
				if ($GraphTemplate["split_component"]){
					$elem[$index_id]["split"] = 1;
					$elem[$index_id]["metrics"] = array();
				}
				$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_id."' ORDER BY `metric_name`");
				while ($metrics_ret =& $DBRESULT2->fetchrow()){	
					$metrics[$metrics_ret["metric_id"]] = $metrics_ret;
					$form->addElement('checkbox', $metrics_ret["metric_name"], $metrics_ret["metric_name"]);
					if (isset($elem[$index_id]["split"]))
						#if (!isset($metrics_activate) || (isset($metrics_activate) && isset($metrics_activate[$metrics_ret["metric_id"]]) && $metrics_activate[$metrics_ret["metric_id"]])){
							$elem[$index_id]["metrics"][$metrics_ret["metric_id"]] = $metrics_ret["metric_id"];	
						#}
				}
				$DBRESULT2->free();
				
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
						$graph =& $DBRESULT2->fetchRow();
						$period = $graph["period"];
					}
				} else if ($_GET["period"])
					$period = $_GET["period"];
				
				if (!isset($start) && !isset($end)){
					$start = time() - ($period + 30);
					$end = time() + 10;
				}			
				$tpl->assign('end', $end);
				$tpl->assign('start', $start);
				if (isset($_GET["template_id"]))
					$elem[$index_id]['template_id'] = $_GET["template_id"];	
				unset($metrics_activate);
			}
		}
	}
	
	if (isset($host_name))
		$host_nameForm->setValue($host_name);
	
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	$tpl->assign('min', $min);
	$tpl->assign('isAvl', 1);
	
	$tpl->assign('sid', session_id());	
	$tpl->assign('session_id', session_id());
	
	if (isset($host_name)){
		if ($host_name == "_Module_Meta")
			$host_name = "Meta Services";
		$tpl->assign('host_name', $host_name);
	}
	if (isset($elem))
		$tpl->assign('elemArr', $elem);
	$tpl->assign('advanced', "Advanced");
	$tpl->assign('session_id', session_id());
	$tpl->display("graphODSByHost.ihtml");
?>