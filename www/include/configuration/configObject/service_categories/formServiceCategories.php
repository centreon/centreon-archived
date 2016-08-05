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

if (!$oreon->user->admin) {
    if ($sc_id && $scString != "''" && false === strpos($scString, "'".$sc_id."'")) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this service category'));
        return null;
    }
}

/*
 * Database retrieve information for Contact
 */
$cct = array();
if (($o == "c" || $o == "w") && $sc_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '".$sc_id."' LIMIT 1");
    /*
     * Set base value
     */
    $sc = array_map("myDecode", $DBRESULT->fetchRow());
    $DBRESULT->free();
    $sc['sc_severity_level'] = $sc['level'];
    $sc['sc_severity_icon'] = $sc['icon_id'];

    $sc["sc_svc"] = array();
}

/*
 * Get Service Template Available
 */
$hServices = array();
$DBRESULT = $pearDB->query("SELECT service_alias, service_description, service_id FROM service WHERE service_register = '0' ORDER BY service_alias, service_description");
while ($elem = $DBRESULT->fetchRow()) {
    $elem["service_description"] = str_replace('#S#', "/", $elem["service_description"]);
    $elem["service_description"] = str_replace('#BS#', "\\", $elem["service_description"]);
    $elem["service_alias"] = str_replace('#S#', "/", $elem["service_alias"]);
    $elem["service_alias"] = str_replace('#BS#', "\\", $elem["service_alias"]);
    $hServicesTpl[$elem["service_id"]] = $elem["service_alias"] . " (".$elem["service_description"].")";
}
$DBRESULT->free();

/*
 * Define Template
 */
$attrsText      = array("size"=>"30");
$attrsText2     = array("size"=>"60");
$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
$attrsTextarea  = array("rows"=>"5", "cols"=>"40");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrServicetemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonServicetemplates'
);

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Service Category"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Service Category"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Service Category"));
}

/*
 * Contact basic information
 */
$form->addElement('header', 'information', _("Information"));
$form->addElement('header', 'links', _("Relations"));

/*
 * No possibility to change name and alias, because there's no interest
 */
$form->addElement('text', 'sc_name', _("Name"), $attrsText);
$form->addElement('text', 'sc_description', _("Description"), $attrsText);

/*
 * Severity
 */
$sctype = $form->addElement('checkbox', 'sc_type', _('Severity type'), null, array('id' => 'sc_type'));
if (isset($sc_id) && isset($sc['level']) && $sc['level'] != "") {
    $sctype->setValue('1');
}
$form->addElement('text', 'sc_severity_level', _("Level"), array("size" => "10"));
$iconImgs = return_image_list(1);
$form->addElement('select', 'sc_severity_icon', _("Icon"), $iconImgs, array(
                                                                            "id" => "icon_id",
                                                                            "onChange" => "showLogo('icon_id_ctn', this.value)",
                                                                            "onkeyup" => "this.blur(); this.focus();"));

$attrServicetemplate1 = array_merge(
    $attrServicetemplates,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=defaultValues&target=servicecategories&field=sc_svcTpl&id=' . $sc_id)
);

$form->addElement('select2', 'sc_svcTpl', _("Linked Templates"), array(), $attrServicetemplate1);

$sc_activate[] = HTML_QuickForm::createElement('radio', 'sc_activate', null, _("Enabled"), '1');
$sc_activate[] = HTML_QuickForm::createElement('radio', 'sc_activate', null, _("Disabled"), '0');
$form->addGroup($sc_activate, 'sc_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('sc_activate' => '1'));

$form->addElement('hidden', 'sc_id');
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

/*
 * Form Rules
 */
function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["contact_name"]));
}

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('contact_name', 'myReplace');
$from_list_menu = false;

$form->addRule('sc_name', _("Compulsory Name"), 'required');
$form->addRule('sc_description', _("Compulsory Alias"), 'required');

$form->registerRule('existName', 'callback', 'testServiceCategorieExistence');
$form->addRule('sc_name', _("Name is already in use"), 'existName');

$form->addRule('sc_severity_level', _("Must be a number"), 'numeric');

$form->registerRule('shouldNotBeEqTo0', 'callback', 'shouldNotBeEqTo0');
$form->addRule('sc_severity_level', _("Can't be equal to 0"), 'shouldNotBeEqTo0');

$form->addFormRule('checkSeverity');

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
     * Just watch a service_categories information
     */
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sc_id=".$sc_id."'"));
    }
    $form->setDefaults($sc);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a service_categories information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($sc);
} elseif ($o == "a") {
    /*
     * Add a service_categories information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate() && $from_list_menu == false) {
    $cctObj = $form->getElement('sc_id');
    if ($form->getSubmitValue("submitA")) {
        $cctObj->setValue(insertServiceCategorieInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateServiceCategorieInDB($cctObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path."listServiceCategories.php");
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
    $tpl->assign('p', $p);
    $tpl->display("formServiceCategories.ihtml");
}
