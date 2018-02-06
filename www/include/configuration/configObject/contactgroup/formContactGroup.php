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

if (!$centreon->user->admin && $cg_id) {
    $aclOptions = array('fields'     => array('cg_id','cg_name'),
                        'keys'       => array('cg_id'),
                        'get_row'    => 'cg_name',
                        'conditions' => array('cg_id' => $cg_id));
    $cgs = $acl->getContactGroupAclConf($aclOptions);
    if (!count($cgs)) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this contact group'));
        return null;
    }
}
        
$initialValues = array();
        
/*
 * Database retrieve information for Contact
 */
$cg = array();
if (($o == "c" || $o == "w") && $cg_id) {
    /*
     * Get host Group information
     */
    $DBRESULT = $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");

    /*
     * Set base value
     */
    $cg = array_map("myDecode", $DBRESULT->fetchRow());

    /*
     * Set Contact Childs
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT `contact_contact_id` FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'");
    for ($i = 0; $contacts = $DBRESULT->fetchRow(); $i++) {
        if (!$centreon->user->admin && !isset($allowedContacts[$contacts['contact_contact_id']])) {
            $initialValues['cg_contacts'][] = $contacts["contact_contact_id"];
        } else {
            $cg["cg_contacts"][$i] = $contacts["contact_contact_id"];
        }
    }
    $DBRESULT->free();

    /*
     * Get acl group
     */
    $sql = "SELECT acl_group_id 
        FROM acl_group_contactgroups_relations 
        WHERE cg_cg_id = " .$pearDB->escape($cg_id);
    $res = $pearDB->query($sql);
    for ($i = 0; $aclgroup = $res->fetchRow(); $i++) {
        if (!$centreon->user->admin && !isset($allowedAclGroups[$aclgroup['acl_group_id']])) {
            $initialValues['cg_acl_groups'][] = $aclgroup["acl_group_id"];
        } else {
            $cg["cg_acl_groups"][$i] = $aclgroup["acl_group_id"];
        }
    }
}

/*
 * Contacts comes from DB -> Store in $contacts Array
 */
$contacts = array();
$DBRESULT = $pearDB->query("SELECT DISTINCT `contact_id`, `contact_name`, `contact_register` 
                                FROM `contact` ".
                               $acl->queryBuilder('WHERE', 'contact_id', $contactstring).
                               " ORDER BY `contact_name`");
while ($contact = $DBRESULT->fetchRow()) {
    $contacts[$contact["contact_id"]] = $contact["contact_name"];
    if ($contact['contact_register'] == 0) {
        $contacts[$contact["contact_id"]] .= "(Template)";
    }
}
unset($contact);
$DBRESULT->free();

$aclgroups = array();
$aclCondition = "";
if (!$centreon->user->admin) {
    $aclCondition = " WHERE acl_group_id IN (".$acl->getAccessGroupsString().") ";
}
$sql = "SELECT acl_group_id, acl_group_name
    FROM acl_groups
    {$aclCondition}
    ORDER BY acl_group_name";
$res = $pearDB->query($sql);
while ($aclg = $res->fetchRow()) {
    $aclgroups[$aclg['acl_group_id']] = $aclg['acl_group_name'];
}

$attrsText      = array("size"=>"30");
$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
$attrsTextarea  = array("rows"=>"5", "cols"=>"60");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrContacts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonContact'
);
$attrAclgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonAclGroup'
);

/*
 * form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Contact Group"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Contact Group"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Contact Group"));
}

/*
 * Contact basic information
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'cg_name', _("Contact Group Name"), $attrsText);
$form->addElement('text', 'cg_alias', _("Alias"), $attrsText);

/*
 * Contacts Selection
 */
$form->addElement('header', 'notification', _("Relations"));

$attrContact1 = array_merge(
    $attrContacts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=defaultValues&target=contactgroup&field=cg_contacts&id=' . $cg_id)
);
$form->addElement('select2', 'cg_contacts', _("Linked Contacts"), array(), $attrContact1);


/*
 * Acl group selection
 */
$attrAclgroup1 = array_merge(
    $attrAclgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_administration_aclgroup&action=defaultValues&target=contactgroup&field=cg_acl_groups&id=' . $cg_id)
);
$form->addElement('select2', 'cg_acl_groups', _("Linked ACL groups"), array(), $attrAclgroup1);


/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$cgActivation[] = HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Enabled"), '1');
$cgActivation[] = HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Disabled"), '0');
$form->addGroup($cgActivation, 'cg_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('cg_activate' => '1'));
$form->addElement('textarea', 'cg_comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'cg_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

    $init = $form->addElement('hidden', 'initialValues');
    $init->setValue(serialize($initialValues));
    
/*
 * Set rules
 */
$form->applyFilter('__ALL__', 'myTrim');
//$form->applyFilter('cg_name', 'myReplace');
$form->addRule('cg_name', _("Compulsory Name"), 'required');
$form->addRule('cg_alias', _("Compulsory Alias"), 'required');

if (!$centreon->user->admin) {
    $form->addRule('cg_acl_groups', _('Compulsory field'), 'required');
}

$form->registerRule('exist', 'callback', 'testContactGroupExistence');
$form->addRule('cg_name', _("Name is already in use"), 'exist');
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
     * Just watch a Contact Group information
     */
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
    }
    $form->setDefaults($cg);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a Contact Group information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($cg);
} elseif ($o == "a") {
    /*
     * Add a Contact Group information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $cgObj = $form->getElement('cg_id');

    if ($form->getSubmitValue("submitA")) {
        $cgObj->setValue(insertContactGroupInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateContactGroupInDB($cgObj->getValue());
    }

    $o = null;
    $valid = true;
}
if ($valid) {
    require_once($path."listContactGroup.php");
} else {
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formContactGroup.ihtml");
}
