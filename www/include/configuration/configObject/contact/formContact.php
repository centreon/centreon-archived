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
    exit();
}

if (!$centreon->user->admin && $contact_id) {
    $aclOptions = array(
        'fields' => array('contact_id', 'contact_name'),
        'keys' => array('contact_id'),
        'get_row' => 'contact_name',
        'conditions' => array('contact_id' => $contact_id)
    );
    $contacts = $acl->getContactAclConf($aclOptions);
    if (!count($contacts)) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this contact'));
        return null;
    }
}

$cgs = $acl->getContactGroupAclConf(
    array(
        'fields' => array('cg_id', 'cg_name'),
        'keys' => array('cg_id'),
        'get_row' => 'cg_name',
        'order' => array('cg_name')
    )
);

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

$initialValues = array();

$cct = array();
if (($o == "c" || $o == "w") && $contact_id) {
    /**
     * Init Tables informations
     */
    $cct["contact_hostNotifCmds"] = array();
    $cct["contact_svNotifCmds"] = array();
    $cct["contact_cgNotif"] = array();

    $DBRESULT = $pearDB->query("SELECT * FROM contact WHERE contact_id = '" . intval($contact_id) . "' LIMIT 1");
    $cct = array_map("myDecode", $DBRESULT->fetchRow());
    $cct["contact_passwd"] = null;
    $DBRESULT->free();

    /**
     * Set Host Notification Options
     */
    $tmp = explode(',', $cct["contact_host_notification_options"]);
    foreach ($tmp as $key => $value) {
        $cct["contact_hostNotifOpts"][trim($value)] = 1;
    }

    /**
     * Set Service Notification Options
     */
    $tmp = explode(',', $cct["contact_service_notification_options"]);
    foreach ($tmp as $key => $value) {
        $cct["contact_svNotifOpts"][trim($value)] = 1;
    }
    $DBRESULT->free();

    /**
     * Set Contact Group Parents
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '" . intval($contact_id) . "'");
    for ($i = 0; $notifCg = $DBRESULT->fetchRow(); $i++) {
        if (!$centreon->user->admin && !isset($cgs[$notifCg['contactgroup_cg_id']])) {
            $initialValues['contact_cgNotif'][] = $notifCg["contactgroup_cg_id"];
        } else {
            $cct["contact_cgNotif"][$i] = $notifCg["contactgroup_cg_id"];
        }
    }
    $DBRESULT->free();

    /**
     * Set Host Notification Commands
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '" . intval($contact_id) . "'");
    for ($i = 0; $notifCmd = $DBRESULT->fetchRow(); $i++) {
        $cct["contact_hostNotifCmds"][$i] = $notifCmd["command_command_id"];
    }
    $DBRESULT->free();

    /**
     * Set Service Notification Commands
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '" . intval($contact_id) . "'");
    for ($i = 0; $notifCmd = $DBRESULT->fetchRow(); $i++) {
        $cct["contact_svNotifCmds"][$i] = $notifCmd["command_command_id"];
    }
    $DBRESULT->free();

    /**
     * Get DLAP auth informations
     */
    $DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'ldap_auth_enable'");
    while ($ldap_auths = $DBRESULT->fetchRow()) {
        $ldap_auth[$ldap_auths["key"]] = myDecode($ldap_auths["value"]);
    }
    $DBRESULT->free();

    /**
     * Get ACL informations for this user
     */
    $DBRESULT = $pearDB->query("SELECT acl_group_id FROM `acl_group_contacts_relations` WHERE `contact_contact_id` = '" . intval($contact_id) . "'");
    for ($i = 0; $data = $DBRESULT->fetchRow(); $i++) {
        if (!$centreon->user->admin && !isset($allowedAclGroups[$data['acl_group_id']])) {
            $initialValues['contact_acl_groups'] = $data['acl_group_id'];
        } else {
            $cct["contact_acl_groups"][$i] = $data["acl_group_id"];
        }
    }
    $DBRESULT->free();
}

/**
 * Get Langs
 */
$langs = array();
$langs = getLangs();
if ($o == "mc") {
    array_unshift($langs, null);
}

/**
 * Timeperiods comes from DB -> Store in $notifsTps Array
 * When we make a massive change, give the possibility to not crush value
 */
$notifTps = array(null => null);
$DBRESULT = $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
while ($notifTp = $DBRESULT->fetchRow()) {
    $notifTps[$notifTp["tp_id"]] = $notifTp["tp_name"];
}
$DBRESULT->free();

/**
 * Notification commands comes from DB -> Store in $notifsCmds Array
 */
