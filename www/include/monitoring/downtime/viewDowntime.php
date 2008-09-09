<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	
	if (!isset($oreon))
		exit();

	/*
	 * ACL
	 */
	if (!$is_admin)
		$lcaHostByName = getLcaHostByName($pearDB);
	
	$ndo_base_prefix = getNDOPrefix();
	include_once("./DBNDOConnect.php");
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "template/");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tab_downtime_host = array();
	$tab_downtime_svc = array();

	/*
	 * Hosts Downtimes
	 */
	$rq2 =	" SELECT dtm.internal_downtime_id, dtm.entry_time, dtm.duration, dtm.author_name, dtm.comment_data, dtm.is_fixed, dtm.scheduled_start_time, dtm.scheduled_end_time, obj.name1 host_name, obj.name2 service_description " .
			" FROM ".$ndo_base_prefix."downtimehistory dtm, ".$ndo_base_prefix."objects obj " .
			" WHERE obj.name1 IS NOT NULL AND obj.name2 IS  NULL AND obj.object_id = dtm.object_id AND dtm.was_cancelled = '0' AND dtm.scheduled_end_time > '".date("Y-m-d G:i:s", time())."' ORDER BY dtm.actual_start_time";
	$DBRESULT_NDO =& $pearDBndo->query($rq2);
	if (PEAR::isError($DBRESULT_NDO))
		print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
	for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++){
		$tab_downtime_host[$i] = $data;
		$tab_downtime_host[$i]["duration"] .= " "._("s");
	}
	unset($data);	

	
	$en = array("0" => _("No"), "1" => _("Yes"));
	foreach ($tab_downtime_host as $key => $value)
		$tab_downtime_host[$key]["is_fixed"] = $en[$tab_downtime_host[$key]["is_fixed"]];
	
	/*
	 * Service Downtimes
	 */
	$rq2 =	" SELECT dtm.internal_downtime_id, dtm.entry_time, dtm.duration, dtm.author_name, dtm.comment_data, dtm.is_fixed, dtm.scheduled_start_time, dtm.scheduled_end_time, obj.name1 host_name, obj.name2 service_description " .
			" FROM ".$ndo_base_prefix."downtimehistory dtm, ".$ndo_base_prefix."objects obj " .
			" WHERE obj.name1 IS NOT NULL AND obj.name2 IS NOT NULL AND obj.object_id = dtm.object_id AND dtm.was_cancelled = '0' AND dtm.scheduled_end_time > '".date("Y-m-d G:i:s", time())."' ORDER BY dtm.actual_start_time";
	$DBRESULT_NDO =& $pearDBndo->query($rq2);
	if (PEAR::isError($DBRESULT_NDO))
		print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
	for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++)
		$tab_downtime_svc[$i] = $data;
	unset($data);	

	$en = array("0" => _("No"), "1" => _("Yes"));
	foreach ($tab_downtime_svc as $key => $value)
		$tab_downtime_svc[$key]["is_fixed"] = $en[$tab_downtime_svc[$key]["is_fixed"]];

	if (!$is_admin){
		$tab_downtime_host2 = array();
		for ($n=0,$i=0; $i < count($tab_downtime_host); $i++) {
			if (isset($lcaHostByName["LcaHost"][$tab_downtime_host[$i]["host_name"]]))
				$tab_downtime_host2[$n++] = $tab_downtime_host[$i];
		}
		$tab_downtime_svc2 = array();
		for ($n=0,$i=0; $i < count($tab_downtime_svc); $i++) {
			if (isset($lcaHostByName["LcaHost"][$tab_downtime_svc[$i]["host_name"]]))
				$tab_downtime_svc2[$n++] = $tab_downtime_svc[$i];
		}

		$tab_downtime_host = $tab_downtime_host2;
		$tab_downtime_svc = $tab_downtime_svc2;
	}

	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	$tpl->assign("p", $p);
	
	$tpl->assign("tab_downtime_host", $tab_downtime_host);
	$tpl->assign("tab_downtime_svc", $tab_downtime_svc);
	$tpl->assign("nb_downtime_host", count($tab_downtime_host));
	$tpl->assign("nb_downtime_svc", count($tab_downtime_svc));
	
	$tpl->assign("dtm_host_name", _("Host Name"));
	$tpl->assign("dtm_service_descr", _("Services"));
	$tpl->assign("dtm_start_time", _("start Time"));
	$tpl->assign("dtm_end_time", _("End Time"));
	$tpl->assign("dtm_author", _("Author"));
	$tpl->assign("dtm_comment", _("Comments"));
	$tpl->assign("dtm_fixed", _("Fixed"));
	$tpl->assign("dtm_duration", _("Duration"));
	$tpl->assign("dtm_host_downtime", _("Hosts Downtimes"));
	$tpl->assign("dtm_service_downtime", _("Services Downtimes"));
	
	$tpl->assign("no_svc_dtm", _("No downtime scheduled for services"));
	$tpl->assign("no_host_dtm", _("No downtime scheduled for hosts"));

	$tpl->assign("delete", _("Delete"));
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("downtime.ihtml");
?>