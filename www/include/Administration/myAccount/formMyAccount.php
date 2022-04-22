<?php

/*
 * Copyright 2005-2020 Centreon
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

require_once __DIR__ . '/../../../class/centreon.class.php';
require_once "./include/common/common-Func.php";

require_once './class/centreonFeature.class.php';
require_once __DIR__ . '/../../../class/centreonContact.class.php';

$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

/*
 * Path to the configuration dir
 */
$path = "./include/Administration/myAccount/";

// PHP Functions
require_once $path . "DB-Func.php";

if (!isset($centreonFeature)) {
    $centreonFeature = new CentreonFeature($pearDB);
}

/**
 * Get the Security Policy for automatic generation password.
 */
$passwordSecurityPolicy = (new CentreonContact($pearDB))->getPasswordSecurityPolicy();
$encodedPasswordPolicy = json_encode($passwordSecurityPolicy);

/*
 * Database retrieve information for the User
 */
$cct = array();
if ($o == "c") {
    $query = "SELECT contact_id, contact_name, contact_alias, contact_lang, contact_email, contact_pager,
        contact_autologin_key, default_page, show_deprecated_pages, contact_auth_type,
        enable_one_click_export
        FROM contact WHERE contact_id = :id";
    $DBRESULT = $pearDB->prepare($query);
    $DBRESULT->bindValue(':id', $centreon->user->get_id(), \PDO::PARAM_INT);
    $DBRESULT->execute();

    // Set base value
    $cct = array_map("myDecode", $DBRESULT->fetch());
    $res = $pearDB->prepare(
        "SELECT cp_key, cp_value
        FROM contact_param
        WHERE cp_contact_id = :id"
    );
    $res->bindValue(':id', $centreon->user->get_id(), \PDO::PARAM_INT);
    $res->execute();

    while ($row = $res->fetch()) {
        $cct[$row['cp_key']] = $row['cp_value'];
    }

    // selected by default is Resources status page
    $cct['default_page'] = $cct['default_page'] ?: CentreonAuth::DEFAULT_PAGE;
}

/*
 * Database retrieve information for different elements list we need on the page
 *
 * Langs -> $langs Array
 */
$langs = array();
$langs = getLangs();
$attrsText = array("size" => "35");

