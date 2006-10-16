<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$pagination = "maxViewConfiguration";
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	if (PEAR::isError($res)) 
		print "Mysql Error : ".$pearDB->getMessage();
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	
	if (isset($_GET["plugin_dir"]) && $_GET["plugin_dir"])
		$dir = $_GET["plugin_dir"];
	else
		$dir = "";
	
	if (isset($_GET["create"]) && $_GET["create"]){
		mkdir(str_replace("//", "/", $oreon->optGen["nagios_path_plugins"].$dir.$_GET["new_dir"]));
	}
	
	$plugin_list = return_plugin_list($dir);
	$plugin_dir = return_plugin_dir("");
	
	$rows = count($plugin_list);

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_path", $lang['plg_path']);
	$tpl->assign("headerMenu_size", $lang['plg_size']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu

	#List of elements - Depends on different criteria
	
	$form = new HTML_QuickForm('form', 'GET', "?p=".$p);
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
			$moptions = "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=d&select[".$cmd['command_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>";
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
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	$form->addElement('select', 'plugin_dir', "Directory", $plugin_dir, array("onChange" => "this.form.submit('')"));
	
	# Form2
	
	$form2 = new HTML_QuickForm('form', 'POST', "?p=".$p);
	$form2->addElement('submit', 'create', "Create");
	$form2->addElement('text', 'new_dir', "Create New Directory");
	$file =& $form2->addElement('file', 'filename', $lang["upl_file"]);
	$form2->addElement('submit', 'load', "Load");
	
	if (isset($_GET["filename"])){
		print $_GET["filename"];
				
	}
	
	if ($form2->validate()) {
		print "ok !!";
		$ret = $form2->getSubmitValues();
		$fDataz = array();
		$buf = NULL;
		$fDataz =& $file->getValue();
		print $fDataz["type"];
		/*
		# File Moving
		switch ($fDataz["type"])	{
			case "application/x-zip-compressed" : $msg .= $fDataz["name"]." ".$lang["upl_uplBadType"]."<br>"; break;
			case "application/x-gzip" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." ".$lang["upl_uplOk"]."<br>"; break; // tar.gz
			case "application/octet-stream" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $lang["upl_manualDef"]." ".$lang["upl_uplOk"]."<br>"; break; // Text
			default : $msg .= $lang["upl_uplKo"]."<br>";
		}
		*/
	}

	#
	##Toolbar select 'More actions...'
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");	  
        $form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o1' => NULL));
			$o1 =& $form->getElement('o1');
		$o1->setValue(NULL);
	
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o2' => NULL));
	if ($form->validate())	{
		$o2 =& $form->getElement('o2');
		$o2->setValue(NULL);
	
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
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");
?>