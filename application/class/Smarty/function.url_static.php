<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.url_static.php
 * Type:     function
 * Name:     url_static
 * Purpose:  returns url with complete for static file
 * -------------------------------------------------------------
 */
function smarty_function_url_static($params) {
    $finalRoute = '';
    if (isset($params['url'])) {
        $config = \Centreon\Core\Di::getDefault()->get('config');
        $finalRoute = $config->get('global', 'base_url', '/centreon');
        $finalRoute .= '/static/' . $params['url'];
    }
    return str_replace('//', '/', $finalRoute);
}
