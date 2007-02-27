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
**/

	if (!isset($oreon))
		exit();

	# LCA 
	if ($isRestreint){
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
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
		
	$graphTs = array( NULL => NULL );
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

	$form->addElement('select', 'template_id', $lang["giv_gg_tpl"], $graphTs);
	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	
	$form->addElement('reset', 'reset', $lang["reset"]);
  	$form->addElement('button', 'advanced', $lang["advanced"], array("onclick"=>"DisplayHidden('div1');"));

	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", $lang['giv_gt_name']);
	$tpl->assign("lgMetric", $lang['giv_ct_metric']);
	$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		$nb_rsp = 0;

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows())
		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";	
		
	$elem = array();
	if (preg_match("/([0-9]*)\_([0-9]*)/", $_GET["index"], $matches)){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE host_id = '".$matches[1]."' AND service_id = '".$matches[2]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$DBRESULT2->fetchInto($svc_id);
	} else {
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE id = '".$_GET["index"]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$DBRESULT2->fetchInto($svc_id);
	}
	
	$service_id = $svc_id["service_id"];
	$index_id = $svc_id["id"];
	
	$indexF =& $form->addElement('hidden', 'index');
	$indexF->setValue($index_id);
	
	if (!$service_id) $tpl->assign('msg', $lang["no_graph_found"]); else $tpl->assign('msg', NULL);	
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
			
	$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY `metric_name`");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	$counter = 0;
	while ($DBRESULT2->fetchInto($metrics_ret)){
		$metrics[$metrics_ret["metric_id"]]["metric_name"] = $metrics_ret["metric_name"];
		$metrics[$metrics_ret["metric_id"]]["metric_id"] = $metrics_ret["metric_id"];
		$metrics[$metrics_ret["metric_id"]]["class"] = $tab_class[$counter % 2];
		$counter++;
	}
		
	if (isset($_GET["metric"])){
		$metrics_active =& $_GET["metric"];
		$tpl->assign('metric_active', $metrics_active);	
		$DBRESULT =& $pearDB->query("DELETE FROM `ods_view_details` WHERE index_id = '".$_GET["index"]."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		foreach ($metrics_active as $key => $metric){
			$DBRESULT =& $pearDB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$key."', '".$oreon->user->user_id."', '0', '".$_GET["index"]."');");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
		}
	} else {
		$DBRESULT =& $pearDB->query("SELECT metric_id FROM `ods_view_details` WHERE index_id = '".$_GET["index"]."' AND `contact_id` = '".$oreon->user->user_id."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$metrics_active = array();
		if ($DBRESULT->numRows())
			while ($DBRESULT->fetchInto($metric))
				$metrics_active[$metric["metric_id"]] = 1;		
		else
			foreach ($metrics as $key => $value)
				$metrics_active[$key] = 1;	
	}
	
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	$tpl->assign('host_name', $svc_id);

	$tpl->assign('metrics', $metrics);
	$tpl->assign('metrics_active', $metrics_active);

	$tpl->assign('isAvl', 1);
	$tpl->assign('lang', $lang);
	$tpl->assign('index', $index_id);
	$tpl->assign('min', $min);	
	$tpl->assign('session_id', session_id());
	$tpl->display("graphODSService.ihtml");
?>