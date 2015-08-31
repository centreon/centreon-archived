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
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	isset($_GET["list"]) ? $list = $_GET["list"] : $list = NULL;
	
        $aclCond = "";
        if (!$oreon->user->admin) {
            $aclCond = " AND meta_service_meta_id IN ($metastr) ";
        }
        
	# HostGroup LCA
	$rq = "SELECT COUNT(*) FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                        FROM dependency_metaserviceParent_relation dmspr 
                        WHERE dmspr.dependency_dep_id = dep.dep_id $aclCond) > 0 
                 OR    (SELECT DISTINCT COUNT(*) 
                        FROM dependency_metaserviceChild_relation dmspr 
                        WHERE dmspr.dependency_dep_id = dep.dep_id $aclCond) > 0";
	if ($search)
		$rq .= " AND (dep_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR dep_alias LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')";
	$DBRESULT = $pearDB->query($rq);
	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
	
	include("./include/common/checkPagination.php");
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r'; 
	$tpl->assign('mode_access', $lvl_access);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_description", _("Description"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu
	#Dependcy list
	$rq = "SELECT dep_id, dep_name, dep_description FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                        FROM dependency_metaserviceParent_relation dmspr 
                        WHERE dmspr.dependency_dep_id = dep.dep_id $aclCond) > 0 
                 OR    (SELECT DISTINCT COUNT(*) 
                        FROM dependency_metaserviceChild_relation dmspr 
                        WHERE dmspr.dependency_dep_id = dep.dep_id $aclCond) > 0";
	if ($search)
		$rq .= " AND (dep_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR dep_description LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')";
	$rq .= " ORDER BY dep_name, dep_description LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = $pearDB->query($rq);

	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $dep = $DBRESULT->fetchRow(); $i++) {		
		$moptions = "";
		$selectedElements = $form->addElement('checkbox', "select[".$dep['dep_id']."]");	
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$dep['dep_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$dep["dep_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&dep_id=".$dep['dep_id'],
						"RowMenu_description"=>$dep["dep_description"],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	

	include("./include/common/checkPagination.php");

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
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");	  
        $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
			$o1 = $form->getElement('o1');
		$o1->setValue(NULL);
	
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
	$form->setDefaults(array('o2' => NULL));

		$o2 = $form->getElement('o2');
		$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listMetaServiceDependency.ihtml");
?>