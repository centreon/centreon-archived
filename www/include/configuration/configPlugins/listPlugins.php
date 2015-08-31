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

	if (!isset($oreon))
		exit();

	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	include_once("./include/common/quickSearch.php");

	if (isset($_POST["plugin_dir"]) && $_POST["plugin_dir"]) {
		$dir = $_POST["plugin_dir"];
	} elseif (isset($_GET['template']) && $_GET['template']) {
	    $dir = $_GET['template'];
	} else {
		$dir = "";
	}
    $template = $dir;

	if (isset($_GET["create"]) && $_GET["create"])
		mkdir(str_replace("//", "/", $oreon->optGen["nagios_path_plugins"].$dir.$_GET["new_dir"]));

	$plugin_list = return_plugin_list($dir, $search);
	$plugin_dir = return_plugin_dir("");

	$rows = count($plugin_list);

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_path", _("Path"));
	$tpl->assign("headerMenu_size", _("Size"));
	$tpl->assign("headerMenu_date", _("Last modified"));

	# List of elements - Depends on different criteria

	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	# Different style between each lines
	$style = "one";

	# Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	$i = 0;
	$i_real = 0;
	$begin = $num * $limit;
	$end = ($num + 1 ) * $limit;
	foreach ($plugin_list as $name => $path) {
		if (!$search || ($search && stristr($name, $search))) {
			if ($i >= $begin && $i < $end){
				$cmd["command_id"] = 1;
				$path = str_replace('#BR#', "\\n", $path);
				$path = str_replace('#T#', "\\t", $path);
				$path = str_replace('#R#', "\\r", $path);
				$path = str_replace('#S#', "/", $path);
				$path = str_replace('#BS#', "\\", $path);

				$tab = stat($oreon->optGen["nagios_path_plugins"].$dir.$path);
				$mdate = date("d-m-Y H:i", $tab["mtime"]);

				$elemArr[$i_real] = array("MenuClass"=>"list_".$style,
								"RowMenu_name" => substr($name, 1),
								"RowMenu_num" => $i,
								"RowMenu_size" => round(filesize($oreon->optGen["nagios_path_plugins"].$dir.$path) / 1024,2),
								"RowMenu_path" => str_replace("//", "/", $dir.$path),
								"RowMenu_date"=>$mdate);
				$style != "two" ? $style = "two" : $style = "one";
				$i_real++;
			}
			$i++;
		}
	}
	$tpl->assign("elemArr", $elemArr);

	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
$form->addElement('select', 'plugin_dir', _("Directory"), $plugin_dir, array("onChange" => "this.form.submit('')"));
    $form->setDefaults(array('plugin_dir' => $dir));

	# Form2

	$form2 = new HTML_QuickForm('form', 'POST', "?p=".$p);
	$form2->addElement('submit', 'create', _("Create"));
	$form2->addElement('text', 'new_dir', _("Create New Directory"));
	$file = $form2->addElement('file', 'filename', _("File (zip, tar or cfg)"));
	$form2->addElement('submit', 'load', _("Load"));

	if (isset($_GET["filename"]))
		print $_GET["filename"];

	if ($form2->validate()) {
		$ret = $form2->getSubmitValues();
		$fDataz = array();
		$buf = NULL;
		$fDataz = $file->getValue();
		print $fDataz["type"];
	}

	#
	##Toolbar select more_actions
	#
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);

	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer2 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);

	$form->accept($renderer);
	$form2->accept($renderer2);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('form2', $renderer2->toArray());
	$tpl->assign('p', $p);

	$tpl->display("listPlugins.ihtml");
?>