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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

$hostObj = new CentreonHost($pearDB);

#
## Database retrieve information for Host
#
$host = array();
$macroArray = array();
if (($o == "c" || $o == "w") && $host_id) {
    if (isset($lockedElements[$host_id])) {
        $o = "w";
    }
    $DBRESULT = $pearDB->query("SELECT * FROM host, extended_host_information ehi WHERE host_id = '".$host_id."' AND ehi.host_host_id = host.host_id LIMIT 1");

    
    # Set base value
    if ($DBRESULT->numRows()) {
        $host = array_map("myDecode", $DBRESULT->fetchRow());
        $cmdId = $host['command_command_id'];
        # Set Host Notification Options
        $tmp = explode(',', $host["host_notification_options"]);
        foreach ($tmp as $key => $value) {
            $host["host_notifOpts"][trim($value)] = 1;
        }

        # Set Stalking Options
        $tmp = explode(',', $host["host_stalking_options"]);
        foreach ($tmp as $key => $value) {
            $host["host_stalOpts"][trim($value)] = 1;
        }
        $DBRESULT->free();

        # Set Contact Group
        $DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_host_relation WHERE host_host_id = '".$host_id."'");
        for ($i = 0; $notifCg = $DBRESULT->fetchRow(); $i++) {
            $host["host_cgs"][$i] = $notifCg["contactgroup_cg_id"];
        }
        $DBRESULT->free();

        /*
		 * Set Contacts
		 */
        $DBRESULT = $pearDB->query("SELECT DISTINCT contact_id FROM contact_host_relation WHERE host_host_id = '".$host_id."'");
        for ($i = 0; $notifC = $DBRESULT->fetchRow(); $i++) {
            $host["host_cs"][$i] = $notifC["contact_id"];
        }
        $DBRESULT->free();

        # Set Host Parents
        $DBRESULT = $pearDB->query("SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation WHERE host_host_id = '".$host_id."'");
        for ($i = 0; $parent = $DBRESULT->fetchRow(); $i++) {
            $host["host_parents"][$i] = $parent["host_parent_hp_id"];
        }
        $DBRESULT->free();

        # Set Service Templates Childs
        $DBRESULT = $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$host_id."'");
        for ($i = 0; $svTpl = $DBRESULT->fetchRow(); $i++) {
            $DBRESULT2 = $pearDB->query("SELECT service_register FROM service WHERE service_id = '".$svTpl['service_service_id']."' LIMIT 1");
            $tpReg = $DBRESULT2->fetchRow();
            if ($tpReg['service_register'] == 0) {
                $host["host_svTpls"][$i] = $svTpl["service_service_id"];
            }
        }
        $DBRESULT->free();

        /*
         * Set Host Category Parents
         */
        $DBRESULT = $pearDB->query('SELECT DISTINCT hostcategories_hc_id 
            FROM hostcategories_relation hcr, hostcategories hc
            WHERE hcr.hostcategories_hc_id = hc.hc_id
            AND hc.level IS NULL
            AND hcr.host_host_id = \''.$host_id.'\'');
        for ($i = 0; $hc = $DBRESULT->fetchRow(); $i++) {
            $host["host_hcs"][$i] = $hc['hostcategories_hc_id'];
        }
        $DBRESULT->free();
                    
        /*
         * Set criticality
         */
        $res = $pearDB->query("SELECT hc.hc_id 
            FROM hostcategories hc, hostcategories_relation hcr
            WHERE hcr.host_host_id = " . $pearDB->escape($host_id). "
            AND hcr.hostcategories_hc_id = hc.hc_id
            AND hc.level IS NOT NULL
            ORDER BY hc.level ASC
            LIMIT 1");
        if ($res->numRows()) {
            $cr = $res->fetchRow();
            $host['criticality_id'] = $cr['hc_id'];
        }
    }
    
    /*
     * Preset values of macros
     */
    $aTemplates = $hostObj->getTemplateChain($host_id, array(), -1);

    if (!isset($cmdId)) {
        $cmdId = "";
    }

    $macroArray = $hostObj->getMacros($host_id, true, $aTemplates, $cmdId, $_POST);
}


$cdata = CentreonData::getInstance();

