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

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
		
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
	if (isset($_GET["start"])){
		$startF =& $form->addElement('hidden', 'start');
		$startF->setValue($_GET["start"]);
	}
	if (isset($_GET["end"])){
		$endF =& $form->addElement('hidden', 'end');
		$endF->setValue($_GET["end"]);
	}

	$form->addElement('select', 'template_id', $lang["giv_gg_tpl"], $graphTs);
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
	
	if (isset($_GET["template_id"]))
		$tpl->assign('template_id', $_GET["template_id"]);				
	
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	$tpl->assign('host_name', $svc_id);
	if (isset($_GET["start"]))
		$tpl->assign('start', $_GET["start"]);
	if (isset($_GET["end"]))
		$tpl->assign('end', $_GET["end"]);
	$tpl->assign('isAvl', 1);
	$tpl->assign('lang', $lang);
	$tpl->assign('index', $_GET["index"]);
	$tpl->assign('session_id', session_id());
	$tpl->display("graphODSServiceZoom.ihtml");
?>