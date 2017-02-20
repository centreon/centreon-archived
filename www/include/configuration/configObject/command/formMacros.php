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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

require_once _CENTREON_PATH_ . "/www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon.class.php";
require_once _CENTREON_PATH_ . 'www/class/centreonLang.class.php';
require_once _CENTREON_PATH_ . "/www/class/centreonCommand.class.php";

session_start();
session_write_close();

$centreon = $_SESSION['centreon'];
if (!isset($centreon)) {
    exit();
}

$centreonLang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonLang->bindLang();

require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

if (!isset($pearDB) || is_null($pearDB)) {
    $pearDB = new CentreonDB();
    global $pearDB;
}

$macros = array();
$macrosServiceDesc = array();
$macrosHostDesc = array();

$nb_arg = 0;

if (isset($_GET['cmd_line']) && $_GET['cmd_line']) {
    $str = $_GET['cmd_line'];
    $iIdCmd = (int) $_GET['cmdId'];
    
    $oCommande = new CentreonCommand($pearDB);
    
    $macrosHostDesc = $oCommande->matchObject($iIdCmd, $str, '1');
    $macrosServiceDesc = $oCommande->matchObject($iIdCmd, $str, '2');
    
    $nb_arg = count($macrosHostDesc) + count($macrosServiceDesc);
    
    $macros = array_merge($macrosServiceDesc, $macrosHostDesc);
}

/* FORM */
$path = _CENTREON_PATH_."/www/include/configuration/configObject/command/";

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "60");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/* Basic info */
$form = new HTML_QuickForm('Form', 'post');
$form->addElement('header', 'title', _("Macro Descriptions"));
$form->addElement('header', 'information', _("Macros"));


$subS = $form->addElement('button', 'submitSaveAdd', _("Save"), array("onClick" => "setMacrosDescriptions();"));
$subS = $form->addElement('button', 'close', _("Close"), array("onClick" => "closeBox();"));

/*
 *  Smarty template
 */
define('SMARTY_DIR', _CENTREON_PATH_."/GPL_LIB/Smarty/libs/");
require_once SMARTY_DIR . "Smarty.class.php";

$tpl = new Smarty();
$tpl->template_dir = $path;
$tpl->compile_dir = _CENTREON_PATH_."/GPL_LIB/SmartyCache/compile";
$tpl->config_dir = _CENTREON_PATH_."/GPL_LIB/SmartyCache/config";
$tpl->cache_dir = _CENTREON_PATH_."/GPL_LIB/SmartyCache/cache";
$tpl->caching = 0;
$tpl->compile_check = true;
$tpl->force_compile = true;

$tpl->assign('nb_arg', $nb_arg);
 
$tpl->assign('macros', $macros);
$tpl->assign('noArgMsg', _("Sorry, your command line does not contain any \$_SERVICE\$ macro or \$_HOST\$ macro."));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1"></font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("formMacros.ihtml");
