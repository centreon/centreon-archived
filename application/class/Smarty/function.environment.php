<?php
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
    $html = "<div id=\"environment-menu\" style=\"display: none;\">";
    $html .= "<ul class=\"list-inline\">";
    $m = \Centreon\Core\Di::getDefault()->get('menu');
    $envmenu = $m->getMenu();
    foreach ($envmenu as $menu) {
        $html .= "<li class=\"envmenu\" ".($menu['bgcolor'] ? "style=\"background-color: {$menu['bgcolor']};\"" : ""). "data-menu=\"{$menu['menu_id']}\">";
        $html .= "<div class=\"icon\">";
        if ($menu['icon_class']) {
            $html .= "<i class=\"{$menu['icon_class']}\"></i>";
        } elseif ($menu['icon_img']) {
            $html .= "<img src=\"{$menu['icon_img']}\" class=\"\">";
        }
        $html .= "</div>";
        $html .= "<div class=\"name\">";
        $html .= $menu['name'];
        $html .= "</div>";
        $html .= "</li>";
    }
    $html .= "</ul>";
    $html .= "</div>";
    return $html;
}
