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

	require_once($path."/reporting/DB-Func.php");
	#
	## Database retrieve information
	#
	$DBRESULT =& $pearDB->query("SELECT * FROM `contact_param` WHERE cp_contact_id is null");
	# Set base value

	$params = array();
	$params["dayList"] = array();
	while ($param =& $DBRESULT->fetchRow())
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
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"2", "maxlength"=>"2");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	$form->setDefaults($param);

    #
	## Debug information
	#
	$form->addElement('header', 'title', _("Reporting time period"));

	$form->addElement('text', 'report_hour_start', _("Start Hour"), $attrsText);
	$form->addElement('text', 'report_minute_start', _("Start Minute"), $attrsText);
	$form->addElement('text', 'report_hour_end', _("End Hour"), $attrsText);
	$form->addElement('text', 'report_minute_end', _("End Minute"), $attrsText);

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Monday', 	'&nbsp;', _("&nbsp;Monday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Tuesday', 	'&nbsp;', _("&nbsp;Tuesday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Wednesday', '&nbsp;', _("&nbsp;Wednesday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Thursday', 	'&nbsp;', _("&nbsp;Thursday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Friday', 	'&nbsp;', _("&nbsp;Friday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Saturday', 	'&nbsp;', _("&nbsp;Saturday"));
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', 'report_Sunday', 	'&nbsp;', _("&nbsp;Sunday"));
	$form->addGroup($Opt, 'dayList', _("Days") , '&nbsp;&nbsp;');


	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'reporting/', $tpl);

	$form->setDefaults($params);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateReportingTimePeriodInDB();
		$o = "reporting";
   		$valid = true;
		$form->freeze();
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=reporting'"));
	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("form_reporting.ihtml");
?>
