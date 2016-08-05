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
    
if (!isset($oreon)) {
    exit;
}

include("./include/common/autoNumLimit.php");

$SearchTool = null;
$search = '';
if (isset($_POST['searchVM']) && $_POST['searchVM']) {
    $search = $_POST['searchVM'];
    $SearchTool = " WHERE vmetric_name LIKE '%".$search."%'";
}

$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM virtual_metrics".$SearchTool);
if (PEAR::isError($DBRESULT)) {
    print "DB Error : ".$DBRESULT->getDebugInfo();
}

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
$tpl->assign("headerMenu_unit", _("Unit"));
$tpl->assign("headerMenu_rpnfunc", _("Function"));
$tpl->assign("headerMenu_count", _("Data Count"));
$tpl->assign("headerMenu_dtype", _("DEF Type"));
$tpl->assign("headerMenu_hidden", _("Hidden"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));
$rq = "SELECT  * FROM virtual_metrics $SearchTool ORDER BY index_id,vmetric_name LIMIT ".$num * $limit.", ".$limit;
$DBRESULT = $pearDB->query($rq);
if (PEAR::isError($DBRESULT)) {
    print "Mysql Error : ".$DBRESULT->getDebugInfo();
}
    
$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$deftype = array(0 => "CDEF", 1 => "VDEF");
$yesOrNo = array(null => "No", 0 => "No", 1 => "Yes");
$elemArr = array();
for ($i = 0; $vmetric = $DBRESULT->fetchRow(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[".$vmetric['vmetric_id']."]");
    if ($vmetric["vmetric_activate"]) {
        $moptions = "<a href='main.php?p=".$p."&vmetric_id=".$vmetric['vmetric_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>";
    } else {
        $moptions = "<a href='main.php?p=".$p."&vmetric_id=".$vmetric['vmetric_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/enabled.png' class='ico-14 margin_right'  border='0' alt='"._("Enabled")."'></a>";
    }
    $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$vmetric['vmetric_id']."]'></input>";
    $dbindd     = $pearDBO->query("SELECT id,host_id,service_id FROM index_data WHERE id = '".$vmetric['index_id']."'");
    if (PEAR::isError($dbindd)) {
        print "DB Error : ".$dbindd->getDebugInfo()."<br />";
    }
    $indd = $dbindd->fetchRow();
    $dbindd->free();
    $dbhsrname = $pearDB->query("(SELECT concat(h.host_name,' > ',s.service_description) full_name FROM host_service_relation AS hsr, host AS h, service AS s WHERE hsr.host_host_id = h.host_id AND hsr.service_service_id = s.service_id AND h.host_id = '".$indd["host_id"]."' AND s.service_id = '".$indd["service_id"]."') UNION (SELECT concat(h.host_name,' > ',s.service_description) full_name FROM host_service_relation AS hsr, host AS h, service AS s, hostgroup_relation AS hr WHERE hsr.hostgroup_hg_id = hr.hostgroup_hg_id AND hr.host_host_id = h.host_id AND hsr.service_service_id = s.Service_id AND h.host_id = '".$indd["host_id"]."' AND s.service_id = '".$indd["service_id"]."') ORDER BY full_name");
    if (PEAR::isError($dbhsrname)) {
        print "DB Error : ".$dbhsrname->getDebugInfo()."<br />";
    }
    $hsrname = $dbhsrname->fetchRow();
    $dbhsrname->free();
    $hsrname["full_name"] = str_replace('#S#', "/", $hsrname["full_name"]);
    $hsrname["full_name"] = str_replace('#BS#', "\\", $hsrname["full_name"]);

### TODO : data_count
    $elemArr[$i] = array("MenuClass"=>"list_".$style,
                    "title"=>$hsrname["full_name"],
                    "RowMenu_select"=>$selectedElements->toHtml(),
                    "RowMenu_ckstate"=>$vmetric["ck_state"],
                    "RowMenu_name"=>$vmetric["vmetric_name"],
                    "RowMenu_link"=>"?p=".$p."&o=c&vmetric_id=".$vmetric['vmetric_id'],
                    "RowMenu_unit"=>$vmetric["unit_name"],
                    "RowMenu_rpnfunc"=>$vmetric["rpn_function"],
                    "RowMenu_count"=>"-",
                    "RowMenu_dtype"=>$deftype[$vmetric["def_type"]],
                    "RowMenu_hidden"=>$yesOrNo[$vmetric["hidden"]],
                    "RowMenu_status"=>$vmetric["vmetric_activate"] ? _("Enabled") : _("Disabled"),
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
            "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "");
                  
    $form->addElement('select', 'o1', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);

$form->setDefaults(array('o1' => null));
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array(
    'onchange'=>"javascript: " .
            "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "");
$form->addElement('select', 'o2', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
$form->setDefaults(array('o2' => null));

$o2 = $form->getElement('o2');
$o2->setValue(null);
$tpl->assign('limit', $limit);
$tpl->assign('searchVM', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listVirtualMetrics.ihtml");

