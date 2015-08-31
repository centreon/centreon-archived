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
	$path = "./include/options/centStorage/";

	/*
	 * Set URL for search
	 */
	$url = "viewData.php";

	/*
	 * PHP functions
	 */
	require_once("./include/options/oreon/generalOpt/DB-Func.php");
	require_once("./include/common/common-Func.php");
	require_once("./class/centreonDB.class.php");

    include("./include/common/autoNumLimit.php");
	/*
	 * Prepare search engine
	 */
	if (isset($_POST["search"])) {
		$searchH = $_POST["searchH"];
		$_POST["search"] = $_POST["searchH"];
		$oreon->historySearch[$url] = $search;
	} else if (isset($oreon->historySearch[$url])) {
		$searchH = $oreon->historySearch[$url];
	} else {
		$searchH = NULL;
	}

	if (isset($_POST["searchS"])) {
		$searchS = $_POST["searchS"];
		$oreon->historySearchService[$url] = $searchS;
	} else if (isset($oreon->historySearchService[$url])) {
		$searchS = $oreon->historySearchService[$url];
	} else {
		$searchS = NULL;
	}

	if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])){
		if ($_POST["o"] == "rg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$key."'");
			}
            $brk = new CentreonBroker($pearDB);
            if ($brk->getBroker() == 'broker') {
                $brk->reload();
            }
		} else if ($_POST["o"] == "nrg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '0' WHERE id = '".$key."' AND `must_be_rebuild` = '1'");
			}
		} else if ($_POST["o"] == "ed" && isset($_POST["select"])){
			$selected = $_POST["select"];
			$listMetricsToDelete = array();
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("SELECT metric_id FROM metrics WHERE  `index_id` = '".$key."'");
				while ($metrics = $DBRESULT->fetchRow()){
				    $listMetricsToDelete[] = $metrics['metric_id'];
				}
			}
			$listMetricsToDelete = array_unique($listMetricsToDelete);
			if (count($listMetricsToDelete) > 0) {
    			$pearDBO->query("UPDATE metrics SET to_delete = 1 WHERE metric_id IN (" . join(', ', $listMetricsToDelete) . ")");
                        $pearDBO->query("UPDATE index_data SET to_delete = 1 WHERE id IN (" . join(', ', array_keys($selected)) . ")");
    			$pearDB->query("DELETE FROM ods_view_details WHERE metric_id IN (" . join(', ', $listMetricsToDelete) . ")");
                        $brk = new CentreonBroker($pearDB);
                        if ($brk->getBroker() == 'broker') {
                            $brk->reload();
                        }
			}
		} else if ($_POST["o"] == "hg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '1' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "nhg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `hidden` = '0' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "lk" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '1' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "nlk" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT = $pearDBO->query("UPDATE index_data SET `locked` = '0' WHERE id = '".$key."'");
			}
		}
	}

	if (isset($_POST["o"]) && $_POST["o"] == "d" && isset($_POST["id"])){
		$DBRESULT = $pearDBO->query("UPDATE index_data SET `trashed` = '1' WHERE id = '".htmlentities($_POST["id"], ENT_QUOTES, 'UTF-8')."'");
	}

	if (isset($_POST["o"]) && $_POST["o"] == "rb" && isset($_POST["id"])){
		$DBRESULT = $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".htmlentities($_POST["id"], ENT_QUOTES, 'UTF-8')."'");
	}

	$search_string = "";
	if ($searchH != "" || $searchS != "") {
		if ($searchH != ""){
			$search_string .= " AND i.host_name LIKE '%".htmlentities($searchH, ENT_QUOTES, 'UTF-8')."%' ";
		}
		if ($searchS != "") {
			$search_string .= " AND i.service_description LIKE '%".htmlentities($searchS, ENT_QUOTES, 'UTF-8')."%' ";
		}
	}

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");
	$yesOrNo = array(0 => "No", 1 => "Yes", 2 => "Rebuilding");

	$data = array();
	$DBRESULT = $pearDBO->query("SELECT SQL_CALC_FOUND_ROWS DISTINCT i.* FROM index_data i, metrics m WHERE i.id = m.index_id $search_string ORDER BY host_name, service_description LIMIT ".$num * $limit.", $limit");
	$rows = $pearDBO->numberRows();
	
	for ($i = 0; $index_data = $DBRESULT->fetchRow(); $i++) {
		$DBRESULT2 = $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_data["id"]."' ORDER BY metric_name");
		$metric = "";
		for ($im = 0;$metrics = $DBRESULT2->fetchRow();$im++){
			if ($im) {
				$metric .= " - ";
			}
			$metric .= "<a href='./main.php?p=5010602&o=mmtrc&index_id=".$index_data["id"]."'>".$metrics["metric_name"]."</a>";
			if (isset($metrics["unit_name"]) && $metrics["unit_name"]) {
				$metric .= " (".$metrics["unit_name"].") ";
			}
		}
		$index_data["metrics_name"] = $metric;
		$index_data["service_description"] = "<a href='./main.php?p=5010602&o=msvc&index_id=".$index_data["id"]."'>".$index_data["service_description"]."</a>";
		
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
	</SCRIPT>
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
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Delete graphs"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

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
	$form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Delete graphs"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs2);
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
	$tpl->assign("data", $data);
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
?>
