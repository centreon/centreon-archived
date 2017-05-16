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

if (!isset($centreon)) {
    exit();
}
require_once './class/centreonDuration.class.php';
require_once './class/centreonBroker.class.php';
include_once("./include/monitoring/common-Func.php");

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the option dir
 */
$path = "./include/Administration/performance/";

/*
 * Set URL for search
 */
$url = "viewData.php";

/*
 * PHP functions
 */
require_once("./include/Administration/parameters/DB-Func.php");
require_once("./include/common/common-Func.php");
require_once("./class/centreonDB.class.php");

include("./include/common/autoNumLimit.php");

/*
 * Prepare search engine
 */
$inputArguments = array(
    'search' => FILTER_SANITIZE_STRING,
    'searchH' => FILTER_SANITIZE_STRING,
    'num' => FILTER_SANITIZE_STRING,
    'searchS' => FILTER_SANITIZE_STRING,
    'search' => FILTER_SANITIZE_STRING,
    'searchP' => FILTER_SANITIZE_STRING,
    'o' => FILTER_SANITIZE_STRING,
    'o1' => FILTER_SANITIZE_STRING,
    'o2' => FILTER_SANITIZE_STRING,
    'select' => array(
        'filter' => FILTER_SANITIZE_STRING,
        'flags'  => FILTER_REQUIRE_ARRAY
    ),
    'id' => FILTER_SANITIZE_STRING
);
$inputGet = filter_input_array(
    INPUT_GET,
    $inputArguments
);
$inputPost = filter_input_array(
    INPUT_POST,
    $inputArguments
);

$inputs = array();
foreach ($inputArguments as $argumentName => $argumentValue) {
    if (!is_null($inputGet[$argumentName])) {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } else {
        $inputs[$argumentName] = $inputPost[$argumentName];
    }
}


if (isset($inputs["search"])) {
    $searchH = $inputs["searchH"];
    $num = $inputs['num'] = 0;
    $inputs["search"] = $inputs["searchH"];
    $oreon->historySearch[$url] = $search;
} elseif (isset($oreon->historySearch[$url])) {
    $searchH = $oreon->historySearch[$url];
} else {
    $searchH = null;
}

if (isset($inputs["searchS"])) {
    $searchS = $inputs["searchS"];
    $num = $inputs['num'] = 0;
    $oreon->historySearchService[$url] = $searchS;
} elseif (isset($oreon->historySearchService[$url])) {
    $searchS = $oreon->historySearchService[$url];
} else {
    $searchS = null;
}

/* Search for poller */
if (isset($inputs['searchP']) && is_numeric($inputs['searchP'])) {
    $searchP = $inputs['searchP'];
    $num = $inputs['num'] = 0;
} else {
    $searchP = null;
}

/* Get broker type */
$brk = new CentreonBroker($pearDB);

if ((isset($inputs["o1"]) && $inputs["o1"]) || (isset($inputs["o2"]) && $inputs["o2"])) {
    if ($inputs["o"] == "rg" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$key."'");
        }
        $brk->reload();
    } elseif ($inputs["o"] == "nrg" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '0' WHERE id = '".$key."' AND `must_be_rebuild` = '1'");
        }
    } elseif ($inputs["o"] == "ed" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        $listMetricsToDelete = array();
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("SELECT metric_id FROM metrics WHERE  `index_id` = '".$key."'");
            while ($metrics = $DBRESULT->fetchRow()) {
                $listMetricsToDelete[] = $metrics['metric_id'];
            }
        }
        $listMetricsToDelete = array_unique($listMetricsToDelete);
        if (count($listMetricsToDelete) > 0) {
            $pearDBO->query("UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" . join(', ', $listMetricsToDelete) . ")");
            $pearDBO->query("UPDATE index_data SET to_delete = 1 WHERE id IN (" . join(', ', array_keys($selected)) . ")");
            $pearDB->query("DELETE FROM ods_view_details WHERE metric_id IN (" . join(', ', $listMetricsToDelete) . ")");
            $brk->reload();
        }
    } elseif ($inputs["o"] == "hg" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '1' WHERE id = '".$key."'");
        }
    } elseif ($inputs["o"] == "nhg" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '0' WHERE id = '".$key."'");
        }
    } elseif ($inputs["o"] == "lk" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '1' WHERE id = '".$key."'");
        }
    } elseif ($inputs["o"] == "nlk" && isset($inputs["select"])) {
        $selected = $inputs["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '0' WHERE id = '".$key."'");
        }
    }
}

if (isset($inputs["o"]) && $inputs["o"] == "d" && isset($inputs["id"])) {
    $DBRESULT = $pearDBO->query("UPDATE index_data SET `trashed` = '1' WHERE id = '".htmlentities($inputs["id"], ENT_QUOTES, 'UTF-8')."'");
}

if (isset($inputs["o"]) && $inputs["o"] == "rb" && isset($inputs["id"])) {
    $DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".htmlentities($inputs["id"], ENT_QUOTES, 'UTF-8')."'");
}

