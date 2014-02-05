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
    $smarty->assign('objectAddUrl', $params['objectAddUrl']);
    
    $datatableParameters = \Centreon\Core\Datatable::getConfiguration($params['object']);
    
    // Process Column
    $dCol = array();
    $depth = \Centreon\Repository\Repository::array_depth($datatableParameters['column']);
    foreach ($datatableParameters['column'] as $columnLabel => $columnContent) {
        if (is_array($columnContent)) {
            $dCol['firstLevel'][$columnLabel]['lab'] = $columnLabel;
            $dCol['firstLevel'][$columnLabel]['att'] = 'colspan="'.count($columnContent).'"';
            foreach ($columnContent as $subColumnLabel => $subColumnName) {
                $dCol['secondLevel'][]['lab'] = $subColumnLabel;
            }
        } else {
            $dCol['firstLevel'][$columnLabel]['lab'] = $columnLabel;
            $dCol['firstLevel'][$columnLabel]['att'] = 'rowspan="'.$depth.'"';
        }
    }
    $datatableParameters['column'] = $dCol;
    unset($dCol);
    
    $smarty->assign('datatableParameters', $datatableParameters);
    
    return $smarty->fetch('tools/datatable.tpl');
}
