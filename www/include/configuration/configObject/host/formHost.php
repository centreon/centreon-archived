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

if (!$centreon->user->admin) {
    if (is_numeric($host_id) && false === strpos($aclHostString, "'" . $host_id . "'")) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this host'));
        return null;
    }
}

const PASSWORD_REPLACEMENT_VALUE = '**********';

$hostObj = new CentreonHost($pearDB);

$initialValues = array();

// host categories
$hcString = $acl->getHostCategoriesString();

// notification contacts
$notifCs = $acl->getContactAclConf(array(
    'fields' => array('contact_id', 'contact_name'),
    'get_row' => 'contact_name',
    'keys' => array('contact_id'),
    'conditions' => array('contact_register' => '1'),
    'order' => array('contact_name')
));

// notification contact groups
$notifCgs = $acl->getContactGroupAclConf(array(
    'fields' => array('cg_id', 'cg_name'),
    'get_row' => 'cg_name',
    'keys' => array('cg_id'),
    'order' => array('cg_name')
), false);

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

/*
 * Database retrieve information for Host
 */
$host = array();

// define macros as empty array to avoid null counting
$aMacros = array();

// Used to store all macro passwords
$macroPasswords = [];

if (($o === HOST_MODIFY || $o === HOST_WATCH) && isset($host_id)) {
    $statement = $pearDB->prepare(
        'SELECT * FROM host
        INNER JOIN extended_host_information ehi
            ON ehi.host_host_id = host.host_id
        WHERE host_id = :host_id LIMIT 1'
    );
    $statement->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
    $statement->execute();

    // Set base value
    $host_list = $statement->fetch();
    $host = array_map("myDecode", $host_list);

    $cmdId = $host['command_command_id'];

    // Set Host Notification Options
    $tmp = explode(',', $host["host_notification_options"]);
    foreach ($tmp as $key => $value) {
        $host["host_notifOpts"][trim($value)] = 1;
    }

    // Set Stalking Options
    $tmp = explode(',', $host["host_stalking_options"]);
    foreach ($tmp as $key => $value) {
        $host["host_stalOpts"][trim($value)] = 1;
    }

    // Set Host Category Parents
    $statement = $pearDB->prepare(
        'SELECT DISTINCT hostcategories_hc_id
        FROM hostcategories_relation hcr
        INNER JOIN hostcategories hc
            ON hcr.hostcategories_hc_id = hc.hc_id
        WHERE hc.level IS NULL AND hcr.host_host_id = :host_id'
    );
    $statement->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
    $statement->execute();
    for ($i = 0; $hc = $statement->fetch(); $i++) {
        if (!$centreon->user->admin && false === strpos($hcString, "'" . $hc['hostcategories_hc_id'] . "'")) {
            $initialValues['host_hcs'][] = $hc['hostcategories_hc_id'];
            $host["host_hcs"][$i] = $hc['hostcategories_hc_id'];
        } else {
            $host["host_hcs"][$i] = $hc['hostcategories_hc_id'];
        }
    }

    // Set Host and Nagios Server Relation
    $statement = $pearDB->prepare('SELECT `nagios_server_id` FROM `ns_host_relation` WHERE `host_host_id` = :host_id');
    $statement->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
    $statement->execute();
    for (($o !== HOST_MASSIVE_CHANGE) ? $i = 0 : $i = 1; $ns = $statement->fetch(); $i++) {
        $host["nagios_server_id"][$i] = $ns["nagios_server_id"];
    }
    unset($ns);

    // Set critically
    $statement = $pearDB->prepare(
        'SELECT hc.hc_id
        FROM hostcategories hc
        INNER JOIN hostcategories_relation hcr
            ON hcr.hostcategories_hc_id = hc.hc_id
        WHERE hc.level IS NOT NULL AND hcr.host_host_id = :host_id
        ORDER BY hc.level ASC LIMIT 1'
    );
    $statement->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
    $statement->execute();
    if ($statement->rowCount()) {
        $cr = $statement->fetch();
        $host['criticality_id'] = $cr['hc_id'];
    }

    $aTemplates = $hostObj->getTemplateChain($host_id, array(), -1, true, "host_name,host_id,command_command_id");
    if (!isset($cmdId)) {
        $cmdId = "";
    }

    if (isset($_REQUEST['macroInput'])) {
        /**
         * We don't taking into account the POST data sent from the interface in order the retrieve the original value
         * of all passwords.
         */
        $aMacros = $hostObj->getMacros($host_id, $aTemplates, $cmdId);

        /**
         * If a password has been modified from the interface, we retrieve the old password existing in the repository
         * (giving by the $aMacros variable) to inject it before saving.
         * Passwords will be saved using the $_REQUEST variable.
         */
        foreach ($_REQUEST['macroInput'] as $index => $macroName) {
            if (
                !isset($_REQUEST['macroFrom'][$index])
                || !isset($_REQUEST['macroPassword'][$index])
                || $_REQUEST['macroPassword'][$index] !== '1'                      // Not a password
                || $_REQUEST['macroValue'][$index] !== PASSWORD_REPLACEMENT_VALUE  // The password has not changed
            ) {
                continue;
            }
            foreach ($aMacros as $macroAlreadyExist) {
                if (
                    $macroAlreadyExist['macroInput_#index#'] === $macroName
                    && $_REQUEST['macroFrom'][$index] === $macroAlreadyExist['source']
                ) {
                    /**
                     * if the password has not been changed, we replace the password coming from the interface with
                     * the original value (from the repository) before saving.
                     */
                    $_REQUEST['macroValue'][$index] = $macroAlreadyExist['macroValue_#index#'];
                }
            }
        }
    }

    // We taking into account the POST data sent from the interface
    $aMacros = $hostObj->getMacros($host_id, $aTemplates, $cmdId, $_POST);

    // We hide all passwords in the jsData property to prevent them from appearing in the HTML code.
    foreach ($aMacros as $index => $macroValues) {
        if ($macroValues['macroPassword_#index#'] === 1) {
            $macroPasswords[$index]['password'] = $aMacros[$index]['macroValue_#index#'];
            // It's a password macro
            $aMacros[$index]['macroOldValue_#index#'] = PASSWORD_REPLACEMENT_VALUE;
            $aMacros[$index]['macroValue_#index#'] = PASSWORD_REPLACEMENT_VALUE;
            // Keep the original name of the input field in case its name changes.
            $aMacros[$index]['macroOriginalName_#index#'] = $aMacros[$index]['macroInput_#index#'];
        }
    }
}

