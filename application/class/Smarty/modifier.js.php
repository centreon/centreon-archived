<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.js.php
 * Type:     modifier
 * Name:     js
 * Purpose:  outputs a full script tag for a javascript file
 * -------------------------------------------------------------
 */
function smarty_modifier_js($jsFile) {
    $di = \Centreon\Core\Di::getDefault();
    $config = $di->get('config');
    $jsPath = $config->get('main','jsPath');
    $jsIncludeLine = '<script src="'.$jsPath.$jsFile.'"></script>';
    return $jsIncludeLine;
}
