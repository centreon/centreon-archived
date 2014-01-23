<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.url.php
 * Type:     modifier
 * Name:     url
 * Purpose:  outputs a full url
 * -------------------------------------------------------------
 */
function smarty_modifier_url($url) {
    $di = \Centreon\Core\Di::getDefault();
    $config = $di->get('config');
    $fullUrl = rtrim($config->get('global','base_url'), '/').$url;
    return $fullUrl;
}
