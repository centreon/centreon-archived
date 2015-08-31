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

include("./include/common/autoNumLimit.php");

/*
 * start quickSearch form
 */
$advanced_search = 0;
include_once("./include/common/quickSearch.php");

$elements = $criticality->getList($search);
$rows = count($elements);

include("./include/common/checkPagination.php");

/*
 *  Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_level", _("Criticality level"));
$tpl->assign("headerMenu_icon", _("Icon"));
$tpl->assign("headerMenu_comments", _("Comments"));

$form = new HTML_QuickForm('select_form', 'POST', "?p=" . $p);

$media = new CentreonMedia($pearDB);

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
$rowz = $criticality->getList($search, "level", "ASC", $num * $limit, $limit);
foreach ($rowz as $criticalityId => $row) {
    $selectedElements = $form->addElement('checkbox', "select[" . $criticalityId . "]");
    $elemArr[] = array("RowMenu_select" => $selectedElements->toHtml(),
                       "RowMenu_name" => $row["name"],
                       "RowMenu_level" => $row["level"],
                       "RowMenu_comments" => $row["comments"],
                       "RowMenu_link" => "?p=" . $p . "&o=c&crit_id=" . $criticalityId,
                       "RowMenu_icon" => $media->getFilename($row["icon_id"]));
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array("addL" => "?p=" . $p . "&o=a", "addT" => _("Add"), "delConfirm" => _("Do you confirm the deletion ?")));
?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</SCRIPT>
<?php

$attrs1 = array(
    'onchange' => "javascript: " .
    "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" . _("Do you confirm the deletion?") . "')) {" .
    " 	setO(this.form.elements['o1'].value); submit();} " .    
    "this.form.elements['o1'].selectedIndex = 0");
$form->addElement('select', 'o1', NULL, array(NULL => _("More actions..."), "d" => _("Delete")), $attrs1);
$form->setDefaults(array('o1' => NULL));

$attrs2 = array(
    'onchange' => "javascript: " .
    "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" . _("Do you confirm the deletion?") . "')) {" .
    " 	setO(this.form.elements['o2'].value); submit();} " .    
    "this.form.elements['o1'].selectedIndex = 0");
$form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "d" => _("Delete")), $attrs2);
$form->setDefaults(array('o2' => NULL));

$o1 = $form->getElement('o1');
$o1->setValue(NULL);
$o1->setSelected(NULL);

$o2 = $form->getElement('o2');
$o2->setValue(NULL);
$o2->setSelected(NULL);

$tpl->assign('limit', $limit);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("list.ihtml");
?>
