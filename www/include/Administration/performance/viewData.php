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


const REBUILD_RRD = "rg";
const STOP_REBUILD_RRD = "nrg";
const DELETE_GRAPH = "ed";
const HIDE_GRAPH = "hg";
const SHOW_GRAPH = "nhg";
const LOCK_SERVICE = "lk";
const UNLOCK_SERVICE = "nlk";

/*
 * Prepare search engine
 */
$inputArguments = array(
    'Search' => FILTER_SANITIZE_STRING,
    'searchH' => FILTER_SANITIZE_STRING,
    'num' => FILTER_SANITIZE_NUMBER_INT,
    'limit' => FILTER_SANITIZE_NUMBER_INT,
    'searchS' => FILTER_SANITIZE_STRING,
    'searchP' => FILTER_SANITIZE_STRING,
    'o' => FILTER_SANITIZE_STRING,
    'o1' => FILTER_SANITIZE_STRING,
    'o2' => FILTER_SANITIZE_STRING,
    'select' => array(
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_REQUIRE_ARRAY
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
    if (!empty($inputPost[$argumentName]) && (
            (is_array($inputPost[$argumentName]) && $inputPost[$argumentName]) ||
            (!is_array($inputPost[$argumentName]) && trim($inputPost[$argumentName]) != '')
        )
    ) {
        $inputs[$argumentName] = $inputPost[$argumentName];
    } else {
        $inputs[$argumentName] = $inputGet[$argumentName];
    }
}

$searchS = null;
$searchH = null;
$searchP = null;

if (isset($inputs['Search'])) {
    $num = 0;
    $centreon->historySearch[$url] = array();
    $searchH = $inputs["searchH"];
    $centreon->historySearch[$url]["searchH"] = $searchH;
    $searchS = $inputs["searchS"];
    $centreon->historySearch[$url]["searchS"] = $searchS;
    $searchP = $inputs["searchP"];
    $centreon->historySearch[$url]["searchP"] = $searchP;
} else {
    if (isset($centreon->historySearch[$url]['searchH'])) {
        $searchH = $centreon->historySearch[$url]['searchH'];
    }
    if (isset($centreon->historySearch[$url]['searchS'])) {
        $searchS = $centreon->historySearch[$url]['searchS'];
    }
    if (isset($centreon->historySearch[$url]['searchP'])) {
        $searchP = $centreon->historySearch[$url]['searchP'];
    }
}

/* Get broker type */
$brk = new CentreonBroker($pearDB);

if ((isset($inputs["o1"]) && $inputs["o1"]) || (isset($inputs["o2"]) && $inputs["o2"])) {
    //filter integer keys
    $selected = array_filter(
        $inputs["select"],
        function ($k) {
            if (is_int($k)) {
                return $k;
            }
        },
        ARRAY_FILTER_USE_KEY
    );
    if ($inputs["o"] == REBUILD_RRD && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = " . $id);
        }
        $brk->reload();
    } elseif ($inputs["o"] == STOP_REBUILD_RRD && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $query = "UPDATE index_data SET `must_be_rebuild` = '0' WHERE `must_be_rebuild` = '1' AND id = " . $id;
            $pearDBO->query($query);
        }
    } elseif ($inputs["o"] == DELETE_GRAPH && !empty($selected)) {
        $listMetricsToDelete = array();
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("SELECT metric_id FROM metrics WHERE  `index_id` = " . $id);
            while ($metrics = $DBRESULT->fetchRow()) {
                $listMetricsToDelete[] = $metrics['metric_id'];
            }
        }
        $listMetricsToDelete = array_unique($listMetricsToDelete);
        if (count($listMetricsToDelete) > 0) {
            $query = "UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" .
                implode(', ', $listMetricsToDelete) . ")";
            $pearDBO->query($query);
            $query = "UPDATE index_data SET to_delete = 1 WHERE id IN (" . implode(', ', array_keys($selected)) . ")";
            $pearDBO->query($query);
            $query = "DELETE FROM ods_view_details WHERE metric_id IN (" . implode(', ', $listMetricsToDelete) . ")";
            $pearDB->query($query);
            $brk->reload();
        }
    } elseif ($inputs["o"] == HIDE_GRAPH && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '1' WHERE id = " . $id);
        }
    } elseif ($inputs["o"] == SHOW_GRAPH && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '0' WHERE id = " . $id);
        }
    } elseif ($inputs["o"] == LOCK_SERVICE && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '1' WHERE id = " . $id);
        }
    } elseif ($inputs["o"] == UNLOCK_SERVICE && !empty($selected)) {
        foreach (array_keys($selected) as $id) {
            $DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '0' WHERE id = " . $id);
        }
    }
}

