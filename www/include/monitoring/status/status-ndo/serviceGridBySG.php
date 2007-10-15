<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Cedrick Facon

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
		
	$pagination = "maxViewMonitoring";
	include("./include/common/autoNumLimit.php");
	
	# set limit & num
	$DBRESULT =& $pearDB->query("SELECT ndo_base_prefix,maxViewMonitoring FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];
	!isset($_GET["order"]) ? $order = 'ASC' : $order = $_GET["order"];

	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
//	!isset($_GET["limit"]) ? $limit = 0 : $limit = $_GET["limit"];
	!isset($_GET["search_type_host"]) ? $search_type_host = 1 : $search_type_host = $_GET["search_type_host"];
	!isset($_GET["search_type_service"]) ? $search_type_service = 1 : $search_type_service = $_GET["search_type_service"];
	!isset($_GET["sort_type"]) ? $sort_type = "host_name" : $sort_type = $_GET["sort_type"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 0;
	
	/* row ? */
	$rows = 10;

		
	include_once("makeJS_serviceGridBySG.php");

	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("sort_types", $sort_types);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("mon_host", $lang['m_mon_hosts']);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_ip", $lang['mon_ip']); 
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']);


	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);	


	$tpl->assign("lang", $lang);

	$tpl->assign("order", strtolower($order));
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);


	##Toolbar select $lang["lgd_more_actions"]
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = 0;
		document.forms['form'].elements['o2'].selectedIndex = 0;
	}
	</SCRIPT>
	<?

	$attrs = array(	'onchange'=>"javascript: setO(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, array(	NULL	=>	$lang["lgd_more_actions"], 
													"1"		=>	$lang['m_mon_resubmit_im_checks'], 
													"2"		=>	$lang['m_mon_resubmit_im_checks_f'], 
													"70" 	=> 	$lang['m_mon_acknoledge_thos_svc_pb'], 
													"71" 	=> 	$lang['m_mon_stop_acknoledge_svc_pb'],
													"80" 	=> 	$lang['m_mon_notification_svc_en'], 
													"81" 	=> 	$lang['m_mon_notification_svc_dis'],
													"90" 	=> 	$lang['m_mon_check_svc_en'], 
													"91" 	=> 	$lang['m_mon_check_svc_dis'],
													"72" 	=> 	$lang['m_mon_acknoledge_thos_host_pb'],
													"73" 	=> 	$lang['m_mon_stop_acknoledge_host_pb'], 
													"82" 	=> 	$lang['m_mon_notification_host_en'],
													"83" 	=> 	$lang['m_mon_notification_host_dis'],
													"92" 	=> 	$lang['m_mon_check_host_en'],
													"93" 	=> 	$lang['m_mon_check_host_dis']), $attrs);
	
	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array('onchange'=>"javascript: setO(this.form.elements['o2'].value); submit();");
    $form->addElement('select', 'o2', NULL, array(	NULL	=>	$lang["lgd_more_actions"], 
													"1"		=>	$lang['m_mon_resubmit_im_checks'], 
													"2"		=>	$lang['m_mon_resubmit_im_checks_f'], 
													"70" 	=> 	$lang['m_mon_acknoledge_thos_svc_pb'], 
													"71" 	=> 	$lang['m_mon_stop_acknoledge_svc_pb'],
													"80" 	=> 	$lang['m_mon_notification_svc_en'], 
													"81" 	=> 	$lang['m_mon_notification_svc_dis'],
													"90" 	=> 	$lang['m_mon_check_svc_en'], 
													"91" 	=> 	$lang['m_mon_check_svc_dis'],
													"72" 	=> 	$lang['m_mon_acknoledge_thos_host_pb'],
													"73" 	=> 	$lang['m_mon_stop_acknoledge_host_pb'], 
													"82" 	=> 	$lang['m_mon_notification_host_en'],
													"83" 	=> 	$lang['m_mon_notification_host_dis'],
													"92" 	=> 	$lang['m_mon_check_host_en'],
													"93" 	=> 	$lang['m_mon_check_host_dis']), $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);	
	$tpl->assign('limit', $limit);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());	
	$tpl->display("service.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);

	if ($oreon->optGen["nagios_version"] == 2 && isset($pgr_nagios_stat["created"])) 	
		$pgr_nagios_stat["created"] = date("d/m/Y G:i", $pgr_nagios_stat["created"]);
	else
		$pgr_nagios_stat["created"] = 0;
?>	