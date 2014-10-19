<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
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
 * File:     function.environment.php
 * Type:     function
 * Name:     environment
 * Purpose:  returns html of environment
 * -------------------------------------------------------------
 */
function smarty_function_environment($params, $template) {
    $html = '<div class="navbar-left">';
    $html .= '<ul class="nav navbar-nav navbar-left">';
    $m = \Centreon\Internal\Di::getDefault()->get('menu');
    $envmenu = $m->getMenu();
    foreach ($envmenu as $menu) {
        $html .= '<li>';
        $html .= '<a href="#" class="envmenu" data-menu="' . $menu['menu_id'] . '">';
        if (isset($menu['icon_class']) && $menu['icon_class']) {
            $html .= "<i class=\"{$menu['icon_class']}\"></i>";
        } elseif (isset($menu['icon']) && $menu['icon']) {
            $html .= "<img src=\"{$menu['icon']}\" class=\"\">";
        }
        $html .= " " . $menu['name'];
        $html .= "</a>";
        $html .= "</li>";
    }
    $html .= "</ul>";
    $html .= "</div>";
    return $html;
}