// Preset values of macros
$cdata = CentreonData::getInstance();

$cdata->addJsData('clone-values-macro', htmlspecialchars(
    json_encode($aMacros),
    ENT_QUOTES
));

$cdata->addJsData('clone-count-macro', count($aMacros));

// Preset values of host templates
$tplArray = $hostObj->getTemplates(isset($host_id) ? $host_id : null);
$cdata->addJsData('clone-values-template', htmlspecialchars(
    json_encode($tplArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-template', count($tplArray));

// Nagios Server comes from DB -> Store in $nsServer Array
$nsServers = array();
if ($o === HOST_MASSIVE_CHANGE) {
    $nsServers[null] = null;
}
$DBRESULT = $pearDB->query(
    "SELECT id, name FROM nagios_server " .
    ($aclPollerString != "''" ? $acl->queryBuilder('WHERE', 'id', $aclPollerString) : "") .
    " ORDER BY name"
);
while ($nsServer = $DBRESULT->fetch()) {
    $nsServers[$nsServer["id"]] = $nsServer["name"];
}
$DBRESULT->closeCursor();

// IMG comes from DB -> Store in $extImg Array
$extImg = array();
$extImg = return_image_list(1);
$extImgStatusmap = array();
$extImgStatusmap = return_image_list(2);

// Host multiple templates relations stored in DB
$mTp = $hostObj->getSavedTpl($host_id);


// Var information to format the element
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "6");
$attrsAdvSelect = array("style" => "width: 270px; height: 100px;");
$attrsAdvSelectsmall = array("style" => "width: 270px; height: 50px;");
$attrsAdvSelectbig = array("style" => "width: 270px; height: 130px;");
$attrsTextarea = array("rows" => "4", "cols" => "80");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />'
    . '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$timeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timeRoute,
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);
$contactRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=list';
$attrContacts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContact'
);
$contactGrRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactGrRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);
$attrCommands = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => false,
    'linkedObject' => 'centreonCommand'
);
$hostRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$hostGrAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostGrAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);
$hostCatAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostcategory'
    . '&action=list&t=c';
$attrHostcategories = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostCatAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHostcategories'
);

#
## Form begin
#

$TemplateValues = array();

/*
 * For a shitty reason, Quickform set checkbox with stal[o] name
 */
unset($_POST['o']);
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

$form->registerRule('validate_geo_coords', 'function', 'validateGeoCoords');

if ($o === HOST_ADD) {
    $form->addElement('header', 'title', _("Add a Host"));
} elseif ($o === HOST_MODIFY) {
    $form->addElement('header', 'title', _("Modify a Host"));
} elseif ($o === HOST_WATCH) {
    $form->addElement('header', 'title', _("View a Host"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    $form->addElement('header', 'title', _("Massive Change"));
}

## Sort 1 - Host Configuration
#
## Host basic information
#
$form->addElement('header', 'information', _("General Information"));
# No possibility to change name and alias, because there's no interest
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->addElement('text', 'host_name', _("Name"), $attrsText);
    $form->addElement('text', 'host_alias', _("Alias"), $attrsText);
    $form->addElement(
        'text',
        'host_address',
        _("Address"),
        array_merge(array('id' => 'host_address'), $attrsText)
    );
    $form->addElement(
        'button',
        'host_resolve',
        _("Resolve"),
        array(
            'onClick' => 'resolveHostNameToAddress(document.getElementById(\'host_address\').value,'
                . ' function(err, ip){if (!err) document.getElementById(\'host_address\').value = ip});',
            'class' => 'btc bt_info'
        )
    );
}
$form->addElement('text', 'host_snmp_community', _("SNMP Community"), $attrsText);
$form->addElement('select', 'host_snmp_version', _("Version"), array(null => null, 1 => "1", "2c" => "2c", 3 => "3"));

/*
 * Include GMT Class
 */
$timezDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone'
    . '&action=defaultValues&target=host&field=host_location&id=' . $host_id;
$timezAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=list';
$attrTimezones = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timezAvRoute,
    'defaultDatasetRoute' => $timezDeRoute,
    'multiple' => false,
    'linkedObject' => 'centreonGMT'
);
$form->addElement('select2', 'host_location', _("Timezone"), array(), $attrTimezones);

