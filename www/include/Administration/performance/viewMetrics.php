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

require_once './class/centreonBroker.class.php';

const DELETE_GRAPH = "ed";
const RRD_ABSOLUTE = "dst_a";
const RRD_COUNTER = "dst_c";
const RRD_DERIVE = "dst_d";
const RRD_GAUGE = "dst_g";
const HIDE_GRAPH = "hg";
const SHOW_GRAPH = "nhg";
const LOCK_SERVICE = "lk";
const UNLOCK_SERVICE = "nlk";

$indexId = filter_var($_GET["index_id"], FILTER_VALIDATE_INT);

if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])) {
    //filter integer keys
    $selected = array_filter(
        $_POST["select"],
        function ($k) {
            if (is_int($k)) {
                return $k;
            }
        },
        ARRAY_FILTER_USE_KEY
    );

    if ($_POST["o1"] == DELETE_GRAPH || $_POST["o2"] == DELETE_GRAPH) {
        $listMetricsId = array_keys($selected);
        if (count($listMetricsId) > 0) {
            $brk = new CentreonBroker($pearDB);
            $pearDBO->query("UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" .
                implode(', ', $listMetricsId) . ")");
            $brk->reload();
            $pearDB->query("DELETE FROM ods_view_details WHERE metric_id IN (" .
                implode(', ', $listMetricsId) . ")");
        }
    } elseif ($_POST["o1"] == HIDE_GRAPH || $_POST["o2"] == HIDE_GRAPH) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `hidden` = '1' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == SHOW_GRAPH || $_POST["o2"] == SHOW_GRAPH) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `hidden` = '0' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == LOCK_SERVICE || $_POST["o2"] == LOCK_SERVICE) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `locked` = '1' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == UNLOCK_SERVICE || $_POST["o2"] == UNLOCK_SERVICE) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `locked` = '0' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == RRD_GAUGE || $_POST["o2"] == RRD_GAUGE) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `data_source_type` = '0' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == RRD_COUNTER || $_POST["o2"] == RRD_COUNTER) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `data_source_type` = '1' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == RRD_DERIVE || $_POST["o2"] == RRD_DERIVE) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `data_source_type` = '2' WHERE `metric_id` = " . (int)$id);
        }
    } elseif ($_POST["o1"] == RRD_ABSOLUTE || $_POST["o2"] == RRD_ABSOLUTE) {
        foreach (array_keys($selected) as $id) {
            $pearDBO->query("UPDATE metrics SET `data_source_type` = '3' WHERE `metric_id` = " . (int)$id);
        }
    }
}

$query = "SELECT COUNT(*) FROM metrics WHERE to_delete = 0 AND index_id = :indexId";
$stmt = $pearDBO->prepare($query);
$stmt->bindParam(':indexId', $indexId, PDO::PARAM_INT);
$stmt->execute();
$tmp = $stmt->fetch(\PDO::FETCH_ASSOC);
$rows = $tmp["COUNT(*)"];

$tab_class = array("0" => "list_one", "1" => "list_two");
$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");
$yesOrNo = array(null => "No", 0 => "No", 1 => "Yes", 2 => "Rebuilding");
$rrd_dst = array(0 => "GAUGE", 1 => "COUNTER", 2 => "DERIVE", 3 => "ABSOLUTE");

$query = "SELECT * FROM metrics WHERE to_delete = 0 AND index_id = :indexId ORDER BY metric_name";
$stmt2 = $pearDBO->prepare($query);
$stmt2->bindParam(':indexId', $indexId, PDO::PARAM_INT);
$stmt2->execute();
unset($data);
for ($im = 0; $metrics = $stmt2->fetch(\PDO::FETCH_ASSOC); $im++) {
    $metric = array();
    $metric["metric_id"] = $metrics["metric_id"];
    $metric["class"] = $tab_class[$im % 2];
    $metric["metric_name"] = str_replace("#S#", "/", $metrics["metric_name"]);
    $metric["metric_name"] = str_replace("#BS#", "\\", $metric["metric_name"]);
    $metric["unit_name"] = $metrics["unit_name"];
    if (!isset($metrics["data_source_type"]) ||
        isset($metrics["data_source_type"]) &&
        $metrics["data_source_type"] == null
    ) {
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

    $metric["path"] = _CENTREON_VARLIB_ . '/metrics/' . $metric["metric_id"] . ".rrd";

    $data[$im] = $metric;
    unset($metric);
}

include_once "./include/common/checkPagination.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$form = new HTML_QuickFormCustom('form', 'POST', "?p=" . $p);

/*
 * Toolbar select
 */
?>
    <script type="text/javascript">
        var confirm_messages = [
            '<?php echo _("Do you confirm the deletion ?") ?>',
            '<?php echo _("Do you confirm the change of the RRD data source type ? " .
                "If yes, you must rebuild the RRD Database") ?>',
            '<?php echo _("Do you confirm the change of the RRD data source type ? " .
                "If yes, you must rebuild the RRD Database") ?>',
            '<?php echo _("Do you confirm the change of the RRD data source type ? " .
                "If yes, you must rebuild the RRD Database") ?>',
            '<?php echo _("Do you confirm the change of the RRD data source type ? " .
                "If yes, you must rebuild the RRD Database") ?>'
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
    null => _("More actions..."),
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
$form->addElement('select', 'o1', null, $actions, array('onchange' => "javascript:on_action_change('o1')"));
$form->setDefaults(array('o1' => null));

$form->addElement('select', 'o2', null, $actions, array('onchange' => "javascript:on_action_change('o2')"));
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
