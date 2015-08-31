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
		exit ();

	require_once "HTML/QuickForm.php";
	require_once "HTML/QuickForm/Renderer/ArraySmarty.php";
	require_once "./include/monitoring/common-Func.php";
	require_once "./class/centreonDB.class.php";

	$broker = $oreon->broker->getBroker();
	if ($broker == "ndo") {
	    $pearDBndo = new CentreonDB("ndo");
	} elseif ($broker == "broker") {
            $pearDBndo = $pearDBO;
	}

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = NULL;
	isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = NULL;
	isset($_GET["en"]) ? $en = $_GET["en"] : $en = 1;

	$path = "./include/monitoring/acknowlegement/";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, './templates/');

	if (!$is_admin)
		$lcaHostByName["LcaHost"] = $oreon->user->access->getHostServicesName($pearDBndo);

	/*
	 * HOST LCA
	 */
	if ($is_admin || (isset($lcaHostByName["LcaHost"][$host_name]))){
		## Form begin
		$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p."&host_name=".urlencode($host_name)."&service_description=".urlencode($service_description));
		$form->addElement('header', 'title', _("Acknowledge a Service"));

		$tpl->assign('hostlabel', _("Host Name"));
		$tpl->assign('hostname', $host_name);
		$tpl->assign('en', $en);

		$tpl->assign('servicelabel', _("Service"));
		$tpl->assign('servicedescription', $service_description);
		$tpl->assign('authorlabel', _("Alias"));
		$tpl->assign('authoralias', $oreon->user->get_alias());

		$ckbx[] = $form->addElement('checkbox', 'notify', _("notify"));
                if (isset($oreon->optGen['monitoring_ack_notify']) && $oreon->optGen['monitoring_ack_notify']) {
                    $ckbx[0]->setChecked(true);
                }

		$ckbx1[] = $form->addElement('checkbox', 'sticky', _("sticky"));
                if (isset($oreon->optGen['monitoring_ack_sticky']) && $oreon->optGen['monitoring_ack_sticky']) {
                    $ckbx1[0]->setChecked(true);
                }

		$ckbx2[] = $form->addElement('checkbox', 'persistent', _("persistent"));
                if (isset($oreon->optGen['monitoring_ack_persistent']) && $oreon->optGen['monitoring_ack_persistent']) {
                    $ckbx2[0]->setChecked(true);
                }

		$ckbx3[] = $form->addElement('checkbox', 'force_check', _("Force active check"));
                if (isset($oreon->optGen['monitoring_ack_active_checks']) && $oreon->optGen['monitoring_ack_active_checks']) {
                    $ckbx3[0]->setChecked(true);
                }

		$form->addElement('hidden', 'host_name', $host_name);
		$form->addElement('hidden', 'service_description', $service_description);
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
		$form->addElement('hidden', 'en', $en);

		$form->applyFilter('__ALL__', 'myTrim');

		$textarea = $form->addElement('textarea', 'comment', _("comment"), array("rows"=>"8", "cols"=>"80"));
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

		$tpl->display("serviceAcknowledge.ihtml");
	}
?>
