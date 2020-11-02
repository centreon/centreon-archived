<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

use Security\Encryption;

if (!isset($centreon)) {
    exit();
}

require_once 'DB-Func.php';

/*
 * Set encryption parameters
 */
require_once _CENTREON_PATH_ . "/src/Security/Encryption.php";
if (file_exists(_CENTREON_PATH_ . '/.env.local.php')) {
    $localEnv = @include _CENTREON_PATH_ . '/.env.local.php';
}

if (empty($localEnv) || !isset($localEnv['APP_SECRET'])) {
    exit();
}
define("SECOND_KEY", base64_encode('api_remote_credentials'));

/*
 * Check ACL
 */
if (!$centreon->user->admin && $contactId) {
    $aclOptions = [
        'fields' => ['contact_id', 'contact_name'],
        'keys' => ['contact_id'],
        'get_row' => 'contact_name',
        'conditions' => ['contact_id' => $contactId]
    ];
    $contacts = $acl->getContactAclConf($aclOptions);
    if (!count($contacts)) {
        include_once _CENTREON_PATH_ . "/www/include/core/errors/alt_error.php";
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
    include_once _CENTREON_PATH_ . "/www/include/core/errors/alt_error.php";
    return null;
}
$dbResult->closeCursor();

/**
 * Decrypt credentials
 */
$centreonEncryption = new Encryption();
$decrypted = '';
try {
    $centreonEncryption->setFirstKey($localEnv['APP_SECRET'])->setSecondKey(SECOND_KEY);
    if (!empty($result['apiUsername'])) {
        $decryptResult = $centreonEncryption->decrypt($result['apiCredentials'] ?? '');
    }
} catch (Exception $e) {
    unset($result['apiCredentials']);
    $errorMsg = _('The password cannot be decrypted. Please re-enter the account password and submit the form');
    echo "<div class='msg' align='center'>" . $errorMsg . "</div>";
}

/**
 * form
 */
$attrsText = array("size" => "40");
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$tpl = new Smarty();
$tpl = initSmartyTpl($path . "remote", $tpl);
$form->addElement('header', 'title', _("Remote access"));
$form->addElement('header', 'information', _("Central's API credentials"));
$form->addElement(
    'text',
    'apiUsername',
    _("Username"),
    $attrsText
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
        'apiCredentials' => (!empty($decryptResult) ? CentreonAuth::PWS_OCCULTATION : '')
    ]
);

//URI
$form->addElement('header', 'informationUri', _("Central's API URI"));

$form->addElement(
    'select',
    'apiScheme',
    _("Build full URI (SCHEME://IP:PORT/PATH)."),
    ['http' => 'http', 'https' => 'https'],
    ['id' => 'apiScheme', 'onChange' => 'checkSsl(this.value)']
);
$apiScheme = $result['apiScheme'] ?: 'http';
$sslVisibility = $apiScheme === 'http' ? 'hidden' : 'visible';
$form->setDefaults($apiScheme);

$form->addElement('header', 'informationIp', $result['authorizedMaster']);

$form->addElement(
    'text',
    'apiPath',
    _("apiPath"),
    $attrsText
);
$form->addRule('apiPath', _("Required Field"), 'required');
$form->registerRule('validateApiPort', 'callback', 'validateApiPort');
$form->addElement('text', 'apiPort', _("Port"), ["size" => "8", 'id' => 'apiPort']);
$form->addRule('apiPort', _('Must be a number between 1 and 65335 included.'), 'validateApiPort');
$form->addRule('apiPort', _('Required Field'), 'required');

$form->addElement(
    'checkbox',
    'apiPeerValidation',
    _("Allow self signed certificate"),
    null
);
$form->setDefaults(1);

$form->setDefaults(
    [
        'apiPath' => $result['apiPath'],
        'apiPort' => $result['apiPort'],
        'apiScheme' => $result['apiScheme'],
        'apiPeerValidation' => ($result['apiPeerValidation'] == 'yes' ? 0 : 1)
    ]
);

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$subC = $form->addElement('submit', 'submitC', _("Save"), ["class" => "btc bt_success"]);
$form->addElement('reset', 'reset', _("Reset"), ["class" => "btc bt_default"]);

// Prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    //Update or insert data in DB
    updateRemoteAccessCredentials($pearDB, $form, $centreonEncryption);

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
$tpl->assign('sslVisibility', $sslVisibility);
$tpl->assign('valid', $valid);
$tpl->display("formRemote.ihtml");
?>
<script type='text/javascript' src='./include/common/javascript/keygen.js'></script>
