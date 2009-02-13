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

	$o = "svcd";

	if (!isset ($oreon))
		exit ();

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = NULL;
	isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = NULL;

	$path = "./include/monitoring/submitPassivResults/";
	
	# HOST LCA
	if (!$is_admin){
		$lcaHostByName = getLcaHostByName($pearDB);
	}
	
	if ($is_admin || (isset($lcaHostByName["LcaHost"][$host_name]) && !$is_admin)){

		#Pear library
		require_once "HTML/QuickForm.php";
		require_once 'HTML/QuickForm/advmultiselect.php';
		require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
		$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
		$form->addElement('header', 'title', 'Command Options');

		$hosts = array($host_name=>$host_name);

		$DBRESULT =& $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$host_name."' ORDER BY host_name");
		$host =& $DBRESULT->fetchRow();
		$host_id = $host["host_id"];
		
		$services = array();
		if (isset($host_id))
			$services_id = getMyHostServices($host_id);
		
		$services = array();	
		foreach ($services_id as $id => $value){
			$svc_desc = getMyServiceName($id);
			$services[$svc_desc] = $svc_desc;
		}
		
		$form->addElement('select', 'host_name', _("Host Name"), $hosts, array("onChange" =>"this.form.submit();"));
		$form->addElement('select', 'service_description', _("Services"), $services);
	   	
		$form->addRule('host_name', _("Required Field"), 'required');
		$form->addRule('service_description', _("Required Field"), 'required');
	
		$return_code = array("0" => "OK","1" => "WARNING", "3" => "UNKNOWN", "2" => "CRITICAL");
	
		$form->addElement('select', 'return_code', 'checkResult',$return_code);
		$form->addElement('text', 'output', _("Check output"), array("size"=>"100"));
		$form->addElement('text', 'dataPerform', _("Performance data"), array("size"=>"100"));
	
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
	
		$form->addElement('submit', 'submit', _("Save"));
		$form->addElement('reset', 'reset', _("Reset"));
		
		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl);
			
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);			
		
		$tpl->assign('form', $renderer->toArray());	
		$tpl->display("servicePassiveCheck.ihtml");
	}
?>