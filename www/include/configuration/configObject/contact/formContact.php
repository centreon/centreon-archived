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

use Centreon\Infrastructure\Event\EventDispatcher;

if (!isset($centreon)) {
    exit();
}

if (!$centreon->user->admin && $contactId) {
    $aclOptions = array(
        'fields' => array('contact_id', 'contact_name'),
        'keys' => array('contact_id'),
        'get_row' => 'contact_name',
        'conditions' => array('contact_id' => $contactId)
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

try {
    $licenseObject = $dependencyInjector['lm.license'];
    $isLicenseValid = $licenseObject->validate(true);
} catch (\Exception $ex) {
    $isLicenseValid = false;
}

define('HTML_SPACEMENT', '&nbsp;');
$initialValues = array();

/*
 * Check if this server is a Remote Server to hide some part of form
 */
$dbResult = $pearDB->query("SELECT i.value FROM informations i WHERE i.key = 'isRemote'");
$result = $dbResult->fetch();
if ($result === false) {
    $isRemote = false;
} else {
    $isRemote = array_map("myDecode", $result);
    $isRemote = ($isRemote['value'] === 'yes') ? true : false;
}
$dbResult->closeCursor();

$cct = array();
if (($o == "c" || $o == "w") && $contactId) {
    /**
     * Init Tables informations
     */
    $cct["contact_hostNotifCmds"] = array();
    $cct["contact_svNotifCmds"] = array();
    $cct["contact_cgNotif"] = array();

    $dbResult = $pearDB->prepare("SELECT * FROM contact WHERE contact_id = :contactId LIMIT 1");
    $dbResult->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
    $dbResult->execute();
    $cct = array_map("myDecode", $dbResult->fetch());
    $cct["contact_passwd"] = null;
    $dbResult->closeCursor();

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
    $DBRESULT->closeCursor();

    /**
     * Get DLAP auth informations
     */
    $DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'ldap_auth_enable'");
    while ($ldap_auths = $DBRESULT->fetchRow()) {
        $ldap_auth[$ldap_auths["key"]] = myDecode($ldap_auths["value"]);
    }
    $DBRESULT->closeCursor();

    /**
     * Get ACL informations for this user
     */
    $DBRESULT = $pearDB->query("SELECT acl_group_id 
                                FROM `acl_group_contacts_relations` 
                                WHERE `contact_contact_id` = '" . intval($contactId) . "'");
    for ($i = 0; $data = $DBRESULT->fetchRow(); $i++) {
        if (!$centreon->user->admin && !isset($allowedAclGroups[$data['acl_group_id']])) {
            $initialValues['contact_acl_groups'] = $data['acl_group_id'];
        } else {
            $cct["contact_acl_groups"][$i] = $data["acl_group_id"];
        }
    }
    $DBRESULT->closeCursor();
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
 * Contact Groups come from DB -> Store in $notifCcts Array
 */
$notifCgs = array();

$cg = new CentreonContactgroup($pearDB);
$notifCgs = $cg->getListContactgroup(false);

if (
    $centreon->optGen['ldap_auth_enable'] == 1
    && $cct['contact_auth_type'] == 'ldap' && isset($cct['ar_id']) && $cct['ar_id']
) {
    $ldap = new CentreonLDAP($pearDB, null, $cct['ar_id']);
    if (false !== $ldap->connect()) {
        $cgLdap = $ldap->listGroupsForUser($cct['contact_ldap_dn']);
    }
}

/**
 * Contacts Templates
 */
if (isset($contactId)) {
    $strRestrinction = " AND contact_id != '" . intval($contactId) . "'";
} else {
    $strRestrinction = "";
}

$contactTpl = array(null => "           ");
$DBRESULT = $pearDB->query("SELECT contact_id, contact_name
                            FROM contact
                            WHERE contact_register = '0' $strRestrinction 
                            ORDER BY contact_name");
while ($contacts = $DBRESULT->fetchRow()) {
    $contactTpl[$contacts["contact_id"]] = $contacts["contact_name"];
}
$DBRESULT->closeCursor();

/**
 * Template / Style for Quickform input
 */
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "60");
$attrsTextDescr = array("size" => "80");
$attrsTextMail = array("size" => "90");
$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
$attrsTextarea = array("rows" => "15", "cols" => "100");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />'
    . '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$timeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timeRoute,
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);
$attrCommands = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => true,
    'linkedObject' => 'centreonCommand'
);
$contactRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);
$aclRoute = './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=list';
$attrAclgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $aclRoute,
    'multiple' => true,
    'linkedObject' => 'centreonAclGroup'
);

