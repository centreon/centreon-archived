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

	include("./include/common/autoNumLimit.php");

	!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];
	!isset($_GET["order"]) ? $order = 'ASC' : $order = $_GET["order"];
	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
	!isset($_GET["host_search"]) ? $host_search = 0 : $host_search = $_GET["host_search"];
	!isset($_GET["search_type_host"]) ? $search_type_host = 1 : $search_type_host = $_GET["search_type_host"];
	!isset($_GET["search_type_service"]) ? $search_type_service = 1 : $search_type_service = $_GET["search_type_service"];
	!isset($_GET["sort_type"]) ? $sort_type = "host_name" : $sort_type = $_GET["sort_type"];

	/*
	 * Check search value in Host search field
	 */
	if (isset($_GET["host_search"])) {
		$centreon->historySearch[$url] = $_GET["host_search"];
	}

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 10;

	include_once("./include/monitoring/status/Common/default_poller.php");
	include_once("./include/monitoring/status/Common/default_hostgroups.php");
	include_once($svc_path."/serviceSummaryJS.php");


	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($svc_path, $tpl, "/templates/");

	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("sort_types", $sort_types);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("mon_host", _("Hosts"));
	$tpl->assign("mon_status", _("Status"));
	$tpl->assign("mon_ip", _("IP"));
	$tpl->assign("mon_last_check", _("Last Check"));
	$tpl->assign("mon_duration", _("Duration"));
	$tpl->assign("mon_status_information", _("Status information"));

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tpl->assign("order", strtolower($order));
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc");
	$tpl->assign("tab_order", $tab_order);


	##Toolbar select $lang["lgd_more_actions"]
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = 0;
		document.forms['form'].elements['o2'].selectedIndex = 0;
	}
	</SCRIPT>
	<?php

	$attrs = array(	'onchange'=>"javascript: setO(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, array(	NULL	=>	_("More actions..."),
													"3"		=>	_("Verification Check"),
													"4"		=>	_("Verification Check (Forced)"),
													"70" 	=> 	_("Services : Acknowledge"),
													"71" 	=> 	_("Services : Disacknowledge"),
													"80" 	=> 	_("Services : Enable Notification"),
													"81" 	=> 	_("Services : Disable Notification"),
													"90" 	=> 	_("Services : Enable Check"),
													"91" 	=> 	_("Services : Disable Check"),
													"72" 	=> 	_("Hosts : Acknowledge"),
													"73" 	=> 	_("Hosts : Disacknowledge"),
													"82" 	=> 	_("Hosts : Enable Notification"),
													"83" 	=> 	_("Hosts : Disable Notification"),
													"92" 	=> 	_("Hosts : Enable Check"),
													"93" 	=> 	_("Hosts : Disable Check")), $attrs);

	$form->setDefaults(array('o1' => NULL));
	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array('onchange'=>"javascript: setO(this.form.elements['o2'].value); submit();");
    $form->addElement('select', 'o2', NULL, array(	NULL	=>	_("More actions..."),
													"3"		=>	_("Verification Check"),
													"4"		=>	_("Verification Check (Forced)"),
													"70" 	=> 	_("Services : Acknowledge"),
													"71" 	=> 	_("Services : Disacknowledge"),
													"80" 	=> 	_("Services : Enable Notification"),
													"81" 	=> 	_("Services : Disable Notification"),
													"90" 	=> 	_("Services : Enable Check"),
													"91" 	=> 	_("Services : Disable Check"),
													"72" 	=> 	_("Hosts : Acknowledge"),
													"73" 	=> 	_("Hosts : Disacknowledge"),
													"82" 	=> 	_("Hosts : Enable Notification"),
													"83" 	=> 	_("Hosts : Disable Notification"),
													"92" 	=> 	_("Hosts : Enable Check"),
													"93" 	=> 	_("Hosts : Disable Check")), $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	$tpl->assign('limit', $limit);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('search', _('Search'));
    $tpl->assign('pollerStr', _('Poller'));
    $tpl->assign('poller_listing', $oreon->user->access->checkAction('poller_listing'));
	$tpl->assign('hgStr', _('Hostgroup'));
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("serviceGrid.ihtml");
?>