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
	$page =& $form->addElement('hidden', 'index');
	$page->setValue($_GET["index"]);
			
	# Verify if template exists
	$DBRESULT =& $pearDBO->query("SELECT storage_type,host_name,service_description FROM index_data WHERE id = '".$_GET["index"]."'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	$DBRESULT->fetchInto($index);
	
	# Init variable in the page
	$label = NULL;
	$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
	if (isset($graph))
		$tpl->assign("graph", $graph["name"]);
	$tpl->assign("lgGraph", $lang['giv_gt_name']);
	$tpl->assign("lgMetric", $lang['giv_ct_metric']);
	$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);
		
	$indexF =& $form->addElement('hidden', 'index');
	$indexF->setValue($_GET["index"]);
		
	$storage_type = array(0 => "RRDTool", 1 => "MySQL", 2 => "RRDTool & MySQL");	
	$tpl->assign('storage_type_possibility', $storage_type);
	$tpl->assign('storage_type', $index["storage_type"]);
	
	$form->addElement('select', 'storage_type', $lang['ods_storage_type'], $storage_type);
	$form->setDefaults($index);
	
	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	
	$tpl->assign('host_name', $index["host_name"]);
	$tpl->assign('service_description', $index["service_description"]);
	
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	
	$tpl->assign('lang', $lang);
	$tpl->assign('index', $_GET["index"]);
	$tpl->assign('session_id', session_id());
	$tpl->display("changeODSGraphProperties.ihtml");
?>