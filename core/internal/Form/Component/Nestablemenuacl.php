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

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Acl;
use Centreon\Internal\Di;
use CentreonAdministration\Repository\AclmenuRepository;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Nestablemenuacl extends Component
{
    private static $aclmenudata = array();

    /**
     * Get checkbox html
     * 
     * @param int $menuId
     * @param string $uri
     * @return string
     */
    private static function getCheckboxHtml($menuId, $uri)
    {
        $routesData = Di::getDefault()
            ->get('router')
            ->getRoutes();
        $str = "<span class=\"nestable_cb\">";
        $cbArray = array();
        foreach ($routesData as $data) {
            if (strstr($data['route'], $uri) && $data['acl']) {
                $cbArray[$data['acl']] = true;
            }
        }
        $acldata = 0;
        if (isset(self::$aclmenudata[$menuId])) {
            $acldata = self::$aclmenudata[$menuId];
        }
        $tmp = "<span class=\"acl_cb\">%s <input type=\"checkbox\" name=\"acl_%s[{$menuId}]\" %s></input></span>";
        if (isset($cbArray[Acl::ADD])) {
            $checked = Acl::isFlagSet($acldata, Acl::ADD) ? 'checked' : '';
            $str .= sprintf($tmp, _('Create'), 'create', $checked);
        }
        if (isset($cbArray[Acl::DELETE])) {
            $checked = Acl::isFlagSet($acldata, Acl::DELETE) ? 'checked' : '';
            $str .= sprintf($tmp, _('Delete'), 'delete', $checked);
        }
        if (isset($cbArray[Acl::UPDATE])) {
            $checked = Acl::isFlagSet($acldata, Acl::UPDATE) ? 'checked' : '';
            $str .= sprintf($tmp, _('Update'), 'update', $checked);
        }
        if (isset($cbArray[Acl::VIEW])) {
            $checked = Acl::isFlagSet($acldata, Acl::VIEW) ? 'checked' : '';
            $str .= sprintf($tmp, _('View'), 'view', $checked);
        }
        if (isset($cbArray[Acl::ADVANCED])) {
            $checked = Acl::isFlagSet($acldata, Acl::ADVANCED) ? 'checked' : '';
            $str .= sprintf($tmp, _('Advanced'), 'advanced', $checked);
        }
        $str .= "</span>";
        return $str;
    }

    /**
     * Get menu html string
     *
     * @param array $menus
     * @param int $menuId
     * @return string
     */
    private static function getMenuString($menus, $menuId)
    {
        $str = "";
        foreach ($menus as $menu) {
            if ($menu['menu_id'] == $menuId) {
                $str .= "<li class=\"dd-item\" data-id=\"{$menuId}\">";
                $style = "";
                if ($menu['bgcolor']) {
                    $style = "style=\"background: {$menu['bgcolor']}\"";
                }
                $str .= "<div class=\"dd-handle\" {$style}>";
                if ($menu['icon_class']) {
                    $str .= "<i class=\"{$menu['icon_class']}\"></i> ";
                }
                $str .= $menu['name'];
                if (!count($menu['children'])) {
                    $str .= self::getCheckboxHtml($menuId, $menu['url']);
                }
                $str .= "</div>";
                if (count($menu['children'])) {
                    $str .= "<ol class=\"dd-list\">";
                    foreach ($menu['children'] as $cmenu) {
                        $str .= self::getMenuString($menu['children'], $cmenu['menu_id']);
                    }
                    $str .= "</ol>";
                }
                $str .= "</li>";
            }
        }
        return $str;
    }

    /**
     * Render html input
     *
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addCss('nestable.css');
        $tpl->addJs('jquery.nestable.js');
        $menus = Di::getDefault()->get('menu')->getMenu();
        $menuStr = "";
        if (isset($element['label_extra']) && isset($element['label_extra']['id'])) {
            self::$aclmenudata = AclmenuRepository::getAclLevelByAclMenuId(
                $element['label_extra']['id']
            );
        }
        foreach ($menus as $menu) {
            $menuStr .= self::getMenuString($menus, $menu['menu_id']);
        }
        $myHtml = '
            <div class="dd">
            <ol class="dd-list dd-nodrag">
            '.$menuStr.'
            </ol>
            </div>
            ';
        $myJs = '
            $(".dd").nestable({
                handleClass: "dd-nohandle"
            });
        ';
        return array('html' => $myHtml, 'js' => $myJs);
    }
}
