<?php
/*
 * Copyright 2005-2015 Centreon
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
$search = '';
if (isset($_POST['searchCurve']) && $_POST['searchCurve']) {
    $search = $_POST['searchCurve'];
    $SearchTool = " WHERE name LIKE '%".$search."%'";
}

$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM giv_components_template".$SearchTool);

$tmp = $DBRESULT->fetchRow();
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
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Data Source Name"));
$tpl->assign("headerMenu_legend", _("Legend"));
$tpl->assign("headerMenu_stack", _("Stacked"));
$tpl->assign("headerMenu_order", _("Order"));
$tpl->assign("headerMenu_Transp", _("Transparency"));
$tpl->assign("headerMenu_tickness", _("Thickness"));
$tpl->assign("headerMenu_fill", _("Filling"));
$tpl->assign("headerMenu_options", _("Options"));

if ($SearchTool != null) {
    $ClWh1 = "AND host_id IS NULL";
    $ClWh2 = "AND gct.host_id = h.host_id";
} else {
    $ClWh1 = "WHERE host_id IS NULL";
    $ClWh2 = "WHERE gct.host_id = h.host_id";
}
$rq = "( SELECT compo_id, NULL as host_name, host_id, service_id, name, ds_stack, ds_order, ds_name, ds_color_line, ds_color_area, ds_filled, ds_legend, default_tpl1, ds_tickness, ds_transparency FROM giv_components_template $SearchTool $ClWh1 ) UNION ( SELECT compo_id, host_name, gct.host_id, gct.service_id, name, ds_stack, ds_order, ds_name, ds_color_line, ds_color_area, ds_filled, ds_legend, default_tpl1, ds_tickness, ds_transparency FROM giv_components_template AS gct, host AS h $SearchTool $ClWh2 ) ORDER BY host_name, name LIMIT ".$num * $limit.", ".$limit;
$DBRESULT = $pearDB->query($rq);

$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$yesOrNo = array(null => _("No"), 0 => _("No"), 1 => _("Yes"));
$elemArr = array();
for ($i = 0; $compo = $DBRESULT->fetchRow(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[".$compo['compo_id']."]");
    $moptions = "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$compo['compo_id']."]'></input>";
    $titles = $pearDB->query("SELECT h.host_name FROM giv_components_template AS gct, host AS h WHERE gct.host_id = '".$compo["host_id"]."' AND gct.host_id = h.host_id");
    if ($titles->numRows()) {
        $title = $titles->fetchRow();
    } else {
        $title = array("host_name"=>"Global");
    }
    $titles->free();
    $elemArr[$i] = array("MenuClass"=>"list_".$style,
                        "title"=>$title["host_name"],
                        "RowMenu_select"=>$selectedElements->toHtml(),
                        "RowMenu_name"=>$compo["name"],
                        "RowMenu_link"=>"?p=".$p."&o=c&compo_id=".$compo['compo_id'],
                        "RowMenu_desc"=>$compo["ds_name"],
                        "RowMenu_legend"=>$compo["ds_legend"],
                        "RowMenu_stack"=>$yesOrNo[$compo["ds_stack"]],
                        "RowMenu_order"=>$compo["ds_order"],
                        "RowMenu_transp"=>$compo["ds_transparency"],
                        "RowMenu_clrLine"=>$compo["ds_color_line"],
                        "RowMenu_clrArea"=>$compo["ds_color_area"],
                        "RowMenu_fill"=>$yesOrNo[$compo["ds_filled"]],
                        "RowMenu_tickness"=>$compo["ds_tickness"],
                        "RowMenu_options"=>$moptions);
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

/*
 * Toolbar select
 */
?>
<script type="text/javascript">
function setO(_i) {
    document.forms['form'].elements['o'].value = _i;
}
</script>
<?php
$attrs1 = array(
    'onchange'=>"javascript: " .
                "if (this.form.elements['o1'].selectedIndex === 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                " 	setO(this.form.elements['o1'].value); submit();} " .
                "else if (this.form.elements['o1'].selectedIndex === 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                " 	setO(this.form.elements['o1'].value); submit();} ".
                "this.form.elements['o1'].selectedIndex = 0;".
                "");
$form->addElement('select', 'o1', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array(
    'onchange'=>"javascript: " .
                "if (this.form.elements['o2'].selectedIndex === 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                " 	setO(this.form.elements['o2'].value); submit();} " .
                "else if (this.form.elements['o2'].selectedIndex === 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                " 	setO(this.form.elements['o2'].value); submit();} " .
                "this.form.elements['o2'].selectedIndex = 0;".
                "");
$form->addElement('select', 'o2', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
$o2 = $form->getElement('o2');
$o2->setValue(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchCurve', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listComponentTemplates.ihtml");
