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

	$DBRESULT = $pearDB->query("SELECT * FROM `options`");
	while ($opt = $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
	}

	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	/*
	 * Nagios information
	 */
	$form->addElement('header', 'nagios', _("Nagios information"));
	$form->addElement('text', 'nagios_path_img', _("Images Directory"), $attrsText);
	$form->addElement('text', 'nagios_path_plugins', _("Plugins Directory"), $attrsText);
	$form->addElement('text', 'mailer_path_bin', _("Directory + Mailer Binary"), $attrsText);
	$form->addElement('select', 'monitoring_engine', _("Default Engine"), array("ICINGA" => "Icinga", "NAGIOS" => "Nagios", "SHINKEN" => "Shinken"));
	$form->addElement('select', 'broker', _("Broker engine used by Centreon"), array("ndo" => "NDOutils", "broker" => "Centreon Broker"));

	/*
	 * Acknowledgement form
	 */
    $form->addElement('checkbox', 'monitoring_ack_sticky', _("Sticky"));
    $form->addElement('checkbox', 'monitoring_ack_notify', _("Notify"));
    $form->addElement('checkbox', 'monitoring_ack_persistent', _("Persistent"));
    $form->addElement('checkbox', 'monitoring_ack_active_checks', _("Force Active Checks"));
    $form->addElement('checkbox', 'monitoring_ack_svc', _("Acknowledge services attached to hosts"));

    /*
     * Downtime form
     */
    $form->addElement('checkbox', 'monitoring_dwt_fixed', _("Fixed"));
    $form->addElement('checkbox', 'monitoring_dwt_svc', _("Set downtimes on services attached to hosts"));
    $form->addElement('text', 'monitoring_dwt_duration', _("Duration"), $attrsText2);

	$form->addElement('hidden', 'gopt_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */

	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('nagios_path', 'slash');
	$form->applyFilter('nagios_path_img', 'slash');
	$form->applyFilter('nagios_path_plugins', 'slash');

	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');

	$form->addRule('nagios_path_img', _("The directory isn't valid"), 'is_valid_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."/nagios", $tpl);

	if (!isset($gopt["monitoring_engine"])) {
		$gopt["monitoring_engine"] = "NAGIOS";
	}

	$form->setDefaults($gopt);

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		/*
		 * Update in DB
		 */
		updateNagiosConfigData($form->getSubmitValue("gopt_id"));

		/*
		 * Update in Oreon Object
		 */
		$oreon->initOptGen($pearDB);

		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("impossible to validate, one or more field is incorrect")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=nagios'"));

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_nagios_version", _("Monitoring Engine"));
	$tpl->assign("genOpt_dbLayer", _("Monitoring database layer"));
	$tpl->assign("genOpt_nagios_direstory", _("Engine Directories"));
	$tpl->assign("genOpt_mailer_path", _("Mailer path"));
	$tpl->assign("genOpt_monitoring_properties", "Monitoring properties");
    $tpl->assign("acknowledgement_default_settings", _("Default acknowledgement settings"));
    $tpl->assign("downtime_default_settings", _("Default downtime settings"));
    $tpl->assign("seconds", _("seconds"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
