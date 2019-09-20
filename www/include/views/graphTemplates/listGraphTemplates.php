<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit;
}

include("./include/common/autoNumLimit.php");

$SearchTool = null;
$queryValues = array();
$search = null;

if (isset($_POST['searchGT'])) {
    $search = $_POST['searchGT'];
    $centreon->historySearch[$url] = $search;
} elseif (isset($_GET['searchGT'])) {
    $search = $_GET['searchGT'];
    $centreon->historySearch[$url] = $search;
} elseif (isset($centreon->historySearch[$url])) {
    $search = $centreon->historySearch[$url];
}

if ($search) {
    $SearchTool = " WHERE name LIKE :search";
    $queryValues['search'] = '%' . $search . '%';
}

$rq = 'SELECT SQL_CALC_FOUND_ROWS graph_id, name, default_tpl1, vertical_label, base, split_component FROM ' .
    'giv_graphs_template gg ' . $SearchTool . ' ORDER BY name LIMIT ' . $num * $limit . ', ' . $limit;
$stmt = $pearDB->prepare($rq);
foreach ($queryValues as $key => $value) {
    $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
}

$stmt->execute();

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Description"));
$tpl->assign("headerMenu_split_component", _("Split Components"));
$tpl->assign("headerMenu_base", _("Base"));
$tpl->assign("headerMenu_options", _("Options"));

#List
$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);
//Different style between each lines
$style = "one";

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

//Fill a tab with a mutlidimensionnal Array we put in $tpl
$elemArr = array();
for ($i = 0; $graph = $stmt->fetch(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[" . $graph['graph_id'] . "]");
    $moptions = "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;" .
        "\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $graph['graph_id'] . "]' />";
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => $graph["name"],
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&graph_id=" . $graph['graph_id'],
        "RowMenu_desc" => $graph["vertical_label"],
        "RowMenu_base" => $graph["base"],
        "RowMenu_split_component" => $graph["split_component"] ? _("Yes") : _("No"),
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign(
    'msg',
    array("addL" => "main.php?p=" . $p . "&o=a", "addT" => _("Add"), "delConfirm" => _("Do you confirm the deletion ?"))
);

/*
 * Toolbar select
 */
?>
    <script type="text/javascript">
        function setO(_i) {
            document.forms['form'].elements['o'].value = _i;
        }
    </SCRIPT>
<?php
$attrs1 = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o1',
    null,
    array(null => _("More actions..."), "m" => _("Duplicate"), "d" => _("Delete")),
    $attrs1
);
$form->setDefaults(array('o1' => null));
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o2',
    null,
    array(null => _("More actions..."), "m" => _("Duplicate"), "d" => _("Delete")),
    $attrs
);
$form->setDefaults(array('o2' => null));

$o2 = $form->getElement('o2');
$o2->setValue(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchGT', htmlentities($search));

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listGraphTemplates.ihtml");
