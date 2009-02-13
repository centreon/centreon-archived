<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL
 * SVN : $Id: 
 * 
 */
 
	if (!isset($oreon))
		exit();

	/*
	 * ACL Actions
	 */
	$GroupListofUser = array();
	$GroupListofUser = $oreon->user->access->getAccessGroups();

	$allActions = false;
	// Get list of actions allowed for user
	if (count($GroupListofUser) > 0 && $is_admin == 0) {
		$authorized_actions = array();
		$authorized_actions = $oreon->user->access->getActions();
	} else {
	 	// if user is admin, or without ACL, he cans perform all actions
		$allActions = true;
	}

	include("./include/common/autoNumLimit.php");

	/*
	 * set limit & num
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM options WHERE key = 'maxViewMonitoring' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$data =& $DBRESULT->fetchRow();
	$gopt[$data['key']] = myDecode($data['key']);

	!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];
	!isset($_GET["search_type_host"]) ? $search_type_host = 1 : $search_type_host = $_GET["search_type_host"];
	!isset($_GET["search_type_service"]) ? $search_type_service = 1 : $search_type_service = $_GET["search_type_service"];
	!isset($_GET["host_name"]) ? $host_name = "" : $host_name = $_GET["host_name"];
	
	if ($o == "svcpb" || $o == "svc_unhandled") {
		if (!isset($_GET["sort_type"])) {
			$sort_type = $oreon->optGen["problem_sort_type"];
		} else
			$sort_type = $_GET["sort_type"];
		if (!isset($_GET["order"])) {
			$order = $oreon->optGen["problem_sort_order"];
		} else
			$order = $_GET["order"];
			
	} else {
		if (!isset($_GET["sort_type"]))
			$sort_type = "host_name";
		else
			$sort_type = $_GET["sort_type"];
		if (!isset($_GET["order"])) {
			$order = "ASC";
		} else
			$order = $_GET["order"];
	}
	

	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");

	$tab_class = array("0" => "list_one", "1" => "list_two");

	$rows = 10;

	include_once($svc_path."/serviceJS.php");

	/*
	 * Smarty template Init
	 */
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

	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = 0;
		document.forms['form'].elements['o2'].selectedIndex = 0;
	}
	</SCRIPT>
	<?php
	
	$action_list = array();
	$action_list[]	=	_("More actions...");
	
	/*
	 * Showing actions allowed for current user
	 */
	if (isset($authorized_actions) && $allActions == false){		
		foreach ($authorized_actions as $action_name) {
			if ($action_name == "service_schedule_check" || $allActions == true) 
				$action_list[3] = _("Schedule immediate check");
			if ($action_name == "service_schedule_check" || $allActions == true) 
				$action_list[4] = _("Schedule immediate check (Forced)");
			if ($action_name == "service_acknowledgement" || $allActions == true) 
				$action_list[70] = _("Services : Acknowledge");
			if ($action_name == "service_acknowledgement" || $allActions == true) 
				$action_list[71] = _("Services : Disacknowledge");
			if ($action_name == "service_notifications" || $allActions == true)
				$action_list[80] = _("Services : Enable Notification");
			if ($action_name == "service_notifications" || $allActions == true) 
				$action_list[81] = _("Services : Disable Notification");
			if ($action_name == "service_checks" || $allActions == true) 
				$action_list[90] = _("Services : Enable Check");
			if ($action_name == "service_checks" || $allActions == true) 
				$action_list[91] = _("Services : Disable Check");
			if ($action_name == "host_acknowledgement" || $allActions == true) 
				$action_list[72] = _("Hosts : Acknowledge");
			if ($action_name == "host_acknowledgement" || $allActions == true) 
				$action_list[73] = _("Hosts : Disacknowledge");
			if ($action_name == "host_notifications" || $allActions == true) 
				$action_list[82] = _("Hosts : Enable Notification");
			if ($action_name == "host_notifications" || $allActions == true) 
				$action_list[83] = _("Hosts : Disable Notification");
			if ($action_name == "host_checks" || $allActions == true) 
				$action_list[92] = _("Hosts : Enable Check");
			if ($action_name == "host_checks" || $allActions == true) 
				$action_list[93] = _("Hosts : Disable Check");		
		}
	} else {
		$action_list[3] = _("Schedule immediate check");
		$action_list[4] = _("Schedule immediate check (Forced)");
		$action_list[70] = _("Services : Acknowledge");
		$action_list[71] = _("Services : Disacknowledge");
		$action_list[80] = _("Services : Enable Notification");
		$action_list[81] = _("Services : Disable Notification");
		$action_list[90] = _("Services : Enable Check");
		$action_list[91] = _("Services : Disable Check");
		$action_list[72] = _("Hosts : Acknowledge");
		$action_list[73] = _("Hosts : Disacknowledge");
		$action_list[82] = _("Hosts : Enable Notification");
		$action_list[83] = _("Hosts : Disable Notification");
		$action_list[92] = _("Hosts : Enable Check");
		$action_list[93] = _("Hosts : Disable Check");		
	}
	
	$attrs = array(	'onchange'=>"javascript: setO(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, $action_list, $attrs);

	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array('onchange'=>"javascript: setO(this.form.elements['o2'].value); submit();");
    $form->addElement('select', 'o2', NULL, $action_list, $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	$tpl->assign('limit', $limit);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());
	$tpl->display("service.ihtml");
?>