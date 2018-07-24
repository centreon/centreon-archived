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

require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . 'www/class/centreonLang.class.php';
require_once _CENTREON_PATH_  . 'bootstrap.php';

session_start();
session_write_close();

$centreon = $_SESSION['centreon'];
if (!isset($centreon)) {
    exit();
}

$centreonLang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonLang->bindLang();

$args = array();
$str = "";
$nb_arg = 0;
if (isset($_GET['cmd_line']) && $_GET['cmd_line']) {
    $str = str_replace("\$", "@DOLLAR@", $_GET['cmd_line']);
    $nb_arg = preg_match_all("/@DOLLAR@ARG([0-9]+)@DOLLAR@/", $str, $matches);
}

if (isset($_GET['textArea']) && $_GET['textArea']) {
    $textArea = urldecode($_GET['textArea']);
    $tab = preg_split("/\;\;\;/", $textArea);
    foreach ($tab as $key => $value) {
        $tab2 = preg_split("/\ \:\ /", $value, 2);
        $index = str_replace("ARG", "", $tab2[0]);
        if (isset($tab2[0]) && $tab2[0]) {
            $args[$index] = htmlentities($tab2[1]);
        }
    }
}

/* FORM */

$path = _CENTREON_PATH_ . "/www/include/configuration/configObject/command/";

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "60");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/*Basic info */
$form = new HTML_QuickFormCustom('Form', 'post');
$form->addElement('header', 'title', _("Argument Descriptions"));
$form->addElement('header', 'information', _("Arguments"));

$subS = $form->addElement(
    'button',
    'submitSaveAdd',
    _("Save"),
    array(
        "onClick" => "setDescriptions();",
        "class" => "btc bt_success"
    )
);
$subS = $form->addElement(
    'button',
    'close',
    _("Close"),
    array(
        "onClick" => "closeBox();",
        "class" => "btc bt_default"
    )
);

/*
 *  Smarty template
 */

$tpl = new Smarty();
$tpl->template_dir = $path;
$tpl->compile_dir = _CENTREON_PATH_ . "/GPL_LIB/SmartyCache/compile";
$tpl->config_dir = _CENTREON_PATH_ . "/GPL_LIB/SmartyCache/config";
$tpl->cache_dir = _CENTREON_PATH_ . "/GPL_LIB/SmartyCache/cache";
$tpl->plugins_dir[] = _CENTREON_PATH_ . "/GPL_LIB/smarty-plugins";
$tpl->caching = 0;
$tpl->compile_check = true;
$tpl->force_compile = true;

$tpl->assign('nb_arg', $nb_arg);
$dummyTab = array();
$defaultDesc = array();

for ($i = 1; $i <= $nb_arg; $i++) {
    $dummyTab[$i] = $matches[1][$i - 1];
    $defaultDesc[$i] = "";
    if (isset($args[$dummyTab[$i]]) && $args[$dummyTab[$i]]) {
        $defaultDesc[$i] = $args[$dummyTab[$i]];
    }
}
$tpl->assign('dummyTab', $dummyTab);
$tpl->assign('defaultDesc', $defaultDesc);
$tpl->assign('noArgMsg', _("Sorry, your command line does not contain any \$ARGn\$ macro."));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1"></font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('args', $args);
$tpl->display("formArguments.ihtml");