$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
/**
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/**
 * @var $moduleFormManager \Centreon\Domain\Service\ModuleFormManager
 */

if ($o == "a") {
    $form->addElement('header', 'title', _("Add a User"));

    $eventDispatcher->notify(
        'contact.form',
        EventDispatcher::EVENT_DISPLAY,
        [
            'form' => $form,
            'tpl' => $tpl,
            'contact_id' => $contactId
        ]
    );
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a User"));

    $eventDispatcher->notify(
        'contact.form',
        EventDispatcher::EVENT_READ,
        [
            'form' => $form,
            'tpl' => $tpl,
            'contact_id' => $contactId
        ]
    );
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a User"));

    $eventDispatcher->notify(
        'contact.form',
        EventDispatcher::EVENT_READ,
        [
            'form' => $form,
            'tpl' => $tpl,
            'contact_id' => $contactId
        ]
    );
} elseif ($o == "mc") {
    $form->addElement('header', 'title', _("Massive Change"));

    $eventDispatcher->notify(
        'contact.form',
        EventDispatcher::EVENT_DISPLAY,
        [
            'form' => $form,
            'tpl' => $tpl,
            'contact_id' => $contactId
        ]
    );
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
    $mc_mod_cg[] = $form->createElement('radio', 'mc_mod_cg', null, _("Incremental"), '0');
    $mc_mod_cg[] = $form->createElement('radio', 'mc_mod_cg', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cg, 'mc_mod_cg', _("Update mode"), HTML_SPACEMENT);
    $form->setDefaults(array('mc_mod_cg' => '0'));
}

$defaultDatasetRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=contact&field=contact_cgNotif&id=' . $contactId;

$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => $defaultDatasetRoute)
);
$form->addElement('select2', 'contact_cgNotif', _("Linked to Contact Groups"), array(), $attrContactgroup1);

/**
 * Contact Centreon information
 */
$form->addElement('header', 'oreon', _("Centreon"));
$tab = array();
$tab[] = $form->createElement('radio', 'contact_oreon', null, _("Yes"), '1');
$tab[] = $form->createElement('radio', 'contact_oreon', null, _("No"), '0');
$form->addGroup($tab, 'contact_oreon', _("Reach Centreon Front-end"), HTML_SPACEMENT);

$form->addElement(
    'password',
    'contact_passwd',
    _("Password"),
    array("size" => "30", "autocomplete" => "new-password", "id" => "passwd1", "onkeypress" => "resetPwdType(this);")
);
$form->addElement(
    'password',
    'contact_passwd2',
    _("Confirm Password"),
    array("size" => "30", "autocomplete" => "new-password", "id" => "passwd2", "onkeypress" => "resetPwdType(this);")
);
$form->addElement('button', 'contact_gen_passwd', _("Generate"), array('onclick' => 'generatePassword("passwd");'));

$form->addElement('select', 'contact_lang', _("Default Language"), $langs);
$form->addElement(
    'select',
    'contact_type_msg',
    _("Mail Type"),
    array(null => null, "txt" => "txt", "html" => "html", "pdf" => "pdf")
);

