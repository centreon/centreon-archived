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
    require_once $centreon_path . "www/autoloader.php";
    
    // Adding requirements
    require_once "HTML/QuickForm.php";
    require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
    
    /**
     * Path to the configuration dir
     */
    global $path;

    /**
     * Set login messages (errors)
     */
    $loginMessages = array();
    if (isset($msg_error)) {
        $loginMessages[] = $msg_error;
    } elseif (isset($_POST["centreon_token"])) {
        $loginMessages[] = 'Invalid user';
    }
    
    if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2) {
        $loginMessages[] = 'Session Expired.';
    }
    
    if ($file_install_acces) {
        $loginMessages[] = $error_msg;
    }
    
    if (isset($msg) && $msg) {
        $loginMessages[] = $msg;
    }
    
    /**
     * Getting Centreon Version
     */
    $DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
    $release = $DBRESULT->fetchRow();
    
    /**
     * Defining Login Form
     */
    $form = new HTML_QuickForm('Form', 'post', './index.php');
    $form->addElement('text', 'useralias', _("Login:"));
    $form->addElement('password', 'password', _("Password"));
    $submitLogin = $form->addElement('submit', 'submitLogin', _("Connect >>"));
    
    $loginValidate = $form->validate();
    
    /**
     * Adding hidden value
     */
    if (isset($_GET['p'])) {
        $pageElement = $form->addElement('hidden', 'p');
        $pageElement->setValue($_GET['p']);
    }
    
    /**
     * Adding validation rule
     */
    $form->addRule('useralias', _("You must specifiy a username"), 'required');
    $form->addRule('password', _("You must specifiy a password"), 'required');
    
    /**
     * Form parameters
     */
    if (isset($freeze) && $freeze) {
        $form->freeze();
    }
    if ($file_install_acces) {
        $submitLogin->freeze();
    }
    
    /*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
    
    // Initializing variables
    $tpl->assign('skin', $skin);
    $tpl->assign('loginMessages', $loginMessages);
    $tpl->assign('centreonVersion', $release['value']);
    $tpl->assign('currentDate', date("d/m/Y"));
    
    // Applying and Displaying template
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->display("login.ihtml");
    require_once("./processLogin.php");