$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Change my settings"));
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'contact_name', _("Name"), $attrsText);
if ($cct["contact_auth_type"] != 'ldap') {
    $form->addElement('text', 'contact_alias', _("Alias / Login"), $attrsText);
} else {
    $form->addElement('text', 'contact_alias', _("Alias / Login"), $attrsText)->freeze();
}
$form->addElement('text', 'contact_email', _("Email"), $attrsText);
$form->addElement('text', 'contact_pager', _("Pager"), $attrsText);
if ($cct["contact_auth_type"] != 'ldap') {
    $form->addFormRule('validatePasswordModification');
    $statement = $pearDB->prepare(
        "SELECT creation_date FROM contact_password WHERE contact_id = :contactId ORDER BY creation_date DESC LIMIT 1"
    );
    $statement->bindValue(':contactId', $centreon->user->get_id(), \PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetchColumn();
    if ($result) {
        $passwordCreationDate = (int) $result;
        $passwordExpirationDate =
            $passwordCreationDate + $passwordSecurityPolicy['password_expiration']['expiration_delay'];
        $isPasswordExpired = time() > $passwordExpirationDate;
        if (!in_array($centreon->user->get_alias(), $passwordSecurityPolicy['password_expiration']['excluded_users'])) {
            if ($isPasswordExpired) {
                $expirationMessage = _("Your password has expired. Please change it.");
            } else {
                $expirationMessage = sprintf(
                    _("Your password will expire in %s days."),
                    ceil(($passwordExpirationDate - time()) / 86400)
                );
            }
        }
    }
    $form->addElement(
        'password',
        'contact_passwd',
        _("Password"),
        ["size" => "30", "autocomplete" => "new-password", "id" => "passwd1", "onkeypress" => "resetPwdType(this);"]
    );
    $form->addElement(
        'password',
        'contact_passwd2',
        _("Confirm Password"),
        ["size" => "30", "autocomplete" => "new-password", "id" => "passwd2", "onkeypress" => "resetPwdType(this);"]
    );
    $form->addElement(
        'button',
        'contact_gen_passwd',
        _("Generate"),
        ['onclick' => "generatePassword('passwd', '$encodedPasswordPolicy');", 'class' => 'btc bt_info']
    );
}
$form->addElement('text', 'contact_autologin_key', _("Autologin Key"), array("size" => "30", "id" => "aKey"));
$form->addElement(
    'button',
    'contact_gen_akey',
    _("Generate"),
    ['onclick' => "generatePassword('aKey', '$encodedPasswordPolicy');", 'class' => 'btc bt_info']
);
$form->addElement('select', 'contact_lang', _("Language"), $langs);
$form->addElement('checkbox', 'show_deprecated_pages', _("Use deprecated pages"), null, $attrsText);
if (!$isRemote) {
    $form->addElement(
        'checkbox',
        'enable_one_click_export',
        _("Enable the one-click export button for poller configuration [BETA]"),
        null,
        $attrsText
    );
}


/* ------------------------ Topoogy ---------------------------- */
$pages = [];
$aclUser = $centreon->user->lcaTStr;
if (!empty($aclUser)) {
    $acls = array_flip(explode(',', $aclUser));
    /**
    * Transform [1, 2, 101, 202, 10101, 20201] to :
    *
    * 1
    *   101
    *     10101
    * 2
    *   202
    *     20201
    */
    $createTopologyTree = function (array $topologies): array {
        ksort($topologies, \SORT_ASC);
        $parentsLvl = [];

        // Classify topologies by parents
        foreach (array_keys($topologies) as $page) {
            if (strlen($page) == 1) {
                // MENU level 1
                if (!array_key_exists($page, $parentsLvl)) {
                    $parentsLvl[$page] = [];
                }
            } elseif (strlen($page) == 3) {
                // MENU level 2
                $parentLvl1 = substr($page, 0, 1);
                if (!array_key_exists($parentLvl1, $parentsLvl)) {
                    $parentsLvl[$parentLvl1] = [];
                }
                if (!array_key_exists($page, $parentsLvl[$parentLvl1])) {
                    $parentsLvl[$parentLvl1][$page] = [];
                }
            } elseif (strlen($page) == 5) {
                // MENU level 3
                $parentLvl1 = substr($page, 0, 1);
                $parentLvl2 = substr($page, 0, 3);
                if (!array_key_exists($parentLvl1, $parentsLvl)) {
                    $parentsLvl[$parentLvl1] = [];
                }
                if (!array_key_exists($parentLvl2, $parentsLvl[$parentLvl1])) {
                    $parentsLvl[$parentLvl1][$parentLvl2] = [];
                }
                if (!in_array($page, $parentsLvl[$parentLvl1][$parentLvl2])) {
                    $parentsLvl[$parentLvl1][$parentLvl2][] = $page;
                }
            }
        }

        return $parentsLvl;
    };

    /**
     * Check if at least one child can be shown
     */
    $oneChildCanBeShown = function () use (&$childrenLvl3, &$translatedPages): bool {
        $isCanBeShow = false;
        foreach ($childrenLvl3 as $topologyPage) {
            if ($translatedPages[$topologyPage]['show']) {
                $isCanBeShow = true;
                break;
            }
        }
        return $isCanBeShow;
    };

    $topologies = $createTopologyTree($acls);

    /**
     * Retrieve the name of all topologies available for this user
     */
    $aclResults = $pearDB->query(
        "SELECT topology_page, topology_name, topology_show "
        . "FROM topology "
        . "WHERE topology_page IN ($aclUser)"
    );

    $translatedPages = [];

    while ($acl = $aclResults->fetch(\PDO::FETCH_ASSOC)) {
        $translatedPages[$acl['topology_page']] = [
            'i18n' => _($acl['topology_name']),
            'show' => ((int)$acl['topology_show'] === 1)
        ];
    }

    /**
     * Create flat tree for menu with the topologies names
     * [item1Id] = menu1 > submenu1 > item1
     * [item2Id] = menu2 > submenu2 > item2
     */
    foreach ($topologies as $parentLvl1 => $childrenLvl2) {
        $parentNameLvl1 = $translatedPages[$parentLvl1]['i18n'];
        foreach ($childrenLvl2 as $parentLvl2 => $childrenLvl3) {
            $parentNameLvl2 = $translatedPages[$parentLvl2]['i18n'];
            $isThirdLevelMenu = false;
            $parentLvl3 = null;

            if ($oneChildCanBeShown()) {
                /**
                 * There is at least one child that can be shown then we can
                 * process the third level
                 */
                foreach ($childrenLvl3 as $parentLvl3) {
                    if ($translatedPages[$parentLvl3]['show']) {
                        $parentNameLvl3 = $translatedPages[$parentLvl3]['i18n'];

                        if ($parentNameLvl2 === $parentNameLvl3) {
                            /**
                             * The name between lvl2 and lvl3 are equals.
                             * We keep only lvl1 and lvl3
                             */
                            $pages[$parentLvl3] = $parentNameLvl1 . ' > '
                                . $parentNameLvl3;
                        } else {
                            $pages[$parentLvl3] = $parentNameLvl1 . ' > '
                                . $parentNameLvl2 . ' > '
                                . $parentNameLvl3;
                        }
                    }
                }

                $isThirdLevelMenu = true;
            }

            // select parent from level 2 if level 3 is missing
            $pageId = $parentLvl3 ?: $parentLvl2;

            if (!$isThirdLevelMenu && $translatedPages[$pageId]['show']) {
                /**
                 * We show only first and second level
                 */
                $pages[$pageId] =
                    $parentNameLvl1 . ' > ' . $parentNameLvl2;
            }
        }
    }
}

$form->addElement('select', 'default_page', _("Default page"), $pages);

$form->addElement('checkbox', 'monitoring_host_notification_0', _('Show Up status'));
$form->addElement('checkbox', 'monitoring_host_notification_1', _('Show Down status'));
$form->addElement('checkbox', 'monitoring_host_notification_2', _('Show Unreachable status'));
$form->addElement('checkbox', 'monitoring_svc_notification_0', _('Show OK status'));
$form->addElement('checkbox', 'monitoring_svc_notification_1', _('Show Warning status'));
$form->addElement('checkbox', 'monitoring_svc_notification_2', _('Show Critical status'));
$form->addElement('checkbox', 'monitoring_svc_notification_3', _('Show Unknown status'));

/* Add feature information */
$features = $centreonFeature->getFeatures();
$defaultFeatures = array();
foreach ($features as $feature) {
    $featRadio = array();
    $featRadio[] = $form->createElement('radio', $feature['version'], null, _('New version'), '1');
    $featRadio[] = $form->createElement('radio', $feature['version'], null, _('Legacy version'), '0');
    $feat = $form->addGroup($featRadio, 'features[' . $feature['name'] . ']', $feature['name'], '&nbsp;');
    $defaultFeatures['features'][$feature['name']][$feature['version']] = '0';
}

$sound_files = scandir(_CENTREON_PATH_ . "www/sounds/");
$sounds = array(null => null);
foreach ($sound_files as $f) {
    if ($f == "." || $f == "..") {
        continue;
    }
    $info = pathinfo($f);
    $fname = basename($f, "." . $info['extension']);
    $sounds[$fname] = $fname;
}
$form->addElement('select', 'monitoring_sound_host_notification_0', _("Sound for Up status"), $sounds);
$form->addElement('select', 'monitoring_sound_host_notification_1', _("Sound for Down status"), $sounds);
$form->addElement('select', 'monitoring_sound_host_notification_2', _("Sound for Unreachable status"), $sounds);
$form->addElement('select', 'monitoring_sound_svc_notification_0', _("Sound for OK status"), $sounds);
$form->addElement('select', 'monitoring_sound_svc_notification_1', _("Sound for Warning status"), $sounds);
$form->addElement('select', 'monitoring_sound_svc_notification_2', _("Sound for Critical status"), $sounds);
$form->addElement('select', 'monitoring_sound_svc_notification_3', _("Sound for Unknown status"), $sounds);

$availableRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=list';
$defaultRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone' .
    '&action=defaultValues&target=contact&field=contact_location&id=' . $centreon->user->get_id();
$attrTimezones = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $availableRoute,
    'defaultDatasetRoute' => $defaultRoute,
    'multiple' => false,
    'linkedObject' => 'centreonGMT'
);
$form->addElement('select2', 'contact_location', _("Timezone / Location"), array(), $attrTimezones);

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["contact_name"]));
}

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('contact_name', 'myReplace');
$form->addRule('contact_name', _("Compulsory name"), 'required');
$form->addRule('contact_alias', _("Compulsory alias"), 'required');
$form->addRule('contact_email', _("Valid Email"), 'required');
if ($cct["contact_auth_type"] !== 'ldap') {
    $form->addRule(array('contact_passwd', 'contact_passwd2'), _("Passwords do not match"), 'compare');
}
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('contact_name', _("Name already in use"), 'exist');
$form->registerRule('existAlias', 'callback', 'testAliasExistence');
$form->addRule('contact_alias', _("Name already in use"), 'existAlias');
$form->setRequiredNote("<font style='color: red;'>*</font>" . _("Required fields"));
$form->addFormRule('checkAutologinValue');

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$form->setDefaults($defaultFeatures);

