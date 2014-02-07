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
    $baseUrl = rtrim($config->get('global','base_url'), '/').'/static/centreon/js/';
    $jsIncludeLine = '<script src="'.$baseUrl.$jsFile.'"></script>';
    return $jsIncludeLine;
}
