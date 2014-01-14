<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.css.php
 * Type:     modifier
 * Name:     css
 * Purpose:  outputs a full link tag for a javascript file
 * -------------------------------------------------------------
 */
function smarty_modifier_css($cssFile) {
    $di = \Centreon\Core\Di::getDefault();
    $config = $di->get('config');
    $cssPath = $config->get('main','cssPath');
    $cssIncludeLine = '<link href="'.
            $cssPath.$cssFile.
            '" rel="stylesheet" type="text/css"/>';
    return $cssIncludeLine;
}
