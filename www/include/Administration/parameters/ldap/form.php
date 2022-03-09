<?php

/*
 * Copyright 2005-2021 Centreon
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

if (!isset($oreon)) {
    exit();
}

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/CentreonLDAPAdmin.class.php';
require_once __DIR__ . '/DB-Func.php';

$attrsText = array("size" => "40");
$attrsText2 = array("size" => "5");
$attrsTextarea = array("rows" => "4", "cols" => "60");
$attrsAdvSelect = null;

$arId = filter_var(
    $_POST['ar_id'] ?? $_GET['ar_id'] ?? 0,
    FILTER_VALIDATE_INT
);

/**
 * Ldap form
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p . "&o=" . $o);
$form->addElement('header', 'title', _("Modify General Options"));

/**
 * Ldap info
 */
$form->addElement('header', 'ldap', _("General information"));

$form->addElement('text', 'ar_name', _('Configuration name'), $attrsText);
$form->addElement('textarea', 'ar_description', _('Description'), $attrsTextarea);

$ldapEnable[] = $form->createElement('radio', 'ldap_auth_enable', null, _("Yes"), '1');
$ldapEnable[] = $form->createElement('radio', 'ldap_auth_enable', null, _("No"), '0');
$form->addGroup($ldapEnable, 'ldap_auth_enable', _("Enable LDAP authentication"), '&nbsp;');

$ldapStorePassword[] = $form->createElement('radio', 'ldap_store_password', null, _("Yes"), '1');
$ldapStorePassword[] = $form->createElement('radio', 'ldap_store_password', null, _("No"), '0');
$form->addGroup($ldapStorePassword, 'ldap_store_password', _("Store LDAP password"), '&nbsp;');

$ldapAutoImport[] = $form->createElement('radio', 'ldap_auto_import', null, _("Yes"), '1');
$ldapAutoImport[] = $form->createElement('radio', 'ldap_auto_import', null, _("No"), '0');
$form->addGroup($ldapAutoImport, 'ldap_auto_import', _("Auto import users"), '&nbsp;');

$ldapUseDns[] = $form->createElement(
    'radio',
    'ldap_srv_dns',
    null,
    _("Yes"),
    '1',
    array('id' => 'ldap_srv_dns_y', 'onclick' => "toggleParams(false);")
);
$ldapUseDns[] = $form->createElement(
    'radio',
    'ldap_srv_dns',
    null,
    _("No"),
    '0',
    array('id' => 'ldap_srv_dns_n', 'onclick' => "toggleParams(true);")
);
$form->addGroup($ldapUseDns, 'ldap_srv_dns', _("Use service DNS"), '&nbsp;');

$form->addElement('text', 'ldap_dns_use_domain', _("Alternative domain for ldap"), $attrsText);

$form->addElement('text', 'ldap_search_limit', _('LDAP search size limit'), $attrsText2);
$form->addElement('text', 'ldap_search_timeout', _('LDAP search timeout'), $attrsText2);

/**
 * LDAP's scanning options sub menu
 */
$form->addElement('header', 'ldapScanOption', _("Synchronization Options"));
// option to disable the auto-scan of the LDAP - by default auto-scan is enabled
$ldapAutoScan[] = $form->createElement(
    'radio',
    'ldap_auto_sync',
    null,
    _("Yes"),
    '1',
    array('id' => 'ldap_auto_sync_y', 'onclick' => "toggleParamSync(false);")
);
$ldapAutoScan[] = $form->createElement(
    'radio',
    'ldap_auto_sync',
    null,
    _("No"),
    '0',
    array('id' => 'ldap_auto_sync_n', 'onclick' => "toggleParamSync(true);")
);
$form->addGroup($ldapAutoScan, 'ldap_auto_sync', _("Enable LDAP synchronization on login"), '&nbsp;');
// default interval before re-scanning the whole LDAP. By default, a duration of one hour is set
$form->addElement('text', 'ldap_sync_interval', _('LDAP synchronization interval (in hours)'), $attrsText2);
$form->addRule('ldap_sync_interval', _("Compulsory field"), 'required');
// A minimum value of 1 hour is required and it should be an integer
$form->registerRule('minimalValue', 'callback', 'minimalValue');
$form->addRule('ldap_sync_interval', _("An integer with a minimum value of 1 is required"), 'minimalValue');

