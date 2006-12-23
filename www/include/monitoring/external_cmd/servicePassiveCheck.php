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
*/

	if (!isset ($oreon))
		exit ();

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = NULL;
	isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = NULL;

	$path = $pathExternal;
	
	# HOST LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	
	if ($oreon->user->admin || !$idRestreint || (isset($lcaHostByName["LcaHost"][$host_name]) && $idRestreint)){

		#Pear library
		require_once "HTML/QuickForm.php";
		require_once 'HTML/QuickForm/advmultiselect.php';
		require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
		#
		## Form begin
		#	

		$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
		$form->addElement('header', 'title', 'Command Options');
/*
		$tpl->assign('hostlabel', $lang['h_name']);	
		$tpl->assign('hostname', $host_name);	
	
		$tpl->assign('authorlabel', $lang['cg_alias']);	
		$tpl->assign('authoralias', $oreon->user->get_alias());	
		$tpl->assign('servicelabel', $lang['sv']);
		$tpl->assign('service_description', $service_description);	
	
		$return_code = array("0" => "OK","1" => "WARNING", "3" => "UNKNOWN", "2" => "CRITICAL");
	
		$form->addElement('select', 'return_code', 'checkResult',$return_code);
		$form->addElement('text', 'output', $lang["mon_checkOutput"]);
		$form->addElement('text', 'dataPerform', $lang["mon_dataPerform"]);
	
		$form->addElement('hidden', 'host_name', $host_name);
		$form->addElement('hidden', 'service_description', $service_description);
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
	//	$form->addElement('hidden', 'o', 'svcd');
	
		$form->addElement('submit', 'submit', $lang["save"]);
		$form->addElement('reset', 'reset', $lang["reset"]);
*/		
		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl);
			
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);			
		
		$tpl->assign('form', $renderer->toArray());	
		$tpl->display(servicePassiveCheck.ihtml");
	}
?>