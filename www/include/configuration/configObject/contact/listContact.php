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

/**
 * allowed access
 */
const WRITE = 'w';
const READ = 'r';

/**
 * specific action available to admins
 */
const LDAP_SYNC = 'sync';

include_once "./class/centreonUtils.class.php";

include "./include/common/autoNumLimit.php";

//Create Timeperiod Cache
$tpCache = array("" => "");
$dbResult = $pearDB->query("SELECT tp_name, tp_id FROM timeperiod");
while ($data = $dbResult->fetch()) {
    $tpCache[$data["tp_id"]] = $data["tp_name"];
}
unset($data);
$dbResult->closeCursor();

$selectedContact = filter_var(
    $_GET['selectedContact'] ?? null,
    FILTER_VALIDATE_INT
);

$p = filter_var(
    $_GET['p'] ?? $_POST['p'],
    FILTER_VALIDATE_INT
);

$searchContact = filter_var(
    $_POST['searchC'] ?? $_GET['searchC'] ?? null,
    FILTER_SANITIZE_STRING
);

$search = filter_var(
    $_POST['Search'] ?? $_GET['Search'] ?? null,
    FILTER_SANITIZE_STRING
);

$contactGroup = filter_var(
    $_POST["contactGroup"] ?? $_GET["contactGroup"] ?? 0,
    FILTER_VALIDATE_INT
);

if ($search) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $searchContact;
    $centreon->historySearch[$url]['contactGroup'] = $contactGroup;
} else {
    //restoring saved values
    $searchContact = $centreon->historySearch[$url]['search'] ?? null;
    $contactGroup = $centreon->historySearch[$url]['contactGroup'] ?? 0;
}

$clauses = array();
if ($searchContact) {
    $clauses = array(
        'contact_name' => array('LIKE', '%' . $searchContact . '%'),
        'contact_alias' => array('OR', 'LIKE', '%' . $searchContact . '%')
    );
}

$join = array();
if (!empty($contactGroup)) {
    $join = array(
        array(
            'table' => 'contactgroup_contact_relation',
            'condition' => 'contact_contact_id = contact_id',
        )
    );
    if ($searchContact) {
        $clauses['contactgroup_cg_id'] = array(') AND (', '=', $contactGroup);
    } else {
        $clauses['contactgroup_cg_id'] = array('=', $contactGroup);
    }
}

$aclOptions = array(
    'fields' => array(
        'contact_id',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'contact_name',
        'contact_alias',
        'contact_lang',
        'contact_oreon',
        'contact_host_notification_options',
        'contact_service_notification_options',
        'contact_activate',
        'contact_email',
        'contact_admin',
        'contact_register',
        'contact_auth_type',
        'contact_ldap_required_sync'
    ),
    'keys' => array('contact_id'),
    'order' => array('contact_name'),
    'conditions' => $clauses ,
    'join' => $join
);
$contacts = $acl->getContactAclConf($aclOptions);
$rows = count($contacts);

include "./include/common/checkPagination.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? WRITE : READ;
$tpl->assign('mode_access', $lvl_access);

// massive contacts data synchronization request using the event handler
$chosenContact = array();
if ($centreon->user->admin && $selectedContact && $o === "sync") {
    $chosenContact[$selectedContact] = 1;
    synchronizeContactWithLdap($chosenContact);
}

