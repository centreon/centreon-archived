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
 
	if (!isset($oreon))
		exit();

	require_once $path."/DB-Func.php";
	
	/*
	 * Database retrieve information
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `contact_param` WHERE `cp_contact_id` IS NULL");

	$params = array();
	$params["dayList"] = array();
	while ($param = $DBRESULT->fetchRow())
		if ($param["cp_key"] != "report_hour_start" 
			&& $param["cp_key"] != "report_hour_end" 
			&& $param["cp_key"] != "report_minute_start" 
			&& $param["cp_key"] != "report_minute_end") {
			if ($param["cp_value"] == 1){
				$params["dayList"][$param["cp_key"]] = true;
			} else {
				$params["dayList"][$param["cp_key"]] = false;
			}
		} else {
			$params[$param["cp_key"]] = $param["cp_value"];
		}

	/*
	 * Format template
	 */
	$attrsText 		= array("size"=>"2", "maxlength"=>"2");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	$form->setDefaults($param);

	/*
	 * Debug information
	 */
	$form->addElement('header', 'title', _("Reporting time period"));

	$form->addElement('text', 'report_hour_start', 		_("Start Hour"), $attrsText);
	$form->addElement('text', 'report_minute_start', 	_("Start Minute"), $attrsText);
	$form->addElement('text', 'report_hour_end', 		_("End Hour"), $attrsText);
	$form->addElement('text', 'report_minute_end', 		_("End Minute"), $attrsText);

	$Opt = array();
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Monday', 	'&nbsp;', _("Monday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Tuesday', 	'&nbsp;', _("Tuesday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Wednesday', '&nbsp;', _("Wednesday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Thursday', 	'&nbsp;', _("Thursday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Friday', 	'&nbsp;', _("Friday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Saturday', 	'&nbsp;', _("Saturday"));
 	$Opt[] = HTML_QuickForm::createElement('checkbox', 'report_Sunday', 	'&nbsp;', _("Sunday"));
	$form->addGroup($Opt, 'dayList', _("Days") , '&nbsp;&nbsp;');


	/*
	 * Defined Rules
	 */	
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}

	$form->addElement('hidden', 'gopt_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$form->setDefaults($params);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'reporting/', $tpl);


	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

	/*
	 * Check Formulary
	 */
    $valid = false;
	if ($form->validate())	{
		/*
		 * Update in DB
		 */
		updateReportingTimePeriodInDB();
		$o = "reporting";
   		$valid = true;
		$form->freeze();
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=reporting'"));
	
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
	$tpl->display("form.ihtml");
?>