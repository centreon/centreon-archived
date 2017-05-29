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

global $form_service_type;

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

$serviceObj = new CentreonService($pearDB);

/**
 *
 * Database retrieve information for Service
 * @param unknown_type $arg
 */
function myDecodeService($arg)
{
    $arg = str_replace('#BR#', "\\n", $arg);
    $arg = str_replace('#T#', "\\t", $arg);
    $arg = str_replace('#R#', "\\r", $arg);
    $arg = str_replace('#S#', "/", $arg);
    $arg = str_replace('#BS#', "\\", $arg);

    return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
}

if (!$centreon->user->admin) {
    if ($service_id) {
        $checkres = $pearDB->query("SELECT service_id
                                        FROM $acldbname.centreon_acl
                                        WHERE service_id = " . $pearDB->escape($service_id) . "
                                        AND group_id IN (" . $acl->getAccessGroupsString() . ")");
        if (!$checkres->numRows()) {
            $msg = new CentreonMsg();
            $msg->setImage("./img/icons/warning.png");
            $msg->setTextStyle("bold");
            $msg->setText(_('You are not allowed to access this service'));
            return null;
        }
    }
}

/* notification contacts */
$notifCs = $acl->getContactAclConf(array(
    'fields' => array('contact_id', 'contact_name'),
    'get_row' => 'contact_name',
    'keys' => array('contact_id'),
    'conditions' => array('contact_register' => '1'),
    'order' => array('contact_name')
));

/* notification contact groups */
$notifCgs = $acl->getContactGroupAclConf(array(
    'fields' => array('cg_id', 'cg_name'),
    'get_row' => 'cg_name',
    'keys' => array('cg_id'),
    'order' => array('cg_name')
), false);

/* hosts */
$hosts = $acl->getHostAclConf(null, 'broker', array(
    'fields' => array('host.host_id', 'host.host_name'),
    'keys' => array('host_id'),
    'get_row' => 'host_name',
    'order' => array('host_name')
));

/* hostgroups */
$hgs = $acl->getHostGroupAclConf(null, 'broker', array(
    'fields' => array('hg_id', 'hg_name'),
    'keys' => array('hg_id'),
    'get_row' => 'hg_name',
    'order' => array('hg_name')
));

/* service groups */
$sgs = $acl->getServiceGroupAclConf(null, 'broker', array(
    'fields' => array('servicegroup.sg_id', 'servicegroup.sg_name'),
    'keys' => array('sg_id'),
    'get_row' => 'sg_name',
    'order' => array('sg_name')
));

/* service categories */
$service_categories = array();
$scstring = $acl->getServiceCategoriesString();
$rescat = $pearDB->query("SELECT sc_name, sc_id
                              FROM service_categories " .
    "WHERE level IS NULL " .
    ($scstring != "''" ? $acl->queryBuilder('AND', 'sc_id', $acl->getServiceCategoriesString()) : "") .
    " ORDER BY sc_name");
while ($scat = $rescat->fetchRow()) {
    $service_categories[$scat['sc_id']] = $scat['sc_name'];
}

$cmdId = 0;
$service = array();
$serviceTplId = null;
$initialValues = array();
if (($o == "c" || $o == "w") && $service_id) {
    $DBRESULT = $pearDB->query("SELECT *
                                FROM service
                                LEFT JOIN extended_service_information esi
                                ON esi.service_service_id = service_id
                                WHERE service_id = '" . $service_id . "' LIMIT 1");
    /*
     * Set base value
     */
    $service = array_map("myDecodeService", $DBRESULT->fetchRow());
    $serviceTplId = $service['service_template_model_stm_id'];
    $cmdId = $service['command_command_id'];

    /*
     * Grab hostgroup || host
     */
    $DBRESULT = $pearDB->query("SELECT host_host_id
                                FROM host_service_relation hsr, host
                                WHERE hsr.service_service_id = '" . $service_id . "'
                                AND host_host_id IS NOT NULL
                                AND host_id = host_host_id ORDER BY host_name, host_alias");
    while ($parent = $DBRESULT->fetchRow()) {
        if ($parent["host_host_id"]) {
            if (!isset($hosts[$parent['host_host_id']])) {
                $initialValues['service_hPars'][] = $parent['host_host_id'];
            } else {
                $service["service_hPars"][$parent["host_host_id"]] = $parent["host_host_id"];
            }
        }
    }
    $DBRESULT->free();

    $DBRESULT = $pearDB->query("SELECT hostgroup_hg_id
                                FROM host_service_relation hsr, hostgroup
                                WHERE hsr.service_service_id = '" . $service_id . "'
                                AND hostgroup_hg_id IS NOT NULL
                                AND hostgroup_hg_id = hg_id ORDER BY hg_name, hg_alias");
    while ($parent = $DBRESULT->fetchRow()) {
        if ($parent["hostgroup_hg_id"]) {
            if (!isset($hgs[$parent['hostgroup_hg_id']])) {
                $initialValues['service_hgPars'][] = $parent['hostgroup_hg_id'];
            } else {
                $service["service_hgPars"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
            }
        }
    }
    $DBRESULT->free();

    /*
     * Set Service Notification Options
     */
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
     * Set Contact Group
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id
                                FROM contactgroup_service_relation
                                WHERE service_service_id = '" . $service_id . "'");
    for ($i = 0; $notifCg = $DBRESULT->fetchRow(); $i++) {
        if (!isset($notifCgs[$notifCg['contactgroup_cg_id']])) {
            $initialValues['service_cgs'][] = $notifCg["contactgroup_cg_id"];
        } else {
            $service["service_cgs"][$i] = $notifCg["contactgroup_cg_id"];
        }
    }
    $DBRESULT->free();

    /*
     * Set Contacts
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT contact_id
                                FROM contact_service_relation
                                WHERE service_service_id = '" . $service_id . "'");
    for ($i = 0; $notifC = $DBRESULT->fetchRow(); $i++) {
        if (!isset($notifCs[$notifC['contact_id']])) {
            $initialValues['service_cs'][] = $notifC['contact_id'];
        } else {
            $service["service_cs"][$i] = $notifC["contact_id"];
        }
    }
    $DBRESULT->free();

    /*
     * Set Service Group Parents
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT servicegroup_sg_id
                                FROM servicegroup_relation
                                WHERE service_service_id = '" . $service_id . "'");
    for ($i = 0; $sg = $DBRESULT->fetchRow(); $i++) {
        if (!isset($sgs[$sg['servicegroup_sg_id']])) {
            $initialValues['service_sgs'][] = $sg['servicegroup_sg_id'];
        } else {
            $service["service_sgs"][$i] = $sg["servicegroup_sg_id"];
        }
    }
    $DBRESULT->free();

    /*
     * Set Traps
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT traps_id
                                FROM traps_service_relation
                                WHERE service_id = '" . $service_id . "'");
    for ($i = 0; $trap = $DBRESULT->fetchRow(); $i++) {
        $service["service_traps"][$i] = $trap["traps_id"];
    }
    $DBRESULT->free();

    /*
     * Set Categories
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT sc_id
                                FROM service_categories_relation
                                WHERE service_service_id = '" . $service_id . "'
                                AND NOT EXISTS(SELECT sc_id
                                                FROM service_categories sc
                                                WHERE sc.sc_id = service_categories_relation.sc_id
                                                AND sc.level IS NOT NULL)");
    for ($i = 0; $service_category = $DBRESULT->fetchRow(); $i++) {
        if (!isset($service_categories[$service_category['sc_id']])) {
            $initialValues['service_categories'][] = $service_category['sc_id'];
        } else {
            $service["service_categories"][$i] = $service_category["sc_id"];
        }
    }
    $DBRESULT->free();

    /*
     * Set criticality
     */
    $res = $pearDB->query("SELECT sc.sc_id
                            FROM service_categories sc, service_categories_relation scr
                            WHERE scr.service_service_id = " . $pearDB->escape($service_id) . "
                            AND scr.sc_id = sc.sc_id
                            AND sc.level IS NOT NULL
                            ORDER BY sc.level ASC
                            LIMIT 1");
    if ($res->numRows()) {
        $cr = $res->fetchRow();
        $service['criticality_id'] = $cr['sc_id'];
    }
}

if (($o == "c" || $o == "w") && $service_id) {
    $aListTemplate = getListTemplates($pearDB, $service_id);

    if (!isset($cmdId)) {
        $cmdId = "";
    }

    $aMacros = $serviceObj->getMacros($service_id, $aListTemplate, $cmdId, $_POST);
}

$cdata = CentreonData::getInstance();
$cdata->addJsData('clone-values-macro', htmlspecialchars(
    json_encode($aMacros),
    ENT_QUOTES
));

$cdata->addJsData('clone-count-macro', count($aMacros));

# Service Templates comes from DB -> Store in $svTpls Array
$svTpls = array(null => null);
$DBRESULT = $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id
                            FROM service
                            WHERE service_register = '0'
                            AND service_id != '" . $service_id . "' ORDER BY service_description");
while ($svTpl = $DBRESULT->fetchRow()) {
    if (!$svTpl["service_description"]) {
        $svTpl["service_description"] = getMyServiceName($svTpl["service_template_model_stm_id"]) . "'";
    } else {
        $svTpl["service_description"] = str_replace('#S#', "/", $svTpl["service_description"]);
        $svTpl["service_description"] = str_replace('#BS#', "\\", $svTpl["service_description"]);
    }
    $svTpls[$svTpl["service_id"]] = $svTpl["service_description"];
}
$DBRESULT->free();

# Timeperiods comes from DB -> Store in $tps Array
$tps = array(null => null);
$DBRESULT = $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
while ($tp = $DBRESULT->fetchRow()) {
    $tps[$tp["tp_id"]] = $tp["tp_name"];
}
$DBRESULT->free();

# Check commands comes from DB -> Store in $checkCmds Array
$checkCmds = array(null => null);
$DBRESULT = $pearDB->query("SELECT command_id, command_name
                            FROM command
                            WHERE command_type = '2'
                            ORDER BY command_name");
while ($checkCmd = $DBRESULT->fetchRow()) {
    $checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$DBRESULT->free();

# Check commands comes from DB -> Store in $checkCmdEvent Array
$checkCmdEvent = array(null => null);
$DBRESULT = $pearDB->query("SELECT command_id, command_name
                            FROM command
                            WHERE command_type = '2'
                            OR command_type = '3'
                            ORDER BY command_name");
while ($checkCmd = $DBRESULT->fetchRow()) {
    $checkCmdEvent[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$DBRESULT->free();

# Graphs Template comes from DB -> Store in $graphTpls Array
$graphTpls = array(null => null);
$DBRESULT = $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
while ($graphTpl = $DBRESULT->fetchRow()) {
    $graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
}
$DBRESULT->free();

# Traps definition comes from DB -> Store in $traps Array
$traps = array();
if (isset($service_id)) {
    $DBRESULT = $pearDB->query("SELECT t.traps_id, t.traps_name
                                FROM traps t, traps_service_relation sr
                                WHERE t.traps_id = sr.traps_id
                                AND sr.service_id = '" . $service_id . "' ORDER BY t.traps_name");
    while ($trap = $DBRESULT->fetchRow()) {
        $traps[$trap["traps_id"]] = $trap["traps_name"];
    }
    $DBRESULT->free();
}

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
$attrsTextURL = array("size" => "50");
$attrsAdvSelect_small = array("style" => "width: 270px; height: 70px;");
$attrsAdvSelect = array("style" => "width: 270px; height: 100px;");
$attrsAdvSelect_big = array("style" => "width: 270px; height: 200px;");
$attrsTextarea = array("rows" => "5", "cols" => "40");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br />'
    . '<br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

$timeperiodsRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_timeperiod&action=list';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timeperiodsRoute,
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

$contactgRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_contactgroup&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $contactgRoute,
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


$hostgroupsRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostgroupsRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);

$servicetemplatesRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_servicetemplate&action=list';
$attrServicetemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $servicetemplatesRoute,
    'multiple' => false,
    'linkedObject' => 'centreonServicetemplates'
);

$servicegRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_servicegroup&action=list';
$attrServicegroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $servicegRoute,
    'multiple' => true,
    'linkedObject' => 'centreonServicegroups'
);

$servicecateRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_servicecategory&action=list&t=c';
$attrServicecategories = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $servicecateRoute,
    'multiple' => true,
    'linkedObject' => 'centreonServicecategories'
);

$trapsRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_trap&action=list';
$attrTraps = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $trapsRoute,
    'multiple' => true,
    'linkedObject' => 'centreonTraps'
);

$graphtemplatesRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_graphtemplate&action=list';
$attrGraphtemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $graphtemplatesRoute,
    'multiple' => false,
    'linkedObject' => 'centreonGraphTemplate'
);

#
## Form begin
#
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Service"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Service"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Service"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title', _("Massive Change"));
}

#
## Service basic information
#

/*
 * - No possibility to change name and alias, because there's no interest
 * - May be ? #409
 */
if ($o != "mc") {
    $form->addElement('text', 'service_description', _("Description"), $attrsText);
}
$form->addElement('text', 'service_alias', _("Alias"), $attrsText);

$servicetemplateRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate'
    . '&action=defaultValues&target=service&field=service_template_model_stm_id&id=' . $service_id;
$attrServicetemplate1 = array_merge(
    $attrServicetemplates,
    array('defaultDatasetRoute' => $servicetemplateRoute)
);
$serviceTplSelect = $form->addElement(
    'select2',
    'service_template_model_stm_id',
    _("Template"),
    array(),
    $attrServicetemplate1
);
$serviceTplSelect->addJsCallback('change', 'changeServiceTemplate(this.value)');

$form->addElement('static', 'tplText', _("Using a Template exempts you to fill required fields"));

#
## Check information
#
$form->addElement('header', 'check', _("Service State"));

$serviceIV[] = HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Yes"), '1');
$serviceIV[] = HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("No"), '0');
$serviceIV[] = HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Default"), '2');
$form->addGroup($serviceIV, 'service_is_volatile', _("Is Volatile"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_is_volatile' => '2'));
}
$availableCommandRoute1 = './include/common/webServices/rest/internal.php?object=centreon_configuration_command' .
    '&action=list&t=2';
$defaultCommandRoute1 = './include/common/webServices/rest/internal.php?object=centreon_configuration_command' .
    '&action=defaultValues&target=service&field=command_command_id&id=' . $service_id;
$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => $defaultCommandRoute1,
        'availableDatasetRoute' => $availableCommandRoute1
    )
);
$checkCommandSelect = $form->addElement('select2', 'command_command_id', _("Check Command"), array(), $attrCommand1);
if ($o == "mc") {
    $checkCommandSelect->addJsCallback(
        'change',
        'setArgument(jQuery(this).closest("form").get(0),"command_command_id","example1");'
    );
} else {
    $checkCommandSelect->addJsCallback('change', 'changeCommand(this.value);');
}

$form->addElement('text', 'command_command_id_arg', _("Args"), $attrsText);
$form->addElement('text', 'service_max_check_attempts', _("Max Check Attempts"), $attrsText2);
$form->addElement('text', 'service_normal_check_interval', _("Normal Check Interval"), $attrsText2);
$form->addElement('text', 'service_retry_check_interval', _("Retry Check Interval"), $attrsText2);

$serviceEHE[] = HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Yes"), '1');
$serviceEHE[] = HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("No"), '0');
$serviceEHE[] = HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Default"), '2');
$form->addGroup($serviceEHE, 'service_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_event_handler_enabled' => '2'));
}