if ($centreon->user->admin) {
    $tab = array();
    $tab[] = $form->createElement('radio', 'contact_admin', null, _("Yes"), '1');
    $tab[] = $form->createElement('radio', 'contact_admin', null, _("No"), '0');
    $form->addGroup($tab, 'contact_admin', _("Admin"), HTML_SPACEMENT);

    $tab = array();
    $tab[] = $form->createElement('radio', 'reach_api', null, _("Yes"), '1');
    $tab[] = $form->createElement('radio', 'reach_api', null, _("No"), '0');
    $form->addGroup($tab, 'reach_api', _("Reach API Configuration"), HTML_SPACEMENT);

    $tab = array();
    $tab[] = $form->createElement('radio', 'reach_api_rt', null, _("Yes"), '1');
    $tab[] = $form->createElement('radio', 'reach_api_rt', null, _("No"), '0');
    $form->addGroup($tab, 'reach_api_rt', _("Reach API Realtime"), HTML_SPACEMENT);
}

/**
 * ACL configurations
 */
if ($o == "mc") {
    $mc_mod_cg = array();
    $mc_mod_cg[] = $form->createElement('radio', 'mc_mod_acl', null, _("Incremental"), '0');
    $mc_mod_cg[] = $form->createElement('radio', 'mc_mod_acl', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cg, 'mc_mod_acl', _("Update mode"), HTML_SPACEMENT);
    $form->setDefaults(array('mc_mod_acl' => '0'));
}

$defaultDatasetRoute = './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup'
    . '&action=defaultValues&target=contact&field=contact_acl_groups&id=' . $contactId;
$attrAclgroup1 = array_merge(
    $attrAclgroups,
    array('defaultDatasetRoute' => $defaultDatasetRoute)
);
$form->addElement('select2', 'contact_acl_groups', _("Access list groups"), array(), $attrAclgroup1);

/**
 * Include GMT Class
 */
require_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";

$CentreonGMT = new CentreonGMT($pearDB);

$availableDatasetRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_timezone&action=list';
$defaultDatasetRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone'
    . '&action=defaultValues&target=contact&field=contact_location&id=' . $contactId;
$attrTimezones = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $availableDatasetRoute,
    'defaultDatasetRoute' => $defaultDatasetRoute,
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
    $form->setDefaults(array(
        'contact_oreon' => '1',
        'contact_admin' => '0',
        'reach_api' => '0',
        'reach_api_rt' => '0'
    ));
}
$form->addElement('select', 'contact_auth_type', _("Authentication Source"), $auth_type);

/**
 * Notification informations
 */
$form->addElement('header', 'notification', _("Notification"));

$tab = array();
$tab[] = $form->createElement('radio', 'contact_enable_notifications', null, _("Yes"), '1');
$tab[] = $form->createElement('radio', 'contact_enable_notifications', null, _("No"), '0');
$tab[] = $form->createElement('radio', 'contact_enable_notifications', null, _("Default"), '2');
$form->addGroup($tab, 'contact_enable_notifications', _("Enable Notifications"), HTML_SPACEMENT);
if ($o != "mc") {
    $form->setDefaults(array('contact_enable_notifications' => '2'));
}

/** * *****************************
 * Host notifications
 */