$search_string = "";
$extTables = "";
if ($searchH != "" || $searchS != "" || $searchP != "") {
    if ($searchH != "") {
        $search_string .= " AND i.host_name LIKE '%".htmlentities($searchH, ENT_QUOTES, 'UTF-8')."%' ";
    }
    if ($searchS != "") {
        $search_string .= " AND i.service_description LIKE '%".htmlentities($searchS, ENT_QUOTES, 'UTF-8')."%' ";
    }
    if ($searchP != "") {
        /* Centron Broker */
        $extTables = ", hosts h";
        $search_string .= " AND i.host_id = h.host_id AND h.instance_id = " . $searchP;
    }
}

$tab_class = array("0" => "list_one", "1" => "list_two");
$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");
$yesOrNo = array(0 => "No", 1 => "Yes", 2 => "Rebuilding");

$data = array();
$DBRESULT = $pearDBO->query("SELECT SQL_CALC_FOUND_ROWS DISTINCT i.* FROM index_data i, metrics m" . $extTables . " WHERE i.id = m.index_id $search_string ORDER BY host_name, service_description LIMIT ".$num * $limit.", $limit");
$rows = $pearDBO->numberRows();

for ($i = 0; $index_data = $DBRESULT->fetchRow(); $i++) {
    $DBRESULT2 = $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_data["id"]."' ORDER BY metric_name");
    $metric = "";
    for ($im = 0; $metrics = $DBRESULT2->fetchRow(); $im++) {
        if ($im) {
            $metric .= " - ";
        }
        $metric .= $metrics["metric_name"];
        if (isset($metrics["unit_name"]) && $metrics["unit_name"]) {
            $metric .= "(".$metrics["unit_name"].")";
        }
    }
    $index_data["metrics_name"] = $metric;
    $index_data["service_description"] = "<a href='./main.php?p=50119&o=msvc&index_id=".$index_data["id"]."'>".$index_data["service_description"]."</a>";

    $index_data["storage_type"] = $storage_type[$index_data["storage_type"]];
    $index_data["must_be_rebuild"] = $yesOrNo[$index_data["must_be_rebuild"]];
    $index_data["to_delete"] = $yesOrNo[$index_data["to_delete"]];
    $index_data["trashed"] = $yesOrNo[$index_data["trashed"]];
    $index_data["hidden"] = $yesOrNo[$index_data["hidden"]];

    if (isset($index_data["locked"])) {
        $index_data["locked"] = $yesOrNo[$index_data["locked"]];
    } else {
        $index_data["locked"] = $yesOrNo[0];
    }

    $index_data["class"] = $tab_class[$i % 2];
    $data[$i] = $index_data;
}

/* Get the list of running poller */
$queryPollers = "SELECT instance_id, name FROM instances ORDER BY name";
$res = $pearDBO->query($queryPollers);
$instances = array();
if (false === PEAR::isError($res)) {
    while ($row = $res->fetchRow()) {
        $instances[$row['instance_id']] = $row['name'];
    }
}

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$form = new HTML_QuickForm('form', 'POST', "?p=".$p);

?>
<script type="text/javascript">
function setO(_i) {
    document.forms['form'].elements['o'].value = _i;
}
</script>
<?php
$attrs1 = array(
    'onchange'=>"javascript: " .
            "if (this.form.elements['o1'].selectedIndex == 1) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 2) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 3 && confirm('"._('Do you confirm the deletion ?')."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 4) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 5) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 6) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 7) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "");
$form->addElement('select', 'o1', null, array(null=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Delete graphs"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs1);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange'=>"javascript: " .
            "if (this.form.elements['o2'].selectedIndex == 1) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 2) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 3 && confirm('"._('Do you confirm the deletion ?')."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 4) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 5) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 6) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 7) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "");
$form->addElement('select', 'o2', null, array(null=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Delete graphs"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs2);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);

$tpl->assign("p", $p);
$tpl->assign('o', $o);
$tpl->assign("num", $num);
$tpl->assign("limit", $limit);
$tpl->assign("data", $data);
$tpl->assign("instances", $instances);
$tpl->assign("Host", _("Host"));
$tpl->assign("Service", _("Service"));
$tpl->assign("Metrics", _("Metrics"));
$tpl->assign("RebuildWaiting", _("Rebuild Waiting"));
$tpl->assign("Delete", _("Delete"));
$tpl->assign("Hidden", _("Hidden"));
$tpl->assign("Locked", _("Locked"));
$tpl->assign("StorageType", _("Storage Type"));
$tpl->assign("Actions", _("Actions"));

$tpl->assign('Services', _("Services"));
$tpl->assign('Hosts', _("Hosts"));
$tpl->assign('Pollers', _("Pollers"));
$tpl->assign('Search', _("Search"));

if (isset($searchH)) {
    $tpl->assign('searchH', $searchH);
}
if (isset($searchS)) {
    $tpl->assign('searchS', $searchS);
}
if (isset($searchP)) {
    $tpl->assign('searchP', $searchP);
} else {
    $tpl->assign('searchP', '');
}

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("viewData.ihtml");
