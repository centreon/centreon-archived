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

if (!isset($oreon)) {
    exit();
}

require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$checkboxGroup = array(
    'backup_database_full',
    'backup_database_partial'
);
$DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE options.key LIKE 'backup_%'");
while ($opt = $DBRESULT->fetchRow()) {
    if (in_array($opt["key"], $checkboxGroup)) {
        $values = explode(',', $opt["value"]);
        foreach ($values as $value) {
            $gopt[$opt["key"]][trim($value)] = 1;
        }
    } else {
        $gopt[$opt["key"]] = myDecode($opt["value"]);
    }
}
$DBRESULT->free();

$attrsText = array("size" => "40");
$attrsText2 = array("size" => "3");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);

/*
 * General Options
 */
$backupEnabled = array();
$backupEnabled[] = HTML_QuickForm::createElement('radio', 'backup_enabled', null, _("Yes"), '1');
$backupEnabled[] = HTML_QuickForm::createElement('radio', 'backup_enabled', null, _("No"), '0');
$form->addGroup($backupEnabled, 'backup_enabled', _("Backup enabled"), '&nbsp;');
$form->setDefaults(array('backup_enabled'=>'0'));
$form->addElement('text', 'backup_backup_directory', _("Backup directory"), $attrsText);
$form->addRule('backup_backup_directory', _("Mandatory field"), 'required');
$form->addElement('text', 'backup_tmp_directory', _("Temporary directory"), $attrsText);
$form->addRule('backup_tmp_directory', _("Mandatory field"), 'required');


/*
 * Database Options
 */
$form->addElement('checkbox', 'backup_database_centreon', _("Backup database centreon"));
$form->addElement('checkbox', 'backup_database_centreon_storage', _("Backup database centreon_storage"));
$backupDatabaseType = array();
$backupDatabaseType[] = HTML_QuickForm::createElement('radio', 'backup_database_type', null, _("Dump"), '0');
$backupDatabaseType[] = HTML_QuickForm::createElement('radio', 'backup_database_type', null, _("LVM Snapshot"), '1');
$form->addGroup($backupDatabaseType, 'backup_database_type', _("Backup type"), '&nbsp;');
$form->setDefaults(array('backup_database_type'=>'1'));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("Monday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '2', '&nbsp;', _("Tuesday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '3', '&nbsp;', _("Wednesday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '4', '&nbsp;', _("Thursday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '5', '&nbsp;', _("Friday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '6', '&nbsp;', _("Saturday"));
$backupDatabasePeriodFull[] = HTML_QuickForm::createElement('checkbox', '0', '&nbsp;', _("Sunday"));
$form->addGroup($backupDatabasePeriodFull, 'backup_database_full', _("Full backup"), '&nbsp;&nbsp;');
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("Monday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '2', '&nbsp;', _("Tuesday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '3', '&nbsp;', _("Wednesday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '4', '&nbsp;', _("Thursday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '5', '&nbsp;', _("Friday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '6', '&nbsp;', _("Saturday"));
$backupDatabasePeriodPartial[] = HTML_QuickForm::createElement('checkbox', '0', '&nbsp;', _("Sunday"));
$form->addGroup($backupDatabasePeriodPartial, 'backup_database_partial', _("Partial backup"), '&nbsp;&nbsp;');
$form->addElement('text', 'backup_retention', _("Backup retention"), $attrsText2);
$form->addRule('backup_retention', _("Mandatory field"), 'required');
$form->addRule('backup_retention', _('Must be a number'), 'numeric');

/*
 * Configuration Files Options
 */
$form->addElement('checkbox', 'backup_configuration_files', _("Backup configuration files"));
$form->addElement('text', 'backup_mysql_conf', _("MySQL configuration file path"), $attrsText);
$form->addElement('text', 'backup_zend_conf', _("Zend configuration file path"), $attrsText);

/*
 * Export Options
 */
$scpEnabled = array();
$scpEnabled[] = HTML_QuickForm::createElement('radio', 'backup_export_scp_enabled', null, _("Yes"), '1');
$scpEnabled[] = HTML_QuickForm::createElement('radio', 'backup_export_scp_enabled', null, _("No"), '0');
$form->addGroup($scpEnabled, 'backup_export_scp_enabled', _("SCP export enabled"), '&nbsp;');
$form->setDefaults(array('backup_export_scp_enabled'=>'0'));
$form->addElement('text', 'backup_export_scp_user', _("Remote user"), $attrsText);
$form->addElement('text', 'backup_export_scp_host', _("Remote host"), $attrsText);
$form->addElement('text', 'backup_export_scp_directory', _("Remote directory"), $attrsText);

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->applyFilter('__ALL__', 'myTrim');

$form->setDefaults($gopt);

$form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . "/backup", $tpl);

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    /*
     * Update in DB
     */
    updateBackupConfigData($pearDB, $form, $oreon);

    $o = null;
    $valid = true;
    $form->freeze();
}
if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("impossible to validate, one or more field is incorrect") . "</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=backup'", 'class' => 'btc bt_info')
);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign('valid', $valid);

$tpl->display("formBackup.html");
