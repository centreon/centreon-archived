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

	$args = array();
	$str = "";
	$nb_arg = 0;
	if (isset($_GET['cmd_line']) && $_GET['cmd_line']) {
		$str = str_replace("\$", "@DOLLAR@", $_GET['cmd_line']);
		$nb_arg = preg_match_all("/@DOLLAR@ARG([0-9]+)@DOLLAR@/", $str, $matches);
	}

	if (isset($_GET['textArea']) && $_GET['textArea']) {
		$tab = preg_split("/\;\;\;/", $_GET['textArea']);
		foreach ($tab as $key=>$value) {
			$tab2 = preg_split("/\ \:\ /", $value, 2);
			$index = str_replace("ARG", "", $tab2[0]);
			if (isset($tab2[0]) && $tab2[0])
				$args[$index] = $tab2[1];
		}
	}

	/* FORM */

$path = "$centreon_path/www/include/configuration/configObject/command/";

$attrsText 		= array("size"=>"30");
$attrsText2 	= array("size"=>"60");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");

/*Basic info */
$form = new HTML_QuickForm('Form', 'post');
$form->addElement('header', 'title', _("Argument Descriptions"));
$form->addElement('header', 'information', _("Arguments"));

/*$form->addElement('text', 'ba_group_name', _("Name"), $attrsText);*/


$subS = $form->addElement('button', 'submitSaveAdd', _("Save"), array("onClick"=>"setDescriptions();"));
$subS = $form->addElement('button', 'close', _("Close"), array("onClick"=>"closeBox();"));

/*
 *  Smarty template
 */
define('SMARTY_DIR', "$centreon_path/GPL_LIB/Smarty/libs/");
require_once SMARTY_DIR."Smarty.class.php";

$tpl = new Smarty();
$tpl->template_dir = $path;
$tpl->compile_dir = "$centreon_path/GPL_LIB/SmartyCache/compile";
$tpl->config_dir = "$centreon_path/GPL_LIB/SmartyCache/config";
$tpl->cache_dir = "$centreon_path/GPL_LIB/SmartyCache/cache";
$tpl->caching = 0;
$tpl->compile_check = true;
$tpl->force_compile = true;

$tpl->assign('nb_arg', $nb_arg);
$dummyTab = array();
$defaultDesc = array();

for ($i = 1; $i <= $nb_arg; $i++) {
	$dummyTab[$i] = $matches[1][$i - 1];
	$defaultDesc[$i] = "";
	if (isset($args[$dummyTab[$i]]) && $args[$dummyTab[$i]])
		$defaultDesc[$i] = $args[$dummyTab[$i]];
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

?>