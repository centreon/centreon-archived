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

/*
 * Object init
 */
$mediaObj = new CentreonMedia($pearDB);

include "./include/common/autoNumLimit.php";

$o = "";

$search = filter_var(
    $_POST['searchST'] ?? $_GET['searchST'] ?? $centreon->historySearch[$url]['search'] ?? '',
    FILTER_SANITIZE_STRING
);

$displayLocked = filter_var(
    $_POST['displayLocked'] ?? $_GET['displayLocked'] ?? 'off',
    FILTER_VALIDATE_BOOLEAN
);

// keep checkbox state if navigating in pagination
// this trick is mandatory cause unchecked checkboxes do not post any data
if (
    isset($centreon->historyPage[$url])
    && ($centreon->historyPage[$url] > 0 || $num !== 0)
    && isset($centreon->historySearch[$url]['displayLocked'])
) {
    $displayLocked = $centreon->historySearch[$url]['displayLocked'];
}

// store filters in session
$centreon->historySearch[$url] = [
    'search' => $search,
    'displayLocked' => $displayLocked
];

// Locked filter
$lockedFilter = $displayLocked ? "" : "AND sv.service_locked = 0 ";

//Service Template Model list
if ($search) {
    $query = "SELECT SQL_CALC_FOUND_ROWS sv.service_id, sv.service_description, sv.service_alias, " .
        "sv.service_activate, sv.service_template_model_stm_id " .
        "FROM service sv " .
        "WHERE (sv.service_description LIKE '%" . $search . "%' OR sv.service_alias LIKE '%" . $search . "%') " .
        "AND sv.service_register = '0' " .
        $lockedFilter .
        "ORDER BY service_description LIMIT " . $num * $limit . ", " . $limit;
} else {
    $query = "SELECT SQL_CALC_FOUND_ROWS sv.service_id, sv.service_description, sv.service_alias, " .
        "sv.service_activate, sv.service_template_model_stm_id " .
        "FROM service sv " .
        "WHERE sv.service_register = '0' " .
        $lockedFilter .
        "ORDER BY service_description LIMIT " . $num * $limit . ", " . $limit;
}
$dbResult = $pearDB->query($query);
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include "./include/common/checkPagination.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_desc", _("Name"));
$tpl->assign("headerMenu_alias", _("Alias"));
$tpl->assign("headerMenu_retry", _("Scheduling"));
$tpl->assign("headerMenu_parent", _("Templates"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$search = tidySearchKey($search, $advanced_search);

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);
// Different style between each lines
$style = "one";

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();

$interval_length = $oreon->optGen['interval_length'];

$search = str_replace('#S#', "/", $search);
$search = str_replace('#BS#', "\\", $search);

$centreonToken = createCSRFToken();

for ($i = 0; $service = $dbResult->fetch(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[" . $service['service_id'] . "]");
    if (isset($lockedElements[$service['service_id']])) {
        $selectedElements->setAttribute('disabled', 'disabled');
    } else {
        if ($service["service_activate"]) {
            $moptions .= "<a href='main.php?p=" . $p . "&service_id=" . $service['service_id'] . "&o=u&limit=" .
                $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
                "'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                        <path d='M0 0h24v24H0z' fill='none'/>
                        <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
                    </svg>
                </a>&nbsp;&nbsp;";
        } else {
            $moptions .= "<a href='main.php?p=" . $p . "&service_id=" . $service['service_id'] . "&o=s&limit=" .
                $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
                "'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
                        <path d='M0 0h24v24H0z' fill='none'/>
                        <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/>
                    </svg>
                </a>&nbsp;&nbsp;";
        }
        $moptions .= "&nbsp;";
        $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
            "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;" .
            "\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
            $service['service_id'] . "]' />";
    }

    /*If the description of our Service Model is in the Template definition,
     we have to catch it, whatever the level of it :-) */
    if (!$service["service_description"]) {
        $service["service_description"] = getMyServiceName($service['service_template_model_stm_id']);
    }

    //TPL List
    $tplArr = array();
    $tplStr = "";
    $tplArr = getMyServiceTemplateModels($service["service_template_model_stm_id"]);

    if ((is_array($tplArr) || $tplArr instanceof Countable) && count($tplArr)) {
        foreach ($tplArr as $key => $value) {
            $value = str_replace('#S#', "/", $value);
            $value = str_replace('#BS#', "\\", $value);
            $tplStr .= "&nbsp;->&nbsp;<a href='main.php?p=60206&o=c&service_id=" . $key . "'>" . $value . "</a>";
        }
    }

    $service["service_description"] = str_replace("#BR#", "\n", $service["service_description"]);
    $service["service_description"] = str_replace("#T#", "\t", $service["service_description"]);
    $service["service_description"] = str_replace("#R#", "\r", $service["service_description"]);
    $service["service_description"] = str_replace("#S#", '/', $service["service_description"]);
    $service["service_description"] = str_replace("#BS#", '\\', $service["service_description"]);

    $service["service_alias"] = str_replace("#BR#", "\n", $service["service_alias"]);
    $service["service_alias"] = str_replace("#T#", "\t", $service["service_alias"]);
    $service["service_alias"] = str_replace("#R#", "\r", $service["service_alias"]);
    $service["service_alias"] = str_replace("#S#", '/', $service["service_alias"]);
    $service["service_alias"] = str_replace("#BS#", '\\', $service["service_alias"]);

    // Get service intervals in seconds
    $normal_check_interval
        = getMyServiceField($service['service_id'], "service_normal_check_interval") * $interval_length;
    $retry_check_interval
        = getMyServiceField($service['service_id'], "service_retry_check_interval") * $interval_length;

    if ($normal_check_interval % 60 == 0) {
        $normal_units = "min";
        $normal_check_interval = $normal_check_interval / 60;
    } else {
        $normal_units = "sec";
    }

    if ($retry_check_interval % 60 == 0) {
        $retry_units = "min";
        $retry_check_interval = $retry_check_interval / 60;
    } else {
        $retry_units = "sec";
    }

    if (isset($service['esi_icon_image']) && $service['esi_icon_image']) {
        $svc_icon = "./img/media/" . $mediaObj->getFilename($service['esi_icon_image']);
    } elseif (
        $icone = $mediaObj->getFilename(
            getMyServiceExtendedInfoField(
                $service["service_id"],
                "esi_icon_image"
            )
        )
    ) {
        $svc_icon = "./img/media/" . $icone;
    } else {
        $svc_icon = "./img/icons/service.png";
    }

    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_desc" => CentreonUtils::escapeSecure($service["service_description"]),
        "RowMenu_alias" => CentreonUtils::escapeSecure($service["service_alias"]),
        "RowMenu_parent" => CentreonUtils::escapeSecure($tplStr),
        "RowMenu_icon" => $svc_icon,
        "RowMenu_retry" => CentreonUtils::escapeSecure(
            "$normal_check_interval $normal_units / $retry_check_interval $retry_units"
        ),
        "RowMenu_attempts" => getMyServiceField($service['service_id'], "service_max_check_attempts"),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&service_id=" . $service['service_id'],
        "RowMenu_status" => $service["service_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $service["service_activate"] ? "service_ok" : "service_critical",
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

// Toolbar select lgd_more_actions
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
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 " .
        "||this.form.elements['o1'].selectedIndex == 5){" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "this.form.elements['o1'].selectedIndex = 0"
);
$form->addElement(
    'select',
    'o1',
    null,
    array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "mc" => _("Massive Change"),
        "ms" => _("Enable"),
        "mu" => _("Disable")
    ),
    $attrs1
);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 " .
        "||this.form.elements['o2'].selectedIndex == 5){" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "this.form.elements['o1'].selectedIndex = 0"
);
$form->addElement(
    'select',
    'o2',
    null,
    array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "mc" => _("Massive Change"),
        "ms" => _("Enable"),
        "mu" => _("Disable")
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
$tpl->assign('searchST', $search);
$tpl->assign("displayLocked", $displayLocked);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listServiceTemplateModel.ihtml");
