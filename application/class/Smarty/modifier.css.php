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
    $cssPath = trim($config->get('global','base_url'), '/').'/static/centreon/css/';
    $cssIncludeLine = '<link href="'.
            $cssPath.$cssFile.
            '" rel="stylesheet" type="text/css"/>';
    return $cssIncludeLine;
}
