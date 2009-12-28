<?php
/*
 * Copyright 2005-2009 MERETHIS
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
	# end quickSearch form
	
	if (isset($search))
		$res = & $pearDB->query("SELECT COUNT(*) FROM view_img, view_img_dir, view_img_dir_relation WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND img_img_id = img_id AND dir_dir_parent_id = dir_id");
	else
		$res = & $pearDB->query("SELECT COUNT(*) FROM view_img, view_img_dir, view_img_dir_relation WHERE img_img_id = img_id AND dir_dir_parent_id = dir_id");
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Directory"));
	$tpl->assign("headerMenu_img", _("Image"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	if ($search)
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM view_img_dir_relation WHERE img_img_id = img_id GROUP BY img_id ) AS nbr, img_id, img_name, img_path, dir_name, dir_alias FROM view_img, view_img_dir, view_img_dir_relation WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'  OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND img_img_id = img_id AND dir_dir_parent_id = dir_id ORDER BY img_name, dir_alias LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM view_img_dir_relation WHERE img_img_id = img_id GROUP BY img_id ) AS nbr, img_id, img_name, img_path, dir_name, dir_alias FROM view_img, view_img_dir, view_img_dir_relation WHERE img_img_id = img_id AND dir_dir_parent_id = dir_id ORDER BY img_name, dir_alias LIMIT ".$num * $limit.", ".$limit;
	$res =& $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
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
		$style != "two" ? $style = "two" : $style = "one";	
	}
	$tpl->assign("elemArr", $elemArr);
	
	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	
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
	$tpl->assign('p', $p);
	$tpl->assign('session_id', session_id());
	$tpl->assign('syncDir', _("Synchronize Media Directory"));

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listImg.ihtml");
?>
