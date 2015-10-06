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

require_once("@CENTREON_ETC@/centreon.conf.php");

require_once $centreon_path . "/www/class/centreonSession.class.php";
require_once $centreon_path . "/www/class/centreon.class.php";
require_once $centreon_path . 'www/class/centreonLang.class.php';


session_start();

$centreon = $_SESSION['centreon'];
if (!isset($centreon))
    exit();

$centreonLang = new CentreonLang($centreon_path, $centreon);
$centreonLang->bindLang();

require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

if (!isset($pearDB) || is_null($pearDB)) {
    $pearDB = new CentreonDB();
    global $pearDB;

}

/**
 * 
 * @global type $pearDB
 * @param array $aMacro
 * @param string $sType
 * 
 * @return array $aReturn
 */
function getMacrosCommand($aMacro, $sType)
{
    global $pearDB;
    
    $aReturn = array();
    
    if (!in_array($sType, array('1', '2'))) {
        $sType = "1";
    }
    $sRq = "SELECT * FROM `on_demand_macro_command` WHERE command_macro_type = '".$sType."'";
    
    if (count($aMacro) > 0) {
       $sRq .= " AND command_macro_name IN ('".  implode("', '", $aMacro)."') "; 
    }

    $DBRESULT = $pearDB->query($sRq);
    $i = 0;
    while ($row = $DBRESULT->fetchRow()){
        $arr[$i]['id']   = $row['command_macro_id'];
        $arr[$i]['name'] = $row['command_macro_name'];
        $arr[$i]['desciption'] = $row['command_macro_desciption'];
        $i++;
        $aReturn[] = $arr;
    }
    $DBRESULT->free();
    
    return $aReturn;
}

$macros = array();
$macrosService = array();
$macrosHost = array();
$macrosDesc = array();
$macrosServiceDesc = array();
$macrosHostDesc = array();
$matches1 = array();
$matches2 = array();
$nb_arg = 0;
$nb_arg1 = 0;
$nb_arg2 = 0;


if (isset($_GET['cmd_line']) && $_GET['cmd_line']) {
    $str = $_GET['cmd_line'];
    
    $nb_arg1 = preg_match_all('/\$_SERVICE(\w+)\$/', $str, $matches1, PREG_SET_ORDER);

    foreach ($matches1 as $match) {
        $macrosService[] = $match[1];
    }
    
    $i = 0;
    if (count($macrosService) > 0) {
        $macrosServiceDesc =  getMacrosCommand($macrosService, '1');
       
        foreach ($macrosService as $detail) {
            if (!in_array($detail, array_values($macrosServiceDesc))) {
                $macrosServiceDesc[$i]['id']   = "";
                $macrosServiceDesc[$i]['name'] = $detail;
                $macrosServiceDesc[$i]['desciption'] = "";
                $i++;
                
                /*
                $arr[$i]['id']   = "";
                $arr[$i]['name'] = $detail;
                $arr[$i]['desciption'] = "";
                $macrosServiceDesc[] = $arr;
                $i++;
                */
            }
        }
    }
    
   
    $nb_arg2 = preg_match_all('/\$_HOST(\w+)\$/', $str, $matches2, PREG_SET_ORDER);
    foreach ($matches2 as $match) {
        $macrosHost[] = $match[1];
    }

    if (count($macrosHost) > 0) {
        $macrosHostDesc =  getMacrosCommand($macrosHost, '2');
        
        foreach ($macrosHost as $detail2) {
            if (!in_array($detail2, array_values($macrosHostDesc))) {
                $macrosHostDesc[$i]['id']   = "";
                $macrosHostDesc[$i]['name'] = $detail2;
                $macrosHostDesc[$i]['desciption'] = "";
                $i++;
            }
        }
    }
    $nb_arg = $nb_arg1 + $nb_arg2;
    
    $macros = array_merge($macrosServiceDesc, $macrosHostDesc);
}

//echo "<pre>";
//print_r($macros);
//print_r($macrosHostDesc);
//print_r($macrosServiceDesc);
//die;

/* FORM */

$path = "$centreon_path/www/include/configuration/configObject/command/";

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
define('SMARTY_DIR', "$centreon_path/GPL_LIB/Smarty/libs/");
require_once SMARTY_DIR . "Smarty.class.php";

$tpl = new Smarty();
$tpl->template_dir = $path;
$tpl->compile_dir = "$centreon_path/GPL_LIB/SmartyCache/compile";
$tpl->config_dir = "$centreon_path/GPL_LIB/SmartyCache/config";
$tpl->cache_dir = "$centreon_path/GPL_LIB/SmartyCache/cache";
$tpl->caching = 0;
$tpl->compile_check = true;
$tpl->force_compile = true;

$tpl->assign('nb_arg', $nb_arg);
 
$tpl->assign('macros', $macros);
//$tpl->assign('macrosDesc', $macrosDesc);
$tpl->assign('noArgMsg', _("Sorry, your command line does not contain any \$_SERVICE\$ macro or \$_HOST\$ macro."));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1"></font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
//$tpl->assign('args', $args);
$tpl->display("formMacros.ihtml");
?>