$notifCmds = array();
$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '1' ORDER BY command_name");
while ($notifCmd = $DBRESULT->fetchRow()) {
    $notifCmds[$notifCmd["command_id"]] = $notifCmd["command_name"];
}
$DBRESULT->free();

/**
 * Contact Groups comes from DB -> Store in $notifCcts Array
 */
$notifCgs = array();
$cg = new CentreonContactgroup($pearDB);
$notifCgs = $cg->getListContactgroup(false);

if ($centreon->optGen['ldap_auth_enable'] == 1 && $cct['contact_auth_type'] == 'ldap' && isset($cct['ar_id']) && $cct['ar_id']) {
    $ldap = new CentreonLDAP($pearDB, null, $cct['ar_id']);
    if (false !== $ldap->connect()) {
        $cgLdap = $ldap->listGroupsForUser($cct['contact_ldap_dn']);
    }
}

/**
 * Get ACL Groups List
 */
$aclGroups = array();
$aclCond = "";
if (!$centreon->user->admin) {
    $aclCond = " WHERE acl_group_id IN (" . $acl->getAccessGroupsString() . ") ";
}
$sql = "SELECT acl_group_id, acl_group_name 
    FROM acl_groups 
    {$aclCond}
    ORDER BY acl_group_name";
$DBRESULT = $pearDB->query($sql);
while ($aclGroup = $DBRESULT->fetchRow()) {
    $aclGroups[$aclGroup["acl_group_id"]] = $aclGroup["acl_group_name"];
}
$DBRESULT->free();

/**
 * Contacts Templates
 */
if (isset($contact_id)) {
    $strRestrinction = " AND contact_id != '" . intval($contact_id) . "'";
} else {
    $strRestrinction = "";
}

$contactTpl = array(null => "           ");
$DBRESULT = $pearDB->query("SELECT contact_id, contact_name FROM contact WHERE contact_register = '0' $strRestrinction ORDER BY contact_name");
while ($contacts = $DBRESULT->fetchRow()) {
    $contactTpl[$contacts["contact_id"]] = $contacts["contact_name"];
}
$DBRESULT->free();

/**
 * Template / Style for Quickform input
 */
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "60");
$attrsTextDescr = array("size" => "80");
$attrsTextMail = array("size" => "90");
$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
$attrsTextarea = array("rows" => "15", "cols" => "100");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list',
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);
$attrCommands = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => true,
    'linkedObject' => 'centreonCommand'
);
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);
$attrAclgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonAclGroup'
);

$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a User"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a User"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a User"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title', _("Massive Change"));
}

/**
 * Contact basic information
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('header', 'additional', _("Additional Information"));
$form->addElement('header', 'centreon', _("Centreon Authentication"));
$form->addElement('header', 'acl', _("Access lists"));

/**
 * No possibility to change name and alias, because there's no interest
 */
/**
 * Don't change contact name and alias in massif change
 * Don't change contact name, alias or autologin key in massive change
 */
if ($o != "mc") {
    $form->addElement('text', 'contact_name', _("Full Name"), $attrsTextDescr);
    $form->addElement('text', 'contact_alias', _("Alias / Login"), $attrsText);
    $form->addElement('text', 'contact_autologin_key', _("Autologin Key"), array("size" => "90", "id" => "aKey"));
    $form->addElement('button', 'contact_gen_akey', _("Generate"), array('onclick' => 'generatePassword("aKey");'));
}

$form->addElement('text', 'contact_email', _("Email"), $attrsTextMail);
$form->addElement('text', 'contact_pager', _("Pager"), $attrsText);

/**
 * Contact template used
 */
$form->addElement('select', 'contact_template_id', _("Contact template used"), $contactTpl);

$form->addElement('header', 'furtherAddress', _("Additional Addresses"));
$form->addElement('text', 'contact_address1', _("Address1"), $attrsText);
$form->addElement('text', 'contact_address2', _("Address2"), $attrsText);
$form->addElement('text', 'contact_address3', _("Address3"), $attrsText);
$form->addElement('text', 'contact_address4', _("Address4"), $attrsText);
$form->addElement('text', 'contact_address5', _("Address5"), $attrsText);
$form->addElement('text', 'contact_address6', _("Address6"), $attrsText);

/**
 * Contact Groups Field
 */
