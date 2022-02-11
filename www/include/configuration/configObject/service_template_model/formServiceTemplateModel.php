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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

function myDecodeSvTP($arg)
{
    $arg = str_replace('#BR#', "\\n", $arg);
    $arg = str_replace('#T#', "\\t", $arg);
    $arg = str_replace('#R#', "\\r", $arg);
    $arg = str_replace('#S#', "/", $arg);
    $arg = str_replace('#BS#', "\\", $arg);
    return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
}

const PASSWORD_REPLACEMENT_VALUE = '**********';

$cmdId = 0;
$serviceTplId = null;
$service = array();
$serviceObj = new CentreonService($pearDB);

// Used to store all macro passwords
$macroPasswords = [];

if (($o == SERVICE_TEMPLATE_MODIFY || $o == SERVICE_TEMPLATE_WATCH) && isset($service_id)) {
    if (isset($lockedElements[$service_id])) {
        $o = SERVICE_TEMPLATE_WATCH;
    }
    $statement = $pearDB->prepare(
        'SELECT * 
        FROM service srv
        LEFT JOIN extended_service_information esi 
            ON esi.service_service_id = srv.service_id
        WHERE srv.service_id = :service_id  LIMIT 1'
    );
    $statement->bindValue(':service_id', $service_id, \PDO::PARAM_INT);
    $statement->execute();
    // Set base value
    $service_list = $statement->fetch();
    $service = array_map("myDecodeSvTP", $service_list);
    $serviceTplId = $service['service_template_model_stm_id'];
    $cmdId = $service['command_command_id'];

    // Set Service Notification Options
    $tmp = explode(',', $service["service_notification_options"]);
    foreach ($tmp as $key => $value) {
        $service["service_notifOpts"][trim($value)] = 1;
    }

    /*
     * Set Stalking Options
     */
    $tmp = explode(',', $service["service_stalking_options"]);
    foreach ($tmp as $key => $value) {
        $service["service_stalOpts"][trim($value)] = 1;
    }

    /*
     * Set criticality
     */
    $statement = $pearDB->prepare(
        "SELECT sc.sc_id 
        FROM service_categories sc
        INNER JOIN service_categories_relation scr
            ON scr.sc_id = sc.sc_id
        WHERE scr.service_service_id = :service_id AND sc.level IS NOT NULL
        ORDER BY sc.level ASC LIMIT 1"
    );
    $statement->bindValue(':service_id', $service_id, \PDO::PARAM_INT);
    $statement->execute();
    if ($statement->rowCount()) {
        $cr = $statement->fetch();
        $service['criticality_id'] = $cr['sc_id'];
    }


    $aListTemplate = getListTemplates($pearDB, $service_id);

    if (!isset($cmdId)) {
        $cmdId = "";
    }

    if (isset($_REQUEST['macroInput'])) {
        /**
         * We don't taking into account the POST data sent from the interface in order the retrieve the original value
         * of all passwords.
         */
        $aMacros = $serviceObj->getMacros($service_id, $aListTemplate, $cmdId);

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
    $aMacros = $serviceObj->getMacros($service_id, $aListTemplate, $cmdId);

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

/*
 * Preset values of macros
 */
$cdata = CentreonData::getInstance();
$aMacros = isset($aMacros) ? $aMacros : [];

$cdata->addJsData('clone-values-macro', htmlspecialchars(
    json_encode($aMacros),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-macro', count($aMacros));

# IMG comes from DB -> Store in $extImg Array
$extImg = array();
$extImg = return_image_list(1);
#
# End of "database-retrieved" information
##########################################################
##########################################################
# Var information to format the element
#
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "6");
$attrsTextLong = array("size" => "60");
$attrsAdvSelect_small = array("style" => "width: 300px; height: 70px;");
$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
$attrsAdvSelect_big = array("style" => "width: 300px; height: 200px;");
$attrsTextarea = array("rows" => "5", "cols" => "40");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />'
    . '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

$timeAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timeAvRoute,
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);

$contactAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=list';
$attrContacts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContact'
);

$contactGrAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactGrAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);
$attrCommands = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => false,
    'linkedObject' => 'centreonCommand'
);

$hostAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);

$servTplAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate'
    . '&action=list';
$attrServicetemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $servTplAvRoute,
    'multiple' => false,
    'linkedObject' => 'centreonServicetemplates'
);

$servCatAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicecategory'
    . '&action=list';
$attrServicecategories = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $servCatAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonServicecategories'
);

$trapAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_trap&action=list';
$attrTraps = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $trapAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonTraps'
);

$graphTplAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_graphtemplate'
    . '&action=list';
$attrGraphtemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $graphTplAvRoute,
    'multiple' => false,
    'linkedObject' => 'centreonGraphTemplate'
);

$hostTplRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hosttemplate&action=list';
$attrHosttemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostTplRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHosttemplates'
);

/*
 * For a shitty reason, Quickform set checkbox with stal[o] name
 */
unset($_POST['o']);
#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == SERVICE_TEMPLATE_ADD) {
    $form->addElement('header', 'title', _("Add a Service Template Model"));
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {
    $form->addElement('header', 'title', _("Modify a Service Template Model"));
} elseif ($o == SERVICE_TEMPLATE_WATCH) {
    $form->addElement('header', 'title', _("View a Service Template Model"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('header', 'title', _("Massive Change"));
}

#
## Service basic information
#
$form->addElement('header', 'information', _("General Information"));

if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('text', 'service_description', _("Name"), $attrsText);
}
$form->addElement('text', 'service_alias', _("Alias"), $attrsText);

$servDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate'
    . '&action=defaultValues&target=service&field=service_template_model_stm_id&id=' . $service_id;
$attrServicetemplate1 = array_merge(
    $attrServicetemplates,
    array('defaultDatasetRoute' => $servDeRoute)
);

$serviceTplSelect = $form->addElement(
    'select2',
    'service_template_model_stm_id',
    _("Template"),
    array(),
    $attrServicetemplate1
);
$serviceTplSelect->addJsCallback('change', 'changeServiceTemplate(this.value)');

$form->addElement('static', 'tplText', _("Using a Template Model allows you to have multi-level Template connections"));

$hostTplRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hosttemplate'
    . '&action=defaultValues&target=servicetemplates&field=service_hPars&id=' . $service_id;
$attrHosttemplate1 = array_merge(
    $attrHosttemplates,
    array('defaultDatasetRoute' => $hostTplRoute)
);
$form->addElement('select2', 'service_hPars', _("Linked to host templates"), array(), $attrHosttemplate1);

#
## Check information
#
$form->addElement('header', 'check', _("Service State"));

$serviceIV[] = $form->createElement('radio', 'service_is_volatile', null, _("Yes"), '1');
$serviceIV[] = $form->createElement('radio', 'service_is_volatile', null, _("No"), '0');
$serviceIV[] = $form->createElement('radio', 'service_is_volatile', null, _("Default"), '2');
$form->addGroup($serviceIV, 'service_is_volatile', _("Is volatile"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_is_volatile' => '2'));
}

$comDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=service&field=command_command_id&id=' . $service_id;
$comAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list&t=2';
$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => $comDeRoute,
        'availableDatasetRoute' => $comAvRoute
    )
);
$checkCommandSelect = $form->addElement('select2', 'command_command_id', _("Check Command"), array(), $attrCommand1);
if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $checkCommandSelect->addJsCallback(
        'change',
        'setArgument(jQuery(this).closest("form").get(0),"command_command_id","example1");'
    );
} else {
    $checkCommandSelect->addJsCallback('change', 'changeCommand(this.value);');
}

$form->addElement('text', 'command_command_id_arg', _("Args"), $attrsTextLong);
$form->addElement('text', 'service_max_check_attempts', _("Max Check Attempts"), $attrsText2);
$form->addElement('text', 'service_normal_check_interval', _("Normal Check Interval"), $attrsText2);
$form->addElement('text', 'service_retry_check_interval', _("Retry Check Interval"), $attrsText2);

$serviceEHE[] = $form->createElement('radio', 'service_event_handler_enabled', null, _("Yes"), '1');
$serviceEHE[] = $form->createElement('radio', 'service_event_handler_enabled', null, _("No"), '0');
$serviceEHE[] = $form->createElement('radio', 'service_event_handler_enabled', null, _("Default"), '2');
$form->addGroup($serviceEHE, 'service_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_event_handler_enabled' => '2'));
}

$comAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list';
$comDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=service&field=command_command_id2&id=' . $service_id;
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
$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsTextLong);

$serviceACE[] = $form->createElement('radio', 'service_active_checks_enabled', null, _("Yes"), '1');
$serviceACE[] = $form->createElement('radio', 'service_active_checks_enabled', null, _("No"), '0');
$serviceACE[] = $form->createElement('radio', 'service_active_checks_enabled', null, _("Default"), '2');
$form->addGroup($serviceACE, 'service_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_active_checks_enabled' => '2'));
}

$servicePCE[] = $form->createElement('radio', 'service_passive_checks_enabled', null, _("Yes"), '1');
$servicePCE[] = $form->createElement('radio', 'service_passive_checks_enabled', null, _("No"), '0');
$servicePCE[] = $form->createElement('radio', 'service_passive_checks_enabled', null, _("Default"), '2');
$form->addGroup($servicePCE, 'service_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_passive_checks_enabled' => '2'));
}

$timeDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=service&field=timeperiod_tp_id&id=' . $service_id;
$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $timeDeRoute)
);
$form->addElement('select2', 'timeperiod_tp_id', _("Check Period"), array(), $attrTimeperiod1);

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

/**
 * Acknowledgement timeout
 */
$form->addElement('text', 'service_acknowledgement_timeout', _("Acknowledgement timeout"), $attrsText2);

##
## Notification informations
##
$form->addElement('header', 'notification', _("Notification"));
$serviceNE[] = $form->createElement('radio', 'service_notifications_enabled', null, _("Yes"), '1');
$serviceNE[] = $form->createElement('radio', 'service_notifications_enabled', null, _("No"), '0');
$serviceNE[] = $form->createElement('radio', 'service_notifications_enabled', null, _("Default"), '2');
$form->addGroup($serviceNE, 'service_notifications_enabled', _("Notification Enabled"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_notifications_enabled' => '2'));
}

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $mc_mod_cgs = array();
    $mc_mod_cgs[] = $form->createElement('radio', 'mc_mod_cgs', null, _("Incremental"), '0');
    $mc_mod_cgs[] = $form->createElement('radio', 'mc_mod_cgs', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cgs, 'mc_mod_cgs', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_cgs' => '0'));
}

/*
 * Additive
 */
if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $contactAdditive[] = $form->createElement('radio', 'mc_contact_additive_inheritance', null, _("Yes"), '1');
    $contactAdditive[] = $form->createElement('radio', 'mc_contact_additive_inheritance', null, _("No"), '0');
    $contactAdditive[] = $form->createElement(
        'radio',
        'mc_contact_additive_inheritance',
        null,
        _("Default"),
        '2'
    );
    $form->addGroup($contactAdditive, 'mc_contact_additive_inheritance', _("Contact additive inheritance"), '&nbsp;');

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
    . '&action=defaultValues&target=service&field=service_cs&id=' . $service_id;
$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => $contactDeRoute)
);
$form->addElement('select2', 'service_cs', _("Implied Contacts"), array(), $attrContact1);

/*
 *  Contact groups
 */
$contactGrDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=service&field=service_cgs&id=' . $service_id;
$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => $contactGrDeRoute)
);
$form->addElement('select2', 'service_cgs', _("Implied Contact Groups"), array(), $attrContactgroup1);


if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
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

$form->addElement('text', 'service_first_notification_delay', _("First notification delay"), $attrsText2);

$form->addElement('text', 'service_recovery_notification_delay', _("Recovery notification delay"), $attrsText2);

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
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

$form->addElement('text', 'service_notification_interval', _("Notification Interval"), $attrsText2);

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
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
    . '&action=defaultValues&target=service&field=timeperiod_tp_id2&id=' . $service_id;
$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $timeDeRoute)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Notification Period"), array(), $attrTimeperiod2);

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $mc_mod_notifopts = array();
    $mc_mod_notifopts[] = $form->createElement('radio', 'mc_mod_notifopts', null, _("Incremental"), '0');
    $mc_mod_notifopts[] = $form->createElement('radio', 'mc_mod_notifopts', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopts, 'mc_mod_notifopts', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopts' => '0'));
}

