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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
	if (!isset($oreon))
		exit();

	!isset($_GET["order"]) ? $order = 'ASC' : $order = $_GET["order"];
	
	if ($order == 'sort_asc')
		$order = 'ASC';

	include("./include/common/autoNumLimit.php");

	!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];
	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
	!isset($_GET["search_type_host"]) ? $search_type_host = 1 : $search_type_host = $_GET["search_type_host"];
	!isset($_GET["search_type_service"]) ? $search_type_service = 1 : $search_type_service = $_GET["search_type_service"];
	!isset($_GET["sort_type"]) ? $sort_type = "next_check" : $sort_type = $_GET["sort_type"];
	!isset($_GET["host_name"]) ? $host_name = "" : $host_name = $_GET["host_name"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 10;

	include("./include/common/checkPagination.php");

	include_once($path_sch."serviceScheduleJS.php");

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


	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path_sch, $tpl, "/templates/");

	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("sort_types", $sort_types);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	
	/*
	 * translations
	 */
	$tpl->assign("mon_host", _("Hosts"));
	$tpl->assign("mon_status", _("Status"));
	$tpl->assign("mon_ip", _("IP"));
	$tpl->assign("mon_last_check", _("Last Check"));
	$tpl->assign("mon_duration", _("Duration"));
	$tpl->assign("mon_status_information", _("Status information"));

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

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
		}
	} else {
		$action_list[3] = _("Schedule immediate check");
		$action_list[4] = _("Schedule immediate check (Forced)");	
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
	$tpl->display("serviceSchedule.ihtml");
?>