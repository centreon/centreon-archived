<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

global $centreon;

if (!isset($centreon)) {
    exit();
}

    global $num, $limit, $search, $url, $pearDB, $search_type_service, $search_type_host, $host_name, $hostgroup, $rows, $p, $gopt, $pagination, $poller, $template, $search_output, $search_service;

        $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;
    isset($_GET["o"]) ? $o = $_GET["o"] : $o = null;

if (isset($_GET["num"])) {
    $num = $_GET["num"];
} elseif (!isset($_GET["num"]) && isset($centreon->historyPage[$url]) && $centreon->historyPage[$url]) {
    $num = $centreon->historyPage[$url];
} else {
    $num = 0;
}

    $num = mysql_real_escape_string($num);

    $tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc");

if (isset($_GET["search_type_service"])) {
    $search_type_service = $_GET["search_type_service"];
    $centreon->search_type_service = $_GET["search_type_service"];
} elseif (isset($centreon->search_type_service)) {
         $search_type_service = $centreon->search_type_service;
} else {
    $search_type_service = null;
}

if (isset($_GET["search_type_host"])) {
    $search_type_host = $_GET["search_type_host"];
    $centreon->search_type_host = $_GET["search_type_host"];
} elseif (isset($centreon->search_type_host)) {
         $search_type_host = $centreon->search_type_host;
} else {
    $search_type_host = null;
}

if (!isset($_GET["search_type_host"]) && !isset($centreon->search_type_host) && !isset($_GET["search_type_service"]) && !isset($centreon->search_type_service)) {
    $search_type_host = 1;
    $centreon->search_type_host = 1;
    $search_type_service = 1;
    $centreon->search_type_service = 1;
}

    $url_var = "";
if (isset($search_type_service)) {
    $url_var .= "&search_type_service=" . $search_type_service;
}
if (isset($search_type_host)) {
    $url_var .= "&search_type_host=" . $search_type_host;
}
if (isset($hostgroup)) {
    $url_var .= "&hostgroup=" . $hostgroup;
}
if (isset($host_name)) {
    $url_var .= "&search_host=" . $host_name;
}
if (isset($search_service)) {
    $url_var .= "&search_service=" . $search_service;
}
if (isset($search_output) && $search_output != "") {
    $url_var .= "&search_output=" . $search_output;
}

if (isset($_GET["order"])) {
    $url_var .= "&order=".$_GET["order"];
    $order = $_GET["order"];
}
if (isset($_GET["sort_types"])) {
    $url_var .= "&sort_types=".$_GET["sort_types"];
    $sort_type = $_GET["sort_types"];
}

    /* Fix for downtime */
if (isset($_REQUEST['view_all'])) {
    $url_var .= "&view_all=" . $_REQUEST['view_all'];
}
    
if (isset($_REQUEST['view_downtime_cycle'])) {
    $url_var .= "&view_downtime_cycle=" . $_REQUEST['view_downtime_cycle'];
}
if (isset($_REQUEST['search_author'])) {
    $url_var .= "&search_author=" . $_REQUEST['search_author'];
}

    /* Fix for status in service configuration */
if (isset($_REQUEST['status'])) {
    $url_var .= '&status=' . $_REQUEST['status'];
}
    
    /* Fix for status in service configuration */
if (isset($_REQUEST['hostgroups'])) {
    $url_var .= '&hostgroups=' . $_REQUEST['hostgroups'];
}

    /*
	 * Smarty template Init
	 */
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl, "./include/common/");

    $page_max = ceil($rows / $limit);
if ($num >= $page_max && $rows) {
    $num = $page_max - 1;
}

    $pageArr = array();
    $istart = 0;
for ($i = 5, $istart = $num; $istart && $i > 0; $i--) {
    $istart--;
}
for ($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit - 1)) && ( $i2 < (5 + $i)); $i2++) {
    $iend++;
}