$availableCommandRoute2 = './include/common/webServices/rest/internal.php' .
    '?object=centreon_configuration_command&action=list';
$defaultCommandRoute2 = './include/common/webServices/rest/internal.php?object=centreon_configuration_command'
    . '&action=defaultValues&target=service&field=command_command_id2&id=' . $service_id;
$attrCommand2 = array_merge(
    $attrCommands,
    array(
        'availableDatasetRoute' => $availableCommandRoute2,
        'defaultDatasetRoute' => $defaultCommandRoute2
    )
);
$eventHandlerSelect = $form->addElement('select2', 'command_command_id2', _("Event Handler"), array(), $attrCommand2);
$eventHandlerSelect->addJsCallback(
    'change',
    'setArgument(jQuery(this).closest("form").get(0),"command_command_id2","example2");'
);

$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsText);

$serviceACE[] = HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Yes"), '1');
$serviceACE[] = HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("No"), '0');
$serviceACE[] = HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Default"), '2');
$form->addGroup($serviceACE, 'service_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_active_checks_enabled' => '2'));
}

$servicePCE[] = HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Yes"), '1');
$servicePCE[] = HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("No"), '0');
$servicePCE[] = HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Default"), '2');
$form->addGroup($servicePCE, 'service_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_passive_checks_enabled' => '2'));
}
$attrTimeperiodRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=service&field=timeperiod_tp_id&id=' . $service_id;
$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $attrTimeperiodRoute)
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