// remove illegal chars in data sent by the user
$cct['contact_name'] = CentreonUtils::escapeSecure($cct['contact_name'], CentreonUtils::ESCAPE_ILLEGAL_CHARS);
$cct['contact_alias'] = CentreonUtils::escapeSecure($cct['contact_alias'], CentreonUtils::ESCAPE_ILLEGAL_CHARS);

// Modify a contact information
if ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($cct);
    /* Add saved value for feature testing */
    $userFeatures = $centreonFeature->userFeaturesValue($centreon->user->get_id());
    $defaultUserFeatures = array();
    foreach ($userFeatures as $feature) {
        $defaultUserFeatures['features'][$feature['name']][$feature['version']] = $feature['enabled'];
    }
    $form->setDefaults($defaultUserFeatures);
}

$sessionKeyFreeze = 'administration-form-my-account-freeze';

if ($form->validate()) {
    updateContactInDB($centreon->user->get_id());
    if ($form->getSubmitValue("contact_passwd")) {
        $centreon->user->passwd = md5($form->getSubmitValue("contact_passwd"));
    }
    $o = null;
    $features = $form->getSubmitValue('features');

    if ($features === null) {
        $features = [];
    }

    $centreonFeature->saveUserFeaturesValue($centreon->user->get_id(), $features);
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c'", 'class' => 'btc bt_info')
    );
    $form->freeze();

    $showDeprecatedPages = $form->getSubmitValue("show_deprecated_pages") ? '1' : '0';
    if (
        $form->getSubmitValue("contact_lang") !== $cct['contact_lang']
        || $showDeprecatedPages !== $cct['show_deprecated_pages']
        || $form->getSubmitValue('enable_one_click_export') !== $cct['enable_one_click_export']
    ) {
        $contactStatement = $pearDB->prepare(
            'SELECT * FROM contact WHERE contact_id = :contact_id'
        );
        $contactStatement->bindValue(':contact_id', $centreon->user->get_id(), \PDO::PARAM_INT);
        $contactStatement->execute();
        if ($contact = $contactStatement->fetch()) {
            $_SESSION['centreon'] = new \Centreon($contact);
        }
        $_SESSION[$sessionKeyFreeze] = true;
        echo '<script>parent.location.href = "main.php?p=' . $p . '&o=c";</script>';
        exit;
    } elseif (array_key_exists($sessionKeyFreeze, $_SESSION)) {
        unset($_SESSION[$sessionKeyFreeze]);
    }
} elseif (array_key_exists($sessionKeyFreeze, $_SESSION) && $_SESSION[$sessionKeyFreeze] === true) {
    unset($_SESSION[$sessionKeyFreeze]);
    $o = null;
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c'", 'class' => 'btc bt_info')
    );
    $form->freeze();
}

//Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
if (isset($expirationMessage)) {
    $tpl->assign('expirationMessage', $expirationMessage);
}
$tpl->assign('cct', $cct);
$tpl->assign('o', $o);
$tpl->assign('featuresFlipping', (count($features) > 0));
$tpl->assign('contactIsAdmin', $centreon->user->get_admin());
$tpl->assign('isRemote', $isRemote);

/*
 * prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$tpl->display("formMyAccount.ihtml");
?>
<script type='text/javascript' src='./include/common/javascript/keygen.js'></script>
<script type="text/javascript">
    jQuery(function () {
        jQuery("select[name*='_notification_']").change(function () {
            if (jQuery(this).val()) {
                var snd = new buzz.sound("sounds/" + jQuery(this).val(), {
                    formats: ["ogg", "mp3"]
                });
            }
            snd.play();
        });
    });
</script>
