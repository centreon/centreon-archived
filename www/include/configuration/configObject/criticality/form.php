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

/**
 * Test whether or not criticality already exists
 * 
 * @param bool
 */
function testCriticalityExistence($name) {
    global $pearDB, $form;
    
    $critId = $form->getSubmitValue('criticality_id');    
    $sql = "SELECT COUNT(criticality_id) as nb 
            FROM criticality 
            WHERE name = '".$pearDB->escape($name)."' ";
    if ($critId) {
        $sql .= "AND criticality_id != ".$pearDB->escape($critId);
    }
    $res = $pearDB->query($sql);
    $row = $res->fetchRow();
    if ($row['nb']) {
        return false;
    }
    return true;
}

/*
 * Database retrieve information for HostCategories
 */
$hc = array();
$critInfo = array();
if (($o == "c" || $o == "w") && $critId) {
    $critInfo = $criticality->getData($critId);
}

/*
 * Define Templatse
 */
$attrsText = array("size" => "30");
$attrsTextShort = array("size" => "10");
$attrsTextLong = array("size" => "50");
$attrsAdvSelect = array("style" => "width: 220px; height: 220px;");
$attrsTextarea = array("rows" => "4", "cols" => "60");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

/*
 * Create form
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Criticality Level"));

/*
 * Main info
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'name', _("Name"), $attrsText);
$form->addElement('text', 'level', _("Level"), $attrsTextShort);

$iconImgs = array();
$iconImgs = return_image_list(1);
$form->addElement('select', 'icon_id', _("Icon"), $iconImgs, array("id" => "icon_id",
                                                                   "onChange" => "showLogo('icon_id_ctn', this.value)",
                                                                   "onkeyup" => "this.blur(); this.focus();"));
$form->addElement('textarea', 'comments', _("Comments"), $attrsTextarea);

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
$form->setDefaults(array('action' => '1'));

$form->addElement('hidden', 'criticality_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$compulsoryFieldTxt = _("Compulsory field");
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('name', $compulsoryFieldTxt, 'required');
$form->addRule('level', $compulsoryFieldTxt, 'required');
$form->addRule('level', _('Level must be a number'), 'regex', '#\d+#');
$form->addRule('icon_id', $compulsoryFieldTxt, 'required');
$form->registerRule('exist', 'callback', 'testCriticalityExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>" . _(" Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == "w") {
    $form->addElement("button", "change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&hc_id=" . $hc_id . "'"));
    $form->setDefaults($critInfo);
    $form->freeze();
} elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
    $form->setDefaults($critInfo);
} elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
}

$tpl->assign('p', $p);

$valid = false;
if ($form->validate()) {
    $cObj = $form->getElement('criticality_id');
    if ($form->getSubmitValue("submitA")) {
        $cObj->setValue($criticality->insert($form->getSubmitValues()));
    } elseif ($form->getSubmitValue("submitC")) {
        $criticality->update($cObj->getValue(), $form->getSubmitValues());
    }
    $o = NULL;
    $cObj = $form->getElement('criticality_id');
    $form->addElement("button", "change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&crit_id=" . $cObj->getValue() . "'"));
    $form->freeze();
    $valid = true;
}

$action = $form->getSubmitValue("action");
if ($valid && $action["action"]) {
    require_once($path . "list.php");
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
    $tpl->assign('topdoc', _("Documentation"));
    $helptext = "";
    include_once("help.php");
    foreach ($help as $key => $text) {
        $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
    }
    $tpl->assign("helptext", $helptext);
    $tpl->display("form.ihtml");
?>
<script type='text/javascript' src='./include/common/javascript/showLogo.js'></script>
<script type='text/javascript'>
    showLogo('icon_id_ctn', document.getElementById('icon_id').value);
</script>
<?php
}
?>