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
include "./include/common/autoNumLimit.php";

// list of enum
$tabStatus = array(
    -1 => _("Pending"),
    0 => _("OK"),
    1 => _("Warning"),
    2 => _("Critical"),
    3 => _("Unknown")
);

// list without id 0 for select2
$tabStatusFilter = array(
    1 => _("OK"),
    2 => _("Warning"),
    3 => _("Critical"),
    4 => _("Unknown"),
    5 => _("Pending")
);

$searchTraps = filter_var(
    $_POST['searchT'] ?? $_GET['searchT'] ?? null,
    FILTER_SANITIZE_STRING
);

$searchStatus = null;
if (!empty($_POST['status']) || !empty($_GET['status'])) {
    $searchStatus = filter_var(
        $_POST['status'] ?? $_GET['status'],
        FILTER_VALIDATE_INT
    );
}

$searchVendor = null;
if (!empty($_POST['vendor']) || !empty($_GET['vendor'])) {
    $searchVendor = filter_var(
        $_POST['vendor'] ?? $_GET['vendor'],
        FILTER_VALIDATE_INT
    );
}

if ($searchStatus === false || $searchVendor === false) {
    throw new \InvalidArgumentException('Bad Parameters');
}

if (isset($_POST['Search'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['searchTraps'] = $searchTraps;
    $centreon->historySearch[$url]['searchStatus'] = $searchStatus;
    $centreon->historySearch[$url]['searchVendor'] = $searchVendor;
} else {
    //restoring saved values
    $searchTraps = $centreon->historySearch[$url]['searchTraps'] ?? null;
    $searchStatus = $centreon->historySearch[$url]['searchStatus'] ?? null;
    $searchVendor = $centreon->historySearch[$url]['searchVendor'] ?? null;
}

//convert status filter to enum
if ($searchStatus == 5) {
    $enumStatus = -1;
} else {
    $enumStatus = $searchStatus - 1;
}
$queryValues = array();
$rq = 'SELECT SQL_CALC_FOUND_ROWS * FROM traps WHERE 1 ';
// List of elements - Depends on different criteria
if ($searchTraps) {
    $rq .= ' AND (traps_oid LIKE :trapName OR traps_name LIKE :trapName ' .
        'OR manufacturer_id IN (SELECT id FROM traps_vendor WHERE alias LIKE :trapName )) ';
    $queryValues[':trapName'] = '%' . $searchTraps . '%';
}
if ($searchVendor) {
    $rq .= ' AND manufacturer_id = :manufacturer ';
    $queryValues[':manufacturer'] = (int)$searchVendor;
}
if ($searchStatus) {
    $rq .= ' AND traps_status = :status ';
    $queryValues[':status'] = $enumStatus;
}

$rq .= ' ORDER BY manufacturer_id, traps_name LIMIT ' . (int) ($num * $limit) . ', ' . (int)$limit;

$stmt = $pearDB->prepare($rq);

if (isset($queryValues[':trapName'])) {
    $stmt->bindValue(':trapName', $queryValues[':trapName'], PDO::PARAM_STR);
}
if (isset($queryValues[':manufacturer'])) {
    $stmt->bindValue(':manufacturer', $queryValues[':manufacturer'], PDO::PARAM_INT);
}
if (isset($queryValues[':status'])) {
    $stmt->bindValue(':status', $queryValues[':status'], PDO::PARAM_STR);
}

$stmt->execute();

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();
include "./include/common/checkPagination.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? 'w' : 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("OID"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_manufacturer", _("Vendor Name"));
$tpl->assign("headerMenu_args", _("Output Message"));
$tpl->assign("headerMenu_options", _("Options"));

$form = new HTML_QuickFormCustom('form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

$attrTrapsStatus = null;
if (!empty($searchStatus)) {
    $statusDefault = array($tabStatusFilter[$searchStatus] => $searchStatus);
    $attrTrapsStatus = array(
        'defaultDataset' => $statusDefault
    );
}
$form->addElement('select2', 'status', "", $tabStatusFilter, $attrTrapsStatus);

$vendorResult = $pearDB->query("SELECT id, name FROM traps_vendor ORDER BY name, alias");
$vendors = [];
for ($i = 0; $vendor = $vendorResult->fetch(); $i++) {
    $vendors[$vendor['id']] = $vendor['name'];
}

$attrTrapsVendor = null;
if ($searchVendor) {
    $vendorDefault = array($vendors[$searchVendor] => $searchVendor);
    $attrTrapsVendor = array(
        'defaultDataset' => $vendorDefault
    );
}
$form->addElement('select2', 'vendor', "", $vendors, $attrTrapsVendor);

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
for ($i = 0; $trap = $stmt->fetch(); $i++) {
    $trap = array_map(array("CentreonUtils", "escapeAll"), $trap);
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[" . $trap['traps_id'] . "]");
    $moptions .= "&nbsp;&nbsp;&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;" .
        "\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $trap['traps_id'] . "]' />";
    $dbResult2 = $pearDB->query("select alias from traps_vendor where id='" . $trap['manufacturer_id'] . "' LIMIT 1");
    $mnftr = $dbResult2->fetch();
    $dbResult2->closeCursor();
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => $trap["traps_name"],
        "RowMenu_link" => "?p=$p&o=c&traps_id={$trap['traps_id']}",
        "RowMenu_desc" => substr($trap["traps_oid"], 0, 40),
        "RowMenu_status" => $tabStatus[($trap["traps_status"])] ?? $tabStatus[3],
        "RowMenu_args" => $trap["traps_args"],
        "RowMenu_manufacturer" => CentreonUtils::escapeSecure(
            $mnftr["alias"],
            CentreonUtils::ESCAPE_ALL
        ),
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
$attrs1 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"
        . _("Do you confirm the duplication ?") . "')) {" .
        "   setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"
        . _("Do you confirm the deletion ?") . "')) {" .
        "   setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3) {" .
        "   setO(this.form.elements['o1'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o1',
    null,
    array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete")
    ),
    $attrs1
);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"
        . _("Do you confirm the duplication ?") . "')) {" .
        "   setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"
        . _("Do you confirm the deletion ?") . "')) {" .
        "   setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3) {" .
        "   setO(this.form.elements['o2'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o2',
    null,
    array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete")
    ),
    $attrs2
);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchT', $searchTraps);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listTraps.ihtml");
