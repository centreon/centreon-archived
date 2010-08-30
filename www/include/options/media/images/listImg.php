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
	# end quickSearch form
	
	if (isset($search))
		$res = & $pearDB->query("SELECT COUNT(*) FROM view_img, view_img_dir, view_img_dir_relation WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%') AND img_img_id = img_id AND dir_dir_parent_id = dir_id");
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
	$tpl->assign("headerMenu_comment", _("Comment"));

	if ($search)
		$rq = "SELECT * FROM view_img_dir LEFT JOIN view_img_dir_relation ON dir_dir_parent_id = dir_id LEFT JOIN view_img ON img_img_id = img_id WHERE (img_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%'  OR dir_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%') ORDER BY dir_alias, img_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT * FROM view_img_dir LEFT JOIN view_img_dir_relation ON dir_dir_parent_id = dir_id LEFT JOIN view_img ON img_img_id = img_id ORDER BY dir_alias, img_name LIMIT ".$num * $limit.", ".$limit;
	$res =& $pearDB->query($rq);

	$form = new HTML_QuickForm('form', 'GET', "?p=".$p);

	/* 
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $elem =& $res->fetchRow(); $i++) {
		if (isset($elem['dir_id']) && !isset($elemArr[$elem['dir_id']])) {
			$selectedDirElem =& $form->addElement('checkbox', "select[".$elem['dir_id']."]");
			$selectedDirElem->setAttribute("onclick", "setSubNodes(this, 'select[".$elem['dir_id']."-')");
			$rowOpt = array("RowMenu_select"=>$selectedDirElem->toHtml(),
					"RowMenu_DirLink"=>"?p=".$p."&o=cd&dir_id=".$elem['dir_id'],
					"RowMenu_dir"=>$elem["dir_name"],
					"RowMenu_dir_cmnt"=>$elem["dir_comment"],
					"RowMenu_empty"=>_("Empty directory"),
					"counter"=> 0	);
			$elemArr[$elem['dir_id']] = array("head"=>$rowOpt, "elem"=>array());
		}

		if ($elem['img_id']) {
			if (isset($search) && $search)
			    $searchOpt = "&search=".$search;
			else
			    $searchOpt = "";
			$selectedImgElem =& $form->addElement('checkbox', "select[".$elem['dir_id']."-".$elem['img_id']."]");
			$rowOpt = array("RowMenu_select"=>$selectedImgElem->toHtml(),
					"RowMenu_ImgLink"=>"?p=".$p."&o=ci&img_id=".$elem['img_id'],
					"RowMenu_DirLink"=>"?p=".$p."&o=cd&dir_id=".$elem['dir_id'],
					"RowMenu_dir"=>$elem["dir_name"],
					"RowMenu_img"=>html_entity_decode($elem["dir_alias"]."/".$elem["img_path"], ENT_QUOTES, "UTF-8"),
					"RowMenu_name"=>html_entity_decode($elem["img_name"], ENT_QUOTES, "UTF-8"),
					"RowMenu_comment"=>html_entity_decode($elem["img_comment"], ENT_QUOTES, "UTF-8") );
			$elemArr[$elem['dir_id']]["elem"][$i] = $rowOpt;
			$elemArr[$elem['dir_id']]["head"]["counter"]++;
		}
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
	function submitO(_i) {
		if (document.forms['form'].elements[_i].selectedIndex == 1 && confirm('<?php print _("Do you confirm the deletion ?"); ?>')) {
		 	setO(document.forms['form'].elements[_i].value); document.forms['form'].submit();
		} else if (document.forms['form'].elements[_i].selectedIndex == 2) {
		 	setO(document.forms['form'].elements[_i].value); document.forms['form'].submit();
		}
		document.forms['form'].elements[_i].selectedIndex = 0;
	}

	function setSubNodes(theElement, like) {
	    var theForm = theElement.form;
	    var z = 0;
	    for (z=0; z<theForm.length;z++) {
		if (theForm[z].type == 'checkbox' && theForm[z].disabled == '0' && theForm[z].name.indexOf(like)>=0 ){
			if (theElement.checked && !theForm[z].checked) {
                                theForm[z].checked = true;
                                if (typeof(_selectedElem) != 'undefined') {
                                        putInSelectedElem(theForm[z].id);
                                }
			} else if (!theElement.checked && theForm[z].checked) {
                                theForm[z].checked = false;
                                if (typeof(_selectedElem) != 'undefined') {
                                        removeFromSelectedElem(theForm[z].id);
                                }
                        }
                }
    	    }
	}


	</SCRIPT>
	<?php
	$actions = array(NULL=>_("More actions"), "d"=>_("Delete"), "m"=>_("Move images"));
	$form->addElement('select', 'o1', NULL, $actions, array('onchange'=>"javascript:submitO('o1');"));
	$form->addElement('select', 'o2', NULL, $actions, array('onchange'=>"javascript:submitO('o2');"));
	$form->setDefaults(array('o1' => NULL));
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
