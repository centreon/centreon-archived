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
function smarty_function_datatable($params, $smarty)
{
    $smarty->assign('object', $params['object']);
    $smarty->assign(
        'datatableParameters',
        \Centreon\Core\Datatable::getConfiguration($params['object'])
    );
    return $smarty->fetch('tools/datatable.tpl');
}