/**
 * list of contact template available
 */
$res = $pearDB->prepare(
    "SELECT contact_id, contact_name FROM contact WHERE contact_register = '0'"
);
$res->execute();
$LdapContactTplList = [];
while ($row = $res->fetch()) {
    $LdapContactTplList[$row['contact_id']] = $row['contact_name'];
}
$res->closeCursor();
$form->addElement(
    'select',
    'ldap_contact_tmpl',
    _('Contact template'),
    $LdapContactTplList,
    array('id' => 'ldap_contact_tmpl')
);
$form->addRule('ldap_contact_tmpl', _("Compulsory Field"), 'required');

/**
 * Default contactgroup for imported contact
 */
$cgAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=list';
$cgDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=contact&field=ldap_default_cg&id=' . $arId;
$attrContactGroup = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $cgAvRoute,
    'defaultDatasetRoute' => $cgDeRoute,
    'multiple' => false,
    'linkedObject' => 'centreonContactgroup'
);
$form->addElement('select2', 'ldap_default_cg', _('Default contactgroup'), array(), $attrContactGroup);

$form->addElement('header', 'ldapinfo', _("LDAP Information"));
$form->addElement('header', 'ldapserver', _('LDAP Servers'));
$form->addElement('text', 'bind_dn', _("Bind user"), array("size" => "40", "autocomplete" => "off"));
$form->addElement('password', 'bind_pass', _("Bind password"), array("size" => "40", "autocomplete" => "new-password"));
$form->addElement('select', 'protocol_version', _("Protocol version"), array('3' => 3, '2' => 2));
$form->addElement(
    'select',
    'ldap_template',
    _("Template"),
    array('0' => "", 'Active Directory' => _("Active Directory"), 'Okta' => _("Okta"), 'Posix' => _("Posix")),
    array('id' => 'ldap_template', 'onchange' => 'applyTemplate(this.value);')
);
$form->addElement('text', 'user_base_search', _("Search user base DN"), $attrsText);
$form->addElement('text', 'group_base_search', _("Search group base DN"), $attrsText);
$form->addElement('text', 'user_filter', _("User filter"), $attrsText);
$form->addElement('text', 'alias', _("Login attribute"), $attrsText);
$form->addElement('text', 'user_group', _("User group attribute"), $attrsText);
$form->addElement('text', 'user_name', _("User displayname attribute"), $attrsText);
$form->addElement('text', 'user_firstname', _("User firstname attribute"), $attrsText);
$form->addElement('text', 'user_lastname', _("User lastname attribute"), $attrsText);
$form->addElement('text', 'user_email', _("User email attribute"), $attrsText);
$form->addElement('text', 'user_pager', _("User pager attribute"), $attrsText);
$form->addElement('text', 'group_filter', _("Group filter"), $attrsText);
$form->addElement('text', 'group_name', _("Group attribute"), $attrsText);
$form->addElement('text', 'group_member', _("Group member attribute"), $attrsText);