if ($rows != 0) {
    for ($i = $istart; $i <= $iend; $i++) {
        $pageArr[$i] = array("url_page"=>"./main.php?p=".$p."&num=$i&limit=".$limit."&poller=".$poller."&template=$template&search=".$search."&type=".$type."&o=" . $o . $url_var, "label_page"=>"<b>".($i +1)."</b>","num"=> $i);
    }

    if ($i > 1) {
        $tpl->assign("pageArr", $pageArr);
    }

    $tpl->assign("num", $num);
    $tpl->assign("first", _("First page"));
    $tpl->assign("previous", _("Previous page"));
    $tpl->assign("next", _("Next page"));
    $tpl->assign("last", _("Last page"));

    if (($prev = $num - 1) >= 0) {
        $tpl->assign('pagePrev', ("./main.php?p=".$p."&num=$prev&limit=".$limit."&poller=".$poller."&template=$template&search=".$search."&type=".$type."&o=" . $o .$url_var));
    }

    if (($next = $num + 1) < ($rows/$limit)) {
        $tpl->assign('pageNext', ("./main.php?p=".$p."&num=$next&limit=".$limit."&poller=".$poller."&template=$template&search=".$search."&type=".$type."&o=" . $o .$url_var));
    }

    $pageNumber = ceil($rows / $limit);
    if (($rows / $limit) > 0) {
        $tpl->assign('pageNumber', ($num +1)."/".$pageNumber);
    } else {
        $tpl->assign('pageNumber', ($num)."/".$pageNumber);
    }

    if ($page_max > 5 && $num != 0) {
        $tpl->assign('firstPage', ("./main.php?p=".$p."&num=0&limit=".$limit."&poller=".$poller."&template=$template&search=".$search."&type=".$type."&o=" . $o .$url_var));
    }
    if ($page_max > 5 && $num != ($pageNumber-1)) {
        $tpl->assign('lastPage', ("./main.php?p=".$p."&num=".($pageNumber-1)."&limit=".$limit."&template=$template&poller=".$poller."&search=".$search."&type=".$type."&o=" . $o .$url_var));
    }

    /*
 * Select field to change the number of row on the page
 */
    for ($i = 10; $i <= 100; $i = $i +10) {
        $select[$i]=$i;
    }
    if (isset($gopt[$pagination]) && $gopt[$pagination]) {
        $select[$gopt[$pagination]] = $gopt[$pagination];
    }
    if (isset($rows) && $rows) {
        $select[$rows] = $rows;
    }
    ksort($select);
} else {
    for ($i = 10; $i <= 100; $i = $i +10) {
        $select[$i]=$i;
    }
}

?><script type="text/javascript">
function setL(_this){
    var _l = document.getElementsByName('l');
    document.forms['form'].elements['limit'].value = _this;
    _l[0].value = _this;
    _l[1].value = _this;
}
</SCRIPT>
<?php
$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p."&search_type_service=" . $search_type_service."&search_type_host=" . $search_type_host);
$selLim = $form->addElement('select', 'l', _("Rows"), $select, array("onChange" => "setL(this.value);  this.form.submit()"));
$selLim->setSelected($limit);

/*
 * Element we need when we reload the page
 */
$form->addElement('hidden', 'p');
$form->addElement('hidden', 'search');
$form->addElement('hidden', 'num');
$form->addElement('hidden', 'order');
$form->addElement('hidden', 'type');
$form->addElement('hidden', 'sort_types');
$form->setDefaults(array("p" => $p, "search" => $search, "num"=>$num));

/*
 * Init QuickForm
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

if (isset($_GET['host_name'])) {
    $host_name = $_GET['host_name'];
} elseif (!isset($host_name) || $host_name == "") {
    $host_name = null;
}
isset($_GET["status"]) ? $status = $_GET["status"] : $status = null;

$tpl->assign("host_name", $host_name);
$tpl->assign("status", $status);
$tpl->assign("limite", $limite);
$tpl->assign("begin", $num);
$tpl->assign("end", $limit);
$tpl->assign("pagin_page", _("Page"));
$tpl->assign("order", $_GET["order"]);
$tpl->assign("tab_order", $tab_order);
$tpl->assign('form', $renderer->toArray());
$tpl->display("pagination.ihtml");