$form->addElement('header', 'groupLinks', _("Group Relations"));
if ($o == "mc") {
    $mc_mod_cg = array();
    $mc_mod_cg[] = HTML_QuickForm::createElement('radio', 'mc_mod_cg', null, _("Incremental"), '0');
    $mc_mod_cg[] = HTML_QuickForm::createElement('radio', 'mc_mod_cg', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cg, 'mc_mod_cg', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_cg' => '0'));
}

$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=defaultValues&target=contact&field=contact_cgNotif&id=' . $contact_id)
);
$form->addElement('select2', 'contact_cgNotif', _("Linked to Contact Groups"), array(), $attrContactgroup1);

/**
 * Contact Centreon information
 */
$form->addElement('header', 'oreon', _("Centreon"));
$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'contact_oreon', null, _("Yes"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'contact_oreon', null, _("No"), '0');
$form->addGroup($tab, 'contact_oreon', _("Reach Centreon Front-end"), '&nbsp;');

$form->addElement('password', 'contact_passwd', _("Password"), array("size" => "30", "autocomplete" => "off", "id" => "passwd1", "onkeypress" => "resetPwdType(this);"));
$form->addElement('password', 'contact_passwd2', _("Confirm Password"), array("size" => "30", "autocomplete" => "off", "id" => "passwd2", "onkeypress" => "resetPwdType(this);"));
$form->addElement('button', 'contact_gen_passwd', _("Generate"), array('onclick' => 'generatePassword("passwd");'));

$form->addElement('select', 'contact_lang', _("Default Language"), $langs);
$form->addElement('select', 'contact_type_msg', _("Mail Type"), array(null => null, "txt" => "txt", "html" => "html", "pdf" => "pdf"));

if ($centreon->user->admin) {
    $tab = array();
    $tab[] = HTML_QuickForm::createElement('radio', 'contact_admin', null, _("Yes"), '1');
    $tab[] = HTML_QuickForm::createElement('radio', 'contact_admin', null, _("No"), '0');
    $form->addGroup($tab, 'contact_admin', _("Admin"), '&nbsp;');

    $tab = array();
    $tab[] = HTML_QuickForm::createElement('radio', 'reach_api', null, _("Yes"), '1');
    $tab[] = HTML_QuickForm::createElement('radio', 'reach_api', null, _("No"), '0');
    $form->addGroup($tab, 'reach_api', _("Reach API"), '&nbsp;');
}

/**
 * ACL configurations
 */
if ($o == "mc") {
    $mc_mod_cg = array();
    $mc_mod_cg[] = HTML_QuickForm::createElement('radio', 'mc_mod_acl', null, _("Incremental"), '0');
    $mc_mod_cg[] = HTML_QuickForm::createElement('radio', 'mc_mod_acl', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cg, 'mc_mod_acl', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_acl' => '0'));
}

$attrAclgroup1 = array_merge(
    $attrAclgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=defaultValues&target=contact&field=contact_acl_groups&id=' . $contact_id)
);
$form->addElement('select2', 'contact_acl_groups', _("Access list groups"), array(), $attrAclgroup1);

/**
 * Include GMT Class
 */
require_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";

$CentreonGMT = new CentreonGMT($pearDB);

$attrTimezones = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=list',
    'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=defaultValues&target=contact&field=contact_location&id=' . $contact_id,
    'multiple' => false,
    'linkedObject' => 'centreonGMT'
);
$form->addElement('select2', 'contact_location', _("Timezone / Location"), array(), $attrTimezones);

if ($o != "mc") {
    $auth_type = array();
} else {
    $auth_type = array(null => null);
}

$auth_type["local"] = "Centreon";
if ($centreon->optGen['ldap_auth_enable'] == 1) {
    $auth_type["ldap"] = "LDAP";
    $dnElement = $form->addElement('text', 'contact_ldap_dn', _("LDAP DN (Distinguished Name)"), $attrsText2);
    if (!$centreon->user->admin) {
        $dnElement->freeze();
    }
}
if ($o != "mc") {
    $form->setDefaults(array('contact_oreon' => '1', "contact_admin" => '0', "reach_api" => '0'));
}
$form->addElement('select', 'contact_auth_type', _("Authentication Source"), $auth_type);

/**
 * Notification informations
 */
$form->addElement('header', 'notification', _("Notification"));

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'contact_enable_notifications', null, _("Yes"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'contact_enable_notifications', null, _("No"), '0');
$tab[] = HTML_QuickForm::createElement('radio', 'contact_enable_notifications', null, _("Default"), '2');
$form->addGroup($tab, 'contact_enable_notifications', _("Enable Notifications"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('contact_enable_notifications' => '2'));
}

/** * *****************************
 * Host notifications
 */
