<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {preg_match} function plugin
 *
 * Type:     function
 * Name:     preg_match
 * Purpose:  offers php preg_match function inside a template
 * @author Damiano Venturin
 * @param array parameters
 * @param object $template template object
 * @return boolean, matches array in template
 */
function smarty_function_preg_match($params, $template)
{
    $flags = (empty($params['flags']) ? 0 : $params['flags']);
    $offset = (empty($params['offset']) ? 0 : $params['offset']);
    $match = preg_match("/".$params['pattern']."/", $params['subject'], $matches, $flags, $offset);
    $template->assign('matches', $matches);
    return $match;
}
?>