$form->addElement('select', 'nagios_server_id', _("Monitoring server"), $nsServers);
/*
 * Get default poller id
 */
$DBRESULT = $pearDB->query("SELECT id FROM nagios_server WHERE is_default = '1'");
$defaultServer = $DBRESULT->fetch();
$DBRESULT->closeCursor();
if (isset($defaultServer) && $defaultServer && $o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('nagios_server_id' => $defaultServer["id"]));
}

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_tplp = array();
    $mc_mod_tplp[] = $form->createElement('radio', 'mc_mod_tplp', null, _("Incremental"), '0');
    $mc_mod_tplp[] = $form->createElement('radio', 'mc_mod_tplp', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_tplp, 'mc_mod_tplp', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_tplp' => '0'));
}

$form->addElement('text', 'host_parallel_template', _('Templates'));
$form->addElement(
    'static',
    'tplTextParallel',
    _("A host can have multiple templates, their orders have a significant importance")
    . "<br><a href='#' onmouseover=\"Tip('<img src=\'img/misc/multiple-templates2.png\'>', OPACITY, 70,"
    . " FIX, [this, 0, 10])\" onmouseout=\"UnTip()\">" . _("Here is a self-explanatory image.") . "</a>"
);
$form->addElement('static', 'tplText', _("Using a Template allows you to have multi-level Template connection"));

$cloneSetMacro = array();
$cloneSetMacro[] = $form->addElement(
    'text',
    'macroInput[#index#]',
    _('Name'),
    array(
        'id' => 'macroInput_#index#',
        'size' => 25
    )
);
$cloneSetMacro[] = $form->addElement(
    'text',
    'macroValue[#index#]',
    _('Value'),
    array(
        'id' => 'macroValue_#index#',
        'size' => 25
    )
);
$cloneSetMacro[] = $form->addElement(
    'checkbox',
    'macroPassword[#index#]',
    _('Password'),
    null,
    array(
        'id' => 'macroPassword_#index#',
        'onClick' => 'javascript:change_macro_input_type(this, false)'
    )
);

$cloneSetMacro[] = $form->addElement(
    'hidden',
    'macroFrom[#index#]',
    'direct',
    array('id' => 'macroFrom_#index#')
);

$cloneSetTemplate = array();
$listPpTemplate = $hostObj->getLimitedList();
$listAllTemplate = $hostObj->getList(false, true, null);
$validTemplate = array_diff_key($listAllTemplate, $listPpTemplate);
$listTemplate = array(null => null) + $mTp + $validTemplate;
$cloneSetTemplate[] = $form->addElement(
    'select',
    'tpSelect[#index#]',
    '',
    $listTemplate,
    array(
        "id" => "tpSelect_#index#",
        "class" => "select2",
        "type" => "select-one"
    )
);

$dupSvTpl[] = $form->createElement('radio', 'dupSvTplAssoc', null, _("Yes"), '1');
$dupSvTpl[] = $form->createElement('radio', 'dupSvTplAssoc', null, _("No"), '0');
$form->addGroup($dupSvTpl, 'dupSvTplAssoc', _("Checks Enabled"), '&nbsp;');
if ($o === HOST_MODIFY) {
    $form->setDefaults(array('dupSvTplAssoc' => '0'));
} elseif ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('dupSvTplAssoc' => '1'));
}
$form->addElement('static', 'dupSvTplAssocText', _("Create Services linked to the Template too"));

#
## Check information
#
$form->addElement('header', 'check', _("Host Check Properties"));

$comDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=host&field=command_command_id&id=' . $host_id;
$comAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list&t=2';
$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => $comDeRoute,
        'availableDatasetRoute' => $comAvRoute
    )
);
$checkCommandSelect = $form->addElement('select2', 'command_command_id', _("Check Command"), array(), $attrCommand1);
$checkCommandSelect->addJsCallback(
    'change',
    'setArgument(jQuery(this).closest("form").get(0),"command_command_id","example1");'
);

$form->addElement('text', 'command_command_id_arg1', _("Args"), $attrsText);

$form->addElement('text', 'host_max_check_attempts', _("Max Check Attempts"), $attrsText2);
$form->addElement('text', 'host_check_interval', _("Normal Check Interval"), $attrsText2);
$form->addElement('text', 'host_retry_check_interval', _("Retry Check Interval"), $attrsText2);

$hostEHE[] = $form->createElement('radio', 'host_event_handler_enabled', null, _("Yes"), '1');
$hostEHE[] = $form->createElement('radio', 'host_event_handler_enabled', null, _("No"), '0');
$hostEHE[] = $form->createElement('radio', 'host_event_handler_enabled', null, _("Default"), '2');
$form->addGroup($hostEHE, 'host_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_event_handler_enabled' => '2'));
}

$comDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=host&field=command_command_id2&id=' . $host_id;
$comAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list';
$attrCommand2 = array_merge(
    $attrCommands,
    array(
        'availableDatasetRoute' => $comAvRoute,
        'defaultDatasetRoute' => $comDeRoute
    )
);
$eventHandlerSelect = $form->addElement('select2', 'command_command_id2', _("Event Handler"), array(), $attrCommand2);
$eventHandlerSelect->addJsCallback(
    'change',
    'setArgument(jQuery(this).closest("form").get(0),"command_command_id2","example2");'
);
$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsText);

