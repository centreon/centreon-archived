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
$pagination = "maxViewConfiguration";
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	isset($_GET["list"]) ? $list = $_GET["list"] : $list = NULL;
	$rq = "SELECT COUNT(*) FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM dependency_serviceChild_relation dscr WHERE dscr.dependency_dep_id = dep.dep_id) > 0 ";
	$rq .= " AND (SELECT DISTINCT COUNT(*) FROM dependency_serviceParent_relation dspr WHERE dspr.dependency_dep_id = dep.dep_id) > 0 ";
	if ($search)
		$rq .= " AND dep_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'";
	$res = & $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];
	
	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_description", $lang['description']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	#Dependency list
	$rq = "SELECT dep_id, dep_name, dep_description FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM dependency_serviceChild_relation dscr WHERE dscr.dependency_dep_id = dep.dep_id) > 0 ";
	$rq .= " AND (SELECT DISTINCT COUNT(*) FROM dependency_serviceParent_relation dspr WHERE dspr.dependency_dep_id = dep.dep_id) > 0 ";
	if ($search)
		$rq .= " AND dep_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'";
	$rq .= " LIMIT ".$num * $limit.", ".$limit;
	$res =& $pearDB->query($rq);	
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $res->fetchInto($dep); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$dep['dep_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&dep_id=".$dep['dep_id']."&o=w&search=".$search."&list=".$list."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&dep_id=".$dep['dep_id']."&o=c&search=".$search."&list=".$list."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&dep_id=".$dep['dep_id']."&o=d&select[".$dep['dep_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."&list=".$list."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$dep['dep_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$dep["dep_name"],
						"RowMenu_link"=>"?p=".$p."&o=w&dep_id=".$dep['dep_id'],
						"RowMenu_description"=>$dep["dep_description"],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	
	#
	##Toolbar select $lang["lgd_more_actions"]
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");	  
        $form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs1);
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

		$o2 =& $form->getElement('o2');
		$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);



	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listServiceDependency.ihtml");
	?>