$form->addRule('ar_name', _("Compulsory field"), 'required');
$form->addRule('ar_description', _("Compulsory field"), 'required');

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/**
 * Smarty
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . 'ldap/', $tpl);

$ldapAdmin = new CentreonLdapAdmin($pearDB);

$defaultOpt = array(
    'ldap_auth_enable' => '0',
    'ldap_store_password' => '1',
    'ldap_auto_import' => '0',
    'ldap_srv_dns' => '0',
    'ldap_template' => '1',
    'ldap_version_protocol' => '3',
    'ldap_dns_use_ssl' => '0',
    'ldap_dns_use_tls' => '0',
    'ldap_contact_tmpl' => '0',
    'ldap_default_cg' => '0',
    'ldap_search_limit' => '60',
    'ldap_auto_sync' => '1', // synchronization on user's login is enabled by default
    'ldap_sync_interval' => '1', // minimal value of the interval between two LDAP synchronizations
    'ldap_search_timeout' => '60'
);

$gopt = array();
if ($arId) {
    $gopt = $ldapAdmin->getGeneralOptions($arId);
    $res = $pearDB->prepare(
        "SELECT `ar_name`, `ar_description`, `ar_enable`, `ar_sync_base_date`
        FROM `auth_ressource`
        WHERE ar_id = :arId"
    );
    $res->bindValue('arId', $arId, PDO::PARAM_INT);
    $res->execute();
    while ($row = $res->fetch()) {
        // sanitize name and description
        $gopt['ar_name'] = filter_var($row['ar_name'], FILTER_SANITIZE_STRING);
        $gopt['ar_description'] = filter_var($row['ar_description'], FILTER_SANITIZE_STRING);
        $gopt['ldap_auth_enable'] = $row['ar_enable'];
        $gopt['ar_sync_base_date'] = $row['ar_sync_base_date'];
    }
    unset($res);

    /*
     * Preset values of ldap servers
     */
    $cdata = CentreonData::getInstance();
    $serversArray = $ldapAdmin->getServersFromResId($arId);
    $cdata->addJsData('clone-values-ldapservers', htmlspecialchars(json_encode($serversArray), ENT_QUOTES));
    $cdata->addJsData('clone-count-ldapservers', count($serversArray));
}

/*
 * LDAP servers
 */
$cloneSet = array();
$cloneSet[] = $form->addElement(
    'text',
    'address[#index#]',
    _("Host address"),
    array(
        "size" => "30",
        "id" => "address_#index#"
    )
);
$cloneSet[] = $form->addElement(
    'text',
    'port[#index#]',
    _("Port"),
    array(
        "size" => "10",
        "id" => "port_#index#"
    )
);
$cbssl = $form->addElement(
    'checkbox',
    'ssl[#index#]',
    _("SSL"),
    "",
    array("id" => "ssl_#index#")
);
$cbssl->setText('');
$cloneSet[] = $cbssl;

$cbtls = $form->addElement(
    'checkbox',
    'tls[#index#]',
    _("TLS"),
    "",
    array("id" => "tls_#index#")
);
$cbtls->setText('');
$cloneSet[] = $cbtls;

$gopt = array_merge($defaultOpt, $gopt);
$form->setDefaults($gopt);

$ar = $form->addElement('hidden', 'ar_id');
$ar->setValue($arId);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$nbOfInitialRows = 0;
$maxHostId = 1;
if ($arId) {
    $res = $pearDB->prepare(
        "SELECT COUNT(*) as nb FROM auth_ressource_host WHERE auth_ressource_id = :arId"
    );
    $res->bindValue(':arId', (int)$arId, \PDO::PARAM_INT);
    $res->execute();
    $row = $res->fetch();
    $nbOfInitialRows = $row['nb'];

    $res = $pearDB->prepare(
        "SELECT MAX(ldap_host_id) as cnt 
        FROM auth_ressource_host 
        WHERE auth_ressource_id = :arId"
    );
    $res->bindValue(':arId', (int)$arId, \PDO::PARAM_INT);
    $res->execute();
    if ($res->rowCount()) {
        $row = $res->fetch();
        $maxHostId = $row['cnt'];
    }
}

require_once $path . 'ldap/javascript/ldapJs.php';

