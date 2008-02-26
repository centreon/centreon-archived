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
		$lcaHostByID = getLcaHostByID($pearDB);
		$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
	}
	
	function updateServiceStorageType($index){
		global $form, $pearDBO;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `index_data` SET `storage_type` = '".$ret["storage_type"]."' WHERE `id` = '".$index."' LIMIT 1 ;";
		$DBRESULT =& $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
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
	$page =& $form->addElement('hidden', 'index');
	$page->setValue($_GET["index"]);
	if (isset($_GET["end"])){
		$page =& $form->addElement('hidden', 'end');
		$page->setValue($_GET["end"]);
	}
	if (isset($_GET["start"])){
		$page =& $form->addElement('hidden', 'start');
		$page->setValue($_GET["start"]);
	}
				
	# Verify if template exists
	$DBRESULT =& $pearDBO->query("SELECT storage_type,host_name,service_description FROM index_data WHERE id = '".$_GET["index"]."'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	$DBRESULT->fetchInto($index);
	
	if ($index["host_name"] == "Meta_Module")
		$index["host_name"] = "Meta Services";
	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", _("Graph Renderer"));
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", _("Template Name"));
	$tpl->assign("lgMetric", _("Metric"));
	$tpl->assign("lgCompoTmp", _("Template Name"));
		
	$indexF =& $form->addElement('hidden', 'index');
	$indexF->setValue($_GET["index"]);
		
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");	
	$tpl->assign('storage_type_possibility', $storage_type);
	$tpl->assign('storage_type', $index["storage_type"]);
	
	$form->addElement('select', 'storage_type', _("Storage Type"), $storage_type);
	$form->setDefaults($index);
	
	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$form->addElement('reset', 'reset', _("Reset"));
    $valid = false;
	
	if (isset($_GET["submitC"]) && isset($_GET["storage_type"]))	{
		# Update in DB
		updateServiceStorageType($_GET["index"]);
		# Update in Oreon Object
		
		$DBRESULT =& $pearDBO->query("SELECT storage_type,host_name,service_description FROM index_data WHERE id = '".$_GET["index"]."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT->fetchInto($index);
	}
	
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	
	if ($index["host_name"] == "Meta_Module")
		$index["host_name"] = "Meta Services";
	$tpl->assign('host_name', $index["host_name"]);
	if (preg_match("/meta_([0-9]*)/", $index["service_description"], $matches)){
		$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
		if (PEAR::isError($DBRESULT_meta))
			print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
		$DBRESULT_meta->fetchInto($meta);
		$index["service_description"] = $meta["meta_name"];
	}
	$tpl->assign('service_description', str_replace("#S#", "/", str_replace("#BS#", "\\", $index["service_description"])));
	
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	if (isset($_GET["start"]))
		$tpl->assign('start', $_GET["start"]);
	if (isset($_GET["end"]))
		$tpl->assign('end', $_GET["end"]);
	
	$tpl->assign('admin', $oreon->user->admin);
	
	$tpl->assign('lang', $lang);
	$tpl->assign('index', $_GET["index"]);
	$tpl->assign('session_id', session_id());
	$tpl->display("changeODSGraphProperties.ihtml");
?>