$hostACE[] = $form->createElement('radio', 'host_active_checks_enabled', null, _("Yes"), '1');
$hostACE[] = $form->createElement('radio', 'host_active_checks_enabled', null, _("No"), '0');
$hostACE[] = $form->createElement('radio', 'host_active_checks_enabled', null, _("Default"), '2');
$form->addGroup($hostACE, 'host_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_active_checks_enabled' => '2'));
}

$hostPCE[] = $form->createElement('radio', 'host_passive_checks_enabled', null, _("Yes"), '1');
$hostPCE[] = $form->createElement('radio', 'host_passive_checks_enabled', null, _("No"), '0');
$hostPCE[] = $form->createElement('radio', 'host_passive_checks_enabled', null, _("Default"), '2');
$form->addGroup($hostPCE, 'host_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_passive_checks_enabled' => '2'));
}

$timeDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=host&field=timeperiod_tp_id&id=' . $host_id;
$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $timeDeRoute)
);
$form->addElement('select2', 'timeperiod_tp_id', _("Check Period"), array(), $attrTimeperiod1);

/**
 * Acknowledgement timeout
 */
$form->addElement('text', 'host_acknowledgement_timeout', _("Acknowledgement timeout"), $attrsText2);

##
## Notification informations
##
$form->addElement('header', 'notification', _("Notification"));
$hostNE[] = $form->createElement('radio', 'host_notifications_enabled', null, _("Yes"), '1');
$hostNE[] = $form->createElement('radio', 'host_notifications_enabled', null, _("No"), '0');
$hostNE[] = $form->createElement('radio', 'host_notifications_enabled', null, _("Default"), '2');
$form->addGroup($hostNE, 'host_notifications_enabled', _("Notification Enabled"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_notifications_enabled' => '2'));
}

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_notifopt_first_notification_delay = array();
    $mc_mod_notifopt_first_notification_delay[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_first_notification_delay',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_first_notification_delay[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_first_notification_delay',
        null,
        _("Replacement"),
        '1'
    );
    $form->addGroup(
        $mc_mod_notifopt_first_notification_delay,
        'mc_mod_notifopt_first_notification_delay',
        _("Update mode"),
        '&nbsp;'
    );
    $form->setDefaults(array('mc_mod_notifopt_first_notification_delay' => '0'));
}

$form->addElement('text', 'host_first_notification_delay', _("First notification delay"), $attrsText2);

$form->addElement('text', 'host_recovery_notification_delay', _("Recovery notification delay"), $attrsText2);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_hcg = array();
    $mc_mod_hcg[] = $form->createElement('radio', 'mc_mod_hcg', null, _("Incremental"), '0');
    $mc_mod_hcg[] = $form->createElement('radio', 'mc_mod_hcg', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hcg, 'mc_mod_hcg', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hcg' => '0'));
}

/*
 * Additive
 */
$dbResult = $pearDB->query('SELECT `value` FROM options WHERE `key` = "inheritance_mode"');
$inheritanceMode = $dbResult->fetch();
if ($o === HOST_MASSIVE_CHANGE) {
    $contactAdditive[] = $form->createElement('radio', 'mc_contact_additive_inheritance', null, _("Yes"), '1');
    $contactAdditive[] = $form->createElement('radio', 'mc_contact_additive_inheritance', null, _("No"), '0');
    $contactAdditive[] = $form->createElement(
        'radio',
        'mc_contact_additive_inheritance',
        null,
        _("Default"),
        '2'
    );
    $form->addGroup(
        $contactAdditive,
        'mc_contact_additive_inheritance',
        _("Contact additive inheritance"),
        '&nbsp;'
    );

    $contactGroupAdditive[] = $form->createElement('radio', 'mc_cg_additive_inheritance', null, _("Yes"), '1');
    $contactGroupAdditive[] = $form->createElement('radio', 'mc_cg_additive_inheritance', null, _("No"), '0');
    $contactGroupAdditive[] = $form->createElement(
        'radio',
        'mc_cg_additive_inheritance',
        null,
        _("Default"),
        '2'
    );
    $form->addGroup(
        $contactGroupAdditive,
        'mc_cg_additive_inheritance',
        _("Contact group additive inheritance"),
        '&nbsp;'
    );
} else {
    $form->addElement('checkbox', 'contact_additive_inheritance', '', _('Contact additive inheritance'));
    $form->addElement('checkbox', 'cg_additive_inheritance', '', _('Contact group additive inheritance'));
}

/*
 *  Contacts
 */
$contactDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contact'
    . '&action=defaultValues&target=host&field=host_cs&id=' . $host_id;
$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => $contactDeRoute)
);
$form->addElement('select2', 'host_cs', _("Linked Contacts"), array(), $attrContact1);

/*
 *  Contact groups
 */
$contactGrDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=host&field=host_cgs&id=' . $host_id;
$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => $contactGrDeRoute)
);
$form->addElement('select2', 'host_cgs', _("Linked Contact Groups"), array(), $attrContactgroup1);


if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_notifopt_notification_interval = array();
    $mc_mod_notifopt_notification_interval[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_notification_interval',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_notification_interval[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_notification_interval',
        null,
        _("Replacement"),
        '1'
    );
    $form->addGroup(
        $mc_mod_notifopt_notification_interval,
        'mc_mod_notifopt_notification_interval',
        _("Update mode"),
        '&nbsp;'
    );
    $form->setDefaults(array('mc_mod_notifopt_notification_interval' => '0'));
}

