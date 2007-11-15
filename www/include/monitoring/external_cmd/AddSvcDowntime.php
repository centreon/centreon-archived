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
*/

	if (!isset($oreon))
		exit();
	
	$LCA_error = 0;
	$lcaHostByName = getLcaHostByName($pearDB);
	
	isset($_GET["host_id"]) ? $cG = $_GET["host_id"] : $cG = NULL;
	isset($_POST["host_id"]) ? $cP = $_POST["host_id"] : $cP = NULL;	
	$cG ? $host_id = $cG : $host_id = $cP;

    $svc_description = NULL;
	
	if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
		$host_id = getMyHostID($_GET["host_name"]);
		if (!isset($lcaHostByName[LcaHost] [$_GET["host_name"]]) && $isRestreint)
			$LCA_error = 1;
		$service_id = getMyServiceID($_GET["service_description"], $host_id);
		$host_name = $_GET["host_name"];
		$svc_description = $_GET["service_description"];
	} else
		$host_name = NULL;
	
	if ($LCA_error)
		require_once("./alt_error.php");
	else {
		$data = array("host_id" => $host_id, "service_id" => getMyServiceID($svc_description, $host_id),"start" => date("Y/m/d G:i" , time() + 120), "end" => date("Y/m/d G:i", time() + 7320));
		
		
		#
		## Database retrieve information for differents elements list we need on the page
		#
		$hosts = array(""=>"");
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM `host` WHERE host_register = '1' ORDER BY host_name");
		if (PEAR::isError($DBRESULT))
			print "AddSvcDonwtime RQ 1 - Mysql Error : ".$DBRESULT->getMessage();
		while ($DBRESULT->fetchInto($host)){
			if (!$host["host_name"])
				$host["host_name"] = getMyHostName($host["host_template_model_htm_id"]);
			if (IsHostReadable($lcaHostByName, $host["host_name"]))
				$hosts[$host["host_id"]]= $host["host_name"];
		}
	
		$services = array();
		if (isset($host_id))
			$services = getMyHostServices($host_id);
	
		$debug = 0;
		$attrsTextI		= array("size"=>"3");
		$attrsText 		= array("size"=>"30");
		$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
		
		#
		## Form begin
		#
		
		$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
		$form->addElement('header', 'title', $lang["dtm_addS"]);
		
		#
		## Indicator basic information
		#
		
		$redirect =& $form->addElement('hidden', 'o');
		$redirect->setValue($o);
	    
	    $selHost =& $form->addElement('select', 'host_id', $lang["cmt_host_name"], $hosts, array("onChange" =>"this.form.submit();"));
		$selSv =& $form->addElement('select', 'service_id', $lang["cmt_service_descr"], $services);
	    $form->addElement('checkbox', 'persistant', $lang["dtm_fixed"]);
		$form->addElement('textarea', 'comment', $lang["cmt_comment"], $attrsTextarea);
		
		$form->addElement('text', 'start', $lang["dtm_start_time"], $attrsText);
		$form->addElement('text', 'end', $lang["dtm_end_time"], $attrsText);
		$form->addElement('textarea', 'comment', $lang["cmt_comment"], $attrsTextarea);
		
		$form->addRule('host', $lang['ErrRequired'], 'required');
		$form->addRule('end', $lang['ErrRequired'], 'required');
		$form->addRule('start', $lang['ErrRequired'], 'required');
		$form->addRule('comment', $lang['ErrRequired'], 'required');	
		
		$form->setDefaults($data);
		
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	  
	  	if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate())	{
			if (!isset($_POST["persistant"]))
				$_POST["persistant"] = 0;
			if (!isset($_POST["comment"]))
				$_POST["comment"] = 0;
			AddSvcDowntime($_POST["host_id"], $_POST["service_id"],  $_POST["comment"], $_POST["start"], $_POST["end"], $_POST["persistant"]);
	    	require_once($path."viewDowntime.php");
		} else {
			# Smarty template Init
			$tpl = new Smarty();
			$tpl = initSmartyTpl($path, $tpl, "templates/");
	
			#Apply a template definition
			$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
			$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
			$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
			$form->accept($renderer);		
			$tpl->assign('form', $renderer->toArray());	
			$tpl->assign('o', $o);
			$tpl->display("AddSvcDowntime.ihtml");
	    }
	}
?>