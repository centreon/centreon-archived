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
    exit();
}

include "./include/common/autoNumLimit.php";

$search = filter_var(
    $_POST['searchN'] ?? $_GET['searchN'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchN']) || isset($_GET['searchN'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]['search'] ?? null;
}

$SearchTool = '';
if ($search) {
    $SearchTool .= " WHERE nagios_name LIKE '%" . htmlentities($search, ENT_QUOTES, "UTF-8") . "%' ";
}

$aclCond = "";
if (!$centreon->user->admin && count($allowedMainConf)) {
    if (isset($search) && $search) {
        $aclCond = " AND ";
    } else {
        $aclCond = " WHERE ";
    }
    $aclCond .= "nagios_id IN (" . implode(',', array_keys($allowedMainConf)) . ") ";
}

/*
 * nagios servers comes from DB
 */
$nagios_servers = array(null => "");
$dbResult = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
while ($nagios_server = $dbResult->fetch()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$dbResult->closeCursor();

$dbResult = $pearDB->query(
    'SELECT SQL_CALC_FOUND_ROWS nagios_id, nagios_name, nagios_comment, nagios_activate, nagios_server_id ' .
    'FROM cfg_nagios ' . $SearchTool . $aclCond . ' ORDER BY nagios_name LIMIT ' . $num * $limit . ', ' . $limit
);

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include "./include/common/checkPagination.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl(__DIR__, $tpl);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_instance", _("Satellites"));
$tpl->assign("headerMenu_desc", _("Description"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Nagios list
 */
$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
for ($i = 0; $nagios = $dbResult->fetch(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[" . $nagios['nagios_id'] . "]");
    if ($nagios["nagios_activate"]) {
        $moptions .= "<a href='main.php?p=" . $p . "&nagios_id=" . $nagios['nagios_id'] . "&o=u&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/disabled.png' class='ico-14' border='0' " .
            "alt='" . _("Disabled") . "'></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=" . $p . "&nagios_id=" . $nagios['nagios_id'] . "&o=s&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/enabled.png' " .
            "class='ico-14' border='0' alt='" . _("Enabled") . "'></a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) " .
        "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $nagios['nagios_id'] . "]' />";
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => $nagios["nagios_name"],
        "RowMenu_instance" => $nagios_servers[$nagios["nagios_server_id"]],
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&nagios_id=" . $nagios['nagios_id'],
        "RowMenu_desc" => substr($nagios["nagios_comment"], 0, 40),
        "RowMenu_status" => $nagios["nagios_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $nagios["nagios_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}

$tpl->assign("elemArr", $elemArr);

// Different messages we put in the template
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "delConfirm" => _("Do you confirm the deletion ?")
    )
);

?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</script>
<?php

foreach (array('o1', 'o2') as $option) {
    $attrs = array(
        'onchange' => "javascript: " .
            "if (this.form.elements['" . $option . "'].selectedIndex == 1 && confirm('" .
            _("Do you confirm the duplication ?") . "')) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 2 && confirm('" .
            _("Do you confirm the deletion ?") . "')) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            ""
    );
    $form->addElement(
        'select',
        $option,
        null,
        array(null => _("More actions..."), "m" => _("Duplicate"), "d" => _("Delete")),
        $attrs
    );
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchN', $search);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listNagios.ihtml");