$cdata->addJsData('clone-values-macro', htmlspecialchars(
    json_encode($macroArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-macro', count($macroArray));
/*
 * Preset values of host templates
 */
$tplArray = $hostObj->getTemplates(isset($host_id) ? $host_id : null);
$cdata->addJsData('clone-values-template', htmlspecialchars(
    json_encode($tplArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-template', count($tplArray));
    
#
## Database retrieve information for differents elements list we need on the page
#

/*
 * Get all Templates who use himself
 */
$host_tmplt_who_use_me = array();
if (isset($_GET["host_id"]) && $_GET["host_id"]) {
    $DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_template_model_htm_id = '".$pearDB->escape($_GET["host_id"])."'");
    while ($host_tmpl_father = $DBRESULT->fetchRow()) {
        $host_tmplt_who_use_me[$host_tmpl_father["host_id"]] = $host_tmpl_father["host_name"];
    }
    $DBRESULT->free();
}

/*
 * Host Templates comes from DB -> Store in $hTpls Array
 */
$hTpls = array(null=>null);
$DBRESULT = $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM host WHERE host_register = '0' AND host_id != '".$host_id."' ORDER BY host_name");
while ($hTpl = $DBRESULT->fetchRow()) {
    if (!$hTpl["host_name"]) {
        $hTpl["host_name"] = getMyHostName($hTpl["host_template_model_htm_id"])."'";
    }
    if (!isset($host_tmplt_who_use_me[$hTpl["host_id"]])) {
        $hTpls[$hTpl["host_id"]] = $hTpl["host_name"];
    }
}
$DBRESULT->free();
# Service Templates comes from DB -> Store in $svTpls Array
$svTpls = array();
$DBRESULT = $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service WHERE service_register = '0' ORDER BY service_description");
while ($svTpl = $DBRESULT->fetchRow()) {
    if (!$svTpl["service_description"]) {
        $svTpl["service_description"] = getMyServiceName($svTpl["service_template_model_stm_id"])."'";
    } else {
        $svTpl["service_description"] = str_replace('#S#', "/", $svTpl["service_description"]);
        $svTpl["service_description"] = str_replace('#BS#', "\\", $svTpl["service_description"]);
    }
    $svTpls[$svTpl["service_id"]] = $svTpl["service_description"];
}
$DBRESULT->free();

# Timeperiods comes from DB -> Store in $tps Array
$tps = array(null=>null);
$DBRESULT = $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
while ($tp = $DBRESULT->fetchRow()) {
    $tps[$tp["tp_id"]] = $tp["tp_name"];
}
$DBRESULT->free();

# Check commands comes from DB -> Store in $checkCmds Array
$checkCmds = array(null=>null);
$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
while ($checkCmd = $DBRESULT->fetchRow()) {
    $checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$DBRESULT->free();

# Check commands comes from DB -> Store in $checkCmdEvent Array
$checkCmdEvent = array(null => null);
$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' OR command_type = '3' ORDER BY command_name");
while ($checkCmd = $DBRESULT->fetchRow()) {
    $checkCmdEvent[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$DBRESULT->free();

# Contact Groups comes from DB -> Store in $notifCcts Array
$notifCgs = array();
$cg = new CentreonContactgroup($pearDB);
$notifCgs = $cg->getListContactgroup(true);

/*
* Contacts come from DB -> Store in $notifCs Array
*/
$notifCs = array();
$DBRESULT = $pearDB->query("SELECT contact_id, contact_name FROM contact WHERE contact_register = 1 ORDER BY contact_name");
while ($notifC = $DBRESULT->fetchRow()) {
    $notifCs[$notifC["contact_id"]] = $notifC["contact_name"];
}
$DBRESULT->free();

/*
* Host Categories comes from DB -> Store in $hcs Array
*/
$hcs = array();
$DBRESULT = $pearDB->query("SELECT hc_id, hc_name FROM hostcategories WHERE level IS NULL ORDER BY hc_name");
while ($hc = $DBRESULT->fetchRow()) {
    $hcs[$hc["hc_id"]] = $hc["hc_name"];
}
$DBRESULT->free();

/*
*  Host multiple templates relations stored in DB
*/
$mTp = array();
$k = 0;
$DBRESULT = $pearDB->query("SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '". $host_id ."' ORDER BY `order`");
while ($multiTp = $DBRESULT->fetchRow()) {
    $mTp[$k] = $multiTp["host_tpl_id"];
    $k++;
}
$DBRESULT->free();

/*
*  Host on demand macro stored in DB
*/
$j = 0;
$DBRESULT = $pearDB->query("SELECT host_macro_id, host_macro_name, host_macro_value, host_host_id FROM on_demand_macro_host WHERE host_host_id = '". $host_id ."' ORDER BY `host_macro_id`");
while ($od_macro = $DBRESULT->fetchRow()) {
    $od_macro_id[$j] = $od_macro["host_macro_id"];
    $od_macro_name[$j] = str_replace("\$_HOST", "", $od_macro["host_macro_name"]);
    $od_macro_name[$j] = str_replace("\$", "", $od_macro_name[$j]);
    $od_macro_name[$j] = str_replace("#BS#", "\\", $od_macro_name[$j]);
    $od_macro_name[$j] = str_replace("#S#", "/", $od_macro_name[$j]);
    $od_macro_value[$j] = str_replace("#BS#", "\\", $od_macro["host_macro_value"]);
    $od_macro_value[$j] = str_replace("#S#", "/", $od_macro_value[$j]);
    $od_macro_host_id[$j] = $od_macro["host_host_id"];
    $j++;
}
$DBRESULT->free();

# IMG comes from DB -> Store in $extImg Array
$extImg = array();
$extImg = return_image_list(1);
$extImgStatusmap = array();
$extImgStatusmap = return_image_list(2);
#
# End of "database-retrieved" information
##########################################################
##########################################################
# Var information to format the element
#
$attrsText      = array("size"=>"30");
$attrsText2         = array("size"=>"6");
$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
$attrsAdvSelect2 = array("style" => "width: 300px; height: 200px;");
$attrsTextarea  = array("rows" => "4", "cols" => "80");
$advancedSelectTemplate     = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
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
$attrServices = array(
'datasourceOrigin' => 'ajax',
'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=list',
'multiple' => true,
'linkedObject' => 'centreonServicetemplates'
);
$attrHostcategories = array(
'datasourceOrigin' => 'ajax',
'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostcategory&action=list',
'multiple' => true,
'linkedObject' => 'centreonHostcategories'
);
$attrCommands = array(
'datasourceOrigin' => 'ajax',
'multiple' => false,
'linkedObject' => 'centreonCommand'
);

/*
 * For a shitty reason, Quickform set checkbox with stal[o] name
 */ 
unset($_POST['o']);
#
## Form begin
#
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Host Template"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Host Template"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Host Template"));
} elseif ($o == "mc") {
    $form->addElement('header', 'title', _("Massive Change"));
}

## Sort 1 - Host Template Configuration
#
## Host basic information
#
$form->addElement('header', 'information', _("General Information"));
# No possibility to change name and alias, because there's no interest
if ($o != "mc") {
    $form->addElement('text', 'host_name', _("Name"), $attrsText);
    $form->addElement('text', 'host_alias', _("Alias"), $attrsText);
}
$form->addElement('text', 'host_address', _("IP Address / DNS"), $attrsText);
$form->addElement('select', 'host_snmp_version', _("Version"), array(null=>null, 1=>"1", "2c"=>"2c", 3=>"3"));
$form->addElement('text', 'host_snmp_community', _("SNMP Community"), $attrsText);

$attrTimezones = array(
'datasourceOrigin' => 'ajax',
'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=list',
'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=defaultValues&target=host&field=host_location&id=' . $host_id,
'multiple' => false,
'linkedObject' => 'centreonGMT'
);
$form->addElement('select2', 'host_location', _("Timezone / Location"), array(), $attrTimezones);

$form->addElement('text', 'host_parallel_template', _("Templates"), $hTpls);

if ($o == "mc") {
    $mc_mod_tplp = array();
    $mc_mod_tplp[] = HTML_QuickForm::createElement('radio', 'mc_mod_tplp', null, _("Incremental"), '0');
    $mc_mod_tplp[] = HTML_QuickForm::createElement('radio', 'mc_mod_tplp', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_tplp, 'mc_mod_tplp', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_tplp'=>'0'));
}

?>
<script type="text/javascript" src="lib/wz_tooltip/wz_tooltip.js"></script>
<?php
$form->addElement('static', 'tplTextParallel', _("A host can have multiple templates, their orders have a significant importance")."<br><a href='#' onmouseover=\"Tip('<img src=\'img/misc/multiple-templates2.png\'>', OPACITY, 70)\" onmouseout=\"UnTip()\">"._("Here is a self-explanatory image.")."</a>");
$form->addElement('static', 'tplText', _("Using a Template allows you to have multi-level Template connection"));

$cloneSetMacro = array();
$cloneSetMacro[] = $form->addElement(
    'text',
    'macroInput[#index#]',
    _('Macro name'),
    array(
            'id' => 'macroInput_#index#',
            'size' => 25
        )
);
$cloneSetMacro[] = $form->addElement(
    'text',
    'macroValue[#index#]',
    _('Macro value'),
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

$cloneSetTemplate = array();
$cloneSetTemplate[] = $form->addElement(
    'select',
    'tpSelect[#index#]',
    _("Template"),
    (array(null => null) + $hostObj->getList(false, true)),
    array(
            "id" => "tpSelect_#index#",
            "type" => "select-one"
            )
);
$cloneSetMacro[] = $form->addElement(
    'hidden',
    'macroFrom[#index#]',
    'direct',
    array('id' => 'macroFrom_#index#')
);
    
/*
 * Check information
 */
$form->addElement('header', 'check', _("Host Check Properties"));


$attrCommand1 = array_merge(
    $attrCommands,
    array(
        'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=defaultValues&target=host&field=command_command_id&id=' . $host_id,
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list&t=2'
    )
);
$checkCommandSelect = $form->addElement('select2', 'command_command_id', _("Check Command"), array(), $attrCommand1);
$checkCommandSelect->addJsCallback('change', 'setArgument(jQuery(this).closest("form").get(0),"command_command_id","example1");');

$form->addElement('text', 'command_command_id_arg1', _("Args"), $attrsText);

$form->addElement('text', 'host_max_check_attempts', _("Max Check Attempts"), $attrsText2);

$hostEHE[] = HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, _("Yes"), '1');
$hostEHE[] = HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, _("No"), '0');
$hostEHE[] = HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, _("Default"), '2');
$form->addGroup($hostEHE, 'host_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_event_handler_enabled' => '2'));
}

$attrCommand2 = array_merge(
    $attrCommands,
    array(
            'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=list',
            'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_command&action=defaultValues&target=host&field=command_command_id2&id=' . $host_id
        )
);
$eventHandlerSelect = $form->addElement('select2', 'command_command_id2', _("Event Handler"), array(), $attrCommand2);
$eventHandlerSelect->addJsCallback('change', 'setArgument(jQuery(this).closest("form").get(0),"command_command_id2","example2");');

$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsText);
$form->addElement('text', 'host_check_interval', _("Normal Check Interval"), $attrsText2);
$form->addElement('text', 'host_retry_check_interval', _("Retry Check Interval"), $attrsText2);

$hostACE[] = HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, _("Yes"), '1');
$hostACE[] = HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, _("No"), '0');
$hostACE[] = HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, _("Default"), '2');
$form->addGroup($hostACE, 'host_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_active_checks_enabled' => '2'));
}

