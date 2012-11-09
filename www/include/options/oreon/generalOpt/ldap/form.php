<?php

/*
 * Copyright 2005-2011 MERETHIS
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

require_once $centreon_path . 'www/class/centreonLDAP.class.php';

$attrsText = array("size" => "40");
$attrsText2 = array("size" => "5");
$attrsTextarea = array("rows" => "4", "cols" => "60");
$attrsAdvSelect = null;

$arId = 0;
if (isset($_REQUEST['ar_id'])) {
    $arId = $_REQUEST['ar_id'];
}

/**
 * Ldap form
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Modify General Options"));


/**
 * Ldap info
 */
$form->addElement('header', 'ldap', _("General information"));

$form->addElement('text', 'ar_name', _('Configuration name'), $attrsText);
$form->addElement('textarea', 'ar_description', _('Description'), $attrsTextarea);

$ldapEnable[] = HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("Yes"), '1');
$ldapEnable[] = HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("No"), '0');
$form->addGroup($ldapEnable, 'ldap_auth_enable', _("Enable LDAP authentification"), '&nbsp;');

$ldapStorePassword[] = HTML_QuickForm::createElement('radio', 'ldap_store_password', null, _("Yes"), '1');
$ldapStorePassword[] = HTML_QuickForm::createElement('radio', 'ldap_store_password', null, _("No"), '0');
$form->addGroup($ldapStorePassword, 'ldap_store_password', _("Store LDAP password"), '&nbsp;');

$ldapAutoImport[] = HTML_QuickForm::createElement('radio', 'ldap_auto_import', null, _("Yes"), '1');
$ldapAutoImport[] = HTML_QuickForm::createElement('radio', 'ldap_auto_import', null, _("No"), '0');
$form->addGroup($ldapAutoImport, 'ldap_auto_import', _("Auto import users"), '&nbsp;');

$ldapUseDns[] = HTML_QuickForm::createElement('radio', 'ldap_srv_dns', null, _("Yes"), '1', array('id' => 'ldap_srv_dns_y', 'onclick' => "toggleParams(false);"));
$ldapUseDns[] = HTML_QuickForm::createElement('radio', 'ldap_srv_dns', null, _("No"), '0', array('id' => 'ldap_srv_dns_n', 'onclick' => "toggleParams(true);"));
$form->addGroup($ldapUseDns, 'ldap_srv_dns', _("Use service DNS"), '&nbsp;');

