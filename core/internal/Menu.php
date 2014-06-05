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
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['menu_id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = array();
                }
                if (false === is_null($element['url'])) {
                    $element['url'] = $router->getPathFor($element['url']);
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
            "SELECT menu_id, name, parent_id, url, icon_class, icon, bgcolor, menu_order
            FROM menus
            WHERE module_id IN (SELECT id FROM module WHERE isactivated = '1' OR isactivated = '2')
            ORDER BY menu_order ASC, id ASC"
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
     * @return array
     */
    public function getMenu($menuId = null, $tree = null)
    {
        if (is_null($menuId)) {
            return $this->tree;
        }
        if (is_null($tree)) {
            $tree = $this->tree;
        }
        foreach ($tree as $v) {
            if ($v['menu_id'] == $menuId) {
                return $v;
            }
        }
        return array();
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
