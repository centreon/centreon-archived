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

include_once("./class/centreonDB.class.php");

if ($oreon->broker->getBroker() == "ndo") {
  $pearDBndo = new CentreonDB("ndo");
  $ndo_base_prefix = getNDOPrefix();
}

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
if ($oreon->broker->getBroker() == "ndo") {
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
    $rq2 =	"SELECT SQL_CALC_FOUND_ROWS DISTINCT cmt.internal_comment_id, unix_timestamp(cmt.comment_time) AS entry_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 as host_name, obj.name2 as service_description " .
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
  $rows = $pearDBndo->numberRows();
  for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++){
    $tab_comments_svc[$i] = $data;
    $tab_comments_svc[$i]["is_persistent"] = $en[$tab_comments_svc[$i]["is_persistent"]];
    $tab_comments_svc[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_svc[$i]["entry_time"]);
    $tab_comments_svc[$i]['host_name_link'] = urlencode($tab_comments_svc[$i]['host_name']);
  }
  unset($data);
} else {
  $rq2 = "SELECT SQL_CALC_FOUND_ROWS c.internal_id AS internal_comment_id, c.entry_time, author AS author_name, c.data AS comment_data, c.persistent AS is_persistent, c.host_id, c.service_id, h.name AS host_name, s.description AS service_description " .
    "FROM comments c, hosts h, services s ";
  $rq2 .= "WHERE c.host_id = h.host_id AND c.service_id = s.service_id AND h.host_id = s.host_id ";
  $rq2 .= " AND c.expires = '0' AND h.enabled = 1 AND s.enabled = 1 ";
  $rq2 .= " AND (c.deletion_time IS NULL OR c.deletion_time = 0) ";
  if (!$is_admin) {
    $rq2 .= " AND EXISTS(SELECT 1 FROM centreon_acl WHERE s.host_id = centreon_acl.host_id AND s.service_id = centreon_acl.service_id AND group_id IN (" . $oreon->user->access->getAccessGroupsString() . ")) ";
  }
  
  $rq2 .= (isset($search_service) && $search_service != "" ? " AND s.description LIKE '%$search_service%'" : "") .
    (isset($host_name) && $host_name != "" ? " AND h.name LIKE '%$host_name%'" : "") .
    (isset($search_output) && $search_output != "" ? " AND c.data LIKE '%$search_output%'" : "");
  
  $rq2 .= " ORDER BY entry_time DESC LIMIT ".$num * $limit.", ".$limit;
  
  $DBRESULT = $pearDBO->query($rq2);
  $rows = $pearDBO->numberRows();
  for ($i = 0; $data = $DBRESULT->fetchRow(); $i++){
    $tab_comments_svc[$i] = $data;
    $tab_comments_svc[$i]["is_persistent"] = $en[$tab_comments_svc[$i]["is_persistent"]];
    $tab_comments_svc[$i]["entry_time"] = $centreonGMT->getDate("m/d/Y H:i" , $tab_comments_svc[$i]["entry_time"]);
    $tab_comments_svc[$i]['host_name_link'] = urlencode($tab_comments_svc[$i]['host_name']);
  }
  unset($data);
  $DBRESULT->free();
}

include("./include/common/checkPagination.php");

/*
 * Element we need when we reload the page
 */
$form->addElement('hidden', 'p');
$tab = array ("p" => $p);
$form->setDefaults($tab);

if ($oreon->user->access->checkAction("service_comment")) {
  $tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>_("Add a comment"), "delConfirm"=>_("Do you confirm the deletion ?")));
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