$form->addElement('header', 'hostNotification', _("Host"));
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'd',
    HTML_SPACEMENT,
    _("Down"),
    array('id' => 'hDown', 'onClick' => 'uncheckAllH(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'u',
    HTML_SPACEMENT,
    _("Unreachable"),
    array('id' => 'hUnreachable', 'onClick' => 'uncheckAllH(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'r',
    HTML_SPACEMENT,
    _("Recovery"),
    array('id' => 'hRecovery', 'onClick' => 'uncheckAllH(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'f',
    HTML_SPACEMENT,
    _("Flapping"),
    array('id' => 'hFlapping', 'onClick' => 'uncheckAllH(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    's',
    HTML_SPACEMENT,
    _("Downtime Scheduled"),
    array('id' => 'hScheduled', 'onClick' => 'uncheckAllH(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'n',
    HTML_SPACEMENT,
    _("None"),
    array('id' => 'hNone', 'onClick' => 'javascript:uncheckAllH(this);')
);
$form->addGroup($hostNotifOpt, 'contact_hostNotifOpts', _("Host Notification Options"), '&nbsp;&nbsp;');

$defaultDatasetRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=contact&field=timeperiod_tp_id&id=' . $contactId;
$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $defaultDatasetRoute)
);
$form->addElement('select2', 'timeperiod_tp_id', _("Host Notification Period"), array(), $attrTimeperiod1);


unset($hostNotifOpt);

if ($o == "mc") {
    $mc_mod_hcmds = array();
    $mc_mod_hcmds[] = $form->createElement('radio', 'mc_mod_hcmds', null, _("Incremental"), '0');
    $mc_mod_hcmds[] = $form->createElement('radio', 'mc_mod_hcmds', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hcmds, 'mc_mod_hcmds', _("Update mode"), HTML_SPACEMENT);
    $form->setDefaults(array('mc_mod_hcmds' => '0'));
}

$defaultDatasetRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=contact&field=contact_hostNotifCmds&id=' . $contactId;
$availableDatasetRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_command&action=list&t=1';
$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => $defaultDatasetRoute,
        'availableDatasetRoute' => $availableDatasetRoute
    )
);
$form->addElement('select2', 'contact_hostNotifCmds', _("Host Notification Commands"), array(), $attrCommand1);

/** * *****************************
 * Service notifications
 */