$ldapDnsUseSsl[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_ssl', null, _("Yes"), '1');
$ldapDnsUseSsl[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_ssl', null, _("No"), '0');
$form->addGroup($ldapDnsUseSsl, 'ldap_dns_use_ssl', _("Use SSL connection"), '&nbsp;');
$ldapDnsUseTls[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_tls', null, _("Yes"), '1');
$ldapDnsUseTls[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_tls', null, _("No"), '0');
$form->addGroup($ldapDnsUseTls, 'ldap_dns_use_tls', _("Use TLS connection"), '&nbsp;');
$form->addElement('text', 'ldap_dns_use_domain', _("Alternative domain for ldap"), $attrsText);

$form->addElement('text', 'ldap_search_limit', _('LDAP search size limit'), $attrsText2);
$form->addElement('text', 'ldap_search_timeout', _('LDAP search timeout'), $attrsText2);

$query = "SELECT contact_id, contact_name FROM contact WHERE contact_register = '0'";
$res = $pearDB->query($query);
$tmplList = array();
while ($row = $res->fetchRow()) {
    $tmplList[$row['contact_id']] = $row['contact_name'];
}
$res->free();

$form->addElement('select', 'ldap_contact_tmpl', _('Contact template'), $tmplList, array('id' => 'ldap_contact_tmpl'));

$form->addElement('header', 'ldapinfo', _("LDAP Information"));

$form->addElement('header', 'ldapserver', _('LDAP Servers'));

$form->addElement('text', 'bind_dn', _("Bind user"), $attrsText);
$form->addElement('password', 'bind_pass', _("Bind password"), $attrsText);
$form->addElement('select', 'protocol_version', _("Protocol version"), array('2' => 2, '3' => 3));
$form->addElement('select', 'ldap_template', _("Template"), array('0' => "", 'Posix' => _("Posix"), 'Active Directory' => _("Active Directory")), array('id' => 'ldap_template', 'onchange' => 'applyTemplate(this.value);'));
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

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/**
 * Form rules
 */
function slash($elem = NULL) {
    if ($elem)
        return rtrim($elem, "/") . "/";
}

/**
 * Smarty
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . 'ldap/', $tpl);

$ldapAdmin = new CentreonLdapAdmin($pearDB);

$defaultOpt = array('ldap_auth_enable' => '0',
    'ldap_store_password' => '1',
    'ldap_auto_import' => '0',
    'ldap_srv_dns' => '0',
    'ldap_template' => '1',
    'ldap_version_protocol' => '3',
    'ldap_dns_use_ssl' => '0',
    'ldap_dns_use_tls' => '0',
    'ldap_contact_tmpl' => '0',
    'ldap_search_limit' => '60',
    'ldap_search_timeout' => '60');
$gopt = array();

if ($arId) {
    $gopt = $ldapAdmin->getGeneralOptions($arId);
    $res = $pearDB->query("SELECT `ar_name`, `ar_description`, `ar_enable` FROM `auth_ressource` WHERE ar_id = " .$pearDB->escape($arId));
    while ($row = $res->fetchRow()) {
        $gopt['ar_name'] = $row['ar_name'];
        $gopt['ar_description'] = $row['ar_description'];
        $gopt['ldap_auth_enable'] = $row['ar_enable'];
    }
    unset($res);
}
$gopt = array_merge($defaultOpt, $gopt);
$form->setDefaults($gopt);

$ar = $form->addElement('hidden', 'ar_id');
$ar->setValue($arId);

$subC = $form->addElement('submit', 'submitC', _("Save"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

$nbOfInitialRows = 0;
if ($arId) {
    $query = "SELECT count(*) as nb FROM auth_ressource_host WHERE auth_ressource_id = " . $pearDB->escape($arId);
    $res = $pearDB->query($query);
    $row = $res->fetchRow();
    $nbOfInitialRows = $row['nb'];
}

$maxHostId = 1;
if ($arId) {
    $query = "SELECT MAX(ldap_host_id) as cnt FROM auth_ressource_host WHERE auth_ressource_id = " . $pearDB->escape($arId);
    $res = $pearDB->query($query);    
    if ($res->numRows()) {
        $row = $res->fetchRow();
        $maxHostId = $row['cnt'];
    }
}

require_once $path . 'ldap/javascript/ldapJs.php';

$valid = false;
$filterValid = true;
if ($form->validate()) {
    $values = $form->getSubmitValues();
    /*
     * Test is filter string is validate
     */
    if (isset($_POST['ldapHosts'])) {
        foreach ($_POST['ldapHosts'] as $ldapHost) {
            foreach ($ldapHost as $k => $v) {
                if ($k == 'ldap_user_filter' || $k == 'ldap_group_filter') {
                    if (false === CentreonLDAP::validateFilterPattern($v)) {
                        $filterValid = false;
                    }
                }
            }
        }
    }

    if ($filterValid) {
        if (!isset($values['ldap_contact_tmpl'])) {
            $values['ldap_contact_tmpl'] = "";
        }

        $arId = $ldapAdmin->setGeneralOptions($values['ar_id'], $values);

        $hostOld = array();
        if (isset($_POST['ldapHosts'])) {
            $sortArray = array();
            foreach ($_POST['ldapHosts'] as $ldapHost) {
                if (isset($ldapHost['id'])) {
                    $hostOld[] = $ldapHost['id'];
                }
                foreach ($ldapHost as $k => $v) {
                    if (!isset($sortArray[$k])) {
                        $sortArray[$k] = array();
                    }
                    $sortArray[$k][] = $v;
                }
            }
            if (count($hostOld)) {
                $query = "DELETE FROM auth_ressource_host 
                        WHERE auth_ressource_id = ".$pearDB->escape($values['ar_id'])." 
                        AND ldap_host_id NOT IN (" . join(', ', $hostOld) . ")";
                $pearDB->query($query);
            }
            array_multisort($sortArray['order'], SORT_ASC, $_POST['ldapHosts']);
            $counter = 1;
            foreach ($_POST['ldapHosts'] as $ldapHost) {
                $ldapHost['order'] = $counter;
                if (isset($ldapHost['id'])) {
                    $ldapAdmin->modifyServer($arId, $ldapHost);
                } else {
                    $ldapAdmin->addServer($arId, $ldapHost);
                }
                $counter++;
            }
        } else {
            $query = "DELETE FROM auth_ressource_host WHERE ar_id = ".$pearDB->escape($arId);
            $pearDB->query($query);
        }

        $o = "w";
        $valid = true;

        if (!isset($values['ldap_srv_dns']['ldap_srv_dns']) || !$values['ldap_srv_dns']['ldap_srv_dns']) {
            $tpl->assign("hideDnsOptions", 1);
        } else {
            $tpl->assign("hideDnsOptions", 0);
        }
        $form->freeze();
    }
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("Impossible to validate, one or more field is incorrect") . "</div>");
} elseif (false === $filterValid) {
    print("<div class='msg' align='center'>" . _("Bad ldap filter : missing %s pattern. Check user or group filter") . "</div>");
}

$form->addElement("button", "change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=ldap'"));

/*
 * Prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign("optGen_ldap_properties", _("LDAP Properties"));
$tpl->assign('addNewHostLabel', _('Add a new LDAP server'));
$tpl->assign('manualImport', _('Import users manually'));
$tpl->assign('valid', $valid);
$tpl->display("form.ihtml");
?>