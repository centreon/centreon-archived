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

/* 
 * Initiate Objets
 */
$obj = new CentreonForm($path, $p);

$initialValues = array();
        
/*
 * Database retrieve information for ContactGroup
 */
$cg = array();
if (($o == "c" || $o == "w") && $cg_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");
    $cg = array_map("myDecode", $DBRESULT->fetchRow());
}

/*
 * form begin
 */
if ($o == "a") {
    $obj->addHeader('title', _("Add a Contact Group"));
} elseif ($o == "c") {
    $obj->addHeader('title', _("Modify a Contact Group"));
} elseif ($o == "w") {
    $obj->addHeader('title', _("View a Contact Group"));
}

/*
 * Contact basic information
 */
$obj->addHeader('information', _("General Information"));
$obj->addInputText('cg_name', _("Contact Group Name"));
$obj->addInputText('cg_alias', _("Alias"));

/*
 * Contacts Selection
 */
$obj->addHeader('notification', _("Relations"));
$obj->addSelect2('cg_contacts', _("Linked Contacts"), 'contact', array('object' => 'centreon_configuration_contact', 'action' => 'defaultValues', 'target' => 'contactgroup', 'field' => 'cg_contacts', 'id' => $cg_id));
$obj->addSelect2('cg_acl_groups', _("Linked ACL groups"), 'aclgroup', array('object' => 'centreon_administration_aclgroup', 'action' => 'defaultValues', 'target' => 'contactgroup', 'field' => 'cg_acl_groups', 'id' => $cg_id));

/*
 * Further informations
 */
$obj->addHeader('furtherInfos', _("Additional Information"));
$obj->addRadioButton('cg_activate', _("Status"), array(0 => _("Disabled"), 1 => _("Enabled")), 1);
$obj->addInputTextarea('cg_comment', _("Comments"));
$obj->addHidden('hidden', 'cg_id');
$obj->addHidden('hidden', 'o', $o);
$obj->addHidden('hidden', 'initialValues', serialize($initialValues));
    
/*
 * Set rules
 */
$obj->registerRule('exist', 'callback', 'testContactGroupExistence');

$obj->addRule('cg_name', _("Name is already in use"), 'exist');
$obj->addRule('cg_name', _("Compulsory Name"), 'required');
$obj->addRule('cg_alias', _("Compulsory Alias"), 'required');

if (!$centreon->user->admin) {
    $obj->addRule('cg_acl_groups', _('Compulsory field'), 'required');
}

if ($o == "w") {
    /*
     * Just watch a Contact Group information
     */
    if ($centreon->user->access->page($p) != 2) {
        $obj->addSubmitButton("change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
    }
    $obj->setDefaults($cg);
    $obj->freeze();
} elseif ($o == "c") {
    /*
     * Modify a Contact Group information
     */
    $obj->addSubmitButton('submitC', _("Save"));
    $obj->addResetButton('reset', _("Reset"));
    $obj->setDefaults($cg);
} elseif ($o == "a") {
    /*
     * Add a Contact Group information
     */
    $obj->addSubmitButton('submitA', _("Save"));
    $obj->addResetButton('reset', _("Reset"));
}

$valid = false;
if ($obj->validate()) {
    $cgObj = $obj->getElement('cg_id');

    if ($obj->getSubmitValue("submitA")) {
        $cgObj->setValue(insertContactGroupInDB());
    } elseif ($obj->getSubmitValue("submitC")) {
        updateContactGroupInDB($cgObj->getValue());
    }

    $o = null;
    $valid = true;
}
if ($valid) {
    require_once $path."listContactGroup.php";
} else {
    $obj->display("formContactGroup.ihtml");
}
