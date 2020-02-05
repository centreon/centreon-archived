<?php

if (!isset($centreon)) {
    exit();
}


/*
 * Smarty template Init
 */
$path = "./include/configuration/configWizard/";
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$tpl->display("wizard.html");
