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

	include_once $centreon_path."www/class/centreonGMT.class.php";

	/*
	 * Init GMT class
	 */
	
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession(session_id());

	/*
	 * ACL Actions
	 */
	$GroupListofUser = array();
	$GroupListofUser =  getGroupListofUser($pearDB);
	
	$allActions = false;
	if (count($GroupListofUser) > 0 && isUserAdmin($pearDB) == 1) {
		$authorized_actions = array();
		$authorized_actions = getActionsACLList($GroupListofUser);
	} else {
		$allActions = true;
	}

	/*
	 * LCA
	 */
	if (!$is_admin)
		$lcaHostByName = getLcaHostByName($pearDB);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "template/");

	$ndo_base_prefix = getNDOPrefix();
	include_once("./DBNDOConnect.php");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tab_comments_host = array();
	$tab_comments_svc = array();

	$en = array("0" => _("No"), "1" => _("Yes"));
		
	/*
	 * Hosts Comments
	 */
	$rq2 =	" SELECT cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
			" FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
			" WHERE obj.name1 IS NOT NULL AND obj.name2 IS  NULL AND obj.object_id = cmt.object_id AND cmt.expires = 0 ORDER BY cmt.entry_time";
	$DBRESULT_NDO =& $pearDBndo->query($rq2);
	if (PEAR::isError($DBRESULT_NDO))
		print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
	for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++){
		$tab_comments_host[$i] = $data;
		$tab_comments_host[$i]["is_persistent"] = $en[$tab_comments_host[$i]["is_persistent"]];
		$tab_comments_host[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_host[$i]["entry_time"]);
	}
	unset($data);
	
	/*
	 * Service Comments
	 */
	$rq2 =	" SELECT cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
			" FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
			" WHERE obj.name1 IS NOT NULL AND obj.name2 IS NOT NULL AND obj.object_id = cmt.object_id AND cmt.expires = 0 ORDER BY cmt.entry_time";
	$DBRESULT_NDO =& $pearDBndo->query($rq2);
	if (PEAR::isError($DBRESULT_NDO))
		print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
	for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++){
		$tab_comments_svc[$i] = $data;
		$tab_comments_svc[$i]["is_persistent"] = $en[$tab_comments_svc[$i]["is_persistent"]];
		$tab_comments_svc[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_svc[$i]["entry_time"]);
	}
	unset($data);

	if (!$is_admin) {
		
		$tab_comments_host2 = array();
		for ($n = 0, $i = 0; $i < count($tab_comments_host); $i++) {
			if (isset($lcaHostByName["LcaHost"][$tab_comments_host[$i]["host_name"]]))
				$tab_comments_host2[$n++] = $tab_comments_host[$i];
		}
		
		$tab_comments_svc2 = array();
		for($n = 0, $i = 0; $i < count($tab_comments_svc); $i++) {
			if (isset($lcaHostByName["LcaHost"][$tab_comments_svc[$i]["host_name"]]))
				$tab_comments_svc2[$n++] = $tab_comments_svc[$i];
		}

		$tab_comments_host = $tab_comments_host2;
		$tab_comments_svc = $tab_comments_svc2;
	}

	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);
	
	if(isset($authorized_actions) && $allActions == false){		
		foreach($authorized_actions as $action_name) {
			if ($action_name == "host_comment") 	
				$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
			if ($action_name == "service_comment") 
				$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
		}
	} else {
		$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
		$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));		
	}
	
	$tpl->assign("p", $p);
	$tpl->assign("tab_comments_host", $tab_comments_host);
	$tpl->assign("tab_comments_svc", $tab_comments_svc);

	$tpl->assign("nb_comments_host", count($tab_comments_host));
	$tpl->assign("nb_comments_svc", count($tab_comments_svc));

	$tpl->assign("no_host_comments", _("No Comment for hosts."));
	$tpl->assign("no_svc_comments", _("No Comment for services."));

	$tpl->assign("cmt_host_name", _("Host Name"));
	$tpl->assign("cmt_service_descr", _("Services"));
	$tpl->assign("cmt_entry_time", _("Entry Time"));
	$tpl->assign("cmt_author", _("Author"));
	$tpl->assign("cmt_comment", _("Comments"));
	$tpl->assign("cmt_persistent", _("Persistent"));
	$tpl->assign("cmt_host_comment", _("Hosts Comments"));
	$tpl->assign("cmt_service_comment", _("Services Comments"));

	$tpl->assign("delete", _("Delete"));

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("comments.ihtml");
?>