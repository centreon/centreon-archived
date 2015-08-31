<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($centreon)) {
    exit();
}

$transcoKey = array(
    "enable_autologin" => "yes",
    "display_autologin_shortcut" => "yes",
    "sso_enable" => "yes",
    "enable_gmt" => "yes",
    "strict_hostParent_poller_management" => "yes"
);

$DBRESULT = $pearDB->query("SELECT * FROM `options`");
while ($opt = $DBRESULT->fetchRow()) {
  if (isset($transcoKey[$opt["key"]])) {
    $gopt[$opt["key"]][$transcoKey[$opt["key"]]] = myDecode($opt["value"]);
  } else {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
  }
}


/*
 * Style
 */
$attrsText 		= array("size"=>"40");
$attrsText2		= array("size"=>"5");
$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Modify General Options"));

/*
 * information
 */
$form->addElement('header', 'oreon', _("Centreon information"));
$form->addElement('text', 'oreon_path', _("Directory"), $attrsText);
$form->addElement('text', 'oreon_web_path', _("Centreon Web Directory"), $attrsText);

$form->addElement('text', 'oreon_refresh', _("Refresh Interval"), $attrsText2);
$form->addElement('text', 'session_expire', _("Sessions Expiration Time"), $attrsText2);

$limit = array(10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100, 200 => 200, 300 => 300, 400 => 400, 500 => 500);
$form->addElement('select', 'maxViewMonitoring', _("Limit per page for Monitoring"), $limit);

$form->addElement('text', 'maxViewConfiguration', _("Limit per page (default)"), $attrsText2);
$form->addElement('text', 'AjaxTimeReloadStatistic', _("Refresh Interval for statistics"), $attrsText2);
$form->addElement('text', 'AjaxTimeReloadMonitoring', _("Refresh Interval for monitoring"), $attrsText2);
$form->addElement('text', 'AjaxFirstTimeReloadStatistic', _("First Refresh delay for statistics"), $attrsText2);
$form->addElement('text', 'AjaxFirstTimeReloadMonitoring', _("First Refresh delay for monitoring"), $attrsText2);
$form->addElement('text', 'gmt', _("Default host timezone"), $attrsText2);

$templates = array();
if ($handle  = @opendir($oreon->optGen["oreon_path"]."www/Themes/"))	{
    while ($file = @readdir($handle)) {
        if (!is_file($oreon->optGen["oreon_path"]."www/Themes/".$file) && $file != "." && $file != ".." && $file != ".svn") {
            $templates[$file] = $file;
        }
    }
    @closedir($handle);
 }
$form->addElement('select', 'template', _("Display Template"), $templates);

$global_sort_type = array(
                        "host_name" => _("Hosts"),
                        "last_state_change" => _("Duration"),
						"service_description" => _("Services"),
						"current_state" => _("Status"),
						"last_check" => _("Last check"),
						"output" => _("Output"),
                        "criticality_id" => _("Criticality"),
                        "current_attempt" => _("Attempt"),
                    );

$sort_type = array(	"last_state_change" => _("Duration"),
                    "host_name" => _("Hosts"),
                    "service_description" => _("Services"),
                    "current_state" => _("Status"),
                    "last_check" => _("Last check"),
                    "plugin_output" => _("Output"));

$form->addElement('select', 'global_sort_type', _("Sort by  "), $global_sort_type);
$global_sort_order = array("ASC" => _("Ascending"), "DESC" => _("Descending"));

$form->addElement('select', 'global_sort_order', _("Order sort "), $global_sort_order);

$form->addElement('select', 'problem_sort_type', _("Sort problems by"), $sort_type);

$sort_order = array("ASC" => _("Ascending"), "DESC" => _("Descending"));
$form->addElement('select', 'problem_sort_order', _("Order sort problems"), $sort_order);

$options1[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options1, 'enable_autologin', _("Enable Autologin"), '&nbsp;&nbsp;');

$options2[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options2, 'display_autologin_shortcut', _("Display Autologin shortcut"), '&nbsp;&nbsp;');

/*
 * SSO
 */
$sso_enable[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '', array("onchange" => "javascript:confirm('Are you sure you want to change this parameter ? Please read the help before.')"));
$form->addGroup($sso_enable, 'sso_enable', _("Enable SSO authentication"), '&nbsp;&nbsp;');

$sso_mode = array();
$sso_mode[] = HTML_QuickForm::createElement('radio', 'sso_mode', null, _("SSO only"), '0');
$sso_mode[] = HTML_QuickForm::createElement('radio', 'sso_mode', null, _("Mixed"), '1');
$form->addGroup($sso_mode, 'sso_mode', _("SSO mode"), '&nbsp;');
$form->setDefaults(array('sso_mode'=>'1'));

$form->addElement('text', 'sso_trusted_clients', _('SSO trusted client addresses'), array('size' => 50));

$form->addElement('text', 'sso_header_username', _('SSO login header'), array('size' => 30));
$form->setDefaults(array('sso_header_username'=>'HTTP_AUTH_USER'));

$options3[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options3, 'enable_gmt', _("Enable Timezone management"), '&nbsp;&nbsp;');

$options4[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options4, 'strict_hostParent_poller_management', _("Enable strict mode for host parentship management"), '&nbsp;&nbsp;');

/*
 * Support Email
 */
$form->addElement('text', 'centreon_support_email', _("Centreon Support Email"), $attrsText);

/*
 * Form Rules
 */
function slash($elem = NULL)	{
    if ($elem) {
        return rtrim($elem, "/")."/";
    }
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('nagios_path', 'slash');
$form->applyFilter('nagios_path_img', 'slash');
$form->applyFilter('nagios_path_plugins', 'slash');
$form->applyFilter('oreon_path', 'slash');
$form->applyFilter('oreon_web_path', 'slash');
$form->applyFilter('debug_path', 'slash');
$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
$form->addRule('nagios_path_plugins', _("Can't write in directory"), 'is_writable_path');
$form->addRule('nagios_path_img', _("Can't write in directory"), 'is_writable_path');
$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path.'general/', $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"));
$form->addElement('reset', 'reset', _("Reset"));

$valid = false;
if ($form->validate()) {
    /*
     * Update in DB
     */
    updateGeneralConfigData(1);
    
    /*
     * Update in Oreon Object
     */
    $oreon->initOptGen($pearDB);
    
    $o = NULL;
    $valid = true;
    $form->freeze();
 }
if (!$form->validate() && isset($_POST["gopt_id"]))	{
    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
 }

$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

/*
 * Send variable to template
 */

$tpl->assign('o', $o);
$tpl->assign("sorting", _("Sorting"));
$tpl->assign("genOpt_max_page_size", _("Maximum page size"));
$tpl->assign("genOpt_expiration_properties", _("Sessions Properties"));
$tpl->assign("time_min", _("minutes"));
$tpl->assign("genOpt_refresh_properties", _("Refresh Properties"));
$tpl->assign("time_sec", _("seconds"));
$tpl->assign("genOpt_display_options", _("Display Options"));
$tpl->assign("genOpt_global_display", _("Display properties"));
$tpl->assign("genOpt_problem_display", _("Problem display properties"));
$tpl->assign("genOpt_time_zone", _("Time Zone"));
$tpl->assign("genOpt_auth", _("Authentification properties"));
$tpl->assign("configBehavior", _("Configuration UI behavior"));
$tpl->assign("support", _("Support Information"));
$tpl->assign('valid', $valid);

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$tpl->display("form.ihtml");

?>
