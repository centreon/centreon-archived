<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.css.php
 * Type:     modifier
 * Name:     css
 * Purpose:  outputs a full link tag for a cascading stylesheet file
 * -------------------------------------------------------------
 */
function smarty_modifier_css($cssFile) {
    $di = \Centreon\Core\Di::getDefault();
    $config = $di->get('config');
    $basePath = trim($config->get('global','base_path'), '/');
    $cssPath = trim($config->get('static_file','css_path'), '/');
    $cssIncludeLine = '<link href="'.
            $basePath.'/'.$cssPath.'/'.$cssFile.
            '" rel="stylesheet" type="text/css"/>';
    return $cssIncludeLine;
}