$hostPCE[] = HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, _("Yes"), '1');
$hostPCE[] = HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, _("No"), '0');
$hostPCE[] = HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, _("Default"), '2');
$form->addGroup($hostPCE, 'host_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_passive_checks_enabled' => '2'));
}

$attrTimeperiod1 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=host&field=timeperiod_tp_id&id=' . $host_id)
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
$hostNE[] = HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, _("Yes"), '1');
$hostNE[] = HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, _("No"), '0');
$hostNE[] = HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, _("Default"), '2');
$form->addGroup($hostNE, 'host_notifications_enabled', _("Notification Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_notifications_enabled' => '2'));
}

/*
 * Additive
 */
if ($o == "mc") {
    $contactAdditive[] = HTML_QuickForm::createElement('radio', 'mc_contact_additive_inheritance', null, _("Yes"), '1');
    $contactAdditive[] = HTML_QuickForm::createElement('radio', 'mc_contact_additive_inheritance', null, _("No"), '0');
    $contactAdditive[] = HTML_QuickForm::createElement('radio', 'mc_contact_additive_inheritance', null, _("Default"), '2');
    $form->addGroup($contactAdditive, 'mc_contact_additive_inheritance', _("Contact additive inheritance"), '&nbsp;');
    
    $contactGroupAdditive[] = HTML_QuickForm::createElement('radio', 'mc_cg_additive_inheritance', null, _("Yes"), '1');
    $contactGroupAdditive[] = HTML_QuickForm::createElement('radio', 'mc_cg_additive_inheritance', null, _("No"), '0');
    $contactGroupAdditive[] = HTML_QuickForm::createElement('radio', 'mc_cg_additive_inheritance', null, _("Default"), '2');
    $form->addGroup($contactGroupAdditive, 'mc_cg_additive_inheritance', _("Contact group additive inheritance"), '&nbsp;');
} else {
    $form->addElement('checkbox', 'contact_additive_inheritance', '', _('Contact additive inheritance'));
    $form->addElement('checkbox', 'cg_additive_inheritance', '', _('Contact group additive inheritance'));
}
if ($o == "mc") {
    $mc_mod_notifopt_first_notification_delay = array();
    $mc_mod_notifopt_first_notification_delay[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_first_notification_delay', null, _("Incremental"), '0');
    $mc_mod_notifopt_first_notification_delay[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_first_notification_delay', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopt_first_notification_delay, 'mc_mod_notifopt_first_notification_delay', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopt_first_notification_delay'=>'0'));
}

$form->addElement('text', 'host_first_notification_delay', _("First notification delay"), $attrsText2);

$form->addElement('text', 'host_recovery_notification_delay', _("Recovery notification delay"), $attrsText2);

if ($o == "mc") {
    $mc_mod_hcg = array();
    $mc_mod_hcg[] = HTML_QuickForm::createElement('radio', 'mc_mod_hcg', null, _("Incremental"), '0');
    $mc_mod_hcg[] = HTML_QuickForm::createElement('radio', 'mc_mod_hcg', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hcg, 'mc_mod_hcg', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hcg'=>'0'));
}

/*
 *  Contacts
 */
$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=defaultValues&target=host&field=host_cs&id=' . $host_id)
);
$form->addElement('select2', 'host_cs', _("Linked Contacts"), array(), $attrContact1);


/*
 *  Contact groups
 */
$attrContactgroup1 = array_merge(
    $attrContactgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=defaultValues&target=host&field=host_cgs&id=' . $host_id)
);
$form->addElement('select2', 'host_cgs', _("Linked Contact Groups"), array(), $attrContactgroup1);

/*
 * Categories
 */
if ($o == "mc") {
    $mc_mod_hhc = array();
    $mc_mod_hhc[] = HTML_QuickForm::createElement('radio', 'mc_mod_hhc', null, _("Incremental"), '0');
    $mc_mod_hhc[] = HTML_QuickForm::createElement('radio', 'mc_mod_hhc', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_hhc, 'mc_mod_hhc', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_hhc'=>'0'));
}

$attrHostcategory1 = array_merge(
    $attrHostcategories,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostcategory&action=defaultValues&target=host&field=host_hcs&id=' . $host_id)
);
$form->addElement('select2', 'host_hcs', _("Parent Host Categories"), array(), $attrHostcategory1);

if ($o == "mc") {
    $mc_mod_notifopt_notification_interval = array();
    $mc_mod_notifopt_notification_interval[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_notification_interval', null, _("Incremental"), '0');
    $mc_mod_notifopt_notification_interval[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_notification_interval', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopt_notification_interval, 'mc_mod_notifopt_notification_interval', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopt_notification_interval'=>'0'));
}

$form->addElement('text', 'host_notification_interval', _("Notification Interval"), $attrsText2);

if ($o == "mc") {
    $mc_mod_notifopt_timeperiod = array();
    $mc_mod_notifopt_timeperiod[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_timeperiod', null, _("Incremental"), '0');
    $mc_mod_notifopt_timeperiod[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopt_timeperiod', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopt_timeperiod, 'mc_mod_notifopt_timeperiod', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopt_timeperiod'=>'0'));
}

$attrTimeperiod2 = array_merge(
    $attrTimeperiods,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&action=defaultValues&target=host&field=timeperiod_tp_id2&id=' . $host_id)
);
$form->addElement('select2', 'timeperiod_tp_id2', _("Notification Period"), array(), $attrTimeperiod2);

if ($o == "mc") {
    $mc_mod_notifopts = array();
    $mc_mod_notifopts[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopts', null, _("Incremental"), '0');
    $mc_mod_notifopts[] = &HTML_QuickForm::createElement('radio', 'mc_mod_notifopts', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_notifopts, 'mc_mod_notifopts', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_notifopts'=>'0'));
}

$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"), array('id' => 'notifD', 'onClick' => 'uncheckNotifOption(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"), array('id' => 'notifU', 'onClick' => 'uncheckNotifOption(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', _("Recovery"), array('id' => 'notifR', 'onClick' => 'uncheckNotifOption(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', _("Flapping"), array('id' => 'notifF', 'onClick' => 'uncheckNotifOption(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 's', '&nbsp;', _("Downtime Scheduled"), array('id' => 'notifDS', 'onClick' => 'uncheckNotifOption(this);'));
$hostNotifOpt[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'notifN', 'onClick' => 'uncheckNotifOption(this);'));
$form->addGroup($hostNotifOpt, 'host_notifOpts', _("Notification Options"), '&nbsp;&nbsp;');

$hostStalOpt[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Up"));
$hostStalOpt[] = HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"));
$hostStalOpt[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
$form->addGroup($hostStalOpt, 'host_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

#
## Further informations
#
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$hostActivation[] = HTML_QuickForm::createElement('radio', 'host_activate', null, _("Enabled"), '1');
$hostActivation[] = HTML_QuickForm::createElement('radio', 'host_activate', null, _("Disabled"), '0');
$form->addGroup($hostActivation, 'host_activate', _("Status"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_activate' => '1'));
}

$form->addElement('textarea', 'host_comment', _("Comments"), $attrsTextarea);

#
## Sort 2 - Host Relations
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

$form->addElement('header', 'links', _("Relations"));
if ($o == "mc") {
    $mc_mod_htpl = array();
    $mc_mod_htpl[] = HTML_QuickForm::createElement('radio', 'mc_mod_htpl', null, _("Incremental"), '0');
    $mc_mod_htpl[] = HTML_QuickForm::createElement('radio', 'mc_mod_htpl', null, _("Replacement"), '1');
    $form->addGroup($mc_mod_htpl, 'mc_mod_htpl', _("Update mode"), '&nbsp;');
    $form->setDefaults(array('mc_mod_htpl'=>'0'));
}

$attrService1 = array_merge(
    $attrServices,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=defaultValues&target=host&field=host_svTpls&id=' . $host_id)
);
$form->addElement('select2', 'host_svTpls', _("Linked Service Templates"), array(), $attrService1);

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

$hostOOH[] = HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, _("Yes"), '1');
$hostOOH[] = HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, _("No"), '0');
$hostOOH[] = HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, _("Default"), '2');
$form->addGroup($hostOOH, 'host_obsess_over_host', _("Obsess Over Host"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_obsess_over_host' => '2'));
}

$hostCF[] = HTML_QuickForm::createElement('radio', 'host_check_freshness', null, _("Yes"), '1');
$hostCF[] = HTML_QuickForm::createElement('radio', 'host_check_freshness', null, _("No"), '0');
$hostCF[] = HTML_QuickForm::createElement('radio', 'host_check_freshness', null, _("Default"), '2');
$form->addGroup($hostCF, 'host_check_freshness', _("Check Freshness"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_check_freshness' => '2'));
}

$hostFDE[] = HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, _("Yes"), '1');
$hostFDE[] = HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, _("No"), '0');
$hostFDE[] = HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, _("Default"), '2');
$form->addGroup($hostFDE, 'host_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_flap_detection_enabled' => '2'));
}

$form->addElement('text', 'host_freshness_threshold', _("Freshness Threshold"), $attrsText2);
$form->addElement('text', 'host_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
$form->addElement('text', 'host_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

$hostPPD[] = HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, _("Yes"), '1');
$hostPPD[] = HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, _("No"), '0');
$hostPPD[] = HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, _("Default"), '2');
$form->addGroup($hostPPD, 'host_process_perf_data', _("Process Perf Data"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_process_perf_data' => '2'));
}

$hostRSI[] = HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, _("Yes"), '1');
$hostRSI[] = HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, _("No"), '0');
$hostRSI[] = HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, _("Default"), '2');
$form->addGroup($hostRSI, 'host_retain_status_information', _("Retain Status Information"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_retain_status_information' => '2'));
}

$hostRNI[] = HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, _("Yes"), '1');
$hostRNI[] = HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, _("No"), '0');
$hostRNI[] = HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, _("Default"), '2');
$form->addGroup($hostRNI, 'host_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
if ($o != "mc") {
    $form->setDefaults(array('host_retain_nonstatus_information' => '2'));
}

#
## Sort 4 - Extended Infos
#
if ($o == "a") {
    $form->addElement('header', 'title4', _("Add a Host Extended Info"));
} elseif ($o == "c") {
    $form->addElement('header', 'title4', _("Modify a Host Extended Info"));
} elseif ($o == "w") {
    $form->addElement('header', 'title4', _("View a Host Extended Info"));
}

$form->addElement('header', 'nagios', _("Monitoring engine"));
$form->addElement('text', 'ehi_notes', _("Notes"), $attrsText);
$form->addElement('text', 'ehi_notes_url', _("URL"), $attrsText);
$form->addElement('text', 'ehi_action_url', _("Action URL"), $attrsText);
$form->addElement('select', 'ehi_icon_image', _("Icon"), $extImg, array("id"=>"ehi_icon_image", "onChange"=>"showLogo('ehi_icon_image_img',this.value)", "onkeyup" => "this.blur();this.focus();"));
$form->addElement('text', 'ehi_icon_image_alt', _("Alt icon"), $attrsText);
$form->addElement('select', 'ehi_vrml_image', _("VRML Image"), $extImg, array("id"=>"ehi_vrml_image", "onChange"=>"showLogo('ehi_vrml_image_img',this.value)", "onkeyup" => "this.blur();this.focus();"));
$form->addElement('select', 'ehi_statusmap_image', _("Status Map Image"), $extImgStatusmap, array("id"=>"ehi_statusmap_image", "onChange"=>"showLogo('ehi_statusmap_image_img',this.value)", "onkeyup" => "this.blur();this.focus();"));
$form->addElement('text', 'ehi_2d_coords', _("2d Coords"), $attrsText2);
$form->addElement('text', 'ehi_3d_coords', _("3d Coords"), $attrsText2);

/*
 * Criticality 
 */
$criticality = new CentreonCriticality($pearDB);
$critList = $criticality->getList();
$criticalityIds = array(null => null);
foreach ($critList as $critId => $critData) {
    $criticalityIds[$critId] = $critData['hc_name'].' ('.$critData['level'].')';
}
$form->addElement('select', 'criticality_id', _('Severity level'), $criticalityIds);

$form->addElement('header', 'oreon', _("Centreon"));

#
## Sort 5 - Macros - NAGIOS 3
#

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
$form->addElement('text', 'macroName', _("Macro name"), $attrsText2);
$form->addElement('text', 'macroValue', _("Macro value"), $attrsText2);
$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);