$form->addElement('header', 'information', _("Service Basic Information"));

/**
 * Acknowledgement timeout
 */
$form->addElement('text', 'service_acknowledgement_timeout', _("Acknowledgement timeout"), $attrsText2);


##
## Notification informations
##
$form->addElement('header', 'notification', _("Notification"));
$serviceNE[] = HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Yes"), '1');
$serviceNE[] = HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("No"), '0');
$serviceNE[] = HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Default"), '2');
$form->addGroup($serviceNE, 'service_notifications_enabled', _("Notification Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_notifications_enabled' => '2'));
}

if ($o == "mc") {
    $mc_mod_cgs = array();
    $mc_mod_cgs[] = HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Incremental"), '0');
    $mc_mod_cgs[] = HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_cgs, 'mc_mod_cgs', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_cgs' => '0'));
}

##
## Use only contacts/contacts group of host and host template
##
$form->addElement('header', 'use_only_contacts_from_host', _("Inherit only contacts/contacts group from host"));
$serviceIOHC[] = HTML_QuickForm::createElement('radio', 'service_use_only_contacts_from_host', null, _("Yes"), '1');
$serviceIOHC[] = HTML_QuickForm::createElement('radio', 'service_use_only_contacts_from_host', null, _("No"), '0');
$form->addGroup(
    $serviceIOHC,
    'service_use_only_contacts_from_host',
    _("Inherit only contacts/contacts group from host"),
    '&nbsp;'
);
if ($o != "mc") {
    $form->setDefaults(array('service_use_only_contacts_from_host' => '0'));
}

/*
 * Additive
 */

if ($o == "mc") {
    $contactAdditive[] = HTML_QuickForm::createElement('radio', 'mc_contact_additive_inheritance', null, _("Yes"), '1');
    $contactAdditive[] = HTML_QuickForm::createElement('radio', 'mc_contact_additive_inheritance', null, _("No"), '0');
    $contactAdditive[] = HTML_QuickForm::createElement(
        'radio',
        'mc_contact_additive_inheritance',
        null,
        _("Default"),
        '2'
    );
    $form->addGroup($contactAdditive, 'mc_contact_additive_inheritance', _("Contact additive inheritance"), '&nbsp;');

    $contactGroupAdditive[] = HTML_QuickForm::createElement('radio', 'mc_cg_additive_inheritance', null, _("Yes"), '1');
    $contactGroupAdditive[] = HTML_QuickForm::createElement('radio', 'mc_cg_additive_inheritance', null, _("No"), '0');
    $contactGroupAdditive[] = HTML_QuickForm::createElement(
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
$attrContactRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contact'
    . '&action=defaultValues&target=service&field=service_cs&id=' . $service_id;
$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => $attrContactRoute)
);
$form->addElement('select2', 'service_cs', _("Implied Contacts"), array(), $attrContact1);

/*
 *  Contact groups
 */
$attrContactgroupRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=service&field=service_cgs&id=' . $service_id;
$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => $attrContactgroupRoute)
);
$form->addElement('select2', 'service_cgs', _("Implied Contact Groups"), array(), $attrContactgroup1);


