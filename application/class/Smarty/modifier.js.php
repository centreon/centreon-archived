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
    $basePath = trim($config->get('global','base_path'), '/');
    $jsPath = trim($config->get('static_file','js_path'), '/');
    $jsIncludeLine = '<script src="'.$basePath.'/'.$jsPath.'/'.$jsFile.'"></script>';
    return $jsIncludeLine;
}
