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

	if (!isset($oreon)) {
		exit ();
	}

	$select = array();
	if (isset($_GET['select'])) {
		foreach ($_GET['select'] as $key => $value) {
			if ($cmd == '72') {
				$tmp = preg_split("/\;/", urlencode($key));
				$select[] = $tmp[0];
			} else {
				$select[] = urlencode($key);
			}
		}
	}

	$path = "$centreon_path/www/include/monitoring/external_cmd/popup/";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTplForPopup($path, $tpl, './templates/', $centreon_path);

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	require_once $centreon_path . "www/include/monitoring/common-Func.php";

	/*
	 * Fetch default values for form
	 */
	$user_params = get_user_param($oreon->user->user_id, $pearDB);

	if (!isset($user_params["ack_sticky"]))
		$user_params["ack_sticky"] = 1;

	if (!isset($user_params["ack_notify"]))
		$user_params["ack_notify"] = 0;

	if (!isset($user_params["ack_persistent"]))
		$user_params["ack_persistent"] = 1;

	if (!isset($user_params["ack_services"]))
		$user_params["ack_services"] = 1;

	if (!isset($user_params["force_check"]))
		$user_params["force_check"] = 1;
/*
	$sticky = $user_params["ack_sticky"];
	$notify = $user_params["ack_notify"];
	$persistent = $user_params["ack_persistent"];
	$force_check = $user_params["force_check"];
	$ack_services = $user_params["ack_services"];
*/
	$form = new HTML_QuickForm('select_form', 'GET', 'main.php');

	$form->addElement('header', 'title', _("Acknowledge problems"));

	$tpl->assign('authorlabel', _("Alias"));
	$tpl->assign('authoralias', $oreon->user->get_alias());

	$form->addElement('textarea', 'comment', _("Comment"), array("rows"=>"5", "cols"=>"85", "id"=>"popupComment"));
	$form->setDefaults(array("comment" => sprintf(_("Acknowledged by %s"), $oreon->user->alias)));

	$chckbox[] = $form->addElement('checkbox', 'persistent', _("Persistent"), "", array("id"=>"persistent"));
	if (isset($oreon->optGen['monitoring_ack_persistent']) && $oreon->optGen['monitoring_ack_persistent']) {
	    $chckbox[0]->setChecked(true);
	}

	$chckbox2[] = $form->addElement('checkbox', 'ackhostservice', _("Acknowledge services attached to hosts"), "", array("id"=>"ackhostservice"));
	if (isset($oreon->optGen['monitoring_ack_svc']) && $oreon->optGen['monitoring_ack_svc']) {
	    $chckbox2[0]->setChecked(true);
	}

	$chckbox3[] = $form->addElement('checkbox', 'sticky', _("Sticky"), "", array("id"=>"sticky"));
	if (isset($oreon->optGen['monitoring_ack_sticky']) && $oreon->optGen['monitoring_ack_sticky']) {
	    $chckbox3[0]->setChecked(true);
	}

	$chckbox4[] = $form->addElement('checkbox', 'force_check', _("Force active checks"), "", array("id"=>"force_check"));
	if (isset($oreon->optGen['monitoring_ack_active_checks']) && $oreon->optGen['monitoring_ack_active_checks']) {
	    $chckbox4[0]->setChecked(true);
	}

	$chckbox5[] = $form->addElement('checkbox', 'notify', _("Notify"), "", array("id"=>"notify"));
	if (isset($oreon->optGen['monitoring_ack_notify']) && $oreon->optGen['monitoring_ack_notify']) {
	    $chckbox5[0]->setChecked(true);
	}

	$form->addElement('hidden', 'author', $oreon->user->get_alias(), array("id"=>"author"));

	$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
	$form->setJsWarnings(_("Invalid information entered"), _("Please correct these fields"));

	$form->addElement('button', 'submit', _("Acknowledge selected problems"), array("onClick" => "send_the_command();"));
	$form->addElement('reset', 'reset', _("Reset"));

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());

	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	$tpl->assign('cmd', $cmd);
	$tpl->assign('select', $select);
	$tpl->display("massive_ack.ihtml");
?>