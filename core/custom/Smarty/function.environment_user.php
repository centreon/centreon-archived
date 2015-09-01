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
 * For more information : contact@centreon.com
 * 
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.environment_user.php
 * Type:     function
 * Name:     environment_user
 * Purpose:  returns html of environment
 * -------------------------------------------------------------
 */
function smarty_function_environment_user($params, $template) {
    $html = '';
    $m = \Centreon\Internal\Di::getDefault()->get('menu');
    $envmenu = $m->getMenu(null, null, 'user');
    foreach ($envmenu as $menu) {
        $html .= '<a href="#" class="envmenu" data-menu="' . $menu['menu_id'] . '">';
        if (isset($menu['icon_class']) && $menu['icon_class']) {
            // $html .= "<i class=\"{$menu['icon_class']}\"></i>";
        } elseif (isset($menu['icon']) && $menu['icon']) {
            $html .= "<img src=\"{$menu['icon']}\" class=\"\">";
        }
        $html .= " " . $menu['name'];
        $html .= "</a>";
    }
    return $html;
}