$form->addElement('header', 'serviceNotification', _("Service"));
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'w',
    HTML_SPACEMENT,
    _("Warning"),
    array('id' => 'sWarning', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'u',
    HTML_SPACEMENT,
    _("Unknown"),
    array('id' => 'sUnknown', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'c',
    HTML_SPACEMENT,
    _("Critical"),
    array('id' => 'sCritical', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'r',
    HTML_SPACEMENT,
    _("Recovery"),
    array('id' => 'sRecovery', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'f',
    HTML_SPACEMENT,
    _("Flapping"),
    array('id' => 'sFlapping', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    's',
    HTML_SPACEMENT,
    _("Downtime Scheduled"),
    array('id' => 'sScheduled', 'onClick' => 'uncheckAllS(this);')
);
$svNotifOpt[] = $form->createElement(
    'checkbox',
    'n',
    HTML_SPACEMENT,
    _("None"),
    array('id' => 'sNone', 'onClick' => 'uncheckAllS(this);')
);
$form->addGroup($svNotifOpt, 'contact_svNotifOpts', _("Service Notification Options"), '&nbsp;&nbsp;');

$defaultAttrTimeperiod2Route = './include/common/webServices/rest/internal.php?'
    . 'object=centreon_configuration_timeperiod&action=defaultValues&target=contact&field=timeperiod_tp_id2&id='
    . $contactId;

$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $defaultAttrTimeperiod2Route)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Service Notification Period"), array(), $attrTimeperiod2);

if ($o == "mc") {
    $mc_mod_svcmds = array();
    $mc_mod_svcmds[] = $form->createElement('radio', 'mc_mod_svcmds', null, _("Incremental"), '0');
    $mc_mod_svcmds[] = $form->createElement('radio', 'mc_mod_svcmds', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_svcmds, 'mc_mod_svcmds', _("Update mode"), HTML_SPACEMENT);
    $form->setDefaults(array('mc_mod_svcmds' => '0'));
}

$defaultattrCommand2Route = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=contact&field=contact_svNotifCmds&id=' . $contactId;
$availableCommand2Route = './include/common/webServices/rest/internal.php?'
    . 'object=centreon_configuration_command&action=list&t=1';

$attrCommand2 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => $defaultattrCommand2Route,
        'availableDatasetRoute' => $availableCommand2Route
    )
);
$form->addElement('select2', 'contact_svNotifCmds', _("Service Notification Commands"), array(), $attrCommand2);

/**
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$cctActivation[] = $form->createElement('radio', 'contact_activate', null, _("Enabled"), '1');
$cctActivation[] = $form->createElement('radio', 'contact_activate', null, _("Disabled"), '0');
$form->addGroup($cctActivation, 'contact_activate', _("Status"), HTML_SPACEMENT);
$form->setDefaults(array('contact_activate' => '1'));
if ($o == "c" && $centreon->user->get_id() == $cct["contact_id"]) {
    $form->freeze('contact_activate');
}

$platformDataSendingRadios = [
    $form->createElement('radio', null, null, _('No'), '0'),
    $form->createElement('radio', null, null, _('Contact Details'), '1'),
    $form->createElement('radio', null, null, _('Anonymized'), '2')
];

if ($isLicenseValid) {
    unset($platformDataSendingRadios[0]);
}

$form->addGroup(
    $platformDataSendingRadios,
    'contact_platform_data_sending',
    _('Contextual assistance and associated data sending'),
    HTML_SPACEMENT
);

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
    if ($isRemote == false) {
        $form->addRule('contact_email', _("Valid Email"), 'required');
    }
    $form->addRule('contact_oreon', _("Required Field"), 'required');
    $form->addRule('contact_lang', _("Required Field"), 'required');
    if ($centreon->user->admin) {
        $form->addRule('contact_admin', _("Required Field"), 'required');
    }
    $form->addRule('contact_auth_type', _("Required Field"), 'required');

    if (
        (isset($ret["contact_enable_notifications"]["contact_enable_notifications"])
        && $ret["contact_enable_notifications"]["contact_enable_notifications"] == 1)
        && ($isRemote == false)
    ) {
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
    $form->addRule(
        'contact_alias',
        "<font style='color: red;'>*</font>&nbsp;" . _("Alias already exists"),
        'existAlias'
    );
    $form->registerRule('keepOneContactAtLeast', 'callback', 'keepOneContactAtLeast');
    $form->addRule(
        'contact_alias',
        _("You have to keep at least one contact to access to Centreon"),
        'keepOneContactAtLeast'
    );
} elseif ($o == "mc") {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, '
    . '"orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], '
    . 'WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

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
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&contact_id=" . $contactId . "'")
        );
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
    if (!$centreon->user->admin && $contact_id) {
        $form->removeElement('contact_admin');
        $form->removeElement('reach_api');
        $form->removeElement('reach_api_rt');
    }
    if ($form->getSubmitValue("submitA")) {
        $newContactId = insertContactInDB();
        $cctObj->setValue($contactId);

        $eventDispatcher->notify(
            'contact.form',
            EventDispatcher::EVENT_ADD,
            [
                'form' => $form,
                'contact_id' => $newContactId
            ]
        );
    } elseif ($form->getSubmitValue("submitC")) {
        updateContactInDB($cctObj->getValue());

        $eventDispatcher->notify(
            'contact.form',
            EventDispatcher::EVENT_UPDATE,
            [
                'form' => $form,
                'contact_id' => $contactId
            ]
        );
    } elseif ($form->getSubmitValue("submitMC")) {
        $select = explode(",", $select);
        foreach ($select as $key => $contactId) {
            if ($contactId) {
                updateContactInDB($contactId, true);

                $eventDispatcher->notify(
                    'contact.form',
                    EventDispatcher::EVENT_UPDATE,
                    [
                        'form' => $form,
                        'contact_id' => $contactId
                    ]
                );
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
    $contactAuthType = isset($cct['contact_auth_type']) ? $cct['contact_auth_type'] : null;
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
    $tpl->assign('auth_type', $contactAuthType);

    if ($isRemote == false) {
        $tpl->display("formContact.ihtml");
    } else {
        $tpl->display("formContactLight.ihtml");
    }
}
?>
<script type="text/javascript" src="./include/common/javascript/keygen.js"></script>
<script type="text/javascript">

    function uncheckAllH(object) {
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

    function uncheckAllS(object) {
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
