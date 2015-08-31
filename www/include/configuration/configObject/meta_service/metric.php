<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	#
	## Database retrieve information
	#
	require_once("./class/centreonDB.class.php");

	$pearDBO = new CentreonDB("centstorage");

	$metric = array();
	if (($o == "cs" || $o == "ws") && $msr_id)	{
		# Set base value
		$DBRESULT = $pearDB->query("SELECT * FROM meta_service_relation WHERE msr_id = '".$msr_id."'");

		# Set base value
		$metric1 = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT = $pearDBO->query("SELECT * FROM metrics, index_data WHERE metric_id = '".$metric1["metric_id"]."' and metrics.index_id = index_data.id");
		$metric2 = array_map("myDecode", $DBRESULT->fetchRow());
		$metric = array_merge($metric1, $metric2);
		$host_id = $metric1["host_id"];
		$metric["metric_sel"][0] = getMyServiceID($metric["service_description"], $metric["host_id"]);
		$metric["metric_sel"][1] = $metric["metric_id"];
	}

	#
	## Database retrieve information for differents elements list we need on the page
	#

	/*
	 * Host comes from DB -> Store in $hosts Array
	 */
    $hosts = array(null => null) + $acl->getHostAclConf(null,
                                                        $oreon->broker->getBroker(),
                                                        array('fields'  => array('host.host_id', 'host.host_name'),
                                                              'keys'    => array('host_id'),
                                                              'get_row' => 'host_name',
                                                              'order'   => array('host.host_name')),
                                                        true);

	$services1 = array(NULL => NULL);
	$services2 = array(NULL => NULL);
	if ($host_id)	{
        $services = array(null => null) + $acl->getHostServiceAclConf($host_id,
                                                                      $oreon->broker->getBroker(),
                                                                      array('fields'  => array('service_id', 'service_description'),
                                                                            'keys'    => array('service_id'),
                                                                            'get_row' => 'service_description',
                                                                            'order'   => array('service_description')));
		foreach ($services as $key => $value)	{
			$DBRESULT = $pearDBO->query("SELECT DISTINCT metric_name, metric_id, unit_name
										 FROM metrics m, index_data i
										 WHERE i.host_name = '".$pearDBO->escape(getMyHostName($host_id))."'
										 AND i.service_description = '".$pearDBO->escape($value)."'
										 AND i.id = m.index_id
										 ORDER BY metric_name, unit_name");
			while ($metricSV = $DBRESULT->fetchRow())	{
				$services1[$key] = $value;
				$metricSV["metric_name"] = str_replace("#S#", "/", $metricSV["metric_name"]);
				$metricSV["metric_name"] = str_replace("#BS#", "\\", $metricSV["metric_name"]);
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
		$form->addElement('header', 'title', _("Add a Meta Service indicator"));
	else if ($o == "cs")
		$form->addElement('header', 'title', _("Modify a Meta Service indicator"));
	else if ($o == "ws")
		$form->addElement('header', 'title', _("View a Meta Service indicator"));
	#
	## Indicator basic information
	#

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$formMsrId = $form->addElement('hidden', 'msr_id');
	$formMsrId->setValue($msr_id);
	$formMetaId = $form->addElement('hidden', 'meta_id');
	$formMetaId->setValue($meta_id);
	$formMetricId = $form->addElement('hidden', 'metric_id');
	$formMetricId->setValue($metric_id);

	$hn = $form->addElement('select', 'host_id', _("Host"), $hosts, array("onChange"=>"this.form.submit()"));
	$sel = $form->addElement('hierselect', 'metric_sel', _("Service"));
	$sel->setOptions(array($services1, $services2));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'activate', null, _("Enabled"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('activate' => '1'));
	$form->addElement('textarea', 'msr_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addRule('host_id', _("Compulsory Field"), 'required');

	function checkMetric() {
		global $form;

		$tab = $form->getSubmitValue("metric_sel");
		if (isset($tab[0]) & isset($tab[1])) {
			return 1;
		}
		return 0;
	}

	$form->registerRule('checkMetric', 'callback', 'checkMetric');
	$form->addRule('metric_sel', _("Compulsory Field"), 'checkMetric');

	/*
	 * Just watch
	 */
	if ($o == "ws")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=cs&msr_id=".$msr_id."'"));
	    $form->setDefaults($metric);
		$form->freeze();
	} else if ($o == "cs")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($metric);
	    $hn->freeze();
	    $sel->freeze();
	} else if ($o == "as")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

  	$valid = false;
	if (((isset($_POST["submitA"]) && $_POST["submitA"]) || (isset($_POST["submitC"]) && $_POST["submitC"])) && $form->validate())	{
		$msrObj = $form->getElement('msr_id');
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
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);

		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('valid', $valid);
		$tpl->display("metric.ihtml");
    }
?>
