<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	if (isset($search))
		$res = & $pearDB->query("SELECT COUNT(*) FROM view_img, view_img_dir, view_img_dir_relation WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND img_img_id = img_id AND dir_dir_parent_id = dir_id");
	else
		$res = & $pearDB->query("SELECT COUNT(*) FROM view_img, view_img_dir, view_img_dir_relation WHERE img_img_id = img_id AND dir_dir_parent_id = dir_id");
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Directory"));
	$tpl->assign("headerMenu_img", _("Image"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu
	# img list
	if ($search)
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM view_img_dir_relation WHERE img_img_id = img_id GROUP BY img_id ) AS nbr, img_id, img_name, img_path, dir_name, dir_alias FROM view_img, view_img_dir, view_img_dir_relation WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'  OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND img_img_id = img_id AND dir_dir_parent_id = dir_id ORDER BY img_name, dir_alias LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM view_img_dir_relation WHERE img_img_id = img_id GROUP BY img_id ) AS nbr, img_id, img_name, img_path, dir_name, dir_alias FROM view_img, view_img_dir, view_img_dir_relation WHERE img_img_id = img_id AND dir_dir_parent_id = dir_id ORDER BY img_name, dir_alias LIMIT ".$num * $limit.", ".$limit;
	$res =& $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();	for ($i = 0; $img =& $res->fetchRow(); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$img['img_id']."]");	
		$moptions = "<a href='main.php?p=".$p."&img_id=".$img['img_id']."&o=w&&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='"._("View")."'></a>&nbsp;&nbsp;";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>html_entity_decode($img["img_name"], ENT_QUOTES),
						"RowMenu_link"=>"?p=".$p."&o=c&img_id=".$img['img_id'],
						"RowMenu_dir"=>$img["dir_name"],
						"RowMenu_img"=>html_entity_decode($img["dir_alias"]."/".$img["img_path"], ENT_QUOTES),
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	
	#
	##Toolbar select
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions"), "d"=>_("Delete")/*, "mc"=>_("Massive change")*/), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o2'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions"), "d"=>_("Delete")/*, "mc"=>_("Massive change")*/), $attrs2);
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
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listImg.ihtml");
?>
