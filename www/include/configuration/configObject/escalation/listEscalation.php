<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$pagination = "maxViewConfiguration";
	
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	
	if (PEAR::isError($RES))
		print "Mysql Error : ".$RES->getMessage();
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	isset($_GET["list"]) ? $list = $_GET["list"] : $list = NULL;
	$rq = "SELECT COUNT(*) FROM escalation esc";
	
	if ($list && $list == "h"){
		$oreon->user->admin || !HadUserLca($pearDB) ? $rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_host_relation ehr WHERE ehr.escalation_esc_id = esc.esc_id) > 0" : $rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_host_relation ehr WHERE ehr.escalation_esc_id = esc.esc_id AND ehr.host_host_id IN (".$lcaHoststr.")) > 0";
	} else if ($list && $list == "sv") {
		$oreon->user->admin || !HadUserLca($pearDB) ? $rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_service_relation esr WHERE esr.escalation_esc_id = esc.esc_id) > 0" : $rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_service_relation esr WHERE esr.escalation_esc_id = esc.esc_id) > 0";
	} else if ($list && $list == "hg") {
		if ($oreon->user->admin || !HadUserLca($pearDB))		
			$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_hostgroup_relation ehgr WHERE ehgr.escalation_esc_id = esc.esc_id AND ehgr.hostgroup_hg_id IN (".$lcaHostGroupstr.")) > 0";
		else
			$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_hostgroup_relation ehgr WHERE ehgr.escalation_esc_id = esc.esc_id AND ehgr.hostgroup_hg_id IN (".$lcaHostGroupstr.")) > 0";
	} else if ($list && $list == "ms"){
		if ($oreon->user->admin || !HadUserLca($pearDB))		
			$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_meta_service_relation emsr WHERE emsr.escalation_esc_id = esc.esc_id) > 0";
		else
			$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_meta_service_relation emsr WHERE emsr.escalation_esc_id = esc.esc_id) > 0";
	}
	
	if ($search && $list)
		$rq .= " AND esc.esc_name LIKE '%".$search."%'";
	else if ($search)
		$rq .= " WHERE esc.esc_name LIKE '%".$search."%'";
	$res = & $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_desc", $lang['description']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	#Escalation list
	$rq = "SELECT esc_id, esc_name, esc_comment FROM escalation esc";
	if ($list && $list == "h")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_host_relation ehr WHERE ehr.escalation_esc_id = esc.esc_id AND ehr.host_host_id IN (".$lcaHoststr.")) > 0";
	else if ($list && $list == "sv")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_service_relation esr WHERE esr.escalation_esc_id = esc.esc_id) > 0";
	else if ($list && $list == "hg")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_hostgroup_relation ehgr WHERE ehgr.escalation_esc_id = esc.esc_id AND ehgr.hostgroup_hg_id IN (".$lcaHostGroupstr.")) > 0";
	else if ($list && $list == "ms")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM escalation_meta_service_relation emsr WHERE emsr.escalation_esc_id = esc.esc_id) > 0";
	if ($search && $list)
		$rq .= " AND esc.esc_name LIKE '%".$search."%'";
	else if ($search)
		$rq .= " WHERE esc.esc_name LIKE '%".$search."%'";
	$rq .= " ORDER BY esc_name LIMIT ".$num * $limit.", ".$limit;
	$res = & $pearDB->query($rq);	
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $res->fetchInto($esc); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$esc['esc_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&esc_id=".$esc['esc_id']."&o=w&search=".$search."&list=".$list."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&esc_id=".$esc['esc_id']."&o=c&search=".$search."&list=".$list."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&esc_id=".$esc['esc_id']."&o=d&select[".$esc['esc_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."&list=".$list."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<select style='margin-bottom: 3px;' name='dupNbr[".$esc['esc_id']."]'><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>10</option></select>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$esc["esc_name"],
						"RowMenu_link"=>"?p=".$p."&o=w&esc_id=".$esc['esc_id'],
						"RowMenu_desc"=>substr($esc["esc_comment"], 0, 40),
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listEscalation.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");	
?>