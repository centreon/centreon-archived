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

/* Get the list of contact */
/* notification contacts */
$notifCs = $acl->getContactAclConf(array('fields'     => array('contact_id', 'contact_name'),
                                         'get_row'    => 'contact_name',
                                         'keys'       => array('contact_id'),
                                         'conditions' => array('contact_register' => '1'),
                                         'order'      => array('contact_name')));

/* notification contact groups */
$notifCgs = array();
$cg = new CentreonContactgroup($pearDB);
if ($oreon->user->admin) {
    $notifCgs = $cg->getListContactgroup(true);
} else {
    $cgAcl = $acl->getContactGroupAclConf(array('fields'  => array('cg_id', 'cg_name'),
                                                                   'get_row' => 'cg_name',
                                                                   'keys'    => array('cg_id'),                                                                                                                                                        'order'   => array('cg_name')));
    $cgLdap = $cg->getListContactgroup(true, true);
    $notifCgs = array_intersect_key($cgLdap, $cgAcl);
}

$initialValues = array();
$ms = array();
if (($o == "c" || $o == "w") && $meta_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."' LIMIT 1");
    // Set base value
    $ms = array_map("myDecode", $DBRESULT->fetchRow());

    // Set Service Notification Options
    $tmp = explode(',', $ms["notification_options"]);
    foreach ($tmp as $key => $value) {
        $ms["ms_notifOpts"][trim($value)] = 1;
    }
    
    /*
	 * Set Contacts
	 */
    $DBRESULT = $pearDB->query("SELECT DISTINCT contact_id FROM meta_contact WHERE meta_id = " . $meta_id);
    for ($i = 0; $notifC = $DBRESULT->fetchRow(); $i++) {
        if (!isset($notifCs[$notifC['contact_id']])) {
            $initialValues['ms_cs'][] = $notifC['contact_id'];
        } else {
            $ms["ms_cs"][$i] = $notifC["contact_id"];
        }
    }
    $DBRESULT->free();

    /*
	 * Set Contact Group
	 */
    $DBRESULT = $pearDB->query("SELECT DISTINCT cg_cg_id FROM meta_contactgroup_relation WHERE meta_id = '".$meta_id."'");
    for ($i = 0; $notifCg = $DBRESULT->fetchRow(); $i++) {
        if (!$oreon->user->admin && !isset($notifCgs[$notifCg['cg_cg_id']])) {
            $initialValues['ms_cgs'][] = $notifCg["cg_cg_id"];
        } else {
            $ms["ms_cgs"][$i] = $notifCg["cg_cg_id"];
        }
    }
    $DBRESULT->free();
}

require_once("./class/centreonDB.class.php");
$pearDBO = new CentreonDB("centstorage");

$metrics = array(null=>null);
$DBRESULT = $pearDBO->query("select DISTINCT metric_name from metrics ORDER BY metric_name");
while ($metric = $DBRESULT->fetchRow()) {
    $metrics[$metric["metric_name"]] = $metric["metric_name"];
}
$DBRESULT->free();

/*
 * Timeperiods comes from DB -> Store in $tps Array
 */
$DBRESULT = $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
while ($tp = $DBRESULT->fetchRow()) {
    $tps[$tp["tp_id"]] = $tp["tp_name"];
}
$DBRESULT->free();

/*
 * Check commands comes from DB -> Store in $checkCmds Array
 */
$checkCmds = array(null=>null);
$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
while ($checkCmd = $DBRESULT->fetchRow()) {
    $checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$DBRESULT->free();

/*
 * Escalations comes from DB -> Store in $escs Array
 */
$escs = array();
$DBRESULT = $pearDB->query("SELECT esc_id, esc_name FROM escalation ORDER BY esc_name");
while ($esc = $DBRESULT->fetchRow()) {
    $escs[$esc["esc_id"]] = $esc["esc_name"];
}
$DBRESULT->free();

/*
 * Meta Service Dependencies comes from DB -> Store in $deps Array
 */
$deps = array();
$DBRESULT = $pearDB->query("SELECT meta_id, meta_name FROM meta_service WHERE meta_id != '".$meta_id."' ORDER BY meta_name");
while ($dep = $DBRESULT->fetchRow()) {
    $deps[$dep["meta_id"]] = $dep["meta_name"];
}
$DBRESULT->free();

/*
 * Calc Type
 */
$calType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));

/*
 * Data source type
 */
$dsType = array(0 => "GAUGE", 1 => "COUNTER", 2 => "DERIVE", 3 => "ABSOLUTE");

/*
 * Graphs Template comes from DB -> Store in $graphTpls Array
 */
$graphTpls = array(null=>null);
$DBRESULT = $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
while ($graphTpl = $DBRESULT->fetchRow()) {
    $graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
}
$DBRESULT->free();

/*
 * Init Styles
 */
