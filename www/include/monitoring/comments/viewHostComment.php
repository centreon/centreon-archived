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
	if (!isset($centreon)) {
		exit();
	}

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once "./include/common/autoNumLimit.php";

	if (isset($_POST["hostgroup"]))
	  $hostgroup = $_POST["hostgroup"];
	else if (isset($_GET["hostgroup"]))
	   $hostgroup = $_GET["hostgroup"];
	else if (isset($centreon->hostgroup) && $centreon->hostgroup)
	   $hostgroup = $centreon->hostgroup;
	else
	   $hostgroup = "0";

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

	include_once("./class/centreonDB.class.php");

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDB("ndo");
		$ndo_base_prefix = getNDOPrefix();
	}

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tab_comments_host = array();
	$tab_comments_svc = array();

	$en = array("0" => _("No"), "1" => _("Yes"));

	$acl_host_list = $centreon->user->access->getHostsString("NAME", ($oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO));

	$search_request = "";
	if (isset($host_name)) {
		$search_request = " AND obj.name1 LIKE '%".$pearDBO->escape($host_name)."%'";
	}

	/** *******************************************
	 * Hosts Comments
	 */
	if ($oreon->broker->getBroker() == "ndo") {
            $rq2 = "SELECT SQL_CALC_FOUND_ROWS cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
		   "FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
                   ($hostgroup ? ", ".$ndo_base_prefix."hostgroup_members hgm ".", ".$ndo_base_prefix."objects hgobj,".$ndo_base_prefix."hostgroups hg " : "") .
                   "WHERE obj.name1 IS NOT NULL " .
                   "AND obj.name2 IS NULL " .
                   (isset($search_output) && $search_output != "" ? " AND cmt.comment_data LIKE '%".$pearDBndo->escape($search_output)."%'" : "");
                   if ($hostgroup) {
                        $rq2 .= " AND hgm.hostgroup_id = hg.hostgroup_id
                                  AND hg.hostgroup_object_id = hgobj.object_id
                                  AND hgobj.name1 = '".$pearDBndo->escape($hostgroup)."'
                                  AND hgm.host_object_id = cmt.object_id ";
                   }
                   $rq2 .=	"AND obj.object_id = cmt.object_id $search_request ";
                   $rq2 .= $centreon->user->access->queryBuilder("AND", "obj.name1", $acl_host_list);
                   $rq2 .= "AND cmt.expires = 0 ORDER BY cmt.comment_time DESC LIMIT ".$num * $limit.", ".$limit;
                   $DBRESULT_NDO = $pearDBndo->query($rq2);
                   $rows = $pearDBndo->numberRows();
                   for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++){
			$tab_comments_host[$i] = $data;
			$tab_comments_host[$i] = htmlentities($data['comment_data']);
			$tab_comments_host[$i]["is_persistent"] = $en[$tab_comments_host[$i]["is_persistent"]];
			$tab_comments_host[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_host[$i]["entry_time"]);
			$tab_comments_host[$i]["host_name_link"] = urlencode($tab_comments_host[$i]["host_name"]);
                   }
                   unset($data);
	} else {
		$rq2 = "SELECT SQL_CALC_FOUND_ROWS c.internal_id AS internal_comment_id, c.entry_time, author AS author_name, c.data AS comment_data, c.persistent AS is_persistent, c.host_id, h.name as host_name " .
                       "FROM comments c, hosts h ";
                $rq2 .= ($hostgroup ? ", hosts_hostgroups hgm, hostgroups hg " : "");
		$rq2 .=	"WHERE c.host_id = h.host_id AND c.service_id IS NULL  ";
		if (!$is_admin) {
            $rq2 .= " AND EXISTS(SELECT 1 FROM centreon_acl WHERE c.host_id = centreon_acl.host_id AND group_id IN (" . $oreon->user->access->getAccessGroupsString() . ")) ";
		}
        $rq2 .= (isset($host_name) && $host_name != "" ? " AND h.name LIKE '%".$pearDBO->escape($host_name)."%'" : "") .
                (isset($search_output) && $search_output != "" ? " AND c.data LIKE '%".$pearDBO->escape($search_output)."%'" : "");
        if ($hostgroup) {
             $rq2 .= " AND hg.enabled = 1 
                       AND hgm.hostgroup_id = hg.hostgroup_id
                       AND hg.name = '".$pearDBO->escape($hostgroup)."'
                       AND hgm.host_id = c.host_id ";
        }
		$rq2 .= " AND c.expires = '0' ";
                $rq2 .= " AND (c.deletion_time IS NULL OR c.deletion_time = 0) ";
                $rq2 .= " ORDER BY entry_time DESC LIMIT ".$num * $limit.", ".$limit;
		$DBRESULT = $pearDBO->query($rq2);
		$rows = $pearDBO->numberRows();
		for ($i = 0; $data = $DBRESULT->fetchRow(); $i++){
			$tab_comments_host[$i] = $data;
			$tab_comments_host[$i]['comment_data'] = htmlentities($data['comment_data']);
			$tab_comments_host[$i]['host_name'] = htmlentities($data['host_name']);
			$tab_comments_host[$i]["is_persistent"] = $en[$tab_comments_host[$i]["is_persistent"]];
			$tab_comments_host[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_host[$i]["entry_time"]);
			$tab_comments_host[$i]["host_name_link"] = urlencode($tab_comments_host[$i]["host_name"]);
		}
		unset($data);
		$DBRESULT->free();
	}

	/*
	 * Pagination
	 */
	include("./include/common/checkPagination.php");

	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);


	if ($centreon->user->access->checkAction("host_comment")) {
		$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>_("Add a comment"), "delConfirm"=>_("Do you confirm the deletion ?")));
	}

	$tpl->assign("p", $p);
	$tpl->assign("tab_comments_host", $tab_comments_host);
	$tpl->assign("nb_comments_host", count($tab_comments_host));
	$tpl->assign("no_host_comments", _("No Comment for hosts."));
	$tpl->assign("cmt_host_name", _("Host Name"));
	$tpl->assign("cmt_entry_time", _("Entry Time"));
	$tpl->assign("cmt_author", _("Author"));
	$tpl->assign("cmt_comment", _("Comments"));
	$tpl->assign("cmt_persistent", _("Persistent"));
	$tpl->assign("cmt_host_comment", _("Hosts Comments"));
	$tpl->assign("svc_comment_link", "./main.php?p=".$p."&o=vs");
	$tpl->assign("view_svc_comments", _("View comments of services"));
	$tpl->assign("delete", _("Delete"));


	$tpl->assign("Host", _("Host Name"));
	$tpl->assign("Output", _("Output"));
	$tpl->assign("user", _("Useres"));
	$tpl->assign('Hostgroup', _("Hostgroup"));
	$tpl->assign('Search', _("Search"));
	$tpl->assign("search_output", $search_output);
	$tpl->assign('search_host', $host_name);

	$acldb = $oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO;
        $hg = array();
        if ($oreon->user->access->admin) {
            $query = "SELECT hg_id, hg_name
                      FROM hostgroup
                      WHERE hg_activate = '1'
                      ORDER BY hg_name";
        } else {
            $query = "SELECT DISTINCT hg.hg_id, hg.hg_name " .
                     "FROM hostgroup hg, acl_resources_hg_relations arhr " .
                     "WHERE hg.hg_id = arhr.hg_hg_id " .
                     "AND arhr.acl_res_id IN (".$oreon->user->access->getResourceGroupsString().") " .
                     "AND hg.hg_activate = '1' ".
                     "AND hg.hg_id in (SELECT hostgroup_hg_id
                                       FROM hostgroup_relation
                                       WHERE host_host_id IN (".$oreon->user->access->getHostsString("ID", $acldb).")) " .
                     "ORDER BY hg.hg_name";
        }
        $res = $pearDB->query($query);
        $options = "<option value='0'></options>";
	while ($data = $res->fetchRow()) {
            $options .= "<option value='".$data["hg_name"]."' ".(($hostgroup == $data["hg_name"]) ? 'selected' : "").">".$data["hg_name"]."</option>";
        }
	$res->free();
	$tpl->assign('hostgroup', $options);
	unset($options);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);

	$form->accept($renderer);
	$tpl->assign('limit', $limit);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("hostComments.ihtml");
?>
