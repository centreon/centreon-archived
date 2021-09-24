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

include_once "./class/centreonUtils.class.php";
include_once "./include/common/autoNumLimit.php";

$type = filter_var(
    $_POST['type'] ?? $_GET['type'] ?? $centreon->historySearch[$url]['type'] ?? null,
    FILTER_VALIDATE_INT
);

$search = filter_var(
    $_POST['searchC'] ?? $_GET['searchC'] ?? $centreon->historySearch[$url]['search' . $type] ?? '',
    FILTER_SANITIZE_STRING
);

$displayLocked = filter_var(
    $_POST['displayLocked'] ?? $_GET['displayLocked'] ?? 'off',
    FILTER_VALIDATE_BOOLEAN
);

// keep checkbox state if navigating in pagination
// this trick is mandatory cause unchecked checkboxes do not post any data
if (
    (isset($centreon->historyPage[$url]) && $centreon->historyPage[$url] > 0 || $num !== 0)
    && isset($centreon->historySearch[$url]['displayLocked'])
) {
    $displayLocked = $centreon->historySearch[$url]['displayLocked'];
}

// As the four pages of this menu are generated dynamically from the same ihtml and php files,
// we need to save $type and to overload the $num value set in the pagination.php file to restore each user's filter.
$savedType = $centreon->historySearch[$url]['type'] ?? null;

// As pagination.php will already check if the current page was previously loaded or not,
// we're only checking if the last loaded page have the same $type value (1,2,3 or 4)
if (isset($type) && $type !== $savedType) {
    //if so, we reset the pagination and save the current $type
    $num = $centreon->historyPage[$url] = 0;
} else {
    //saving again the pagination filter
    $centreon->historyPage[$url] = $num;
}

// store filters in session
$centreon->historySearch[$url] = [
    'search' . $type => $search,
    'type' => $type,
    'displayLocked' => $displayLocked
];

// Locked filter
$lockedFilter = $displayLocked ? "" : "AND command_locked = 0 ";

// Type filter
$typeFilter = $type ? "AND `command_type` = " . $type . " " : "";
$search = tidySearchKey($search, $advanced_search);

$rq = "SELECT SQL_CALC_FOUND_ROWS `command_id`, `command_name`, `command_line`, `command_type`, " .
    "`command_activate` FROM `command` WHERE `command_name` LIKE '%" . $search . "%' " .
    $typeFilter . $lockedFilter . " ORDER BY `command_name` LIMIT " . $num * $limit . ", " . $limit;

$dbResult = $pearDB->query($rq);
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include_once "./include/common/checkPagination.php";

//Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? 'w' : 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Command Line"));
$tpl->assign("headerMenu_type", _("Type"));
$tpl->assign("headerMenu_huse", _("Host Uses"));
$tpl->assign("headerMenu_suse", _("Services Uses"));
$tpl->assign("headerMenu_options", _("Options"));

$form = new HTML_QuickFormCustom('form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

$addrType = $type ? "&type=" . $type : "";
$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . $addrType. "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

// Define command Type table
$commandType = array(
    "1" => _("Notification"),
    "2" => _("Check"),
    "3" => _("Miscellaneous"),
    "4" => _("Discovery")
);

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
for ($i = 0; $cmd = $dbResult->fetch(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[" . $cmd['command_id'] . "]");

    if ($cmd["command_activate"]) {
        $moptions = "<a href='main.php?p=" . $p . "&command_id=" . $cmd['command_id'] . "&o=di&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/disabled.png' " .
            "class='ico-14 margin_right' border='0' alt='" . _("Disabled") . "'></a>";
    } else {
        $moptions = "<a href='main.php?p=" . $p . "&command_id=" . $cmd['command_id'] . "&o=en&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/enabled.png' " .
            "class='ico-14 margin_right' border='0' alt='" . _("Enabled") . "'></a>";
    }

    if (isset($lockedElements[$cmd['command_id']])) {
        $selectedElements->setAttribute('disabled', 'disabled');
    } else {
        $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) "
            . "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) "
            . "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr["
            . $cmd['command_id'] . "]' />";
    }
    $decodedCommand = myDecodeCommand($cmd["command_line"]);
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => $cmd["command_name"],
        "RowMenu_link" => "main.php?p=" . $p .
            "&o=c&command_id=" . $cmd['command_id'] . "&type=" . $cmd['command_type'],
        "RowMenu_desc" => (strlen($decodedCommand) > 50)
            ? CentreonUtils::escapeSecure(substr($decodedCommand, 0, 50), CentreonUtils::ESCAPE_ALL) . "..."
            : CentreonUtils::escapeSecure($decodedCommand, CentreonUtils::ESCAPE_ALL),
        "RowMenu_type" => $commandType[$cmd["command_type"]],
        "RowMenu_huse" => "<a name='#' title='" . _("Host links (host template links)") . "'>" .
            getHostNumberUse($cmd['command_id']) . " (" . getHostTPLNumberUse($cmd['command_id']) . ")</a>",
        "RowMenu_suse" => "<a name='#' title='" . _("Service links (service template links)") . "'>" .
            getServiceNumberUse($cmd['command_id']) . " (" . getServiceTPLNumberUse($cmd['command_id']) . ")</a>",
        "RowMenu_status" => $cmd["command_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $cmd["command_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

// Different messages we put in the template
if (isset($_GET['type']) && $_GET['type'] != "") {
    $type = htmlentities($_GET['type'], ENT_QUOTES, "UTF-8");
} elseif (!isset($_GET['type'])) {
    $type = 2;
}

$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a&type=" . $type,
        "addT" => _("Add"),
        "delConfirm" => _("Do you confirm the deletion ?")
    )
);

$redirectType = $form->addElement('hidden', 'type');
$redirectType->setValue($type);

// Toolbar select
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange' => "javascript: " .
            "var bChecked = isChecked(); " .
            "if (this.form.elements['$option'].selectedIndex != 0 && !bChecked) {" .
            "   alert('" . _("Please select one or more items") . "'); return false;} " .
            "if (this.form.elements['$option'].selectedIndex == 1 && confirm('" .
            _("Do you confirm the duplication ?") . "')) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "else if (this.form.elements['$option'].selectedIndex == 2 && confirm('" .
            _("Do you confirm the deletion ?") . "')) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "else if (this.form.elements['$option'].selectedIndex == 3) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "else if (this.form.elements['$option'].selectedIndex == 4) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "this.form.elements['$option'].selectedIndex = 0"
    );
    $form->addElement(
        'select',
        $option,
        null,
        array(
            null => _("More actions..."),
            "m" => _("Duplicate"),
            "d" => _("Delete"),
            "me" => _("Enable"),
            "md" => _("Disable")
        ),
        $attrs1
    );
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</script>
<?php

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('limit', $limit);
$tpl->assign('type', $type);
$tpl->assign('searchC', $search);
$tpl->assign("displayLocked", $displayLocked);
$tpl->display("listCommand.ihtml");
