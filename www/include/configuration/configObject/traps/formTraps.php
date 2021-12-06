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

function myDecodeTrap($arg)
{
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return ($arg);
}

function myReplace()
{
    global $form;
    return (str_replace(" ", "_", $form->getSubmitValue("traps_name")));
}

$trap = array();
$initialValues = array();
$hServices = array();

$cdata = CentreonData::getInstance();
$preexecArray = array();
$mrulesArray = array();
if (($o == TRAP_MODIFY || $o == TRAP_WATCH) && is_int($trapsId)) {
    $DBRESULT = $pearDB->query("SELECT * FROM traps WHERE traps_id = '$trapsId' LIMIT 1");
    # Set base value
    $trap = array_map("myDecodeTrap", $DBRESULT->fetchRow());
    $trap['severity'] = $trap['severity_id'];
    $DBRESULT->closeCursor();

    $preexecArray = $trapObj->getPreexecFromTrapId($trapsId);
    $mrulesArray = $trapObj->getMatchingRulesFromTrapId($trapsId);
}

/*
* Preset values of preexec commands
*/
$cdata->addJsData('clone-values-preexec', htmlspecialchars(
    json_encode($preexecArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-preexec', count($preexecArray));

/*
* Preset values of matching rules
*/
$cdata->addJsData('clone-values-matchingrules', htmlspecialchars(
    json_encode($mrulesArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-matchingrules', count($mrulesArray));

$attrsText = array("size" => "50");
$attrsLongText = array("size" => "120");
$attrsTextarea = array("rows" => "10", "cols" => "120");
$attrsAdvSelect = array("style" => "width: 270px; height: 100px;");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />' .
    '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_manufacturer&action=list';
$attrManufacturer = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $route,
    'multiple' => false,
    'linkedObject' => 'centreonManufacturer'
);

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list&s=s';
$attrServices = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $route,
    'multiple' => true,
    'linkedObject' => 'centreonService'
);

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=list';
$attrServicetemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $route,
    'multiple' => true,
    'linkedObject' => 'centreonServicetemplates'
);

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$trapObj->setForm($form);
if ($o == TRAP_ADD) {
    $form->addElement('header', 'title', _("Add a Trap definition"));
} elseif ($o == TRAP_MODIFY) {
    $form->addElement('header', 'title', _("Modify a Trap definition"));
} elseif ($o == TRAP_WATCH) {
    $form->addElement('header', 'title', _("View a Trap definition"));
}

/*
 * Command information
 */
$form->addElement('text', 'traps_name', _("Trap name"), $attrsText);

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_manufacturer' .
    '&action=defaultValues&target=traps&field=manufacturer_id&id=' . $trapsId;
$attrManufacturer1 = array_merge(
    $attrManufacturer,
    array('defaultDatasetRoute' => $route)
);
$form->addElement('select2', 'manufacturer_id', _("Vendor Name"), array(), $attrManufacturer1);
$form->addElement('textarea', 'traps_comments', _("Comments"), $attrsTextarea);

$traps_mode[] = $form->createElement('radio', 'traps_mode', null, _("Unique"), '0');
$traps_mode[] = $form->createElement('radio', 'traps_mode', null, _("Regexp"), '1');
$form->addGroup($traps_mode, 'traps_mode', _("Mode"), '&nbsp;');
$form->setDefaults(array('traps_mode' => '0'));

/**
 * Generic fields
 */
$form->addElement('text', 'traps_oid', _("OID"), $attrsText);
$form->addElement(
    'select',
    'traps_status',
    _("Default Status"),
    array(0 => _("Ok"), 1 => _("Warning"), 2 => _("Critical"), 3 => _("Unknown")),
    array('id' => 'trapStatus')
);
$severities = $severityObj->getList(null, "level", 'ASC', null, null, true);
$severityArr = array(null => null);
foreach ($severities as $severity_id => $severity) {
    $severityArr[$severity_id] = $severity['sc_name'] . ' (' . $severity['level'] . ')';
}
$form->addElement('select', 'severity', _("Default Severity"), $severityArr);
$form->addElement('text', 'traps_args', _("Output Message"), $attrsText);
$form->addElement(
    'checkbox',
    'traps_advanced_treatment',
    _("Advanced matching mode"),
    null,
    array('id' => 'traps_advanced_treatment')
);
$form->setDefaults(0);

/* *******************************************************************
 * Three possibilities :    - submit result
 *                          - execute a special command
 *                          - resubmit a scheduling force
 */

/*
 * submit result
 */
$cbt = $form->addElement('checkbox', 'traps_submit_result_enable', _("Submit result"));
$form->setDefaults(array('traps_submit_result_enable' => '1'));

/*
 * Schedule svc check forced
 */
$form->addElement('checkbox', 'traps_reschedule_svc_enable', _("Reschedule associated services"));

/*
 * execute commande
 */
$form->addElement('text', 'traps_execution_command', _("Special Command"), $attrsLongText);
$form->addElement('checkbox', 'traps_execution_command_enable', _("Execute special command"));

/*
 * Further informations
 */
$form->addElement('hidden', 'traps_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$attrService1 = array_merge(
    $attrServices,
    array(
        'defaultDatasetRoute' => './api/internal.php?object=centreon_configuration_service&action=defaultValues' .
            '&target=traps&field=services&id=' . $trapsId
    )
);
$form->addElement('select2', 'services', _("Linked Services"), array(), $attrService1);

$query = './api/internal.php?object=centreon_configuration_servicetemplate&action=defaultValues' .
    '&target=traps&field=service_templates&id=' . $trapsId;
$attrServicetemplate1 = array_merge(
    $attrServicetemplates,
    array(
        'defaultDatasetRoute' => $query
    )
);
$form->addElement('select2', 'service_templates', _("Linked Service Templates"), array(), $attrServicetemplate1);

/*
 * Routing
 */
$form->addElement(
    'text',
    'traps_routing_value',
    _('Route definition'),
    $attrsLongText
);
$form->addElement(
    'text',
    'traps_routing_filter_services',
    _('Filter services'),
    $attrsLongText
);
$form->addElement('checkbox', 'traps_routing_mode', _("Enable routing"));

/*
 * Matching rules
 */
$cloneSetMaching = array();
$cloneSetMaching[] = $form->addElement(
    'text',
    'rule[#index#]',
    _("String"),
    array(
        "size" => "50",
        "id" => "rule_#index#",
        "value" => "@OUTPUT@"
    )
);
$cloneSetMaching[] = $form->addElement(
    'text',
    'regexp[#index#]',
    _("Regexp"),
    array(
        "size" => "50",
        "id" => "regexp_#index#",
        "value" => "//"
    )
);
$cloneSetMaching[] = $form->addElement(
    'select',
    'rulestatus[#index#]',
    _("Status"),
    array(
        0 => _('OK'),
        1 => _('Warning'),
        2 => _('Critical'),
        3 => _('Unknown')
    ),
    array(
        "id" => "rulestatus_#index#",
        "type" => "select-one"
    )
);
$cloneSetMaching[] = $form->addElement(
    'select',
    'ruleseverity[#index#]',
    _("Severity"),
    $severityArr,
    array(
        "id" => "ruleseverity_#index#",
        "type" => "select-one"
    )
);

$form->addElement(
    'text',
    'traps_timeout',
    _("Timeout"),
    array('size' => 5)
);

$form->addElement(
    'text',
    'traps_exec_interval',
    _('Execution interval'),
    array('size' => 5)
);

$form->addElement(
    'checkbox',
    'traps_log',
    _("Insert trap's information into database")
);

$form->addElement(
    'text',
    'traps_output_transform',
    _('Output Transform'),
    $attrsLongText
);

$form->addElement('textarea', 'traps_customcode', _("Custom code"), $attrsTextarea);

$form->addElement('select', 'traps_advanced_treatment_default', _("Advanced matching behavior"), array(
    0 => _("If no match, submit default status"),
    1 => _("If no match, disable submit"),
    2 => _("If match, disable submit")
), array('id' => 'traps_advanced_treatment'));

$excecution_type[] = $form->createElement('radio', 'traps_exec_interval_type', null, _("None"), '0');
$excecution_type[] = $form->createElement('radio', 'traps_exec_interval_type', null, _("By OID"), '1');
$excecution_type[] = $form->createElement(
    'radio',
    'traps_exec_interval_type',
    null,
    _("By OID and Host"),
    '2'
);
$excecution_type[] = $form->createElement(
    'radio',
    'traps_exec_interval_type',
    null,
    _("By OID, Host and Service"),
    '3'
);
$form->addGroup($excecution_type, 'traps_exec_interval_type', _("Execution type"), '&nbsp;');
$form->setDefaults(array('traps_exec_interval_type' => '0'));

$excecution_method[] = $form->createElement('radio', 'traps_exec_method', null, _("Parallel"), '0');
$excecution_method[] = $form->createElement('radio', 'traps_exec_method', null, _("Sequential"), '1');
$form->addGroup($excecution_method, 'traps_exec_method', _("Execution method"), '&nbsp;');
$form->setDefaults(array('traps_exec_method' => '0'));

$downtime[] = $form->createElement('radio', 'traps_downtime', null, _("None"), '0');
$downtime[] = $form->createElement('radio', 'traps_downtime', null, _("Real-Time"), '1');
$downtime[] = $form->createElement('radio', 'traps_downtime', null, _("History"), '2');
$form->addGroup($downtime, 'traps_downtime', _("Check Downtime"), '&nbsp;');
$form->setDefaults(array('traps_downtime' => '0'));

/*
 * Pre exec
 */
$cloneSet = array();
$cloneSet[] = $form->addElement(
    'text',
    'preexec[#index#]',
    _("Preexec definition"),
    array(
        "size" => "50",
        "id" => "preexec_#index#"
    )
);

/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('traps_name', 'myReplace');
$form->addRule('traps_name', _("Compulsory Name"), 'required');
$form->addRule('traps_oid', _("Compulsory Name"), 'required');
$form->addRule('manufacturer_id', _("Compulsory Name"), 'required');
$form->addRule('traps_args', _("Compulsory Name"), 'required');
$form->registerRule('exist', 'callback', [$trapObj, "testTrapExistence"]);
$form->registerRule('wellFormated', 'callback', [$trapObj, "testOidFormat"]);
$form->addRule('traps_oid', _("Bad OID Format"), 'wellFormated');
$form->addRule('traps_oid', _("The same OID element already exists"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$tpl->assign('trap_adv_args', _("Advanced matching rules"));

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", ' .
    'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, ' .
    'SHADOW, true, TEXTALIGN, "justify"'
);

/* prepare help texts */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

if ($o == TRAP_WATCH) {
    # Just watch a Command information
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&traps_id=" . $trapsId . "'")
        );
    }
    $form->setDefaults($trap);
    $form->freeze();
} elseif ($o == TRAP_MODIFY) {
    # Modify a Command information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($trap);
} elseif ($o == TRAP_ADD) {
    # Add a Command information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $trapObj = new CentreonTraps($pearDB, $centreon, $form);
    $trapParam = $form->getElement('traps_id');
    if ($form->getSubmitValue("submitA")) {
        $trapParam->setValue($trapObj->insert());
    } elseif ($form->getSubmitValue("submitC")) {
        $trapObj->update($trapParam->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path . "listTraps.php");
} else {
    /* prepare help texts */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);

    $tpl->assign('tabTitle_1', _('Main'));
    $tpl->assign('tabTitle_2', _('Relations'));
    $tpl->assign('tabTitle_3', _('Advanced'));
    $tpl->assign('subtitle0', _("Main information"));
    $tpl->assign('subtitle0', _("Convert Trap information"));
    $tpl->assign('subtitle1', _("Action 1 : Submit result to Monitoring Engine"));
    $tpl->assign('subtitle2', _("Action 2 : Force rescheduling of service check"));
    $tpl->assign('subtitle3', _("Action 3 : Execute a Command"));
    $tpl->assign('subtitle4', _("Trap description"));
    $tpl->assign('routingDefTxt', _('Route parameters'));
    $tpl->assign('resourceTxt', _('Resources'));
    $tpl->assign('preexecTxt', _('Pre execution commands'));
    $tpl->assign('serviceTxt', _('Linked services'));
    $tpl->assign('serviceTemplateTxt', _('Linked service templates'));
    $tpl->assign('admin', $centreon->user->admin);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign('cloneSet', $cloneSet);
    $tpl->assign('cloneSetMaching', $cloneSetMaching);
    $tpl->assign('preexeccmd_str', _('PREEXEC command'));
    $tpl->display("formTraps.ihtml");
}