$form->addElement('header', 'hostNotification', _("Host"));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"), array('id' => 'hDown', 'onClick' => 'uncheckAllH(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"), array('id' => 'hUnreachable', 'onClick' => 'uncheckAllH(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', _("Recovery"), array('id' => 'hRecovery', 'onClick' => 'uncheckAllH(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', _("Flapping"), array('id' => 'hFlapping', 'onClick' => 'uncheckAllH(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 's', '&nbsp;', _("Downtime Scheduled"), array('id' => 'hScheduled', 'onClick' => 'uncheckAllH(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'hNone', 'onClick' => 'javascript:uncheckAllH(this);'));
$form->addGroup($hostNotifOpt, 'contact_hostNotifOpts', _("Host Notification Options"), '&nbsp;&nbsp;');

$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=contact&field=timeperiod_tp_id&id=' . $contact_id)
);
$form->addElement('select2', 'timeperiod_tp_id', _("Host Notification Period"), array(), $attrTimeperiod1);


unset($hostNotifOpt);

if ($o == "mc") {
    $mc_mod_hcmds = array();
    $mc_mod_hcmds[] = HTML_QuickForm::createElement('radio', 'mc_mod_hcmds', null, _("Incremental"), '0');
    $mc_mod_hcmds[] = HTML_QuickForm::createElement('radio', 'mc_mod_hcmds', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hcmds, 'mc_mod_hcmds', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hcmds' => '0'));
}

$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=defaultValues&target=contact&field=contact_hostNotifCmds&id=' . $contact_id,
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list&t=1'
    )
);
$form->addElement('select2', 'contact_hostNotifCmds', _("Host Notification Commands"), array(), $attrCommand1);

/** * *****************************
 * Service notifications
 */
$form->addElement('header', 'serviceNotification', _("Service"));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', _("Recovery"), array('id' => 'sRecovery', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', _("Flapping"), array('id' => 'sFlapping', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 's', '&nbsp;', _("Downtime Scheduled"), array('id' => 'sScheduled', 'onClick' => 'uncheckAllS(this);'));
$svNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone', 'onClick' => 'uncheckAllS(this);'));
$form->addGroup($svNotifOpt, 'contact_svNotifOpts', _("Service Notification Options"), '&nbsp;&nbsp;');

$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=contact&field=timeperiod_tp_id2&id=' . $contact_id)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Service Notification Period"), array(), $attrTimeperiod2);

if ($o == "mc") {
    $mc_mod_svcmds = array();
    $mc_mod_svcmds[] = HTML_QuickForm::createElement('radio', 'mc_mod_svcmds', null, _("Incremental"), '0');
    $mc_mod_svcmds[] = HTML_QuickForm::createElement('radio', 'mc_mod_svcmds', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_svcmds, 'mc_mod_svcmds', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_svcmds' => '0'));
}

$attrCommand2 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=defaultValues&target=contact&field=contact_svNotifCmds&id=' . $contact_id,
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list&t=1'
    )
);
$form->addElement('select2', 'contact_svNotifCmds', _("Service Notification Commands"), array(), $attrCommand2);

/**
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$cctActivation[] = HTML_QuickForm::createElement('radio', 'contact_activate', null, _("Enabled"), '1');
$cctActivation[] = HTML_QuickForm::createElement('radio', 'contact_activate', null, _("Disabled"), '0');
$form->addGroup($cctActivation, 'contact_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('contact_activate' => '1'));
if ($o == "c" && $centreon->user->get_id() == $cct["contact_id"]) {
    $form->freeze('contact_activate');
}

$form->addElement('hidden', 'contact_register');
$form->setDefaults(array('contact_register' => '1'));

$form->addElement('textarea', 'contact_comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'contact_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));

if (is_array($select)) {
    $select_str = null;
    foreach ($select as $key => $value) {
        $select_str .= $key . ",";
    }
    $select_pear = $form->addElement('hidden', 'select');
    $select_pear->setValue($select_str);
}

/**
 * Form Rules
 */