$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'w',
    '&nbsp;',
    _("Warning"),
    array('id' => 'notifW', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'u',
    '&nbsp;',
    _("Unknown"),
    array('id' => 'notifU', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'c',
    '&nbsp;',
    _("Critical"),
    array('id' => 'notifC', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'r',
    '&nbsp;',
    _("Recovery"),
    array('id' => 'notifR', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'f',
    '&nbsp;',
    _("Flapping"),
    array('id' => 'notifF', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    's',
    '&nbsp;',
    _("Downtime Scheduled"),
    array('id' => 'notifDS', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = $form->createElement(
    'checkbox',
    'n',
    '&nbsp;',
    _("None"),
    array('id' => 'notifN', 'onClick' => 'uncheckNotifOption(this);')
);
$form->addGroup($serviceNotifOpt, 'service_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

$serviceStalOpt[] = $form->createElement('checkbox', 'o', '&nbsp;', _("Ok"));
$serviceStalOpt[] = $form->createElement('checkbox', 'w', '&nbsp;', _("Warning"));
$serviceStalOpt[] = $form->createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
$serviceStalOpt[] = $form->createElement('checkbox', 'c', '&nbsp;', _("Critical"));
$form->addGroup($serviceStalOpt, 'service_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

#
## Further informations
#
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$serviceActivation[] = $form->createElement('radio', 'service_activate', null, _("Enabled"), '1');
$serviceActivation[] = $form->createElement('radio', 'service_activate', null, _("Disabled"), '0');
$form->addGroup($serviceActivation, 'service_activate', _("Status"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_activate' => '1'));
}
$form->addElement('textarea', 'service_comment', _("Comments"), $attrsTextarea);

#
## Sort 2 - Service relations
#
if ($o == SERVICE_TEMPLATE_ADD) {
    $form->addElement('header', 'title2', _("Add relations"));
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {
    $form->addElement('header', 'title2', _("Modify relations"));
} elseif ($o == SERVICE_TEMPLATE_WATCH) {
    $form->addElement('header', 'title2', _("View relations"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('header', 'title2', _("Massive Change"));
}

$form->addElement('header', 'links', _("Relations"));

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $mc_mod_traps = array();
    $mc_mod_traps[] = $form->createElement('radio', 'mc_mod_traps', null, _("Incremental"), '0');
    $mc_mod_traps[] = $form->createElement('radio', 'mc_mod_traps', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_traps, 'mc_mod_traps', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_traps' => '0'));
}
$form->addElement('header', 'traps', _("SNMP Traps"));
$trapDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_trap'
    . '&action=defaultValues&target=service&field=service_traps&id=' . $service_id;
$attrTrap1 = array_merge(
    $attrTraps,
    array('defaultDatasetRoute' => $trapDeRoute)
);
$form->addElement('select2', 'service_traps', _("Service Trap Relation"), array(), $attrTrap1);


if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $mc_mod_Pars = array();
    $mc_mod_Pars[] = $form->createElement('radio', 'mc_mod_Pars', null, _("Incremental"), '0');
    $mc_mod_Pars[] = $form->createElement('radio', 'mc_mod_Pars', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_Pars, 'mc_mod_Pars', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_Pars' => '0'));
}

##
## Sort 3 - Data treatment
##

if ($o == SERVICE_TEMPLATE_ADD) {
    $form->addElement('header', 'title3', _("Add Data Processing"));
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {
    $form->addElement('header', 'title3', _("Modify Data Processing"));
} elseif ($o == SERVICE_TEMPLATE_WATCH) {
    $form->addElement('header', 'title3', _("View Data Processing"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('header', 'title2', _("Massive Change"));
}

$form->addElement('header', 'treatment', _("Data Processing"));

$servicePC[] = $form->createElement('radio', 'service_parallelize_check', null, _("Yes"), '1');
$servicePC[] = $form->createElement('radio', 'service_parallelize_check', null, _("No"), '0');
$servicePC[] = $form->createElement('radio', 'service_parallelize_check', null, _("Default"), '2');
$form->addGroup($servicePC, 'service_parallelize_check', _("Parallel Check"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_parallelize_check' => '2'));
}

$serviceOOS[] = $form->createElement('radio', 'service_obsess_over_service', null, _("Yes"), '1');
$serviceOOS[] = $form->createElement('radio', 'service_obsess_over_service', null, _("No"), '0');
$serviceOOS[] = $form->createElement('radio', 'service_obsess_over_service', null, _("Default"), '2');
$form->addGroup($serviceOOS, 'service_obsess_over_service', _("Obsess Over Service"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_obsess_over_service' => '2'));
}

$serviceCF[] = $form->createElement('radio', 'service_check_freshness', null, _("Yes"), '1');
$serviceCF[] = $form->createElement('radio', 'service_check_freshness', null, _("No"), '0');
$serviceCF[] = $form->createElement('radio', 'service_check_freshness', null, _("Default"), '2');
$form->addGroup($serviceCF, 'service_check_freshness', _("Check Freshness"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_check_freshness' => '2'));
}

$serviceFDE[] = $form->createElement('radio', 'service_flap_detection_enabled', null, _("Yes"), '1');
$serviceFDE[] = $form->createElement('radio', 'service_flap_detection_enabled', null, _("No"), '0');
$serviceFDE[] = $form->createElement('radio', 'service_flap_detection_enabled', null, _("Default"), '2');
$form->addGroup($serviceFDE, 'service_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_flap_detection_enabled' => '2'));
}

$form->addElement('text', 'service_freshness_threshold', _("Freshness Threshold"), $attrsText2);
$form->addElement('text', 'service_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
$form->addElement('text', 'service_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

$servicePPD[] = $form->createElement('radio', 'service_process_perf_data', null, _("Yes"), '1');
$servicePPD[] = $form->createElement('radio', 'service_process_perf_data', null, _("No"), '0');
$servicePPD[] = $form->createElement('radio', 'service_process_perf_data', null, _("Default"), '2');
$form->addGroup($servicePPD, 'service_process_perf_data', _("Process Perf Data"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_process_perf_data' => '2'));
}

$serviceRSI[] = $form->createElement('radio', 'service_retain_status_information', null, _("Yes"), '1');
$serviceRSI[] = $form->createElement('radio', 'service_retain_status_information', null, _("No"), '0');
$serviceRSI[] = $form->createElement('radio', 'service_retain_status_information', null, _("Default"), '2');
$form->addGroup($serviceRSI, 'service_retain_status_information', _("Retain Status Information"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_retain_status_information' => '2'));
}

$serviceRNI[] = $form->createElement('radio', 'service_retain_nonstatus_information', null, _("Yes"), '1');
$serviceRNI[] = $form->createElement('radio', 'service_retain_nonstatus_information', null, _("No"), '0');
$serviceRNI[] = $form->createElement('radio', 'service_retain_nonstatus_information', null, _("Default"), '2');
$form->addGroup($serviceRNI, 'service_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->setDefaults(array('service_retain_nonstatus_information' => '2'));
}

#
## Sort 4 - Extended Infos
#
if ($o == SERVICE_TEMPLATE_ADD) {
    $form->addElement('header', 'title4', _("Add an Extended Info"));
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {
    $form->addElement('header', 'title4', _("Modify an Extended Info"));
} elseif ($o == SERVICE_TEMPLATE_WATCH) {
    $form->addElement('header', 'title4', _("View an Extended Info"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('header', 'title2', _("Massive Change"));
}

$form->addElement('header', 'nagios', _("Monitoring Engine"));
$form->addElement('text', 'esi_notes', _("Notes"), $attrsText);
$form->addElement('text', 'esi_notes_url', _("URL"), $attrsText);
$form->addElement('text', 'esi_action_url', _("Action URL"), $attrsText);
$form->addElement('select', 'esi_icon_image', _("Icon"), $extImg, array(
    "id" => "esi_icon_image",
    "onChange" => "showLogo('esi_icon_image_img',this.value)",
    "onkeyup" => "this.blur();this.focus();"
));
$form->addElement('text', 'esi_icon_image_alt', _("Alt icon"), $attrsText);

/*
 * Criticality
 */
$criticality = new CentreonCriticality($pearDB);
$critList = $criticality->getList(null, "level", 'ASC', null, null, true);
$criticalityIds = array(null => null);
foreach ($critList as $critId => $critData) {
    $criticalityIds[$critId] = $critData['sc_name'] . ' (' . $critData['level'] . ')';
}
$form->addElement('select', 'criticality_id', _('Severity level'), $criticalityIds);

$form->addElement('header', 'oreon', _("Centreon"));

$graphTplDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_graphtemplate'
    . '&action=defaultValues&target=service&field=graph_id&id=' . $service_id;
$attrGraphtemplate1 = array_merge(
    $attrGraphtemplates,
    array('defaultDatasetRoute' => $graphTplDeRoute)
);
$form->addElement('select2', 'graph_id', _("Graph Template"), array(), $attrGraphtemplate1);

if ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $mc_mod_sc = array();
    $mc_mod_sc[] = $form->createElement('radio', 'mc_mod_sc', null, _("Incremental"), '0');
    $mc_mod_sc[] = $form->createElement('radio', 'mc_mod_sc', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_sc, 'mc_mod_sc', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_sc' => '0'));
}

$servCatDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicecategory'
    . '&action=defaultValues&target=service&field=service_categories&id=' . $service_id;
$attrServicecategory1 = array_merge(
    $attrServicecategories,
    array('defaultDatasetRoute' => $servCatDeRoute)
);
$form->addElement('select2', 'service_categories', _("Categories"), array(), $attrServicecategory1);

/*
 * Sort 5 - Macros - Nagios 3
 */
if ($o == SERVICE_TEMPLATE_ADD) {
    $form->addElement('header', 'title5', _("Add macros"));
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {
    $form->addElement('header', 'title5', _("Modify macros"));
} elseif ($o == SERVICE_TEMPLATE_WATCH) {
    $form->addElement('header', 'title5', _("View macros"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addElement('header', 'title5', _("Massive Change"));
}

$form->addElement('header', 'macro', _("Macros"));

$form->addElement('text', 'add_new', _("Add a new macro"), $attrsText2);
$form->addElement('text', 'macroName', _("Name"), $attrsText2);
$form->addElement('text', 'macroValue', _("Value"), $attrsText2);
$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);

$form->addElement('hidden', 'service_id');
$reg = $form->addElement('hidden', 'service_register');
$reg->setValue("0");
$service_register = 0;
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);
if (is_array($select)) {
    $select_pear = $form->addElement('hidden', 'select');
    $select_pear->setValue(implode(',', array_keys($select)));
}

$form->applyFilter('__ALL__', 'myTrim');
$from_list_menu = false;
if ($o != SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    $form->addRule('service_description', _("Compulsory Name"), 'required');
    $form->addRule('service_alias', _("Compulsory Name"), 'required');
    $form->registerRule('exist', 'callback', 'testServiceTemplateExistence');
    $form->addRule('service_description', _("Name is already in use"), 'exist');
    $form->registerRule('cg_group_exists', 'callback', 'testCg2');
    $form->addRule(
        'service_cgs',
        _('Contactgroups exists. If you try to use a LDAP contactgroup,'
            . ' please verified if a Centreon contactgroup has the same name.'),
        'cg_group_exists'
    );
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}

$argChecker = $form->addElement("hidden", "argChecker");
$argChecker->setValue(1);
$form->registerRule("argHandler", "callback", "argHandler");
$form->addRule("argChecker", _("You must either fill all the arguments or leave them all empty"), "argHandler");

$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

#
## End of form definition
#

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path2, $tpl);

unset($service['service_template_model_stm_id']);
# Just watch a host information
if ($o == SERVICE_TEMPLATE_WATCH) {
    if (!$min && $centreon->user->access->page($p) != 2 && !isset($lockedElements[$service_id])) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&service_id=" . $service_id . "'")
        );
    }
    $form->setDefaults($service);
    $form->freeze();
} elseif ($o == SERVICE_TEMPLATE_MODIFY) {    // Modify a service information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($service);
} elseif ($o == SERVICE_TEMPLATE_ADD) {    // Add a service information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} elseif ($o == SERVICE_TEMPLATE_MASSIVE_CHANGE) {   // Massive Change
    $subMC = $form->addElement('submit', 'submitMC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

require_once _CENTREON_PATH_ . 'www/include/configuration/configObject/service/javascript/argumentJs.php';

$tpl->assign('msg', array("nagios" => $oreon->user->get_version(), "tpl" => 1));
$tpl->assign("sort1", _("Service Configuration"));
$tpl->assign("sort2", _("Relations"));
$tpl->assign("sort3", _("Data Processing"));
$tpl->assign("sort4", _("Service Extended Info"));
$tpl->assign("sort5", _("Macros"));
$tpl->assign('javascript', '
            <script type="text/javascript" src="./include/common/javascript/showLogo.js"></script>
            <script type="text/javascript" src="./include/common/javascript/centreon/macroPasswordField.js"></script>
            <script type="text/javascript" src="./include/common/javascript/centreon/macroLoadDescription.js"></script>
');
$tpl->assign('time_unit', " * " . $oreon->optGen["interval_length"] . " " . _("seconds"));
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange",'
    . ' TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"],'
    . ' WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

# prepare help texts
$helptext = "";
include_once("include/configuration/configObject/service/help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate() && $from_list_menu == false) {
    $serviceObj = $form->getElement('service_id');
    if ($form->getSubmitValue("submitA")) {
        $serviceObj->setValue(insertServiceInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        /*
         * Before saving, we check if a password macro has changed its name to be able to give it the right password
         * instead of wildcards (PASSWORD_REPLACEMENT_VALUE).
         */
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
        updateServiceInDB($serviceObj->getValue());
    } elseif ($form->getSubmitValue("submitMC")) {
        foreach (array_keys($select) as $svcTemplateIdToUpdate) {
            updateServiceInDB($svcTemplateIdToUpdate, true);
        }
    }
    $action = $form->getSubmitValue("action");
    if ($action !== null && !$action["action"]["action"]) {
        $o = SERVICE_TEMPLATE_WATCH;
    } else {
        $o = null;
    }
    $valid = true;
} elseif ($form->isSubmitted()) {
    $tpl->assign("argChecker", "<font color='red'>" . $form->getElementError("argChecker") . "</font>");
}

if ($valid) {
    require_once($path . "listServiceTemplateModel.php");
} else {
    $dbResult = $pearDB->query('SELECT `value` FROM options WHERE `key` = "inheritance_mode"');
    $inheritanceMode = $dbResult->fetch();
    # Apply a template definition
    require_once _CENTREON_PATH_ . 'www/include/configuration/configObject/service/javascript/argumentJs.php';
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('is_not_template', $service_register);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('v', $oreon->user->get_version());
    $tpl->assign('inheritance', $inheritanceMode['value']);

    $tpl->assign("Freshness_Control_options", _("Freshness Control options"));
    $tpl->assign("Flapping_Options", _("Flapping options"));
    $tpl->assign("Perfdata_Options", _("Perfdata Options"));
    $tpl->assign("History_Options", _("History Options"));
    $tpl->assign("Event_Handler", _("Event Handler"));
    $tpl->assign("topdoc", _("Documentation"));
    $tpl->assign("seconds", _("seconds"));
    $tpl->assign('custom_macro_label', _('Custom macros'));
    $tpl->assign('template_inheritance', _('Template inheritance'));
    $tpl->assign('command_inheritance', _('Command inheritance'));
    $tpl->assign('cloneSetMacro', $cloneSetMacro);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign('isServiceTemplate', 1);
    $tpl->display("formService.ihtml");
    ?>
    <script type="text/javascript">
        setTimeout('transformForm()', 200);
        showLogo('esi_icon_image_img', document.getElementById('esi_icon_image').value);

        function uncheckNotifOption(object) {
            if (object.id == "notifN" && object.checked) {
                document.getElementById('notifW').checked = false;
                document.getElementById('notifU').checked = false;
                document.getElementById('notifC').checked = false;
                document.getElementById('notifR').checked = false;
                document.getElementById('notifF').checked = false;
                document.getElementById('notifDS').checked = false;
            } else {
                document.getElementById('notifN').checked = false;
            }
        }
    </script>
<?php } ?>
