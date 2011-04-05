<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
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