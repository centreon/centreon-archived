<?php
/*
 * Copyright 2005-2019 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/common/pagination.php $
 * SVN : $Id: pagination.php 10473 2010-05-19 21:25:56Z jmathis $
 *
 */
global $centreon;

if (!isset($centreon)) {
    exit();
}

global $bNewChart, $num, $limit, $search, $url, $pearDB, $search_type_service,
       $search_type_host, $host_name, $rows, $p, $gopt, $pagination, $poller, $order, $orderby;

$type = $_REQUEST["type"] ?? null;
$o = $_GET["o"] ?? null;

//saving current pagination filter value and current displayed page
$centreon->historyPage[$url] = $num;
$centreon->historyLastUrl = $url;

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

if (!isset($_GET["search_type_host"])
    && !isset($centreon->search_type_host)
    && !isset($_GET["search_type_service"])
    && !isset($centreon->search_type_service)
) {
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

if (isset($_REQUEST['searchHost']) && $_REQUEST['searchHost']) {
    $url_var .= "&searchHost=" . $_REQUEST['searchHost'];
}

if (isset($_REQUEST['searchHostgroup']) && $_REQUEST['searchHostgroup']) {
    $url_var .= "&searchHostgroup=" . $_REQUEST['searchHostgroup'];
}

if (isset($_REQUEST['searchPoller']) && $_REQUEST['searchPoller']) {
    $url_var .= "&searchPoller=" . $_REQUEST['searchPoller'];
}

if (isset($_REQUEST['searchService']) && $_REQUEST['searchService']) {
    $url_var .= "&searchService=" . $_REQUEST['searchService'];
}

if (isset($_REQUEST['searchServicegroup']) && $_REQUEST['searchServicegroup']) {
    $url_var .= "&searchServicegroup=" . $_REQUEST['searchServicegroup'];
}

if (isset($_REQUEST['searchHostTemplate']) && $_REQUEST['searchHostTemplate']) {
    $url_var .= "&searchHostTemplate=" . $_REQUEST['searchHostTemplate'];
}

if (isset($_REQUEST['searchServiceTemplate']) && $_REQUEST['searchServiceTemplate']) {
    $url_var .= "&searchServiceTemplate=" . $_REQUEST['searchServiceTemplate'];
}

if (isset($_GET["sort_types"])) {
    $url_var .= "&sort_types=" . $_GET["sort_types"];
    $sort_type = $_GET["sort_types"];
}

/*
 * Smarty template Init
 */
$path = "./include/configuration/configKnowledge/";
$tpl = initSmartyTpl($path, new Smarty(), "./");

$page_max = ceil($rows / $limit);
if ($num >= $page_max && $rows) {
    $num = $page_max - 1;
}

$pageArr = array();
$iStart = 0;
for ($i = 5, $iStart = $num; $iStart && $i > 0; $i--) {
    $iStart--;
}

for ($i2 = 0, $iEnd = $num; ($iEnd < ($rows / $limit - 1)) && ($i2 < (5 + $i)); $i2++) {
    $iEnd++;
}

if ($rows != 0) {
    for ($i = $iStart; $i <= $iEnd; $i++) {
        $urlPage = "main.php?p=" . $p . "&order=" . $order . "&orderby=" . $orderby .
        "&num=" . $i . "&limit=" . $limit . "&type=" . $type .
        "&o=" . $o . $url_var;
        $pageArr[$i] = array(
            "url_page" => $urlPage,
            "label_page" => "<b>" . ($i + 1) . "</b>",
            "num" => $i
        );
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
        $tpl->assign(
            'pagePrev',
            ("./main.php?p=" . $p . "&order=" . $order . "&orderby=" . $orderby . "&num=" . $prev . "&limit=" .
                $limit . "&type=" . $type . "&o=" . $o . $url_var)
        );
    }

    if (($next = $num + 1) < ($rows / $limit)) {
        $tpl->assign(
            'pageNext',
            ("./main.php?p=" . $p . "&order=" . $order . "&orderby=" . $orderby . "&num=" . $next . "&limit=" .
                $limit . "&type=" . $type . "&o=" . $o . $url_var)
        );
    }

    $pageNumber = ceil($rows / $limit);
    if (($rows / $limit) > 0) {
        $tpl->assign('pageNumber', ($num + 1) . "/" . $pageNumber);
    } else {
        $tpl->assign('pageNumber', ($num) . "/" . $pageNumber);
    }

    if ($page_max > 5 && $num != 0) {
        $tpl->assign(
            'firstPage',
            ("./main.php?p=" . $p . "&order=" . $order . "&orderby=" . $orderby . "&num=0&limit=" .
                $limit . "&type=" . $type . "&o=" . $o . $url_var)
        );
    }

    if ($page_max > 5 && $num != ($pageNumber - 1)) {
        $tpl->assign(
            'lastPage',
            ("./main.php?p=" . $p . "&order=" . $order . "&orderby=" . $orderby . "&num=" . ($pageNumber - 1) .
                "&limit=" . $limit . "&type=" . $type . "&o=" . $o . $url_var)
        );
    }

    /*
     * Select field to change the number of row on the page
     */
    for ($i = 10; $i <= 100; $i = $i + 10) {
        $select[$i] = $i;
    }

    if (isset($gopt[$pagination]) && $gopt[$pagination]) {
        $select[$gopt[$pagination]] = $gopt[$pagination];
    }

    if (isset($rows) && $rows) {
        $select[$rows] = $rows;
    }

    ksort($select);
}

?>
<script type="text/javascript">
    function setL(_this) {
        var _l = document.getElementsByName('l');
        document.forms['form'].elements['limit'].value = _this;
        _l[0].value = _this;
        _l[1].value = _this;
    }
</script>
<?php
$form = new HTML_QuickFormCustom(
    'select_form',
    'GET',
    "?p=" . $p . "&search_type_service=" . $search_type_service . "&search_type_host=" . $search_type_host
);
$selLim = $form->addElement(
    'select',
    'l',
    _("Rows"),
    $select,
    array("onChange" => "setL(this.value);  this.form.submit()")
);
$selLim->setSelected($limit);

// Element we need when we reload the page
$form->addElement('hidden', 'p');
$form->addElement('hidden', 'search');
$form->addElement('hidden', 'num');
$form->addElement('hidden', 'order');
$form->addElement('hidden', 'type');
$form->addElement('hidden', 'sort_types');
$form->setDefaults(array("p" => $p, "search" => $search, "num" => $num));

// Init QuickForm
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$host_name = $_GET["host_name"] ?? null;
$status = $_GET["status"] ?? null;

$tpl->assign("host_name", $host_name);
$tpl->assign("status", $status);
$tpl->assign("limite", isset($limite) ? $limite : null);
$tpl->assign("begin", $num);
$tpl->assign("end", $limit);
$tpl->assign("pagin_page", _("Page"));
if (isset($_GET["order"])) {
    $tpl->assign("order", $_GET["order"] === "DESC" ? "DESC" : "ASC");
} else {
    $tpl->assign("order", "ASC");
}
$tpl->assign("tab_order", $tab_order);
$tpl->assign('form', $renderer->toArray());

$tpl->display("templates/pagination.ihtml");