function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["contact_name"]));
}

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('contact_name', 'myReplace');
$from_list_menu = false;
if ($o != "mc") {
    $ret = $form->getSubmitValues();
    $form->addRule('contact_name', _("Compulsory Name"), 'required');
    $form->addRule('contact_alias', _("Compulsory Alias"), 'required');
    $form->addRule('contact_email', _("Valid Email"), 'required');
    $form->addRule('contact_oreon', _("Required Field"), 'required');
    $form->addRule('contact_lang', _("Required Field"), 'required');
    $form->addRule('contact_admin', _("Required Field"), 'required');
    $form->addRule('contact_auth_type', _("Required Field"), 'required');

    if (isset($ret["contact_enable_notifications"]["contact_enable_notifications"]) && $ret["contact_enable_notifications"]["contact_enable_notifications"] == 1) {
        if (isset($ret["contact_template_id"]) && $ret["contact_template_id"] == '') {
            $form->addRule('timeperiod_tp_id', _("Compulsory Period"), 'required');
            $form->addRule('timeperiod_tp_id2', _("Compulsory Period"), 'required');
            $form->addRule('contact_hostNotifOpts', _("Compulsory Option"), 'required');
            $form->addRule('contact_svNotifOpts', _("Compulsory Option"), 'required');
            $form->addRule('contact_hostNotifCmds', _("Compulsory Command"), 'required');
            $form->addRule('contact_svNotifCmds', _("Compulsory Command"), 'required');
        }
    }

    $form->addRule(array('contact_passwd', 'contact_passwd2'), _("Passwords do not match"), 'compare');
    $form->registerRule('exist', 'callback', 'testContactExistence');
    $form->addRule('contact_name', "<font style='color: red;'>*</font>&nbsp;" . _("Contact already exists"), 'exist');
    $form->registerRule('existAlias', 'callback', 'testAliasExistence');
    $form->addRule('contact_alias', "<font style='color: red;'>*</font>&nbsp;" . _("Alias already exists"), 'existAlias');
    $form->registerRule('keepOneContactAtLeast', 'callback', 'keepOneContactAtLeast');
    $form->addRule('contact_alias', _("You have to keep at least one contact to access to Centreon"), 'keepOneContactAtLeast');
} elseif ($o == "mc") {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));


/**
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign("helpattr", 'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

if ($o == "w") {
# Just watch a contact information
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&contact_id=" . $contact_id . "'"));
    }
    $form->setDefaults($cct);
    $form->freeze();
} elseif ($o == "c") {
# Modify a contact information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($cct);
} elseif ($o == "a") {
# Add a contact information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} elseif ($o == "mc") {
# Massive Change
    $subMC = $form->addElement('submit', 'submitMC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

if ($centreon->optGen['ldap_auth_enable'] == 1 && $cct['contact_auth_type'] == 'ldap') {
    $tpl->assign("ldap_group", _("Group Ldap"));
    if (isset($cgLdap)) {
        $tpl->assign("ldapGroups", $cgLdap);
    }
}

$valid = false;
if ($form->validate() && $from_list_menu == false) {
    $cctObj = $form->getElement('contact_id');
    if ($form->getSubmitValue("submitA")) {
        $cctObj->setValue(insertContactInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateContactInDB($cctObj->getValue());
    } elseif ($form->getSubmitValue("submitMC")) {
        $select = explode(",", $select);
        foreach ($select as $key => $value) {
            if ($value) {
                updateContactInDB($value, true);
            }
        }
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path . "listContact.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('displayAdminFlag', $centreon->user->admin);
    $tpl->assign("tzUsed", $CentreonGMT->used());
    if ($centreon->optGen['ldap_auth_enable']) {
        $tpl->assign('ldap', $centreon->optGen['ldap_auth_enable']);
    }
    $tpl->assign('auth_type', $cct['contact_auth_type']);
    $tpl->display("formContact.ihtml");
}
?>
<script type="text/javascript" src="./include/common/javascript/keygen.js"></script>
<script type="text/javascript">

function uncheckAllH(object)
{
    if (object.id == "hNone" && object.checked) {
        document.getElementById('hDown').checked = false;
        document.getElementById('hUnreachable').checked = false;
        document.getElementById('hRecovery').checked = false;
        if (document.getElementById('hFlapping')) {
            document.getElementById('hFlapping').checked = false;
        }
        if (document.getElementById('hScheduled')) {
            document.getElementById('hScheduled').checked = false;
        }
    } else {
        document.getElementById('hNone').checked = false;
    }
}

function uncheckAllS(object)
{
    if (object.id == "sNone" && object.checked) {
        document.getElementById('sWarning').checked = false;
        document.getElementById('sUnknown').checked = false;
        document.getElementById('sCritical').checked = false;
        document.getElementById('sRecovery').checked = false;
        if (document.getElementById('sFlapping')) {
            document.getElementById('sFlapping').checked = false;
        }
        if (document.getElementById('sScheduled')) {
            document.getElementById('sScheduled').checked = false;
        }
    } else {
        document.getElementById('sNone').checked = false;
    }
}
</script>