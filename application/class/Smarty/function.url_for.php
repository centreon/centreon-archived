<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.url_for.php
 * Type:     function
 * Name:     url_for
 * Purpose:  returns url with complete parameters
 * -------------------------------------------------------------
 */
function smarty_function_url_for($params) {

   /* $finalRoute = \Centreon\Core\Di::getDefault()
        ->get('config')
        ->get('global', 'base_url');*/
    if (isset($params['url'])) {
        $routeParams = array();
        if (isset($params['params']) && is_array($params['params'])) {
            $routeParams = $params['params'];
        }
        $finalRoute = \Centreon\Core\Di::getDefault()
            ->get('router')
            ->getPathFor($params['url'], $routeParams);
    }
    return str_replace("//", "/", $finalRoute);
}
