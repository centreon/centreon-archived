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

	if (!isset ($oreon))
		exit ();

	require_once "HTML/QuickForm.php";
	require_once "HTML/QuickForm/Renderer/ArraySmarty.php";
	require_once "./include/monitoring/common-Func.php";
	require_once "./class/centreonDB.class.php";

	/*
	 * DB connexion
	 */
	$broker = $oreon->broker->getBroker();
	if ($broker == "ndo") {
	    $pearDBndo = new CentreonDB("ndo");
	} elseif ($broker == "broker") {
            $pearDBndo = $pearDBO;
	}

	isset($_GET["host_name"]) 	? $host_name = htmlentities($_GET["host_name"], ENT_QUOTES, "UTF-8") : $host_name = NULL;
	isset($_GET["cmd"]) 		? $cmd = htmlentities($_GET["cmd"], ENT_QUOTES, "UTF-8") : $cmd = NULL;
	isset($_GET["en"]) 			? $en = htmlentities($_GET["en"], ENT_QUOTES, "UTF-8") : $en = 1;

	$path = "./include/monitoring/acknowlegement/";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, './templates/');

	if (!$is_admin) {
		$lcaHostByName = $oreon->user->access->getHostServicesName($pearDBndo);
    }

	if ($is_admin || (isset($lcaHostByName[$host_name]))) {
		$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p."&host_name=".urlencode($host_name));

		$form->addElement('header', 'title', _("Acknowledge a host"));

		$tpl->assign('hostlabel', _("Host Name"));
		$tpl->assign('hostname', $host_name);
		$tpl->assign('en', $en);
		$tpl->assign('authorlabel', _("Alias"));
		$tpl->assign('authoralias', $oreon->user->get_alias());

		$ckbx[] = $form->addElement('checkbox', 'notify', _("Notify"));
                if (isset($oreon->optGen['monitoring_ack_notify']) && $oreon->optGen['monitoring_ack_notify']) {
                    $ckbx[0]->setChecked(true);
                }

		$ckbx1[] = $form->addElement('checkbox', 'persistent', _("Persistent"));
                if (isset($oreon->optGen['monitoring_ack_persistent']) && $oreon->optGen['monitoring_ack_persistent']) {
                    $ckbx1[0]->setChecked(true);
                }

		$ckbx2[] = $form->addElement('checkbox', 'ackhostservice', _("Acknowledge services attached to hosts"));
                if (isset($oreon->optGen['monitoring_ack_svc']) && $oreon->optGen['monitoring_ack_svc']) {
                    $ckbx2[0]->setChecked(true);
                }

		$ckbx3[] = $form->addElement('checkbox', 'sticky', _("Sticky"));
                if (isset($oreon->optGen['monitoring_ack_sticky']) && $oreon->optGen['monitoring_ack_sticky']) {
                    $ckbx3[0]->setChecked(true);
                }

		$form->addElement('hidden', 'host_name', $host_name);
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
		$form->addElement('hidden', 'en', $en);

		$textarea = $form->addElement('textarea', 'comment', _("Comment"), array("rows"=>"8", "cols"=>"80"));
		$textarea->setValue(sprintf(_("Acknowledged by %s"), $oreon->user->get_alias()));

		$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
		$form->setJsWarnings(_("Invalid information entered"),_("Please correct these fields"));

		$form->addElement('submit', 'submit', ($en == 1) ? _("Add") : _("Delete"));
		$form->addElement('reset', 'reset', _("Reset"));

		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', 'hd');
		$tpl->display("hostAcknowledge.ihtml");
	}
?>