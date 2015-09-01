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

namespace Centreon\Internal;

class Menu
{
    /**
     * @var array
     */
    private $tree;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setMenu();
    }

    /**
     * Takes a set of results and build a tree from it
     *
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    private function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        $router = Di::getDefault()->get('router');
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['menu_id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = array();
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * Init menu
     *
     */
    private function setMenu()
    {
        $cache = Di::getDefault()->get('cache');
        if ($cache->has('app:menu')) {
            $this->tree = $cache->get('app:menu');
            return null;
        }
        $db = Di::getDefault()->get('db_centreon');
        $this->tree = array();
        $stmt = $db->prepare(
            "SELECT menu_id, name, parent_id, url, icon_class, icon, bgcolor, menu_order, menu_block
            FROM cfg_menus
            WHERE module_id IN (SELECT id FROM cfg_modules WHERE isactivated = '1' OR isactivated = '2')
            ORDER BY (CASE WHEN menu_order IS NULL then 1 ELSE 0 END), menu_order ASC, name ASC"
        );
        $stmt->execute();
        $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->tree = $this->buildTree($menus);
        $cache->set('app:menu', $this->tree);
    }

    /**
     * Get menu, can be recursive if $menuId is set.
     * When $menuId is set, the method will return a 
     * specific branch
     *
     * @param int $menuId
     * @param array $tree
     * @param string $block The block where the menu is displayed
     * @return array
     */
    public function getMenu($menuId = null, $tree = null, $menuBlock = null)
    {
        $menu = array();
        if (is_null($menuId)) {
            $menu = $this->tree;
        } else {
            if (is_null($tree)) {
                $tree = $this->tree;
            }
            foreach ($tree as $v) {
                if ($v['menu_id'] == $menuId) {
                    $menu = $v;
                    break;
                }
            }
        }
        if (is_null($menuBlock)) {
            return $menu;
        }
        $menu2 = array();
        foreach ($menu as $item) {
            if ($item['menu_block'] == $menuBlock) {
                $menu2[] = $item;
            }
        }
        return $menu2;
    }

    /**
     * Get menu and returns json string
     *
     * @param int $menuId
     * @return string
     */
    public function getMenuJson($menuId = null)
    {
        return json_encode($this->getMenu($menuId));
    }
}
