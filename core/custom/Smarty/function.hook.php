<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
