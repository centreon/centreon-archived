<?php

/*
 * Copyright 2005-2022 Centreon
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

/*
 * Database retrieve information for Escalation
 */
$esc = array();
if (($o == "c" || $o == "w") && $esc_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM escalation WHERE esc_id = '" . $esc_id . "' LIMIT 1");

    # Set base value
    $esc = array_map("myEncode", $DBRESULT->fetchRow());

    # Set Host Options
    $esc["escalation_options1"] = explode(',', $esc["escalation_options1"]);
    foreach ($esc["escalation_options1"] as $key => $value) {
        $esc["escalation_options1"][trim($value)] = 1;
    }

    # Set Service Options
    $esc["escalation_options2"] = explode(',', $esc["escalation_options2"]);
    foreach ($esc["escalation_options2"] as $key => $value) {
        $esc["escalation_options2"][trim($value)] = 1;
    }
}

#
# End of "database-retrieved" information
##########################################################
##########################################################
# Var information to format the element
#
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "10");
$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
$attrsAdvSelect2 = array("style" => "width: 300px; height: 400px;");
$attrsTextarea = array("rows" => "5", "cols" => "80");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>' .
    '{unselected}</td><td align="center">{add}<br /><br /><br />' .
    '{remove}</td><td><div class="ams">{label_3}</div>{selected}' .
    '</td></tr></table>';

#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add an Escalation"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify an Escalation"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View an Escalation"));
}

#
## Escalation basic information
#
$form->addElement('header', 'information', _("Information"));
$form->addElement('text', 'esc_name', _("Escalation Name"), $attrsText);
$form->addElement('text', 'esc_alias', _("Alias"), $attrsText);
$form->addElement('text', 'first_notification', _("First Notification"), $attrsText2);
$form->addElement('text', 'last_notification', _("Last Notification"), $attrsText2);
$form->addElement('text', 'notification_interval', _("Notification Interval"), $attrsText2);

$timeAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $timeAvRoute,
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);
$form->addElement('select2', 'escalation_period', _("Escalation Period"), array(), $attrTimeperiods);


$tab = array();
$tab[] = $form->createElement('checkbox', 'd', '&nbsp;', _("Down"));
$tab[] = $form->createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
$tab[] = $form->createElement('checkbox', 'r', '&nbsp;', _("Recovery"));
$form->addGroup($tab, 'escalation_options1', _("Hosts Escalation Options"), '&nbsp;&nbsp;');

$tab = array();
$tab[] = $form->createElement('checkbox', 'w', '&nbsp;', _("Warning"));
$tab[] = $form->createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
$tab[] = $form->createElement('checkbox', 'c', '&nbsp;', _("Critical"));
$tab[] = $form->createElement('checkbox', 'r', '&nbsp;', _("Recovery"));
$form->addGroup($tab, 'escalation_options2', _("Services Escalation Options"), '&nbsp;&nbsp;');

$form->addElement('textarea', 'esc_comment', _("Comments"), $attrsTextarea);

$contactDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=defaultValues&target=escalation&field=esc_cgs&id=' . $esc_id;
$contactAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup'
    . '&action=list';
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $contactDeRoute,
    'availableDatasetRoute' => $contactAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);
$form->addElement('select2', 'esc_cgs', _("Linked Contact Groups"), array(), $attrContactgroups);

$form->addElement('checkbox', 'host_inheritance_to_services', '', _('Host inheritance to services'));
$form->addElement('checkbox', 'hostgroup_inheritance_to_services', '', _('Hostgroup inheritance to services'));

$hostDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host'
    . '&action=defaultValues&target=escalation&field=esc_hosts&id=' . $esc_id;
$hostAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $hostDeRoute,
    'availableDatasetRoute' => $hostAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$form->addElement('select2', 'esc_hosts', _("Hosts"), array(), $attrHosts);

$servDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_service'
    . '&action=defaultValues&target=escalation&field=esc_hServices&id=' . $esc_id;
$servAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list';
$attrServices = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $servDeRoute,
    'availableDatasetRoute' => $servAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonService'
);
$form->addElement('select2', 'esc_hServices', _("Services by Host"), array(), $attrServices);

$hostgDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup'
    . '&action=defaultValues&target=escalation&field=esc_hgs&id=' . $esc_id;
$hostgAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $hostgDeRoute,
    'availableDatasetRoute' => $hostgAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);
$form->addElement('select2', 'esc_hgs', _("Host Group"), array(), $attrHostgroups);

$MetaDeRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_meta'
    . '&action=defaultValues&target=escalation&field=esc_metas&id=' . $esc_id;
$MetaAvRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_meta&action=list';
$attrMetas = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $MetaDeRoute,
    'availableDatasetRoute' => $MetaAvRoute,
    'multiple' => true,
    'linkedObject' => 'centreonMeta'
);
$form->addElement('select2', 'esc_metas', _("Meta Service"), array(), $attrMetas);

$sgDefaultRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_servicegroup'
    . '&action=defaultValues&target=escalation&field=esc_sgs&id=' . $esc_id;
$sgAvailableRoute = './include/common/webServices/rest/internal.php'
    . '?object=centreon_configuration_servicegroup&action=list';
$attrServicegroups = array(
    'datasourceOrigin' => 'ajax',
    'defaultDatasetRoute' => $sgDefaultRoute,
    'availableDatasetRoute' => $sgAvailableRoute,
    'multiple' => true,
    'linkedObject' => 'centreonServicegroups'
);
$form->addElement('select2', 'esc_sgs', _("Service Group"), array(), $attrServicegroups);

$form->addElement('hidden', 'esc_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
if (isset($initialValues)) {
    $init->setValue(serialize($initialValues));
}

#
## Form Rules
#
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('esc_name', _("Compulsory Name"), 'required');
$form->addRule('first_notification', _("Required Field"), 'required');
$form->addRule('last_notification', _("Required Field"), 'required');
$form->addRule('notification_interval', _("Required Field"), 'required');
$form->addRule('esc_cgs', _("Required Field"), 'required');
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('esc_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

# Just watch an Escalation information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&esc_id=" . $esc_id . "'")
        );
    }
    $form->setDefaults($esc);
    $form->freeze();
} elseif ($o == "c") { # Modify an Escalation information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($esc);
} elseif ($o == "a") { # Add an Escalation information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('time_unit', " * " . $centreon->optGen["interval_length"] . " " . _("seconds"));

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange",'
    . ' TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300,'
    . ' SHADOW, true, TEXTALIGN, "justify"'
);
# prepare help texts
$helptext = "";
include_once("help.php");

foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    $escObj = $form->getElement('esc_id');
    if ($form->getSubmitValue("submitA")) {
        $escObj->setValue(insertEscalationInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateEscalationInDB($escObj->getValue("esc_id"));
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once("listEscalation.php");
} else {
    #Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formEscalation.ihtml");
}