$form->addElement('hidden', 'host_id');
$reg = $form->addElement('hidden', 'host_register');
$reg->setValue("0");
$host_register = 0;
$assoc = $form->addElement('hidden', 'dupSvTplAssoc');
$assoc->setValue("0");
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);
if (is_array($select)) {
    $select_str = null;
    foreach ($select as $key => $value) {
        $select_str .= $key.",";
    }
    $select_pear = $form->addElement('hidden', 'select');
    $select_pear->setValue($select_str);
}

#
## Form Rules
#
function myReplace()
{
    global $form;
    return (str_replace(" ", "_", $form->getSubmitValue("host_name")));
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('host_name', 'myReplace');
$form->registerRule('existTemplate', 'callback', 'testHostTplExistence');
$form->registerRule('exist', 'callback', 'testHostExistence');
$form->addRule('host_name', _("Template name is already in use"), 'existTemplate');
$form->addRule('host_name', _("Host name is already in use"), 'exist');
$form->addRule('host_name', _("Compulsory Name"), 'required');
$form->addRule('host_alias', _("Compulsory Alias"), 'required');
$form->registerRule('cg_group_exists', 'callback', 'testCg');
$form->addRule('host_cgs', _('Contactgroups exists. If you try to use a LDAP contactgroup, please verified if a Centreon contactgroup has the same name.'), 'cg_group_exists');
$from_list_menu = false;
if ($o == "mc") {
    if ($form->getSubmitValue("submitMC")) {
        $from_list_menu = false;
    } else {
        $from_list_menu = true;
    }
}

#
##End of form definition
#

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path2, $tpl);

