<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

/*
 * Check ACL
 */
if (!$centreon->user->admin && $contactId) {
    $aclOptions = array(
        'fields' => array('contact_id', 'contact_name'),
        'keys' => array('contact_id'),
        'get_row' => 'contact_name',
        'conditions' => array('contact_id' => $contactId)
    );
    $contacts = $acl->getContactAclConf($aclOptions);
    if (!count($contacts)) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to reach this page'));
        return null;
    }
}

/*
 * Retrieve data and Check if this server is a Remote Server
 */
$result = [];
$dbResult = $pearDB->query("SELECT * FROM `informations`");
while ($row = $dbResult->fetch(\PDO::FETCH_ASSOC)) {
    $result[$row['key']] = $row['value'];
}

if ('yes' !== $result['isRemote']) {
    $msg = new CentreonMsg();
    $msg->setImage("./img/icons/warning.png");
    $msg->setTextStyle("bold");
    $msg->setText(_('You are not allowed to reach this page'));
    return null;
}
$dbResult->closeCursor();

/**
 * form
 */
$attrsText = array("size" => "40");
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Remote access"));
$form->addElement('header', 'information', _("Central's API credentials"));
$form->addElement(
    'text',
    'apiUsername',
    _("Username"),
    ["size" => "40"]
);
$form->addRule('apiUsername', _("Required Field"), 'required');

$form->addElement(
    'password',
    'apiCredentials',
    _("Password"),
    ["size" => "40", "autocomplete" => "new-password", "id" => "passwd1", "onFocus" => "resetPwdType(this);"]
);
$form->addRule('apiCredentials', _("Required Field"), 'required');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

// default values
$form->setDefaults(
    [
        'apiUsername' => $result['apiUsername'],
        'apiCredentials' => CentreonAuth::PWS_OCCULTATION
    ]
);

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$tpl = new Smarty();
$tpl = initSmartyTpl($path . "remote", $tpl);
$subC = $form->addElement('submit', 'submitC', _("Save"), ["class" => "btc bt_success"]);
$form->addElement('reset', 'reset', _("Reset"), ["class" => "btc bt_default"]);

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    //Update in DB
    updateRemoteAccessCredentials($pearDB, $form, $centreon);

    $o = 'remote';
    $valid = true;
    $form->freeze();
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    [
        "onClick" => "javascript:window.location.href='?p=" . $p . "&o=remote'",
        'class' => 'btc bt_info'
    ]
);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<style color="red" size="1">*</style>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign('valid', $valid);
$tpl->display("formRemote.ihtml");
?>
<script type='text/javascript' src='./include/common/javascript/keygen.js'></script>