if (isset($inputs["o"]) && $inputs["o"] == "d" && isset($inputs["id"])) {
    $query = "UPDATE index_data SET `trashed` = '1' WHERE id = '" .
        htmlentities($inputs["id"], ENT_QUOTES, 'UTF-8') . "'";
    $pearDBO->query($query);
}

if (isset($inputs["o"]) && $inputs["o"] == "rb" && isset($inputs["id"])) {
    $query = "UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '" .
        htmlentities($inputs["id"], ENT_QUOTES, 'UTF-8') . "'";
    $pearDBO->query($query);
}

$search_string = "";
$extTables = "";
if ($searchH != "" || $searchS != "" || $searchP != "") {
    if ($searchH != "") {
        $search_string .= " AND i.host_name LIKE '%" . htmlentities($searchH, ENT_QUOTES, 'UTF-8') . "%' ";
    }
    if ($searchS != "") {
        $search_string .= " AND i.service_description LIKE '%" . htmlentities($searchS, ENT_QUOTES, 'UTF-8') . "%' ";
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
$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT i.* FROM index_data i, metrics m" . $extTables .
    " WHERE i.id = m.index_id $search_string ORDER BY host_name, service_description LIMIT " . $num * $limit .
    ", $limit";
$DBRESULT = $pearDBO->query($query);
$rows = $pearDBO->query("SELECT FOUND_ROWS()")->fetchColumn();

for ($i = 0; $indexData = $DBRESULT->fetchRow(); $i++) {
    $query = "SELECT * FROM metrics WHERE index_id = '" . $indexData["id"] . "' ORDER BY metric_name";
    $DBRESULT2 = $pearDBO->query($query);
    $metric = "";
    for ($im = 0; $metrics = $DBRESULT2->fetchRow(); $im++) {
        if ($im) {
            $metric .= " - ";
        }
        $metric .= $metrics["metric_name"];
        if (isset($metrics["unit_name"]) && $metrics["unit_name"]) {
            $metric .= "(" . $metrics["unit_name"] . ")";
        }
    }
    $indexData["metrics_name"] = $metric;
    $indexData["service_description"] = "<a href='./main.php?p=50119&o=msvc&index_id=" . $indexData["id"] . "'>" .
        $indexData["service_description"] . "</a>";

    $indexData["storage_type"] = $storage_type[$indexData["storage_type"]];
    $indexData["must_be_rebuild"] = $yesOrNo[$indexData["must_be_rebuild"]];
    $indexData["to_delete"] = $yesOrNo[$indexData["to_delete"]];
    $indexData["trashed"] = $yesOrNo[$indexData["trashed"]];
    $indexData["hidden"] = $yesOrNo[$indexData["hidden"]];

    if (isset($indexData["locked"])) {
        $indexData["locked"] = $yesOrNo[$indexData["locked"]];
    } else {
        $indexData["locked"] = $yesOrNo[0];
    }

    $indexData["class"] = $tab_class[$i % 2];
    $data[$i] = $indexData;
}

//select2 Poller
$poller = $searchP ?? '';
$pollerRoute = './api/internal.php?object=centreon_configuration_poller&action=list';
$attrPoller = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $pollerRoute,
    'multiple' => false,
    'defaultDataset' => $poller,
    'linkedObject' => 'centreonInstance'
);

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$form = new HTML_QuickFormCustom('form', 'POST', "?p=" . $p);

$form->addElement('select2', 'searchP', "", array(), $attrPoller);

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);


?>
    <script type="text/javascript">
        function setO(_i) {
            document.forms['form'].elements['o'].value = _i;
        }
    </script>
<?php
$attrs1 = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o1'].selectedIndex == 1) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3 && confirm('" .
        _('Do you confirm the deletion ?') . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 4) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 5) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 6) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 7) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        ""
);
$form->addElement('select', 'o1', null, array(
    null => _("More actions..."),
    "rg" => _("Rebuild RRD Database"),
    "nrg" => _("Stop rebuilding RRD Databases"),
    "ed" => _("Delete graphs"),
    "hg" => _("Hide graphs of selected Services"),
    "nhg" => _("Stop hiding graphs of selected Services"),
    "lk" => _("Lock Services"),
    "nlk" => _("Unlock Services")
), $attrs1);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o2'].selectedIndex == 1) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3 && confirm('" .
        _('Do you confirm the deletion ?') . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 4) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 5) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 6) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 7) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        ""
);
$form->addElement('select', 'o2', null, array(
    null => _("More actions..."),
    "rg" => _("Rebuild RRD Database"),
    "nrg" => _("Stop rebuilding RRD Databases"),
    "ed" => _("Delete graphs"),
    "hg" => _("Hide graphs of selected Services"),
    "nhg" => _("Stop hiding graphs of selected Services"),
    "lk" => _("Lock Services"),
    "nlk" => _("Unlock Services")
), $attrs2);
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
if (isset($instances)) {
    $tpl->assign("instances", $instances);
}
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

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("viewData.ihtml");
