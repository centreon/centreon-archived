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

require_once dirname(__FILE__) . "/formFunction.php";

$DBRESULT = $pearDB->query("SELECT * FROM `options`");
while ($opt = $DBRESULT->fetchRow()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}
$DBRESULT->free();

$fontList = array('Arial' => 'Arial', 'Times' => 'Times', 'Verdana' => 'Verdana');
$fontSize = array(
    '5' => '5',
    '6' => '6',
    '7' => '7',
    '8' => '8',
    '9' => '9',
    '10' => '10',
    '11' => '11',
    '12' => '12',
    '13' => '13'
);

/*
 * Var information to format the element
 */
$attrsText        = array("size"=>"40");
$attrsText2        = array("size"=>"5");
$attrSelect    = array("style" => "width: 220px;");
$attrSelect2    = array("style" => "width: 50px;");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Modify General Options"));

/*
 * Various information
 */
$form->addElement('text', 'rrdtool_path_bin', _("Directory + RRDTOOL Binary"), $attrsText);
$form->addElement('text', 'rrdtool_version', _("RRDTool Version"), $attrsText2);

/*
 * Unit
 */
$form->addElement('header', 'unit_title', _("Unit Properties"));
$form->addElement('select', 'rrdtool_unit_font', _("Font"), $fontList, $attrSelect);
$form->addElement('select', 'rrdtool_unit_fontsize', _("Font size"), $fontSize, $attrSelect2);

/*
 * Title
 */
$form->addElement('header', 'title_title', _("Title Properties"));
$form->addElement('select', 'rrdtool_title_font', _("Font"), $fontList, $attrSelect);
$form->addElement('select', 'rrdtool_title_fontsize', _("Font size"), $fontSize, $attrSelect2);

/*
 * Axis
 */
$form->addElement('header', 'axis_title', _("Axis Properties"));
$form->addElement('select', 'rrdtool_axis_font', _("Font"), $fontList, $attrSelect);
$form->addElement('select', 'rrdtool_axis_fontsize', _("Font size"), $fontSize, $attrSelect2);

/*
 * Legend
 */
$form->addElement('header', 'legend_title', _("Legend Properties"));
$form->addElement('select', 'rrdtool_legend_font', _("Font"), $fontList, $attrSelect);
$form->addElement('select', 'rrdtool_legend_fontsize', _("Font size"), $fontSize, $attrSelect2);

/*
 * Watermark
 */
$form->addElement('header', 'watermark_title', _("Watermark Properties"));
$form->addElement('select', 'rrdtool_watermark_font', _("Font"), $fontList, $attrSelect);
$form->addElement('select', 'rrdtool_watermark_fontsize', _("Font size"), $fontSize, $attrSelect2);

/*
 * Rrdcached
 */
$attrEnable = array('onclick' => 'toggleRrdcached(this)');
$form->addElement('header', 'rrdcached_title', _("Rrdcached configuration : work only with Centreon Broker"));
$rrdcachedEnable[] = HTML_QuickForm::createElement('radio', 'rrdcached_enable', null, _("Yes"), '1', $attrEnable);
$rrdcachedEnable[] = HTML_QuickForm::createElement('radio', 'rrdcached_enable', null, _("No"), '0', $attrEnable);
$form->addGroup($rrdcachedEnable, 'rrdcached_enable', _("Enable RRDCached"), '&nbsp;');
$form->addElement('text', 'rrdcached_port', _('TCP Port'), $attrsText2);
$form->addElement('text', 'rrdcached_unix_path', _('UNIX Socket path'), $attrsText);

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);



$form->applyFilter('__ALL__', 'myTrim');
$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
$form->registerRule('is_writable_path', 'callback', 'is_writable_path');

$form->registerRule('rrdcached_has_option', 'callback', 'rrdcached_has_option');
$form->registerRule('rrdcached_valid', 'callback', 'rrdcached_valid');
$form->addRule(
    array('rrdcached_enable', 'rrdcached_port', 'rrdcached_unix_path'),
    _('The rrdcached configuration must have a option.'),
    'rrdcached_has_option'
);
$form->addRule(array('rrdcached_port', 'rrdcached_unix_path'), _('Only one option must be set.'), 'rrdcached_valid');
$form->addRule('rrdcached_port', _('The port must be numeric'), 'numeric');

$form->addRule('rrdtool_path_bin', _("Can't execute binary"), 'is_executable_binary');
$form->addRule('oreon_rrdbase_path', _("Can't write in directory"), 'is_writable_path');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path.'rrdtool/', $tpl);

$version = '';
if (isset($gopt['rrdtool_path_bin']) && trim($gopt['rrdtool_path_bin']) != '') {
    $version = getRrdtoolVersion($gopt['rrdtool_path_bin']);
}

$gopt['rrdtool_version'] = $version;

$form->freeze('rrdtool_version');

if (!isset($gopt['rrdcached_enable'])) {
    $gopt['rrdcached_enable'] = '0';
}

if (version_compare('1.4.0', $version, '>')) {
    $gopt['rrdcached_enable'] = '0';
    $form->freeze('rrdcached_enable');
    $form->freeze('rrdcached_port');
    $form->freeze('rrdcached_unix_path');
}

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$valid = false;
if ($form->validate()) {
    /*
     * Update in DB
     */
    updateRRDToolConfigData($form->getSubmitValue("gopt_id"));

    /*
     * Update in Oreon Object
     */
    $oreon->initOptGen($pearDB);

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
    array("onClick"=>"javascript:window.location.href='?p=".$p."&o=rrdtool'", 'class' => 'btc bt_info')
);

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
$tpl->assign('o', $o);
$tpl->assign("genOpt_rrdtool_properties", _("RRDTool Properties"));
$tpl->assign("genOpt_rrdtool_configurations", _("RRDTool Configuration"));
$tpl->assign('valid', $valid);

$tpl->display("form.ihtml");
