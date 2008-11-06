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
 

	#
	## Database retrieve information
	#
	require_once("./DBOdsConnect.php");
	
	$metric = array();
	if (($o == "cs" || $o == "ws") && $msr_id)	{	
		# Set base value
		$DBRESULT =& $pearDB->query("SELECT * FROM meta_service_relation WHERE msr_id = '".$msr_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

		# Set base value
		$metric1 = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT =& $pearDBO->query("SELECT * FROM metrics, index_data WHERE metric_id = '".$metric1["metric_id"]."' and metrics.index_id=index_data.id");		
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$metric2 = array_map("myDecode", $DBRESULT->fetchRow());
		$metric = array_merge($metric1, $metric2);
		$host_id = $metric1["host_id"];
		$metric["metric_sel"][0] = getMyServiceID($metric["service_description"], $metric["host_id"]);
		
		$metric["metric_sel"][1] = $metric["metric_id"];		
	}
	
	#
	## Database retrieve information for differents elements list we need on the page
	#

	# Host comes from DB -> Store in $hosts Array
	$hosts = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host WHERE host_register = '1' AND host_activate = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	while($host =& $DBRESULT->fetchRow())
		//if ($is_admin || (!$is_admin && isset($lcaHostByName["LcaHost"][$host["host_name"]])))
			$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	
	$services1 = array();
	$services2 = array();
	if ($host_id)	{
		$services = array(NULL=>NULL);
		$services = getMyHostServices($host_id);
		foreach ($services as $key => $value)	{
			$value2 = str_replace("/", "#S#", $value);			
			$value2 = str_replace("\\", "#BS#", $value2);			
			$DBRESULT =& $pearDBO->query("SELECT DISTINCT metric_name, metric_id, unit_name FROM metrics m, index_data i WHERE i.host_name = '".getMyHostName($host_id)."' AND i.service_description = '".$value2."' and i.id=m.index_id ORDER BY metric_name, unit_name");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while ($metricSV =& $DBRESULT->fetchRow())	{
				$services1[$key] = $value;
				$services2[$key][$metricSV["metric_id"]] = $metricSV["metric_name"]."  (".$metricSV["unit_name"].")";
			}
		}
		$DBRESULT->free();
	}
	
	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	
	#
	## Form begin
	#
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "as")
		$form->addElement('header', 'title', _("Add a Service"));
	else if ($o == "cs")
		$form->addElement('header', 'title', _("Modify a Service"));
	else if ($o == "ws")
		$form->addElement('header', 'title', _("View a Service"));
	#
	## Indicator basic information
	#
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$formMsrId =& $form->addElement('hidden', 'msr_id');
	$formMsrId->setValue($msr_id);
	$formMetaId =& $form->addElement('hidden', 'meta_id');
	$formMetaId->setValue($meta_id);
	$formMetricId =& $form->addElement('hidden', 'metric_id');
	$formMetricId->setValue($metric_id);
   
	$hn =& $form->addElement('select', 'host_id', _("Host"), $hosts, array("onChange"=>"this.form.submit()"));
	$sel =& $form->addElement('hierselect', 'metric_sel', _("Server"));
	$sel->setOptions(array($services1, $services2));
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Enabled"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('activate' => '1'));
	$form->addElement('textarea', 'msr_comment', _("Comments"), $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	/*
	 * Just watch
	 */
	if ($o == "ws")	{		
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=cs&msr_id=".$msr_id."'"));
	    $form->setDefaults($metric);
		$form->freeze();
	} else if ($o == "cs")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($metric);
	    $hn->freeze();
	    $sel->freeze();
	} else if ($o == "as")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
  
  	$valid = false;
	if (((isset($_POST["submitA"]) && $_POST["submitA"]) || (isset($_POST["submitC"]) && $_POST["submitC"])) && $form->validate())	{
		$msrObj =& $form->getElement('msr_id');
		if ($form->getSubmitValue("submitA"))
			$msrObj->setValue(insertMetric($meta_id));
		else if ($form->getSubmitValue("submitC"))
			updateMetric($msrObj->getValue());
		$o = "ws";
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=cs&msr_id=".$msrObj->getValue()."'"));
		$form->freeze();
		$valid = true;
    }
    
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listMetric.php");
	else	{	
		/*
		 * Smarty template Init
		 */
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl);
			
		/*
		 * Apply a template definition	
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);
		$tpl->assign('valid', $valid);
		$tpl->display("metric.ihtml");
    }
?>