// start header menu
$tpl->assign("headerMenu_name", _("Full Name"));
$tpl->assign("headerMenu_desc", _("Alias / Login"));
$tpl->assign("headerMenu_email", _("Email"));
$tpl->assign("headerMenu_hostNotif", _("Host Notification Period"));
$tpl->assign("headerMenu_svNotif", _("Services Notification Period"));
$tpl->assign("headerMenu_lang", _("Language"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_access", _("Access"));
$tpl->assign("headerMenu_accessTooltip", _("Contacts with the 'Reach Centreon Front-end' option enabled"));
$tpl->assign("headerMenu_admin", _("Admin"));
$tpl->assign("headerMenu_options", _("Options"));

// header title displayed only to admins
if ($centreon->user->admin) {
    $tpl->assign("headerMenu_refreshLdap", _("Refresh"));
    $tpl->assign("headerMenu_refreshLdapTitleTooltip", _("To manually request a LDAP synchronization of a contact"));
}


/*
 * Contact list
 */
$aclOptions['pages'] = $num * $limit . ", " . $limit;
$contacts = $acl->getContactAclConf($aclOptions);

$searchContact = tidySearchKey($searchContact, $advanced_search);

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

$contactGrRoute = './api/internal.php?object=centreon_configuration_contactgroup&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactGrRoute,
    'multiple' => false,
    'defaultDataset' => $contactGroup,
    'linkedObject' => 'centreonContactgroup'
);
$form->addElement('select2', 'contactGroup', "", array(), $attrContactgroups);

// Different style between each lines
$style = "one";

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

$contactTypeIcon = array(
    1 => returnSvg("www/img/icons/admin.svg", "var(--icons-fill-color)", 22, 22),
    2 => returnSvg("www/img/icons/user.svg", "var(--icons-fill-color)", 22, 22),
    3 => returnSvg("www/img/icons/user-template.svg", "var(--icons-fill-color)", 22, 22)
);
$contactTypeIconTitle = array(
    1 => _("This user is an administrator."),
    2 => _("This user is a simple user."),
    3 => _("This is a contact template.")
);

// refresh LDAP icon and tooltip
$refreshLdapHelp = array(
    0 => _("This user isn't linked to a LDAP"),
    1 => _("Manually request to synchronize this contact with his LDAP"),
    2 => _("Already requested, please wait the CRON execution or for the user to login"),
);

// setting a default value for non admin users
$refreshLdapBadge = array(0 => "");

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
$centreonToken = createCSRFToken();

foreach ($contacts as $contact) {
    if ($centreon->user->get_id() == $contact['contact_id']) {
        $selectedElements = $form->addElement(
            'checkbox',
            "select[" . $contact['contact_id'] . "]",
            '',
            '',
            'disabled'
        );
    } else {
        $selectedElements = $form->addElement('checkbox', "select[" . $contact['contact_id'] . "]");
    }
    $moptions = "";
    if ($contact["contact_id"] != $centreon->user->get_id()) {
        if ($contact["contact_activate"]) {
            $moptions .= "<a href='main.php?p=" . $p . "&contact_id=" . $contact['contact_id'] .
                "&o=u&limit=" . $limit . "&num=" . $num . "&search=" . $searchContact .
                "&centreon_token=" . $centreonToken .
                "'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='" .
                _("Disabled") . "'></a>&nbsp;&nbsp;";
        } else {
            $moptions .= "<a href='main.php?p=" . $p . "&contact_id=" . $contact['contact_id'] .
                "&o=s&limit=" . $limit . "&num=" . $num . "&search=" . $searchContact .
                "&centreon_token=" . $centreonToken .
                "'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='" .
                 _("Enabled") . "'></a>&nbsp;&nbsp;";
        }
    } else {
        $moptions .= "&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;&nbsp;&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) " .
        "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $contact['contact_id'] . "]' />";

    $contact_type = 0;
    if ($contact["contact_register"]) {
        if ($contact["contact_admin"] == 1) {
            $contact_type = 1;
        } else {
            $contact_type = 2;
        }
    } else {
        $contact_type = 3;
    }

    // linking the user to its LDAP badge
    $isLinkedToLdap = 0;
    // options displayed only to admins for contacts linked to an LDAP
    if ($centreon->user->admin && $contact['contact_auth_type'] === "ldap") {
        // synchronization is already required
        if ($contact['contact_ldap_required_sync'] === '1') {
            $isLinkedToLdap = 2;
            $refreshLdapBadge[2] =
                "<span class='ico-18'>" .
                returnSvg(
                    "www/img/icons/refresh.svg",
                    "var(--icons-gray-fill-color)",
                    18,
                    18
                ) .
                "</span>";
        } else {
            $isLinkedToLdap = 1;
            $refreshLdapBadge[1] =
                "<span class='ico-18' onclick='submitSync(" . $p . ", " . $contact['contact_id'] . ")'>" .
                returnSvg(
                    "www/img/icons/refresh.svg",
                    "var(--icons-fill-color)",
                    18,
                    18
                ) .
                "</span>";
        }
    }

    $elemArr[] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => CentreonUtils::escapeSecure(
            html_entity_decode($contact["contact_name"], ENT_QUOTES, "UTF-8"),
            CentreonUtils::ESCAPE_ILLEGAL_CHARS
        ),
        "RowMenu_ico" => isset($contactTypeIcon[$contact_type]) ? $contactTypeIcon[$contact_type] : "",
        "RowMenu_ico_title" => isset($contactTypeIconTitle[$contact_type])
            ? $contactTypeIconTitle[$contact_type]
            : "",
        "RowMenu_type" => $contact_type,
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&contact_id=" . $contact['contact_id'],
        "RowMenu_desc" => CentreonUtils::escapeSecure(
            html_entity_decode($contact["contact_alias"], ENT_QUOTES, "UTF-8"),
            CentreonUtils::ESCAPE_ILLEGAL_CHARS
        ),
        "RowMenu_email" => $contact["contact_email"],
        "RowMenu_hostNotif" =>
            html_entity_decode(
                $tpCache[(isset($contact["timeperiod_tp_id"]) ? $contact["timeperiod_tp_id"] : "")],
                ENT_QUOTES,
                "UTF-8"
            ) . " (" . (isset($contact["contact_host_notification_options"])
                ? $contact["contact_host_notification_options"]
                : "") . ")",
        "RowMenu_svNotif" =>
            html_entity_decode(
                $tpCache[(isset($contact["timeperiod_tp_id2"]) ? $contact["timeperiod_tp_id2"] : "")],
                ENT_QUOTES,
                "UTF-8"
            ) . " (" . (isset($contact["contact_service_notification_options"])
                ? $contact["contact_service_notification_options"]
                : "") . ")",
        "RowMenu_lang" => $contact["contact_lang"],
        "RowMenu_access" => $contact["contact_oreon"] ? _("Enabled") : _("Disabled"),
        "RowMenu_admin" => $contact["contact_admin"] ? _("Yes") : _("No"),
        "RowMenu_status" => $contact["contact_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $contact["contact_activate"] ? "service_ok" : "service_critical",
        "RowMenu_refreshLdap" => $isLinkedToLdap ? $refreshLdapBadge[$isLinkedToLdap] : "",
        "RowMenu_refreshLdapHelp" => $isLinkedToLdap ? $refreshLdapHelp[$isLinkedToLdap] : "",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("isAdmin", $centreon->user->admin);
$tpl->assign("elemArr", $elemArr);

// Different messages we put in the template
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "ldap_importL" => "main.php?p=" . $p . "&o=li",
        "ldap_importT" => _("LDAP Import"),
        "view_notif" => _("View contact notifications")
    )
);

// Display import ldap users button if ldap is configured
$res = $pearDB->query(
    "SELECT count(ar_id) as count_ldap " .
    "FROM auth_ressource "
);
$row = $res->fetch();
if ($row['count_ldap'] > 0) {
    $tpl->assign('ldap', '1');
}

// Toolbar select
?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }

    // ask for confirmation when requesting to resynchronize contact data from the LDAP
    function submitSync(p, contactId) {
        // msg = localized message to be displayed in the confirmation popup
        let msg = "<?= _('If the contact is connected, all his instances will be closed. Are you sure you want to ' .
            'request a data synchronization at the next login of this Contact ?'); ?>";
        if (confirm(msg)) {
            $.ajax({
                url: './api/internal.php?object=centreon_ldap_synchro&action=requestLdapSynchro',
                type: 'POST',
                async: false,
                data: {contactId: contactId},
                success: function(data) {
                    if (data === true) {
                        window.location.href = "?p=" + p;
                    }
                }
            });
        }
    }