$form->addElement('text', 'host_notification_interval', _("Notification Interval"), $attrsText2);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_notifopt_timeperiod = array();
    $mc_mod_notifopt_timeperiod[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_timeperiod',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_timeperiod[] = $form->createElement(
        'radio',
        'mc_mod_notifopt_timeperiod',
        null,
        _("Replacement"),
        '1'
    );
    $form->addGroup($mc_mod_notifopt_timeperiod, 'mc_mod_notifopt_timeperiod', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopt_timeperiod' => '0'));
}
$timeDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=host&field=timeperiod_tp_id2&id=' . $host_id;
$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $timeDeRoute)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Notification Period"), array(), $attrTimeperiod2);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_notifopts = array();
    $mc_mod_notifopts[] = $form->createElement('radio', 'mc_mod_notifopts', null, _("Incremental"), '0');
    $mc_mod_notifopts[] = $form->createElement('radio', 'mc_mod_notifopts', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopts, 'mc_mod_notifopts', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopts' => '0'));
}

$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'd',
    '&nbsp;',
    _("Down"),
    array('id' => 'notifD', 'onClick' => 'uncheckNotifOption(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'u',
    '&nbsp;',
    _("Unreachable"),
    array('id' => 'notifU', 'onClick' => 'uncheckNotifOption(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'r',
    '&nbsp;',
    _("Recovery"),
    array('id' => 'notifR', 'onClick' => 'uncheckNotifOption(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'f',
    '&nbsp;',
    _("Flapping"),
    array('id' => 'notifF', 'onClick' => 'uncheckNotifOption(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    's',
    '&nbsp;',
    _("Downtime Scheduled"),
    array('id' => 'notifDS', 'onClick' => 'uncheckNotifOption(this);')
);
$hostNotifOpt[] = $form->createElement(
    'checkbox',
    'n',
    '&nbsp;',
    _("None"),
    array('id' => 'notifN', 'onClick' => 'uncheckNotifOption(this);')
);
$form->addGroup($hostNotifOpt, 'host_notifOpts', _("Notification Options"), '&nbsp;&nbsp;');

$hostStalOpt[] = $form->createElement('checkbox', 'o', '&nbsp;', _("Up"));
$hostStalOpt[] = $form->createElement('checkbox', 'd', '&nbsp;', _("Down"));
$hostStalOpt[] = $form->createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
$form->addGroup($hostStalOpt, 'host_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

#
## Further informations
#
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$hostActivation[] = $form->createElement('radio', 'host_activate', null, _("Enabled"), '1');
$hostActivation[] = $form->createElement('radio', 'host_activate', null, _("Disabled"), '0');
$form->addGroup($hostActivation, 'host_activate', _("Enable/disable resource"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_activate' => '1'));
}
$form->addElement('textarea', 'host_comment', _("Comments"), $attrsTextarea);

#
## Sort 2 - Host Relations
#
if ($o === HOST_ADD) {
    $form->addElement('header', 'title2', _("Add relations"));
} elseif ($o === HOST_MODIFY) {
    $form->addElement('header', 'title2', _("Modify relations"));
} elseif ($o === HOST_WATCH) {
    $form->addElement('header', 'title2', _("View relations"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    $form->addElement('header', 'title2', _("Massive Change"));
}

$form->addElement('header', 'links', _("Relations"));
$form->addElement('header', 'HGlinks', _("Hostgroup Relations"));
$form->addElement('header', 'HClinks', _("Host Categories Relations"));

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_hpar = array();
    $mc_mod_hpar[] = $form->createElement('radio', 'mc_mod_hpar', null, _("Incremental"), '0');
    $mc_mod_hpar[] = $form->createElement('radio', 'mc_mod_hpar', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hpar, 'mc_mod_hpar', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hpar' => '0'));
}

$hostDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host'
    . '&action=defaultValues&target=host&field=host_parents&id=' . $host_id;
/* Host Parents */
$attrHost1 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => $hostDeRoute)
);
$form->addElement('select2', 'host_parents', _("Parent Hosts"), array(), $attrHost1);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_hch = array();
    $mc_mod_hch[] = $form->createElement('radio', 'mc_mod_hch', null, _("Incremental"), '0');
    $mc_mod_hch[] = $form->createElement('radio', 'mc_mod_hch', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hch, 'mc_mod_hch', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hch' => '0'));
}

$hostDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host'
    . '&action=defaultValues&target=host&field=host_childs&id=' . $host_id;
$attrHost2 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => $hostDeRoute)
);
$form->addElement('select2', 'host_childs', _("Child Hosts"), array(), $attrHost2);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_hhg = array();
    $mc_mod_hhg[] = $form->createElement('radio', 'mc_mod_hhg', null, _("Incremental"), '0');
    $mc_mod_hhg[] = $form->createElement('radio', 'mc_mod_hhg', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hhg, 'mc_mod_hhg', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hhg' => '0'));
}

$hostgDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup'
    . '&action=defaultValues&target=host&field=host_hgs&id=' . $host_id;
$attrHostgroup1 = array_merge(
    $attrHostgroups,
    array('defaultDatasetRoute' => $hostgDeRoute)
);
$form->addElement('select2', 'host_hgs', _("Host Groups"), array(), $attrHostgroup1);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_hhc = array();
    $mc_mod_hhc[] = $form->createElement('radio', 'mc_mod_hhc', null, _("Incremental"), '0');
    $mc_mod_hhc[] = $form->createElement('radio', 'mc_mod_hhc', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hhc, 'mc_mod_hhc', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hhc' => '0'));
}

$hostCatDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostcategory'
    . '&action=defaultValues&target=host&field=host_hcs&id=' . $host_id;
$attrHostcategory1 = array_merge(
    $attrHostcategories,
    array('defaultDatasetRoute' => $hostCatDeRoute)
);
$form->addElement('select2', 'host_hcs', _("Host Categories"), array(), $attrHostcategory1);

if ($o === HOST_MASSIVE_CHANGE) {
    $mc_mod_nsid = array();
    $mc_mod_nsid[] = $form->createElement('radio', 'mc_mod_nsid', null, _("Incremental"), '0');
    $mc_mod_nsid[] = $form->createElement('radio', 'mc_mod_nsid', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_nsid, 'mc_mod_nsid', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_nsid' => '0'));
}

#
## Sort 3 - Data treatment
#
if ($o === HOST_ADD) {
    $form->addElement('header', 'title3', _("Add Data Processing"));
} elseif ($o === HOST_MODIFY) {
    $form->addElement('header', 'title3', _("Modify Data Processing"));
} elseif ($o === HOST_WATCH) {
    $form->addElement('header', 'title3', _("View Data Processing"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    $form->addElement('header', 'title3', _("Massive Change"));
}

$form->addElement('header', 'treatment', _("Data Processing"));

$hostOOH[] = $form->createElement('radio', 'host_obsess_over_host', null, _("Yes"), '1');
$hostOOH[] = $form->createElement('radio', 'host_obsess_over_host', null, _("No"), '0');
$hostOOH[] = $form->createElement('radio', 'host_obsess_over_host', null, _("Default"), '2');
$form->addGroup($hostOOH, 'host_obsess_over_host', _("Obsess Over Host"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_obsess_over_host' => '2'));
}

$hostCF[] = $form->createElement('radio', 'host_check_freshness', null, _("Yes"), '1');
$hostCF[] = $form->createElement('radio', 'host_check_freshness', null, _("No"), '0');
$hostCF[] = $form->createElement('radio', 'host_check_freshness', null, _("Default"), '2');
$form->addGroup($hostCF, 'host_check_freshness', _("Check Freshness"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_check_freshness' => '2'));
}

$hostFDE[] = $form->createElement('radio', 'host_flap_detection_enabled', null, _("Yes"), '1');
$hostFDE[] = $form->createElement('radio', 'host_flap_detection_enabled', null, _("No"), '0');
$hostFDE[] = $form->createElement('radio', 'host_flap_detection_enabled', null, _("Default"), '2');
$form->addGroup($hostFDE, 'host_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_flap_detection_enabled' => '2'));
}

$form->addElement('text', 'host_freshness_threshold', _("Freshness Threshold"), $attrsText2);
$form->addElement('text', 'host_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
$form->addElement('text', 'host_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

$hostRSI[] = $form->createElement('radio', 'host_retain_status_information', null, _("Yes"), '1');
$hostRSI[] = $form->createElement('radio', 'host_retain_status_information', null, _("No"), '0');
$hostRSI[] = $form->createElement('radio', 'host_retain_status_information', null, _("Default"), '2');
$form->addGroup($hostRSI, 'host_retain_status_information', _("Retain Status Information"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_retain_status_information' => '2'));
}

$hostRNI[] = $form->createElement('radio', 'host_retain_nonstatus_information', null, _("Yes"), '1');
$hostRNI[] = $form->createElement('radio', 'host_retain_nonstatus_information', null, _("No"), '0');
$hostRNI[] = $form->createElement('radio', 'host_retain_nonstatus_information', null, _("Default"), '2');
$form->addGroup($hostRNI, 'host_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->setDefaults(array('host_retain_nonstatus_information' => '2'));
}

/*
 * Sort 4 - Extended Infos
 */
if ($o === HOST_ADD) {
    $form->addElement('header', 'title4', _("Add a Host Extended Info"));
} elseif ($o === HOST_MODIFY) {
    $form->addElement('header', 'title4', _("Modify a Host Extended Info"));
} elseif ($o === HOST_WATCH) {
    $form->addElement('header', 'title4', _("View a Host Extended Info"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    $form->addElement('header', 'title4', _("Massive Change"));
}

$form->addElement('header', 'nagios', _("Monitoring engine"));
$form->addElement('text', 'ehi_notes', _("Note"), $attrsText);
$form->addElement('text', 'ehi_notes_url', _("Note URL"), $attrsText);
$form->addElement('text', 'ehi_action_url', _("Action URL"), $attrsText);
$form->addElement('select', 'ehi_icon_image', _("Icon"), $extImg, array(
    "id" => "ehi_icon_image",
    "onChange" => "showLogo('ehi_icon_image_img',this.value)",
    "onkeyup" => "this.blur();this.focus();"
));
$form->addElement('text', 'ehi_icon_image_alt', _("Alt icon"), $attrsText);
$form->addElement('select', 'ehi_statusmap_image', _("Status Map Image"), $extImgStatusmap, array(
    "id" => "ehi_statusmap_image",
    "onChange" => "showLogo('ehi_statusmap_image_img',this.value)",
    "onkeyup" => "this.blur();this.focus();"
));
$form->addElement('text', 'ehi_2d_coords', _("2d Coords"), $attrsText2);
$form->addElement('text', 'ehi_3d_coords', _("3d Coords"), $attrsText2);
$form->addElement('text', 'geo_coords', _("Geographic coordinates"), $attrsText);
$form->addRule('geo_coords', _("geo coords are not valid"), 'validate_geo_coords');

if (!$centreon->user->admin && $o === HOST_ADD) {
    $aclDeRoute = './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup'
        . '&action=defaultValues&target=host&field=acl_groups&id=' . $host_id;
    $aclAvRoute = './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=list';
    $attrAclgroups = array(
        'datasourceOrigin' => 'ajax',
        'availableDatasetRoute' => $aclAvRoute,
        'defaultDatasetRoute' => $aclDeRoute,
        'multiple' => true
    );
    $form->addElement('select2', 'acl_groups', _("ACL Resource Groups"), array(), $attrAclgroups);
    $form->addRule('acl_groups', _("Mandatory field for ACL purpose."), 'required');
}

/*
 * Criticality
 */
$criticality = new CentreonCriticality($pearDB);
$critList = $criticality->getList();
$criticalityIds = array(null => null);
foreach ($critList as $critId => $critData) {
    $criticalityIds[$critId] = $critData['hc_name'] . ' (' . $critData['level'] . ')';
}
$form->addElement('select', 'criticality_id', _('Host severity'), $criticalityIds);

/*
 * Sort 5 - Macros - Nagios 3
 */
if ($o === HOST_ADD) {
    $form->addElement('header', 'title5', _("Add macros"));
} elseif ($o === HOST_MODIFY) {
    $form->addElement('header', 'title5', _("Modify macros"));
} elseif ($o === HOST_WATCH) {
    $form->addElement('header', 'title5', _("View macros"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    $form->addElement('header', 'title5', _("Massive Change"));
}

$form->addElement('header', 'macro', _("Macros"));

$form->addElement('text', 'add_new', _("Add a new macro"), $attrsText2);
$form->addElement('text', 'macroName', _("Macro name"), $attrsText2);
$form->addElement('text', 'macroValue', _("Macro value"), $attrsText2);
$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);

$form->addElement('hidden', 'host_id');
$reg = $form->addElement('hidden', 'host_register');
$reg->setValue("1");
$host_register = 1;
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));

if (is_array($select)) {
    $select_pear = $form->addElement('hidden', 'select');
    $select_pear->setValue(implode(',', array_keys($select)));
}

/*
 * Form Rules
 */
function myReplace()
{
    global $form;
    return (str_replace(" ", "_", $form->getSubmitValue("host_name")));
}

$form->applyFilter('__ALL__', 'myTrim');

$form->applyFilter('ehi_notes', 'limitNotesLength');
$form->applyFilter('ehi_notes_url', 'limitUrlLength');
$form->applyFilter('ehi_action_url', 'limitUrlLength');

$from_list_menu = false;
if ($o !== HOST_MASSIVE_CHANGE) {
    $form->applyFilter('host_name', 'myReplace');
    $form->addRule('host_name', _("Compulsory Name"), 'required');

    if (
        isset($centreon->optGen["strict_hostParent_poller_management"])
        && $centreon->optGen["strict_hostParent_poller_management"] == 1
    ) {
        $form->registerRule('testPollerDep', 'callback', 'testPollerDep');
        $form->addRule(
            'nagios_server_id',
            _("Impossible to change server due to parentship with other hosts"),
            'testPollerDep'
        );
    }
    /*
     * Test existence
     */
    $form->registerRule('testModule', 'callback', 'testHostName');
    $form->addRule('host_name', _("_Module_ is not a legal expression"), 'testModule');
    $form->registerRule('existTemplate', 'callback', 'hasHostTemplateNeverUsed');
    $form->registerRule('exist', 'callback', 'hasHostNameNeverUsed');
    $form->registerRule('sanitize', 'callback', 'isNotEmptyAfterStringSanitize');
    $form->addRule('host_name', _("Template name is already in use"), 'existTemplate');
    $form->addRule('host_name', _("Host name is already in use"), 'exist');
    $form->addRule('host_name', _("Unauthorized value"), 'sanitize');
    $form->addRule('host_address', _("Compulsory Address"), 'required');
    $form->addRule('host_address', _("Unauthorized value"), 'sanitize');
    $form->registerRule('cg_group_exists', 'callback', 'testCg');
    $form->addRule(
        'host_cgs',
        _('Contactgroups exists. If you try to use a LDAP contactgroup,'
            . ' please verified if a Centreon contactgroup has the same name.'),
        'cg_group_exists'
    );
} elseif ($o === HOST_MASSIVE_CHANGE) {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}

$form->setRequiredNote("<i style='color: red;'>*</i>&nbsp;" . _("Required fields"));

$macChecker = $form->addElement("hidden", "macChecker");
$macChecker->setValue(1);
$form->registerRule("macHandler", "callback", "hostMacHandler");
$form->addRule("macChecker", _("You cannot override reserved macros"), "macHandler");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign(
    'alert_check_interval',
    _("Warning, unconventional use of interval check. You should prefer to use an interval lower than 24h,"
        . " if needed, pair this configuration with the use of timeperiods")
);

$tpl->assign(
    'alert_max_length_exceeded',
    _("Warning, maximum size exceeded for input '%s' (max: %d), it will be truncated upon saving")
);

if ($o === HOST_WATCH) {
    /*
     * Just watch a host information
     */
    if (!$min && $centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array(
            "onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&host_id=" . $host_id . "'",
            "class" => "btc bt_default"
        ));
    }
    $form->setDefaults($host);
    $form->freeze();
} elseif ($o === HOST_MODIFY) {
    /*
     * Modify a host information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement(
        'button',
        'reset',
        _("Reset"),
        array("onClick" => "history.go(0);", "class" => "btc bt_default")
    );
    $form->setDefaults($host);
} elseif ($o === HOST_ADD) {
    /*
     * Add a host information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} elseif ($o === HOST_MASSIVE_CHANGE) {
    /*
     * Massive Change
     */
    $subMC = $form->addElement('submit', 'submitMC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('msg', array("nagios" => $centreon->user->get_version(), "tpl" => 0));
$tpl->assign('min', $min);
$tpl->assign("sort1", _("Host Configuration"));
$tpl->assign("sort2", _("Notification"));
$tpl->assign("sort3", _("Relations"));
$tpl->assign("sort4", _("Data Processing"));
$tpl->assign("sort5", _("Host Extended Infos"));
$tpl->assign('javascript', '
            <script type="text/javascript" src="./include/common/javascript/showLogo.js"></script>
            <script type="text/javascript" src="./include/common/javascript/centreon/macroPasswordField.js"></script>
            <script type="text/javascript" src="./include/common/javascript/centreon/macroLoadDescription.js"></script>
        ');
$tpl->assign('accessgroups', _('Access groups'));

/*
 * prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

if ($o !== HOST_ADD && $o !== HOST_MODIFY) {
    $tpl->assign('time_unit', " * " . $centreon->optGen["interval_length"] . " " . _("seconds"));
} else {
    /*
     * Get interval for the good poller.
     */
    $tpl->assign('time_unit', " * " . $centreon->optGen["interval_length"] . " " . _("seconds"));
}

$valid = false;
if ($form->validate() && $from_list_menu == false) {
    $hostObj = $form->getElement('host_id');
    if ($form->getSubmitValue("submitA")) {
        $hostObj->setValue(insertHostInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        /*
         * Before saving, we check if a password macro has changed its name to be able to give it the right password
         * instead of wildcards (PASSWORD_REPLACEMENT_VALUE).
         */
        if (isset($_REQUEST['macroInput'])) {
            foreach ($_REQUEST['macroInput'] as $index => $macroName) {
                if (array_key_exists('macroOriginalName_' . $index, $_REQUEST)) {
                    $originalMacroName = $_REQUEST['macroOriginalName_' . $index];
                    if ($_REQUEST['macroValue'][$index] === PASSWORD_REPLACEMENT_VALUE) {
                        /*
                         * The password has not been changed along with the name, so its value is equal to the wildcard.
                         * We will therefore recover the password stored for its original name.
                         */
                        foreach ($aMacros as $indexMacro => $macroDetails) {
                            if ($macroDetails['macroInput_#index#'] === $originalMacroName) {
                                $_REQUEST['macroValue'][$index] = $macroPasswords[$indexMacro]['password'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        updateHostInDB($hostObj->getValue());
    } elseif ($form->getSubmitValue("submitMC")) {
        foreach (array_keys($select) as $hostIdToUpdate) {
            updateHostInDB($hostIdToUpdate, true);
        }
    }
    $o = HOST_WATCH;
    $valid = true;
} elseif ($form->isSubmitted()) {
    $tpl->assign("macChecker", "<i style='color:red;'>" . $form->getElementError("macChecker") . "</i>");
}

if ($valid) {
    require_once($path . "listHost.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<i  style="color:red;" size="1">*</i>');
    $renderer->setErrorTemplate('<i style="color:red;">{$error}</i><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('is_not_template', $host_register);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('seconds', _("seconds"));
    $tpl->assign('p', $p);
    $tpl->assign('inheritance', $inheritanceMode['value']);
    $tpl->assign("Freshness_Control_options", _("Freshness Control options"));
    $tpl->assign("Flapping_Options", _("Flapping options"));
    $tpl->assign("History_Options", _("History Options"));
    $tpl->assign("Event_Handler", _("Event Handler"));
    $tpl->assign("topdoc", _("Documentation"));
    $tpl->assign("hostID", $host_id);
    $tpl->assign("add_mtp_label", _("Add a template"));
    $tpl->assign('custom_macro_label', _('Custom macros'));
    $tpl->assign('template_inheritance', _('Template inheritance'));
    $tpl->assign('command_inheritance', _('Command inheritance'));
    $tpl->assign('select_template', _('Select a template'));
    $tpl->assign('cloneSetMacro', $cloneSetMacro);
    $tpl->assign('cloneSetTemplate', $cloneSetTemplate);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign("tpl", 0);
    $tpl->display("formHost.ihtml");
    ?>
    <script type="text/javascript">
        showLogo('ehi_icon_image_img', document.getElementById('ehi_icon_image').value);
        showLogo('ehi_statusmap_image_img', document.getElementById('ehi_statusmap_image').value);

        function uncheckNotifOption(object) {
            if (object.id == "notifN" && object.checked) {
                document.getElementById('notifD').checked = false;
                document.getElementById('notifU').checked = false;
                document.getElementById('notifR').checked = false;
                document.getElementById('notifF').checked = false;
                document.getElementById('notifDS').checked = false;
            } else {
                document.getElementById('notifN').checked = false;
            }
        }
    </script>
<?php } ?>
