<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

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
        $hookData = \Centreon\Internal\Hook::execute($params['name'], $core_params_to_hook);
        foreach ($hookData as $hook) {
            if (isset($hook['template'])) {
                $tpl = $template->createTemplate($hook['template']);
                if (isset($hook['variables'])) {
                    $tpl->assign('variables', $hook['variables']);
                }
                if (isset($params['container'])) {
                    $contents .= str_replace("[hook]", $template->fetch($tpl), $params['container']);
                }
            }
        }
    }
    return $contents;
}