</script>
<?php

// Manage options
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange' => "javascript: " .
            " var bChecked = isChecked(); " .
            "if (this.form.elements['" . $option . "'].selectedIndex != 0 && !bChecked) {" .
                " alert('" . _("Please select one or more items") . "'); return false;} " .
            "if (this.form.elements['" . $option . "'].selectedIndex == 1 && confirm('" .
            _("Do you confirm the duplication ?") . "')) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 2 && confirm('" .
            _("Do you confirm the deletion ?") . "')) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 3 || this.form.elements['" .
            $option . "'].selectedIndex == 4 || this.form.elements['" . $option . "'].selectedIndex == 5){" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 6 && confirm('" .
            _("The chosen contact(s) will be disconnected. Do you confirm the LDAP synchronization request ?") .
                "')) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
            "this.form.elements['" . $option . "'].selectedIndex = 0"
    );

    $formOptions = array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "mc" => _("Massive Change"),
        "ms" => _("Enable"),
        "mu" => _("Disable"),
    );
    // adding a specific option available only for admin users
    if ($centreon->user->admin) {
        $formOptions["sync"] = _("Synchronize LDAP");
    }

    $form->addElement(
        'select',
        $option,
        null,
        $formOptions,
        $attrs1
    );
    $form->setDefaults(array($option => null));

    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchC', $searchContact);

    // Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listContact.ihtml");
