<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	$rq = "SELECT DISTINCT host_name FROM index_data ".($isRestreint && !$oreon->user->admin ? "WHERE host_id IN ($LcaHostStr) AND " : "WHERE ")."  `trashed` = '0' ORDER BY `host_name`";
	$DBRESULT =& $pearDBO->query($rq);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($DBRESULT->fetchInto($hostInOreon)){
		if ($hostInOreon["host_name"] == "Meta_Module")
			$ppHosts[$hostInOreon["host_name"]] = "Meta Services";
		else if ($hostInOreon["host_name"] != "OSL_Module")
			$ppHosts[$hostInOreon["host_name"]] = $hostInOreon["host_name"];
	}
	$DBRESULT->free();

	if (isset($_GET["host_name"])){
		$ppServices = array( NULL => NULL );
		$rq = "SELECT service_description,host_name FROM index_data WHERE `trashed` = '0' AND host_name = '".$_GET["host_name"]."' ORDER BY `service_description`";
		$DBRESULT =& $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($DBRESULT->fetchInto($svc)){
			$ppServices[$svc["service_description"]] = $svc["service_description"];
		}
		$DBRESULT->free();
	} else if (isset($_GET["host_id"])) {
		$ppServices = array( NULL => NULL );
		$rq = "SELECT service_description,host_name FROM index_data WHERE `trashed` = '0' AND host_id = '".$_GET["host_id"]."' ORDER BY `service_description`";
		$DBRESULT =& $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($DBRESULT->fetchInto($svc)){
			$ppServices[$svc["service_description"]] = $svc["service_description"];
		}
		$DBRESULT->free();
	}

	$graphTs = array(NULL => NULL);
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
	
	if (isset($_GET["host_id"])){
		$minF =& $form->addElement('hidden', 'host_id');
		$minF->setValue($_GET["host_id"]);
	}
	
	$host_nameForm =& $form->addElement('select', 'host_name', $lang["h"], $ppHosts, array("onChange"=>"this.form.submit()"));
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
	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	
	$form->addElement('reset', 'reset', $lang["reset"]);
  	$form->addElement('button', 'advanced', $lang["advanced"], array("onclick"=>"DisplayHidden('div1');"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows())
		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";	
		
	if ($form->validate() && (isset($_GET["host_name"]) || isset($_GET["host_id"]))){
		if (!$isRestreint || ($isRestreint && ((isset($_GET["host_name"]) && isset($lcaHostByName["LcaHost"][$_GET["host_name"]]))||(isset($_GET["host_id"]) && isset($lcaHostByName["LcaHost"][$_GET["host_id"]]))))) {
			# Init variable in the page
			$label = NULL;
			$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
			if (isset($graph))
				$tpl->assign("graph", $graph["name"]);
			$tpl->assign("lgGraph", $lang['giv_gt_name']);
			$tpl->assign("lgMetric", $lang['giv_ct_metric']);
			$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);
			
			$elem = array();
			if (isset($_GET["host_name"])){
				$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_name = '".str_replace(" ", "\ ", $_GET["host_name"])."' AND `trashed` = '0' ORDER BY service_description");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
			} else if (isset($_GET["host_id"])){
				$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_id = '".$_GET["host_id"]."' AND `trashed` = '0' ORDER BY service_description");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
			}
			
			while ($DBRESULT->fetchInto($index_data)){
				
				$template_id = getDefaultGraph($index_data["service_id"], 1);
				$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
				$DBRESULT2->fetchInto($GraphTemplate);
						
				if (isset($_GET["host_name"])){
					$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description FROM index_data WHERE host_name = '".$_GET["host_name"]."' AND service_description = '".$index_data["service_description"]."' ORDER BY `service_description`");	
				} else if (isset($_GET["host_id"])){
					$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE host_id = '".$_GET["host_id"]."' AND service_description = '".$index_data["service_description"]."' ORDER BY `service_description`");	
				}
				if (PEAR::isError($DBRESULT2))
					print "Mysql Error : ".$DBRESULT2->getDebugInfo();
				$DBRESULT2->fetchInto($svc_id);
				$service_id = $svc_id["service_id"];
				$index_id = $svc_id["id"];
				if (isset($_GET["host_name"]))
					$host_name = $_GET["host_name"];
				else
					$host_name = $svc_id["host_name"];
				
				if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
					$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
					if (PEAR::isError($DBRESULT_meta))
						print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
					$DBRESULT_meta->fetchInto($meta);
					$svc_id["service_description"] = $meta["meta_name"];
				}
				
				$elem[$index_id] = array("index_id" => $index_id, "service_description" => str_replace("#S#", "/", str_replace("#BS#", "\\", $svc_id["service_description"])));
				
				if ($GraphTemplate["split_component"]){
					$elem[$index_id]["split"] = 1;
					$elem[$index_id]["metrics"] = array();
				}
				$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_id."' ORDER BY `metric_name`");
				if (PEAR::isError($DBRESULT2))
					print "Mysql Error : ".$DBRESULT2->getDebugInfo();
				while ($DBRESULT2->fetchInto($metrics_ret)){	
					$metrics[$metrics_ret["metric_id"]] = $metrics_ret;
					$form->addElement('checkbox', $metrics_ret["metric_name"], $metrics_ret["metric_name"]);
					if (isset($elem[$index_id]["split"]))
						$elem[$index_id]["metrics"][$metrics_ret["metric_id"]] = $metrics_ret["metric_id"];
				}
				
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
					$start = time() - ($period + 30);
					$end = time() + 10;
				}			
				$tpl->assign('end', $end);
				$tpl->assign('start', $start);
				if (isset($_GET["template_id"]))
					$elem[$index_id]['template_id'] = $_GET["template_id"];	
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
	$tpl->assign('lang', $lang);
	
	if (isset($host_name)){
		if ($host_name == "Meta_Module")
			$host_name = "Meta Services";
		$tpl->assign('host_name', $host_name);
	}
	if (isset($elem))
		$tpl->assign('elemArr', $elem);
	
	$tpl->assign('session_id', session_id());
	$tpl->display("graphODSByHost.ihtml");
?>