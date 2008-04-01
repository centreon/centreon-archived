<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	if (isset($_GET["plugin_dir"]) && $_GET["plugin_dir"])
		$dir = $_GET["plugin_dir"];
	else
		$dir = "";
	
	if (isset($_GET["create"]) && $_GET["create"])
		mkdir(str_replace("//", "/", $oreon->optGen["nagios_path_plugins"].$dir.$_GET["new_dir"]));
	
	$plugin_list = return_plugin_list($dir);
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
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu

	#List of elements - Depends on different criteria
	
	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	$i = 0;
	$i_real = 0;
	$begin = $num * $limit;
	$end = ($num + 1 ) * $limit;
	foreach ($plugin_list as $name => $path) {
		if ($i >= $begin && $i < $end){
			$cmd["command_id"] = 1;
			$selectedElements =& $form->addElement('checkbox', "select[".$cmd['command_id']."]");	
			$moptions = "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=d&select[".$cmd['command_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('"._("Do you confirm the deletion ?")."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='"._("Delete")."'></a>";
			$path = str_replace('#BR#', "\\n", $path);
			$path = str_replace('#T#', "\\t", $path);
			$path = str_replace('#R#', "\\r", $path);
			$path = str_replace('#S#', "/", $path);
			$path = str_replace('#BS#', "\\", $path);
			$elemArr[$i_real] = array("MenuClass"=>"list_".$style, 
							"RowMenu_select"=>$selectedElements->toHtml(),
							"RowMenu_name" => substr($name, 1),
							"RowMenu_num" => $i,
							"RowMenu_size" => round(filesize($oreon->optGen["nagios_path_plugins"].$dir.$path) / 1024,2),
							"RowMenu_path" => str_replace("//", "/", $dir.$path),
							"RowMenu_options"=>$moptions);
			$style != "two" ? $style = "two" : $style = "one";
			$i_real++;
		}
		$i++;
	}
	$tpl->assign("elemArr", $elemArr);
	
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	$form->addElement('select', 'plugin_dir', "Directory", $plugin_dir, array("onChange" => "this.form.submit('')"));
	
	# Form2
	
	$form2 = new HTML_QuickForm('form', 'POST', "?p=".$p);
	$form2->addElement('submit', 'create', "Create");
	$form2->addElement('text', 'new_dir', "Create New Directory");
	$file =& $form2->addElement('file', 'filename', _("File (zip, tar or cfg)"));
	$form2->addElement('submit', 'load', "Load");
	
	if (isset($_GET["filename"]))
		print $_GET["filename"];
	
	if ($form2->validate()) {
		$ret = $form2->getSubmitValues();
		$fDataz = array();
		$buf = NULL;
		$fDataz =& $file->getValue();
		print $fDataz["type"];
		/*
		# File Moving
		switch ($fDataz["type"])	{
			case "application/x-zip-compressed" : $msg .= $fDataz["name"]." ".$lang["upl_uplBadType"]."<br />"; break;
			case "application/x-gzip" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." ".$lang["upl_uplOk"]."<br />"; break; // tar.gz
			case "application/octet-stream" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $lang["upl_manualDef"]." ".$lang["upl_uplOk"]."<br />"; break; // Text
			default : $msg .= $lang["upl_uplKo"]."<br />";
		}
		*/
	}

	#
	##Toolbar select $lang["lgd_more_actions"]
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
				"if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);
	
	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer2 =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	
	$form->accept($renderer);	
	$form2->accept($renderer2);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('form2', $renderer2->toArray());
	$tpl->assign('p', $p);
	$tpl->display("listPlugins.ihtml");
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->display("include/common/legend.ihtml");
?>