$valid = false;
$filterValid = true;
$validNameOrDescription = true;
$allHostsOk = true;
if ($form->validate()) {
    $values = $form->getSubmitValues();
    // sanitize name and description
    $values['ar_name'] = filter_var($values['ar_name'], FILTER_SANITIZE_STRING);
    $values['ar_description'] = filter_var($values['ar_description'], FILTER_SANITIZE_STRING);

    // Check if sanitized name and description are not empty
    if (
        "" === $values['ar_name']
        || "" === $values['ar_description']
    ) {
        $validNameOrDescription = false;
    } else {
        // Test if filter string is valid
        if (!CentreonLDAP::validateFilterPattern($values['user_filter'])) {
            $filterValid = false;
        }

        if (isset($_POST['ldapHosts'])) {
            foreach ($_POST['ldapHosts'] as $ldapHost) {
                if ($ldapHost['hostname'] == '' || $ldapHost['port'] == '') {
                    $allHostsOk = false;
                }
            }
        }

        if ($filterValid && $allHostsOk) {
            if (!isset($values['ldap_contact_tmpl'])) {
                $values['ldap_contact_tmpl'] = "";
            }

            if (!isset($values['ldap_default_cg'])) {
                $values['ldap_default_cg'] = "";
            }

            // setting a reference time to calculate the expected synchronization
            $currentTime = time();
            $values['ar_sync_base_date'] = $currentTime;

            // updating the next expected auto-sync at login if the admin has changed the sync options
            // or it never occurred
            if (
                $gopt['ldap_auto_sync'] === $values['ldap_auto_sync']['ldap_auto_sync']
                && !empty($gopt['ar_sync_base_date'])
                && ($gopt['ar_sync_base_date'] + ($values['ldap_sync_interval'] * 3600)) > $currentTime
                && $currentTime > $gopt['ar_sync_base_date']
            ) {
                // distinguishing the enabled and disabled cases
                if (
                    $values['ldap_auto_sync']['ldap_auto_sync'] == 0
                    || ($values['ldap_auto_sync']['ldap_auto_sync'] == 1
                        && $gopt['ldap_sync_interval'] == $values['ldap_sync_interval'])
                ) {
                    // synchronization parameters have not changed, the reference time shouldn't be updated
                    $values['ar_sync_base_date'] = $gopt['ar_sync_base_date'];
                }
            }

            $arId = $ldapAdmin->setGeneralOptions($values, $values['ar_id']);
            $o = "w";
            $valid = true;

            if (!isset($values['ldap_srv_dns']['ldap_srv_dns']) || !$values['ldap_srv_dns']['ldap_srv_dns']) {
                $tpl->assign("hideDnsOptions", 1);
            } else {
                $tpl->assign("hideDnsOptions", 0);
            }

            $tpl->assign("hideSyncInterval", (int)$values['ldap_auto_sync']['ldap_auto_sync'] ?? 0);
            $form->freeze();
        }
    }
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("Impossible to validate, one or more field is incorrect") . "</div>");
} elseif (false === $filterValid) {
    print("<div class='msg' align='center'>"
        . _("Bad ldap filter: missing %s pattern. Check user or group filter") . "</div>");
} elseif (false === $allHostsOk) {
    print("<div class='msg' align='center'>" . _("Invalid LDAP Host parameters") . "</div>");
} elseif (false === $validNameOrDescription) {
    print("<div class='msg' align='center'>" . _("Invalid name or description") . "</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=ldap&ar_id=" . $arId . "'")
);

/*
 * Prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

if ($valid) {
    require_once $path . 'ldap/list.php';
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign("hideSyncInterval", 0);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign('cloneSet', $cloneSet);
    $tpl->assign('o', $o);
    $tpl->assign("optGen_ldap_properties", _("LDAP Properties"));
    $tpl->assign('addNewHostLabel', _('LDAP servers'));
    $tpl->assign('manualImport', _('Import users manually'));
    $tpl->assign('valid', $valid);
    $tpl->display("form.ihtml");
}
