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

if (!isset($oreon)) {
    exit();
}

if (!$oreon->user->admin) {
    if ($hc_id && $hcString != "''" && false === strpos($hcString, "'".$hc_id."'")) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icones/16x16/warning.gif");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this host category'));
        return null;
    }
}

/*
 * Hosts comes from DB -> Store in $hosts Array
 */
$hosts = array();
$ishost = array();
$DBRESULT = $pearDB->query("SELECT host_id, host_name
                                FROM host
                                WHERE host_register = '1'
                                ORDER BY host_name");
while ($host = $DBRESULT->fetchRow()) {
    $ishost[$host['host_id']] = $host['host_name'];
    if ($oreon->user->admin || false !== strpos($hoststring, "'".$host['host_id']."'")) {
        $hosts[$host["host_id"]] = $host["host_name"];
    }
}
$DBRESULT->free();
unset($host);

/*
 * Hosts comes from DB -> Store in $hosts Array
 */
$hostTpl = array();
$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name");
while ($host = $DBRESULT->fetchRow())
    $hostTpl[$host["host_id"]] = $host["host_name"];
$DBRESULT->free();
unset($host);

$initialValues = array();

/*
 * Database retrieve information for HostCategories
 */
$hc = array();
if (($o == "c" || $o == "w") && $hc_id)	{
    $DBRESULT = $pearDB->query("SELECT * FROM hostcategories WHERE hc_id = '".$hc_id."' LIMIT 1");
    /*
     * Set base value
     */
    $hc = array_map("myDecode", $DBRESULT->fetchRow());
    $hc['hc_severity_level'] = $hc['level'];
    $hc['hc_severity_icon'] = $hc['icon_id'];
    /*
     *  Set hostcategories Childs => Hosts
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT host_host_id FROM hostcategories_relation WHERE hostcategories_hc_id = '".$hc_id."'");
    for ($i = 0, $i2 = 0; $host = $DBRESULT->fetchRow();) {
        if (isset($ishost[$host["host_host_id"]])) {
            if (!$oreon->user->admin && false === strpos($hoststring, "'".$host['host_host_id']."'")) {
                $initialValues['hc_hosts'][] = $host['host_host_id'];
            } else {
                $hc["hc_hosts"][$i] = $host["host_host_id"];
                $i++;
            }
        }
        if (isset($hostTpl[$host["host_host_id"]])) {
            $hc["hc_hostsTemplate"][$i2] = $host["host_host_id"];
            $i2++;
        }
    }
    $DBRESULT->free();
    unset($host);
}

/*
 * hostcategories comes from DB -> Store in $hosts Array
 */
$EDITCOND = "";
if ($o == "w" || $o == "c")
    $EDITCOND = " WHERE `hc_id` != '".$hc_id."' ";

$hostCategories = array();
$DBRESULT = $pearDB->query("SELECT hc_id, hc_name FROM hostcategories $EDITCOND ORDER BY hc_name");
while ($hcs = $DBRESULT->fetchRow())
    $hostGroups[$hcs["hc_id"]] = $hcs["hc_name"];
$DBRESULT->free();
unset($hcs);

/*
 * Contact Groups comes from DB -> Store in $cgs Array
 */
$cgs = array();
$DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
while ($cg = $DBRESULT->fetchRow())
    $cgs[$cg["cg_id"]] = $cg["cg_name"];
$DBRESULT->free();
unset($cg);

/*
 * IMG comes from DB -> Store in $extImg Array
 */
$extImg = array();
$extImg = return_image_list(1);
$extImgStatusmap = array();
$extImgStatusmap = return_image_list(2);

/*
 * Define Templatse
 */
$attrsText 		= array("size"=>"30");
$attrsTextLong 	= array("size"=>"50");
$attrsAdvSelect = array("style" => "width: 220px; height: 220px;");
$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

/*
 * Create formulary
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a")
    $form->addElement('header', 'title', _("Add a host category"));
else if ($o == "c")
    $form->addElement('header', 'title', _("Modify a  host category"));
else if ($o == "w")
    $form->addElement('header', 'title', _("View a  host category"));

/*
 * Catrgorie basic information
 */
$form->addElement('header', 	'information', _("General Information"));
$form->addElement('text', 		'hc_name', _("Host Category Name"), $attrsText);
$form->addElement('text', 		'hc_alias', _("Alias"), $attrsText);

/*
 * Severity
 */
$hctype = $form->addElement('checkbox', 'hc_type', _('Severity type'), null, array('id' => 'hc_type'));
if (isset($hc_id) && isset($hc['level']) && $hc['level'] != "") {
    $hctype->setValue('1');
}
$form->addElement('text', 'hc_severity_level', _("Level"), array("size" => "10"));
$iconImgs = return_image_list(1);
$form->addElement('select', 'hc_severity_icon', _("Icon"), $iconImgs, array("id" => "icon_id",
                                                                            "onChange" => "showLogo('icon_id_ctn', this.value)",
                                                                            "onkeyup" => "this.blur(); this.focus();"));

/*
 * Hosts Selection
 */
$form->addElement('header', 'relation', _("Relations"));
$ams1 = $form->addElement('advmultiselect', 'hc_hosts', array(_("Linked Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect, SORT_ASC);
$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);

$ams1 = $form->addElement('advmultiselect', 'hc_hostsTemplate', array(_("Linked Host Template"), _("Available"), _("Selected")) , $hostTpl, $attrsAdvSelect, SORT_ASC);
$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
$ams1->setElementTemplate($eTemplate);
if (!$oreon->user->admin) {
    $ams1->setPersistantFreeze(true);
    $ams1->freeze();
}
echo $ams1->getElementJs(false);

/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('textarea', 'hc_comment', _("Comments"), $attrsTextarea);
$hcActivation[] = HTML_QuickForm::createElement('radio', 'hc_activate', null, _("Enabled"), '1');
$hcActivation[] = HTML_QuickForm::createElement('radio', 'hc_activate', null, _("Disabled"), '0');
$form->addGroup($hcActivation, 'hc_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('hc_activate' => '1'));

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
$form->setDefaults(array('action' => '1'));

$form->addElement('hidden', 'hc_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));

/*
 * Form Rules
 */
function myReplace()	{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["hc_name"]));
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('hc_name', 'myReplace');
$form->addRule('hc_name', _("Compulsory Name"), 'required');
$form->addRule('hc_alias', _("Compulsory Alias"), 'required');

$form->registerRule('exist', 'callback', 'testHostCategorieExistence');
$form->addRule('hc_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

$form->addRule('hc_severity_level', _("Must be a number"), 'numeric');

$form->registerRule('shouldNotBeEqTo0', 'callback', 'shouldNotBeEqTo0');
$form->addRule('hc_severity_level', _("Can't be equal to 0"), 'shouldNotBeEqTo0');

$form->addFormRule('checkSeverity');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

if ($o == "w")	{
    /*
     * Just watch a HostCategorie information
     */
    if ($centreon->user->access->page($p) != 2)
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hc_id=".$hc_id."'"));
    $form->setDefaults($hc);
    $form->freeze();
} else if ($o == "c")	{
    /*
     * Modify a HostCategorie information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
    $form->setDefaults($hc);
} else if ($o == "a")	{
    /*
     * Add a HostCategorie information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
}

$tpl->assign('p', $p);

$valid = false;
if ($form->validate())	{
    $hcObj = $form->getElement('hc_id');
    if ($form->getSubmitValue("submitA"))
        $hcObj->setValue(insertHostCategoriesInDB());
    else if ($form->getSubmitValue("submitC"))
        updateHostCategoriesInDB($hcObj->getValue());
    $o = NULL;
    $hcObj = $form->getElement('hc_id');
    $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hc_id=".$hcObj->getValue()."'"));
    $form->freeze();
    $valid = true;
}

$action = $form->getSubmitValue("action");
if ($valid && $action["action"]) {
    require_once($path."listHostCategories.php");
} else	{
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('topdoc', _("Documentation"));
    $tpl->display("formHostCategories.ihtml");
}
