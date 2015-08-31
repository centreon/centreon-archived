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

require_once './class/centreonBroker.class.php';
        
if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])) {
    if ($_POST["o1"] == "ed" || $_POST["o2"] == "ed"){
        $selected = $_POST["select"];
        $listMetricsId = array_keys($selected);
        if (count($listMetricsId) > 0) {
            $brk = new CentreonBroker($pearDB);
            if ($brk->getBroker() == 'broker') {
                $pearDBO->query("UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" . join(', ', $listMetricsId) . ")");
                $brk->reload();
            } else {
                $pearDBO->query("DELETE FROM metrics WHERE metric_id IN (" . join(', ', $listMetricsId) . ")");
            }
            $pearDB->query("DELETE FROM ods_view_details WHERE metric_id IN (" . join(', ', $listMetricsId) . ")");
        }
    } else if ($_POST["o1"] == "hg" || $_POST["o2"] == "hg") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `hidden` = '1' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "nhg" || $_POST["o2"] == "nhg") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `hidden` = '0' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "lk" || $_POST["o2"] == "lk") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `locked` = '1' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "nlk" || $_POST["o2"] == "nlk") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `locked` = '0' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "dst_g" || $_POST["o2"] == "dst_g") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '0' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "dst_c" || $_POST["o2"] == "dst_c") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '1' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "dst_d" || $_POST["o2"] == "dst_d") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '2' WHERE `metric_id` = '".$key."'");
        }
    } else if ($_POST["o1"] == "dst_a" || $_POST["o2"] == "dst_a") {
        $selected = $_POST["select"];
        foreach ($selected as $key => $value) {
            $DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '3' WHERE `metric_id` = '".$key."'");
        }
    }
}

$search_string = "";
if (isset($search) && $search) {
    $search_string = " WHERE `host_name` LIKE '%$search%' OR `service_description` LIKE '%$search%'";
}

$DBRESULT = $pearDBO->query("SELECT COUNT(*) FROM metrics WHERE to_delete = 0 AND index_id = '".$_GET["index_id"]."'");
$tmp = $DBRESULT->fetchRow();
$rows = $tmp["COUNT(*)"];

$tab_class = array("0" => "list_one", "1" => "list_two");
$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");
$yesOrNo = array(NULL => "No", 0 => "No", 1 => "Yes", 2 => "Rebuilding");
$rrd_dst = array(0 => "GAUGE", 1 => "COUNTER", 2 => "DERIVE", 3 => "ABSOLUTE");

$DBRESULT2 = $pearDBO->query("SELECT * FROM metrics WHERE to_delete = 0 AND index_id = '".$_GET["index_id"]."' ORDER BY metric_name");
unset($data);
for ($im = 0;$metrics = $DBRESULT2->fetchRow();$im++) {
    $metric = array();
    $metric["metric_id"] = $metrics["metric_id"];
    $metric["class"] = $tab_class[$im % 2];
    $metric["metric_name"] = $metrics["metric_name"];
    $metric["metric_name"] = str_replace("#S#", "/", $metric["metric_name"]);
    $metric["metric_name"] = str_replace("#BS#", "\\", $metric["metric_name"]);
    $metric["unit_name"] = $metrics["unit_name"];
    if (!isset($metrics["data_source_type"]) || isset($metrics["data_source_type"]) && $metrics["data_source_type"] == NULL) {
        $metric["data_source_type"] = $rrd_dst["0"];
    } else {
        $metric["data_source_type"] = $rrd_dst[$metrics["data_source_type"]];
    }
    $metric["hidden"] = $yesOrNo[$metrics["hidden"]];
    $metric["locked"] = $yesOrNo[$metrics["locked"]];
    $metric["min"] = $metrics["min"];
    $metric["max"] = $metrics["max"];
    $metric["warn"] = $metrics["warn"];
    $metric["crit"] = $metrics["crit"];
    $data[$im] = $metric;
    unset($metric);
}

include_once "./include/common/checkPagination.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$form = new HTML_QuickForm('form', 'POST', "?p=".$p);

/*
 * Toolbar select
 */
?>
<script type="text/javascript">
var confirm_messages = [
    '<?php echo _("Do you confirm the deletion ?") ?>',
    '<?php echo _("Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database") ?>',
    '<?php echo _("Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database") ?>',
    '<?php echo _("Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database") ?>',
    '<?php echo _("Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database") ?>'
];

function setO(_i) {
    document.forms['form'].elements['o'].value = _i;
}

function on_action_change(id) {
    var selected_id = this.form.elements[id].selectedIndex - 1;
    
    if (selected_id in confirm_messages && !confirm(confirm_messages[selected_id])) {
        return;
    }
    setO(this.form.elements[id].value);
    document.forms['form'].submit();
}
</script>
<?php
$actions = array(
    NULL => _("More actions..."), 
    "ed" => _("Delete graphs"), 
    "dst_a" => _("Set RRD Data Source Type to ABSOLUTE"),
    "dst_c" => _("Set RRD Data Source Type to COUNTER"),
    "dst_d" => _("Set RRD Data Source Type to DERIVE"),
    "dst_g" => _("Set RRD Data Source Type to GAUGE"), 
    "hg" => _("Hide graphs of selected Services"), 
    "nhg" => _("Stop hiding graphs of selected Services"), 
    "lk" => _("Lock Services"), 
    "nlk" => _("Unlock Services")
);
$form->addElement('select', 'o1', NULL, $actions, array('onchange' => "javascript:on_action_change('o1')"));
$form->setDefaults(array('o1' => NULL));

$form->addElement('select', 'o2', NULL, $actions, array('onchange' => "javascript:on_action_change('o2')"));
$form->setDefaults(array('o2' => NULL));

$o1 = $form->getElement('o1');
$o1->setValue(NULL);
$o1->setSelected(NULL);

$o2 = $form->getElement('o2');
$o2->setValue(NULL);
$o2->setSelected(NULL);

$tpl->assign('limit', $limit);

$tpl->assign("p", $p);
$tpl->assign('o', $o);
$tpl->assign("num", $num);
$tpl->assign("limit", $limit);
$tpl->assign("Metric", _("Metric"));
$tpl->assign("Unit", _("Unit"));
$tpl->assign("Warning", _("Warning"));
$tpl->assign("Critical", _("Critical"));
$tpl->assign("Min", _("Min"));
$tpl->assign("Max", _("Max"));
$tpl->assign("NumberOfValues", _("Number of values"));
$tpl->assign("DataSourceType", _("Data source type"));
$tpl->assign("Hidden", _("Hidden"));
$tpl->assign("Locked", _("Locked"));

$tpl->assign("data", $data);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("viewMetrics.ihtml");

?>