if ($o == "mc") {
    $mc_mod_notifopt_first_notification_delay = array();
    $mc_mod_notifopt_first_notification_delay[] = &HTML_QuickForm::createElement(
        'radio',
        'mc_mod_notifopt_first_notification_delay',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_first_notification_delay[] = &HTML_QuickForm::createElement(
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

if ($o == "mc") {
    $mc_mod_notifopt_notification_interval = array();
    $mc_mod_notifopt_notification_interval[] = &HTML_QuickForm::createElement(
        'radio',
        'mc_mod_notifopt_notification_interval',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_notification_interval[] = &HTML_QuickForm::createElement(
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

if ($o == "mc") {
    $mc_mod_notifopt_timeperiod = array();
    $mc_mod_notifopt_timeperiod[] = &HTML_QuickForm::createElement(
        'radio',
        'mc_mod_notifopt_timeperiod',
        null,
        _("Incremental"),
        '0'
    );
    $mc_mod_notifopt_timeperiod[] = &HTML_QuickForm::createElement(
        'radio',
        'mc_mod_notifopt_timeperiod',
        null,
        _("Replacement"),
        '1'
    );
    $form->addGroup($mc_mod_notifopt_timeperiod, 'mc_mod_notifopt_timeperiod', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopt_timeperiod' => '0'));
}

$attrTimeperiodRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod'
    . '&action=defaultValues&target=service&field=timeperiod_tp_id2&id=' . $service_id;
$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => $attrTimeperiodRoute)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Notification Period"), array(), $attrTimeperiod2);

if ($o == "mc") {
    $mc_mod_notifopts = array();
    $mc_mod_notifopts[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopts', null, _("Incremental"), '0');
    $mc_mod_notifopts[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopts', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopts, 'mc_mod_notifopts', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopts' => '0'));
}

$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'w',
    '&nbsp;',
    _("Warning"),
    array('id' => 'notifW', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'u',
    '&nbsp;',
    _("Unknown"),
    array('id' => 'notifU', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'c',
    '&nbsp;',
    _("Critical"),
    array('id' => 'notifC', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'r',
    '&nbsp;',
    _("Recovery"),
    array('id' => 'notifR', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'f',
    '&nbsp;',
    _("Flapping"),
    array('id' => 'notifF', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    's',
    '&nbsp;',
    _("Downtime Scheduled"),
    array('id' => 'notifDS', 'onClick' => 'uncheckNotifOption(this);')
);
$serviceNotifOpt[] = HTML_QuickForm::createElement(
    'checkbox',
    'n',
    '&nbsp;',
    _("None"),
    array('id' => 'notifN', 'onClick' => 'uncheckNotifOption(this);')
);
$form->addGroup($serviceNotifOpt, 'service_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

$serviceStalOpt[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"));
$serviceStalOpt[] = HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"));
$serviceStalOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
$serviceStalOpt[] = HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"));
$form->addGroup($serviceStalOpt, 'service_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

#
## Further informations
#
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$serviceActivation[] = HTML_QuickForm::createElement('radio', 'service_activate', null, _("Enabled"), '1');
$serviceActivation[] = HTML_QuickForm::createElement('radio', 'service_activate', null, _("Disabled"), '0');
$form->addGroup($serviceActivation, 'service_activate', _("Status"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_activate' => '1'));
}
$form->addElement('textarea', 'service_comment', _("Comments"), $attrsTextarea);

#
## Sort 2 - Service Relations
#
if ($o == "a") {
    $form->addElement('header', 'title2', _("Add relations"));
} elseif ($o == "c") {
    $form->addElement('header', 'title2', _("Modify relations"));
} elseif ($o == "w") {
    $form->addElement('header', 'title2', _("View relations"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title2', _("Massive Change"));
}

if ($o == "mc") {
    $mc_mod_Pars = array();
    $mc_mod_Pars[] = HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Incremental"), '0');
    $mc_mod_Pars[] = HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_Pars, 'mc_mod_Pars', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_Pars' => '0'));
}

$sgReadOnly = false;
if ($form_service_type == "BYHOST") {
    if (isset($service['service_hPars']) && count($service['service_hPars']) > 1) {
        $sgReadOnly = true;
    }
    $attrHostsRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host'
        . '&action=defaultValues&target=service&field=service_hPars&id=' . $service_id;
    $attrHost1 = array_merge(
        $attrHosts,
        array('defaultDatasetRoute' => $attrHostsRoute)
    );
    $form->addElement('select2', 'service_hPars', _("Linked with Hosts"), array(), $attrHost1);
}

if ($form_service_type == "BYHOSTGROUP") {
    $attrHostgroupRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup'
        . '&action=defaultValues&target=service&field=service_hgPars&id=' . $service_id;
    $attrHostgroup1 = array_merge(
        $attrHostgroups,
        array('defaultDatasetRoute' => $attrHostgroupRoute)
    );
    $form->addElement('select2', 'service_hgPars', _("Linked with Host Groups"), array(), $attrHostgroup1);

    if (isset($service['service_hgPars']) && count($service['service_hgPars']) > 1) {
        $sgReadOnly = true;
    }
}

// Service relations
$form->addElement('header', 'links', _("Relations"));
if ($o == "mc") {
    $mc_mod_sgs = array();
    $mc_mod_sgs[] = HTML_QuickForm::createElement('radio', 'mc_mod_sgs', null, _("Incremental"), '0');
    $mc_mod_sgs[] = HTML_QuickForm::createElement('radio', 'mc_mod_sgs', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_sgs, 'mc_mod_sgs', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_sgs' => '0'));
}
$attrServicegroupsRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicegroup'
    . '&action=defaultValues&target=service&field=service_sgs&id=' . $service_id;
$attrServicegroup1 = array_merge(
    $attrServicegroups,
    array('defaultDatasetRoute' => $attrServicegroupsRoute)
);
$ams3 = $form->addElement('select2', 'service_sgs', _("Linked with Servicegroups"), array(), $attrServicegroup1);
if ($sgReadOnly === true) {
    $ams3->freeze();
}

$form->addElement('header', 'traps', _("SNMP Traps"));
if ($o == "mc") {
    $mc_mod_traps = array();
    $mc_mod_traps[] = HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Incremental"), '0');
    $mc_mod_traps[] = HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_traps, 'mc_mod_traps', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_traps' => '0'));
}
$attrTrapRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_trap'
    . '&action=defaultValues&target=service&field=service_traps&id=' . $service_id;
$attrTrap1 = array_merge(
    $attrTraps,
    array('defaultDatasetRoute' => $attrTrapRoute)
);
$form->addElement('select2', 'service_traps', _("Service Trap Relation"), array(), $attrTrap1);

#
## Sort 3 - Data treatment
#
if ($o == "a") {
    $form->addElement('header', 'title3', _("Add Data Processing"));
} elseif ($o == "c") {
    $form->addElement('header', 'title3', _("Modify Data Processing"));
} elseif ($o == "w") {
    $form->addElement('header', 'title3', _("View Data Processing"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title2', _("Massive Change"));
}

$form->addElement('header', 'treatment', _("Data Processing"));

$serviceOOS[] = HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Yes"), '1');
$serviceOOS[] = HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("No"), '0');
$serviceOOS[] = HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Default"), '2');
$form->addGroup($serviceOOS, 'service_obsess_over_service', _("Obsess Over Service"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_obsess_over_service' => '2'));
}

$serviceCF[] = HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Yes"), '1');
$serviceCF[] = HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("No"), '0');
$serviceCF[] = HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Default"), '2');
$form->addGroup($serviceCF, 'service_check_freshness', _("Check Freshness"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_check_freshness' => '2'));
}

$serviceFDE[] = HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Yes"), '1');
$serviceFDE[] = HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("No"), '0');
$serviceFDE[] = HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Default"), '2');
$form->addGroup($serviceFDE, 'service_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_flap_detection_enabled' => '2'));
}

$form->addElement('text', 'service_freshness_threshold', _("Freshness Threshold"), $attrsText2);
$form->addElement('text', 'service_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
$form->addElement('text', 'service_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

$serviceRSI[] = HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Yes"), '1');
$serviceRSI[] = HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("No"), '0');
$serviceRSI[] = HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Default"), '2');
$form->addGroup($serviceRSI, 'service_retain_status_information', _("Retain Status Information"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_retain_status_information' => '2'));
}

$serviceRNI[] = HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Yes"), '1');
$serviceRNI[] = HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("No"), '0');
$serviceRNI[] = HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Default"), '2');
$form->addGroup($serviceRNI, 'service_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('service_retain_nonstatus_information' => '2'));
}

#
## Sort 4 - Extended Infos
#
if ($o == "a") {
    $form->addElement('header', 'title4', _("Add an Extended Info"));
} elseif ($o == "c") {
    $form->addElement('header', 'title4', _("Modify an Extended Info"));
} elseif ($o == "w") {
    $form->addElement('header', 'title4', _("View an Extended Info"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title3', _("Massive Change"));
}

$form->addElement('header', 'nagios', _("Monitoring Engine"));
$form->addElement('text', 'esi_notes', _("Notes"), $attrsText);
$form->addElement('text', 'esi_notes_url', _("URL"), $attrsTextURL);
$form->addElement('text', 'esi_action_url', _("Action URL"), $attrsTextURL);
$form->addElement('select', 'esi_icon_image', _("Icon"), $extImg, array(
    "id" => "esi_icon_image",
    "onChange" => "showLogo('esi_icon_image_img',this.value)",
    "onkeyup" => "this.blur();this.focus();"
));
$form->addElement('text', 'esi_icon_image_alt', _("Alt icon"), $attrsText);
$form->addElement('text', 'geo_coords', _("Geo coordinates"), $attrsText);

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

$graphTemplateRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_graphtemplate'
    . '&action=defaultValues&target=service&field=graph_id&id=' . $service_id;
$attrGraphtemplate1 = array_merge(
    $attrGraphtemplates,
    array('defaultDatasetRoute' => $graphTemplateRoute)
);
$form->addElement('select2', 'graph_id', _("Graph Template"), array(), $attrGraphtemplate1);

if ($o == "mc") {
    $mc_mod_sc = array();
    $mc_mod_sc[] = HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Incremental"), '0');
    $mc_mod_sc[] = HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_sc, 'mc_mod_sc', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_sc' => '0'));
}

$serviceCategorieRoute = './include/common/webServices/rest/internal.php?object='
    . 'centreon_configuration_servicecategory&action=defaultValues&target=service'
    . '&field=service_categories&id=' . $service_id;
$attrServicecategory1 = array_merge(
    $attrServicecategories,
    array('defaultDatasetRoute' => $serviceCategorieRoute)
);
$form->addElement('select2', 'service_categories', _("Categories"), array(), $attrServicecategory1);

/*
 * Sort 5
 */
if ($o == "a") {
    $form->addElement('header', 'title5', _("Add macros"));
} elseif ($o == "c") {
    $form->addElement('header', 'title5', _("Modify macros"));
} elseif ($o == "w") {
    $form->addElement('header', 'title5', _("View macros"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title5', _("Massive Change"));
}

$form->addElement('header', 'macro', _("Macros"));

$form->addElement('text', 'add_new', _("Add a new macro"), $attrsText2);
$form->addElement('text', 'macroName', _("Name"), $attrsText2);
$form->addElement('text', 'macroValue', _("Value"), $attrsText2);
$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);

$form->addElement('hidden', 'service_id');
$reg = $form->addElement('hidden', 'service_register');
$reg->setValue("1");
$service_register = 1;
$page = $form->addElement('hidden', 'p');
$page->setValue($p);
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

/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$from_list_menu = false;
if ($o != "mc") {
    $form->addRule('service_description', _("Compulsory Name"), 'required');
    # If we are using a Template, no need to check the value, we hope there are in the Template
    if (!$form->getSubmitValue("service_template_model_stm_id")) {
        $form->addRule('command_command_id', _("Compulsory Command"), 'required');
        if (!$form->getSubmitValue("service_hPars")) {
            $form->addRule('service_hgPars', _("HostGroup or Host Required"), 'required');
        }
        if (!$form->getSubmitValue("service_hgPars")) {
            $form->addRule('service_hPars', _("HostGroup or Host Required"), 'required');
        }
    }
    if (!$form->getSubmitValue("service_hPars")) {
        $form->addRule('service_hgPars', _("HostGroup or Host Required"), 'required');
    }
    if (!$form->getSubmitValue("service_hgPars")) {
        $form->addRule('service_hPars', _("HostGroup or Host Required"), 'required');
    }
    $form->registerRule('exist', 'callback', 'testServiceExistence');
    $form->addRule(
        'service_description',
        _("This description is in conflict with another one that is already defined in the selected relation(s)"),
        'exist'
    );

    $argChecker = $form->addElement("hidden", "argChecker");
    $argChecker->setValue(1);
    $form->registerRule("argHandler", "callback", "argHandler");
    $form->addRule("argChecker", _("You must either fill all the arguments or leave them all empty"), "argHandler");

    $macChecker = $form->addElement("hidden", "macChecker");
    $macChecker->setValue(1);
    $form->registerRule("macHandler", "callback", "serviceMacHandler");
    $form->addRule("macChecker", _("You cannot override reserved macros"), "macHandler");

    $form->registerRule('cg_group_exists', 'callback', 'testCg2');
    $form->addRule(
        'service_cgs',
        _('Contactgroups exists. If you try to use a LDAP contactgroup, '
            . 'please verified if a Centreon contactgroup has the same name.'),
        'cg_group_exists'
    );

    $form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));
} elseif ($o == "mc") {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}

if (isset($service['service_template_model_stm_id']) && ($service['service_template_model_stm_id'] === '')) {
    unset($service['service_template_model_stm_id']);
}

#
##End of form definition
#
// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign(
    'alert_check_interval',
    _("Warning, unconventional use of interval check. You should prefer to use an interval lower than 24h, "
        . "if needed, pair this configuration with the use of timeperiods")
);

// Just watch a host information
if ($o == "w") {
    if (!$min && $centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&service_id=" . $service_id . "'")
        );
    }
    $form->setDefaults($service);
    $form->freeze();
} elseif ($o == "c") {
    // Modify a service information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement(
        'button',
        'reset',
        _("Reset"),
        array("onClick" => "history.go(0);", "class" => "btc bt_default")
    );
    $form->setDefaults($service);
} elseif ($o == "a") {
    // Add a service information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} elseif ($o == "mc") {
    // Massive Change
    $subMC = $form->addElement('submit', 'submitMC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('msg', array("nagios" => $centreon->user->get_version(), "tpl" => 0));
$tpl->assign(
    'javascript',
    '<script type="text/javascript" src="./include/common/javascript/showLogo.js"></script>'
    . '<script type="text/javascript" src="./include/common/javascript/centreon/macroPasswordField.js"></script>'
    . '<script type="text/javascript" src="./include/common/javascript/centreon/macroLoadDescription.js"></script>'
);
$tpl->assign('time_unit', " * " . $centreon->optGen["interval_length"] . " " . _("seconds"));
$tpl->assign("p", $p);
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, '
    . '"orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], '
    . 'WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

// prepare help texts
$helptext = "";
include_once("help.php");
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
        updateServiceInDB($serviceObj->getValue());
    } elseif ($form->getSubmitValue("submitMC")) {
        $select = explode(",", $select);
        foreach ($select as $key => $value) {
            if ($value) {
                updateServiceInDB($value, true);
            }
        }
    }
    $o = "w";
    $valid = true;
} elseif ($form->isSubmitted()) {
    $tpl->assign("argChecker", "<font color='red'>" . $form->getElementError("argChecker") . "</font>");
    $tpl->assign("macChecker", "<font color='red'>" . $form->getElementError("macChecker") . "</font>");
}

require_once $path . 'javascript/argumentJs.php';

if ($valid) {
    if ($p == "60201") {
        require_once($path . "listServiceByHost.php");
    } elseif ($p == "60202") {
        require_once($path . "listServiceByHostGroup.php");
    } elseif ($p == "602") {
        require_once($path . "listServiceByHost.php");
    }
} else {
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('is_not_template', $service_register);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('custom_macro_label', _('Custom macros'));
    $tpl->assign('template_inheritance', _('Template inheritance'));
    $tpl->assign('command_inheritance', _('Command inheritance'));
    $tpl->assign('cloneSetMacro', $cloneSetMacro);
    $tpl->assign('macros', $aMacros);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign("Freshness_Control_options", _("Freshness Control options"));
    $tpl->assign("Flapping_Options", _("Flapping options"));
    $tpl->assign("History_Options", _("History Options"));
    $tpl->assign("Event_Handler", _("Event Handler"));
    $tpl->assign("topdoc", _("Documentation"));
    $tpl->assign("seconds", _("seconds"));
    $tpl->assign("service_type", $form_service_type);

    $tpl->assign('v', $centreon->user->get_version());
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
<?php }
