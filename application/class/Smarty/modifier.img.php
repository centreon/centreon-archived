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
    $baseWebPath = trim($config->get('global','base_web_path'), '/');
    $imgPath = trim($config->get('static_file','img_path'), '/');
    $imgIncludeLine = '<img alt="'.$imgFile.'"'
        . 'src="'.$baseWebPath.'/'.$imgPath.'/'.$imgFile.'" />';
    return $imgIncludeLine;
}