$attrsText      = array("size"=>"30");
$attrsText2         = array("size"=>"6");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea  = array("rows"=>"5", "cols"=>"40");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrTimeperiods = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=list',
    'multiple' => false,
    'linkedObject' => 'centreonTimeperiod'
);
$attrContacts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonContact'
);
$attrContactgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonContactgroup'
);

#
## Form begin
#
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Meta Service"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Meta Service"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Meta Service"));
}

/*
 * Service basic information
 */
$form->addElement('header', 'information', _("General Information"));

$form->addElement('text', 'meta_name', _("Name"), $attrsText);
$form->addElement('text', 'meta_display', _("Output format string (printf-style)"), $attrsText);
$form->addElement('text', 'warning', _("Warning Level"), $attrsText2);
$form->addElement('text', 'critical', _("Critical Level"), $attrsText2);
$form->addElement('select', 'calcul_type', _("Calculation Type"), $calType);
$form->addElement('select', 'data_source_type', _('Data Source Type'), $dsType);

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'meta_select_mode', null, _("Service List"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'meta_select_mode', null, _("SQL matching"), '2');
$form->addGroup($tab, 'meta_select_mode', _("Selection Mode"), '<br />');
$form->setDefaults(array('meta_select_mode' => array('meta_select_mode'=>'1')));

$form->addElement('text', 'regexp_str', _("SQL LIKE-clause expression"), $attrsText);
$form->addElement('select', 'metric', _("Metric"), $metrics);

/*
 * Check information
 */
$form->addElement('header', 'check', _("Meta Service State"));

$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=meta&field=check_period&id=' . $meta_id)
);
$form->addElement('select2', 'check_period', _("Check Period"), array(), $attrTimeperiod1);

$form->addElement('text', 'max_check_attempts', _("Max Check Attempts"), $attrsText2);
$form->addElement('text', 'normal_check_interval', _("Normal Check Interval"), $attrsText2);
$form->addElement('text', 'retry_check_interval', _("Retry Check Interval"), $attrsText2);

/*
 * Notification informations
 */
$form->addElement('header', 'notification', _("Notification"));
$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("Yes"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("No"), '0');
$tab[] = HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("Default"), '2');
$form->addGroup($tab, 'notifications_enabled', _("Notification Enabled"), '&nbsp;');
$form->setDefaults(array('notifications_enabled' => '2'));

/*
 *  Contacts
 */
$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=defaultValues&target=meta&field=ms_cs&id=' . $meta_id)
);
$form->addElement('select2', 'ms_cs', _("Implied Contacts"), array(), $attrContact1);

$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=defaultValues&target=meta&field=ms_cgs&id=' . $meta_id)
);
$form->addElement('select2', 'ms_cgs', _("Linked Contact Groups"), array(), $attrContactgroup1);

$form->addElement('text', 'notification_interval', _("Notification Interval"), $attrsText2);

$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=meta&field=notification_period&id=' . $meta_id)
);
$form->addElement('select2', 'notification_period', _("Notification Period"), array(), $attrTimeperiod2);

$msNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"));
$msNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
$msNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"));
$msNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', _("Recovery"));
$msNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', _("Flapping"));

$form->addGroup($msNotifOpt, 'ms_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('select', 'graph_id', _("Graph Template"), $graphTpls);
$msActivation[] = HTML_QuickForm::createElement('radio', 'meta_activate', null, _("Enabled"), '1');
$msActivation[] = HTML_QuickForm::createElement('radio', 'meta_activate', null, _("Disabled"), '0');
$form->addGroup($msActivation, 'meta_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('meta_activate' => '1'));
$form->addElement('textarea', 'meta_comment', _("Comments"), $attrsTextarea);

$form->addElement('text', 'geo_coords', _("Geo coordinates"), $attrsText);

$form->addElement('hidden', 'meta_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));

/*
 * Form Rules
 */
function myReplace()
{
    global $form;
    return (str_replace(" ", "_", $form->getSubmitValue("meta_name")));
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('meta_name', 'myReplace');
$form->addRule('meta_name', _("Compulsory Name"), 'required');
$form->addRule('max_check_attempts', _("Required Field"), 'required');
$form->addRule('calcul_type', _("Required Field"), 'required');
$form->addRule('meta_select_mode', _("Required Field"), 'required');
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('meta_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');
# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

if ($o == "w") {
    /*
	 * Just watch a host information
	 */
    if (!$min && $centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&meta_id=".$meta_id."'"));
    }
    $form->setDefaults($ms);
    $form->freeze();
} elseif ($o == "c") {
    /*
	 * Modify a service information
	 */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($ms);
} elseif ($o == "a") {
    /*
	 * Add a service information
	 */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version()));
$tpl->assign('time_unit', " * ".$oreon->optGen["interval_length"]." "._("seconds"));

$valid = false;
if ($form->validate()) {
    $msObj = $form->getElement('meta_id');
    if ($form->getSubmitValue("submitA")) {
        $msObj->setValue(insertMetaServiceInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateMetaServiceInDB($msObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path."listMetaService.php");
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
    $tpl->display("formMetaService.ihtml");
}
