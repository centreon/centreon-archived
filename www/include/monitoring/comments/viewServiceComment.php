<?php
/*
 * Copyright 2005-2010 MERETHIS
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
	if (!isset($oreon)) {
		exit();
	}

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include("./include/common/autoNumLimit.php");

	if (isset($_POST["search_service"]))
	  	$search_service = $_POST["search_service"];
	else if (isset($_GET["search_service"]))
	  	$search_service = $_GET["search_service"];
	else
	  	$search_service = NULL;

	if (isset($_POST["search_host"]))
		$host_name = $_POST["search_host"];
	else if (isset($_GET["search_host"]))
		$host_name = $_GET["search_host"];
	else
		$host_name = NULL;

	if (isset($_POST["search_output"]))
		$search_output = $_POST["search_output"];
	else if (isset($_GET["search_output"]))
		$search_output = $_GET["search_output"];
	else
		$search_output = NULL;

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "template/");

	$ndo_base_prefix = getNDOPrefix();
	include_once("./class/centreonDB.class.php");

	$pearDBndo = new CentreonDB("ndo");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tab_comments_svc = array();

	$en = array("0" => _("No"), "1" => _("Yes"));

	/*
	 * Service Comments
	 */
	if ($is_admin) {
		$rq2 =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
				"FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
				"WHERE obj.name1 IS NOT NULL " .
				"AND obj.name2 IS NOT NULL " .
				(isset($search_service) && $search_service != "" ? " AND obj.name2 LIKE '%$search_service%'" : "") .
				(isset($host_name) && $host_name != "" ? " AND obj.name1 LIKE '%$host_name%'" : "") .
				(isset($search_output) && $search_output != "" ? " AND cmt.comment_data LIKE '%$search_output%'" : "") .
				"AND obj.object_id = cmt.object_id " .
				"AND cmt.expires = 0 ORDER BY entry_time DESC LIMIT ".$num * $limit.", ".$limit;
	} else {
		$rq2 =	"SELECT SQL_CALC_FOUND_ROWS cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
				"FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj, centreon_acl " .
				"WHERE obj.name1 IS NOT NULL " .
				"AND obj.name2 IS NOT NULL " .
				"AND obj.object_id = cmt.object_id " .
				"AND obj.name1 = centreon_acl.host_name " .
				(isset($search_service) && $search_service != "" ? " AND obj.name2 LIKE '%$search_service%'" : "") .
				(isset($host_name) && $host_name != "" ? " AND obj.name1 LIKE '%$host_name%'" : "") .
				(isset($search_output) && $search_output != "" ? " AND cmt.comment_data LIKE '%$search_output%'" : "") .
				"AND obj.name2 = centreon_acl.service_description " .
				"AND centreon_acl.group_id IN (".$oreon->user->access->getAccessGroupsString().") " .
				"AND cmt.expires = 0 ORDER BY entry_time DESC LIMIT ".$num * $limit.", ".$limit;
	}
	$DBRESULT_NDO = $pearDBndo->query($rq2);
	for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++){
		$tab_comments_svc[$i] = $data;
		$tab_comments_svc[$i]["is_persistent"] = $en[$tab_comments_svc[$i]["is_persistent"]];
		$tab_comments_svc[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_svc[$i]["entry_time"]);
	}
	unset($data);

	$rows = $pearDBndo->numberRows();
	include("./include/common/checkPagination.php");

	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	if ($oreon->user->access->checkAction("service_comment")) {
		$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	}

	$tpl->assign("p", $p);
	$tpl->assign("o", $o);
	$tpl->assign("tab_comments_svc", $tab_comments_svc);
	$tpl->assign("nb_comments_svc", count($tab_comments_svc));
	$tpl->assign("no_svc_comments", _("No Comment for services."));

	$tpl->assign("cmt_host_name", _("Host Name"));
	$tpl->assign("cmt_service_descr", _("Services"));
	$tpl->assign("cmt_entry_time", _("Entry Time"));
	$tpl->assign("cmt_author", _("Author"));
	$tpl->assign("cmt_comment", _("Comments"));
	$tpl->assign("cmt_persistent", _("Persistent"));
	$tpl->assign("cmt_service_comment", _("Services Comments"));
	$tpl->assign("host_comment_link", "./main.php?p=".$p."&o=vh");
	$tpl->assign("view_host_comments", _("View comments of hosts"));
	$tpl->assign("delete", _("Delete"));

	$tpl->assign("search", $search_service);

	$tpl->assign("Host", _("Host Name"));
	$tpl->assign("Service", _("Service"));

	$tpl->assign("Output", _("Output"));
	$tpl->assign("user", _("Users"));
	$tpl->assign('Hostgroup', _("Hostgroup"));
	$tpl->assign('Search', _("Search"));
	$tpl->assign("search_output", $search_output);
	$tpl->assign('search_host', $host_name);
	$tpl->assign('search_service', $search_service);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('limit', $limit);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("serviceComments.ihtml");
?>