# Just watch a host information
if ($o == "w") {
    if (!$min && $centreon->user->access->page($p) != 2 && !isset($lockedElements[$host_id])) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&host_id=".$host_id."'"));
    }
    $form->setDefaults($host);
    $form->freeze();
} # Modify a host information
elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($host);
} # Add a host information
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} # Massive Change
elseif ($o == "mc") {
    $subMC = $form->addElement('submit', 'submitMC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('msg', array ("nagios" => $centreon->user->get_version(), "tpl"=>1, "min"=>$min));
$tpl->assign('min', $min);
$tpl->assign('p', $p);
$tpl->assign("sort1", _("Host Configuration"));
$tpl->assign("sort2", _("Notification"));
$tpl->assign("sort3", _("Relations"));
$tpl->assign("sort4", _("Data Processing"));
$tpl->assign("sort5", _("Host Extended Infos"));
$tpl->assign("initJS", "<script type='text/javascript'>
						jQuery(function () {
						init();
						initAutoComplete('Form','city_name','sub');
						});</script>");
$tpl->assign('javascript', '
        <script type="text/javascript" src="./include/common/javascript/showLogo.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/macroPasswordField.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/macroLoadDescription.js"></script>
    ');
$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');

# prepare help texts
$helptext = "";
include_once("include/configuration/configObject/host/help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$tpl->assign('time_unit', " * ".$centreon->optGen["interval_length"]." "._("seconds"));

$valid = false;
if ($form->validate() && $from_list_menu == false) {
    $hostObj = $form->getElement('host_id');
    if ($form->getSubmitValue("submitA")) {
        $hostObj->setValue(insertHostInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateHostInDB($hostObj->getValue());
    } elseif ($form->getSubmitValue("submitMC")) {
        $select = explode(",", $select);
        foreach ($select as $key => $value) {
            if ($value) {
                updateHostInDB($value, true);
            }
        }
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path."listHostTemplateModel.php");
} else {
    #Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);

    $tpl->assign('custom_macro_label', _('Custom macros'));
    $tpl->assign('template_inheritance', _('Template inheritance'));
    $tpl->assign('command_inheritance', _('Command inheritance'));
    $tpl->assign('cloneSetMacro', $cloneSetMacro);
    $tpl->assign('cloneSetTemplate', $cloneSetTemplate);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    $tpl->assign("Freshness_Control_options", _("Freshness Control options"));
    $tpl->assign("Flapping_Options", _("Flapping options"));
    $tpl->assign("Perfdata_Options", _("Perfdata Options"));
    $tpl->assign("History_Options", _("History Options"));
    $tpl->assign("Event_Handler", _("Event Handler"));
    $tpl->assign("add_mtp_label", _("Add a template"));
    $tpl->assign("seconds", _("seconds"));
    $tpl->assign("tpl", 1);
    $tpl->display("formHost.ihtml");

?>
<script type="text/javascript">
        showLogo('ehi_icon_image_img', document.getElementById('ehi_icon_image').value);
        showLogo('ehi_vrml_image_img', document.getElementById('ehi_vrml_image').value);
        showLogo('ehi_statusmap_image_img', document.getElementById('ehi_statusmap_image').value);
        
        function uncheckNotifOption(object)
        {
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
<?php }
