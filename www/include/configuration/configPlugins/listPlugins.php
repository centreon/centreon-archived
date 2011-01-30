<?php
/*
 * Copyright 2005-2010 MERETHIS
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
		
	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	
	if (isset($_POST["plugin_dir"]) && $_POST["plugin_dir"])
		$dir = $_POST["plugin_dir"];
	else
		$dir = "";
	
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
	$form->addElement('select', 'plugin_dir', "Directory", $plugin_dir, array("onChange" => "this.form.submit('')"));
	
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