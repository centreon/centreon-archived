<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.img.php
 * Type:     modifier
 * Name:     img
 * Purpose:  outputs a full script tag for an image file
 * -------------------------------------------------------------
 */
function smarty_modifier_img($imgFile) {
    $di = \Centreon\Core\Di::getDefault();
    $config = $di->get('config');
    $baseUrl = trim($config->get('global','base_url'), '/').'/static/centreon/img/';
    $imgIncludeLine = '<img alt="'.$imgFile.'"'
        . 'src="'.$baseUrl.$imgFile.'" />';
    return $imgIncludeLine;
}
