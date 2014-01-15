<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.hook.php
 * Type:     function
 * Name:     hook
 * Purpose:  returns contents of registered hooks
 * -------------------------------------------------------------
 */
function smarty_function_hook($params, $template) {
    $contents = "";
    if (isset($params['name'])) {
        $core_params_to_hook = array();
        if (isset($params['params'])) {
            $core_params_to_hook = $params['params'];
        }
        if (!isset($params['container'])) {
            $params['container'] = "<div>[hook]</div>";
        }
        $hookData = \Centreon\Core\Hook::execute($params['name'], $core_params_to_hook);
        foreach ($hookData as $hook) {
            if (isset($hook['template']) && isset($hook['variables'])) {
                $tpl = $template->createTemplate($hook['template']);
                $tpl->assign('variables', $hook['variables']);
                if (isset($params['container'])) {
                    $contents .= str_replace("[hook]", $template->fetch($tpl), $params['container']);
                }
            }
        }
    }
    return $contents;
}
