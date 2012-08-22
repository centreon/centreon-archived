<?php

/*
 * Copyright 2005-2012 MERETHIS
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
