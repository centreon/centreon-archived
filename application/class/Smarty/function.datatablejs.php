<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.datatable.php
 * Type:     function
 * Name:     datatable
 * Purpose:  returns a datatable
 * -------------------------------------------------------------
 */
function smarty_function_datatablejs($params, $smarty)
{
    $smarty->assign('objectUrl', $params['objectUrl']);
    return $smarty->fetch('tools/datatableJs.tpl');
}
