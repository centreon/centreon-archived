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

if (isset($_POST["o"]) && $_POST["o"]) {
    $o = $_POST["o"];
}

/*
 * Get data into config table of centstorage
 */
$DBRESULT = $pearDBO->query("SELECT * FROM `config` LIMIT 1");
$gopt = array_map("myDecode", $DBRESULT->fetchRow());

/*
 * Get centstorage state
 */
$DBRESULT2 = $pearDB->query("SELECT * FROM `options` WHERE `key` LIKE 'centstorage%'");
while ($data = $DBRESULT2->fetchRow()) {
    if (isset($data['value']) && $data['key'] == "centstorage") {
        $gopt["enable_centstorage"] = $data['value'];
    } else {
        $gopt[$data['key']] = $data['value'];
    }
}

/*
 * Get insert_data state
 */
$DBRESULT2 = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'index_data'");
while ($data = $DBRESULT2->fetchRow()) {
    if (isset($data['value']) && $data['key'] == "index_data") {
        if ($data['value'] == "1") {
            $gopt["insert_in_index_data"] = "0";
        } elseif ($data['value'] == "0") {
            $gopt["insert_in_index_data"] = "1";
        } else {
            $gopt["insert_in_index_data"] = "1";
        }
    }
}

/*
 * Format of text input
 */
$attrsText        = array("size"=>"40");
$attrsText2        = array("size"=>"5");
$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Modify General Options"));

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->setDefaults($gopt);

/*
 * Header information
 */
$form->addElement('header', 'enable', _("Engine Status"));
$form->addElement('header', 'insert', _("Resources storage"));
$form->addElement('header', 'folder', _("Storage folders"));
$form->addElement('header', 'retention', _("Retention durations"));
$form->addElement('header', 'Input', _("Input treatment options"));
$form->addElement('header', 'reporting', _("Dashboard Integration Properties"));
$form->addElement('header', 'audit', _("Audit log activation"));

/*
 * inputs declaration
 */
$form->addElement('text', 'RRDdatabase_path', _("Path to RRDTool Database For Metrics"), $attrsText);
$form->addElement('text', 'RRDdatabase_status_path', _("Path to RRDTool Database For Status"), $attrsText);
$form->addElement(
    'text',
    'RRDdatabase_nagios_stats_path',
    _("Path to RRDTool Database For Monitoring Engine Statistics"),
    $attrsText
);
$form->addElement('text', 'len_storage_rrd', _("RRDTool database size"), $attrsText2);
$form->addElement('text', 'len_storage_mysql', _("Retention Duration for Data in MySQL"), $attrsText2);
$form->addElement('text', 'len_storage_downtimes', _("Retention Duration for Downtimes"), $attrsText2);
$form->addElement('text', 'len_storage_comments', _("Retention Duration for Comments"), $attrsText2);
$form->addElement('text', 'archive_retention', _("Logs retention duration"), $attrsText2);
$form->addElement('text', 'reporting_retention', _("Reporting retention duration (dashboard)"), $attrsText2);
$form->addElement('checkbox', 'audit_log_option', _("Enable/Disable audit logs"));

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);



$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('RRDdatabase_path', 'slash');
$form->applyFilter('RRDdatabase_status_path', 'slash');
$form->applyFilter('RRDdatabase_nagios_stats_path', 'slash');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path.'centstorage/', $tpl);
$form->setDefaults($gopt);
$centreon->initOptGen($pearDB);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
$valid = false;

if ($form->validate()) {
    /*
     * Update in DB
     */
    updateODSConfigData();

    $centreon->initOptGen($pearDB);

    $o = null;
    $valid = true;
    $form->freeze();
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick"=>"javascript:window.location.href='?p=".$p."&o=storage'", 'class' => 'btc bt_info')
);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);

$tpl->assign("ods_log_retention_unit", _("days"));

/*
 * prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('valid', $valid);
$tpl->assign('o', $o);

$tpl->display("form.ihtml");
