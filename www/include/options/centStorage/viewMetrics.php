<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	if (!isset($oreon))
		exit();

        require_once './class/centreonBroker.class.php';
        
	if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])){
		if ($_POST["o1"] == "ed" || $_POST["o2"] == "ed"){
			$selected = $_POST["select"];
			$listMetricsId = array_keys($selected);
			if (count($listMetricsId) > 0) {
    			$pearDBO->query("UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" . join(', ', $listMetricsId) . ")");
    			$pearDB->query("DELETE FROM ods_view_details WHERE metric_id IN (" . join(', ', $listMetricsId) . ")");
                        $brk = new CentreonBroker($pearDB);
                        if ($brk->getBroker() == 'broker') {
                            $brk->reload();
                        }
		    }
			/*foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("SELECT * FROM metrics WHERE `metric_id` = '".$key."'");
				while ($metrics = $DBRESULT->fetchRow()){
					$DBRESULT2 = $pearDBO->query("DELETE FROM metrics WHERE `metric_id` = '".$metrics['metric_id']."'");
					$pearDB->query("DELETE FROM ods_view_details WHERE metric_id = " . $pearDB->escape($metrics['metric_id']));
				}
			}*/
		} else if ($_POST["o1"] == "hg" || $_POST["o2"] == "hg"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `hidden` = '1' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "nhg" || $_POST["o2"] == "nhg"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `hidden` = '0' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "lk" || $_POST["o2"] == "lk"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `locked` = '1' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "nlk" || $_POST["o2"] == "nlk"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `locked` = '0' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "dst_g" || $_POST["o2"] == "dst_g"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '0' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "dst_c" || $_POST["o2"] == "dst_c"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE metrics SET `data_source_type` = '1' WHERE `metric_id` = '".$key."'");
			}
		}
	}

	$search_string = "";
	if (isset($search) && $search)
		$search_string = " WHERE `host_name` LIKE '%$search%' OR `service_description` LIKE '%$search%'";

	$DBRESULT = $pearDBO->query("SELECT COUNT(*) FROM metrics WHERE to_delete = 0 AND index_id = '".$_GET["index_id"]."'");
	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");
	$yesOrNo = array(NULL => "No", 0 => "No", 1 => "Yes", 2 => "Rebuilding");
	$rrd_dst = array(0 => "GAUGE", 1 => "COUNTER", 2 => "DERIVE", 3 => "ABSOLUTE");

	$DBRESULT2 = $pearDBO->query("SELECT * FROM metrics WHERE to_delete = 0 AND index_id = '".$_GET["index_id"]."' ORDER BY metric_name");
	unset($data);
	for ($im = 0;$metrics = $DBRESULT2->fetchRow();$im++){
		$metric = array();
		$metric["metric_id"] = $metrics["metric_id"];
		$metric["class"] = $tab_class[$im % 2];
		$metric["metric_name"] = $metrics["metric_name"];
		$metric["metric_name"] = str_replace("#S#", "/", $metric["metric_name"]);
		$metric["metric_name"] = str_replace("#BS#", "\\", $metric["metric_name"]);
		$metric["unit_name"] = $metrics["unit_name"];
		if ( !isset($metrics["data_source_type"]) || isset($metrics["data_source_type"]) && $metrics["data_source_type"] == NULL) {
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

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);

	## Toolbar select

	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._('Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database')."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 && confirm('"._('Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database')."')) {" .
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
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "ed"=>_("Empty all Service Data"), "dst_g"=>_("Set RRD Data Source Type to GAUGE"), "dst_c"=>_("Set RRD Data Source Type to COUNTER"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._('Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database')."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 && confirm('"._('Do you confirm the change of the RRD data source type ? If yes, you must rebuild the RRD Database')."')) {" .
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
	$form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "ed"=>_("Empty all Service Data"), "dst_g"=>_("Set RRD Data Source Type to GAUGE"), "dst_c"=>_("Set RRD Data Source Type to COUNTER"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs2);
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
