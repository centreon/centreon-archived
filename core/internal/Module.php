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

class Module
{
    public $moduleId;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Install module
     *
     * @todo inserts module into database
     */
    public static function install()
    {

    }

    /**
     * Uninstall module
     *
     * @todo remove module from database
     * @todo remove hooks from database
     */
    public static function uninstall()
    {

    }

    /**
     * Register hook
     *
     * @param string $hookName
     * @param string $blockName
     * @param string $blockDescription
     */
    public static function registerHook($hookName, $blockName, $blockDescription)
    {
        Hook::register(
            $this->moduleId,
            $hookName,
            $blockName,
            $blockDescription
        );
    }

    /**
     * Unregister hook
     *
     * @param string $blockName
     */
    public static function unregisterHook($blockName)
    {
        Hook::unregister($this->moduleId, $blockName);
    }
    
    public static function parseMenuArray($menus, $parent = null)
    {
        $i = 1;
        foreach ($menus as $menu) {
            $menu['order'] = $i;
            if (!is_null($parent)) {
                $menu['parent'] = $parent;
            }
            \Centreon\Internal\Module::setMenu($menu);
            if (isset($menu['menus']) && count($menu['menus'])) {
                self::parseMenuArray($menu['menus'], $menu['short_name']);
            }
            $i++;
        }
    }

    /**
     * Set menu entry
     * Inserts into database if short_name does not exist, otherwise it just updates entry
     *
     * @param array $data (name, short_name, parent, route, icon_class, icon, bgcolor, order, module)
     */
    public static function setMenu($data)
    {
        static $menus = null;

        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (is_null($menus)) {
            $sql = "SELECT menu_id, short_name FROM menus";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $menus[$row['short_name']] = $row['menu_id'];
            }
        }
        $mandatoryKeys = array('name', 'short_name');
        foreach ($mandatoryKeys as $k) {
            if (!isset($data[$k])) {
                throw new Exception(sprintf('Missing mandatory key %s', $k));
            }
        }
        
        if (isset($data['parent']) && !isset($menus[$data['parent']])) {
            throw new Exception(sprintf('Parent %s does not exist', $data['parent']));
        }

        if (!isset($menus[$data['short_name']])) {
            $sql = "INSERT INTO menus 
                (name, short_name, parent_id, url, icon_class, icon, bgcolor, menu_order, is_module) VALUES
                (:name, :short_name, :parent, :route, :icon_class, :icon, :bgcolor, :order, :module)";
        } else {
            $menuOrder = "";
            if (isset($data['order'])) {
                $menuOrder = " menu_order = :order, ";
            }
            $sql = "UPDATE menus SET name = :name, parent_id = :parent, url = :route, icon_class = :icon_class,
                icon = :icon, bgcolor = :bgcolor, $menuOrder is_module = :module
                WHERE short_name = :short_name";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':short_name', $data['short_name']);
        $parent = isset($data['parent']) && isset($menus[$data['parent']])? $menus[$data['parent']] : null;
        $stmt->bindParam(':parent', $parent);
        $stmt->bindParam(':route', $data['route']);
        $icon_class = isset($data['icon_class']) ? $data['icon_class'] : null;
        $stmt->bindParam(':icon_class', $icon_class);
        $icon = isset($data['icon']) ? $data['icon'] : null;
        $stmt->bindParam(':icon', $icon);
        $bgcolor = isset($data['bgcolor']) ? $data['bgcolor'] : null;
        $stmt->bindParam(':bgcolor', $bgcolor);
        $order = isset($data['order']) ? $data['order'] : null;
        $stmt->bindParam(':order', $order);
        $module = isset($data['module']) ? $data['module'] : 0;
        $stmt->bindParam(':module', $module);
        $stmt->execute();
        if (!isset($menus[$data['short_name']])) {
            $menus[$data['short_name']] = $db->lastInsertId('menus', 'menu_id');
            if (!isset($data['order']) && isset($data['parent'])) {
                $stmt = $db->prepare("SELECT (MAX(menu_order) + 1) as max_order 
                    FROM menus WHERE parent_id = :parent_id");
                $stmt->bindParam(':parent_id', $menus[$data['parent']]);
                $stmt->execute();
                $row = $stmt->fetch();
                $stmt = $db->prepare("UPDATE menus SET menu_order = :menu_order WHERE menu_id = :menu_id");
                $stmt->bindParam(':menu_order', $row['max_order']);
                $stmt->bindParam(':menu_id', $menus[$data['short_name']]);
                $stmt->execute();
            }
        }
    }
}
