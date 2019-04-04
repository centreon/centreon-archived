<?php
/**
 * Copyright 2005-2018 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonLegacy\Core\Menu;

class Menu
{
    /**
     * @var CentreonDB The configuration database connection
     */
    protected $db;
    /**
     * @var string The query filter for ACL
     */
    protected $acl = null;
    /**
     * @var int The current topology page
     */
    protected $currentPage = null;

    /**
     * Constructor
     *
     * @param CentreonDB $db The configuration database connection
     * @param CentreonUser $user The current user
     */
    public function __construct($db, $user = null)
    {
        $this->db = $db;
        if (!is_null($user)) {
            $this->currentPage = $user->getCurrentPage();
            if (!$user->access->admin) {
                $this->acl = ' AND topology_page IN (' . $user->access->getTopologyString() . ') ';
            }
        }
    }

    /**
     * Get all menu (level 1 to 3)
     *
     *
     * array(
     *   "p1" => array(
     *     "label" => "<level_one_label>",
     *     "url" => "<path_to_php_file>"
     *     "active" => "<true|false>"
     *     "color" => "<color_code>"
     *     "children" => array(
     *       "_101" => array(
     *         "label" => "<level_two_label>",
     *         "url" => "<path_to_php_file>",
     *         "active" => "<true|false>"
     *         "children" => array(
     *           "<group_name>" => array(
     *             "_10101" => array(
     *               "label" => "level_three_label",
     *               "url" => "<path_to_php_file>"
     *               "active" => "<true|false>"
     *             )
     *           )
     *         )
     *       )
     *     )
     *   )
     * )
     *
     * @return array The menu
     */
    public function getMenu()
    {
        $groups = $this->getGroups();

        $query = 'SELECT topology_name, topology_page, topology_url, topology_url_opt, '
            . 'topology_group, topology_order, topology_parent, is_react '
            . 'FROM topology '
            . 'WHERE topology_show = "1" '
            . 'AND topology_page IS NOT NULL';

        if (!is_null($this->acl)) {
            $query .= $this->acl;
        }

        $query .= ' ORDER BY topology_parent, topology_group, topology_order, topology_page';
        $stmt = $this->db->prepare($query);

        $stmt->execute();

        $currentLevelOne = null;
        $currentLevelTwo = null;
        $currentLevelThree = null;
        if (!is_null($this->currentPage)) {
            $currentLevelOne = substr($this->currentPage, 0, 1);
            $currentLevelTwo = substr($this->currentPage, 1, 2);
            $currentLevelThree = substr($this->currentPage, 2, 2);
        }

        $menu = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $active = false;
            if (preg_match('/^(\d)$/', $row['topology_page'], $matches)) { // level 1
                if (!is_null($currentLevelOne) && $currentLevelOne == $row['topology_page']) {
                    $active = true;
                }
                $menu['p' . $row['topology_page']] = [
                    'label'    => _($row['topology_name']),
                    'menu_id'  => $row['topology_name'],
                    'url'      => $row['topology_url'],
                    'active'   => $active,
                    'color'    => $this->getColor($row['topology_page']),
                    'children' => [],
                    'options'  => $row['topology_url_opt'],
                    'is_react' => $row['is_react']
                ];
            } elseif (preg_match('/^(\d)(\d\d)$/', $row['topology_page'], $matches)) { // level 2
                if (!is_null($currentLevelTwo) && $currentLevelTwo == $row['topology_page']) {
                    $active = true;
                }
                /**
                 * Add prefix '_' to prevent json list to be reordered by
                 * the browser and to keep menu in order.
                 * This prefix will be remove by front-end.
                 */
                $menu['p' . $matches[1]]['children']['_' . $row['topology_page']] = [
                    'label'    => _($row['topology_name']),
                    'url'      => $row['topology_url'],
                    'active'   => $active,
                    'children' => [],
                    'options'  => $row['topology_url_opt'],
                    'is_react' => $row['is_react']
                ];
            } elseif (preg_match('/^(\d)(\d\d)(\d\d)$/', $row['topology_page'], $matches)) { // level 3
                if (!is_null($currentLevelThree) && $currentLevelThree == $row['topology_page']) {
                    $active = true;
                }
                $levelTwo = $matches[1] . $matches[2];
                $levelThree = [
                    'label'    => _($row['topology_name']),
                    'url'      => $row['topology_url'],
                    'active'   => $active,
                    'options'  => $row['topology_url_opt'],
                    'is_react' => $row['is_react']
                ];
                if (!is_null($row['topology_group']) && isset($groups[$levelTwo][$row['topology_group']])) {
                    /**
                     * Add prefix '_' to prevent json list to be reordered by
                     * the browser and to keep menu in order.
                     * This prefix will be remove by front-end.
                     */
                    $menu
                        ['p' . $matches[1]]['children']
                        ['_' . $levelTwo]['children']
                        [$groups[$levelTwo][$row['topology_group']]]['_' . $row['topology_page']] = $levelThree;
                } else {
                    /**
                     * Add prefix '_' to prevent json list to be reordered by
                     * the browser and to keep menu in order.
                     * This prefix will be remove by front-end.
                     */
                    $menu
                        ['p' . $matches[1]]['children']
                        ['_' . $levelTwo]['children']
                        ['Main Menu']['_' . $row['topology_page']] = $levelThree;
                }
            }
        }
        $stmt->closeCursor();
        return $menu;
    }

    /**
     * Get the list of groups
     *
     * @return array The list of groups
     */
    public function getGroups()
    {
        $query = 'SELECT topology_name, topology_parent, topology_group FROM topology '
            . 'WHERE topology_show = "1" '
            . 'AND topology_page IS NULL '
            . 'ORDER BY topology_group, topology_order';
        $result = $this->db->query($query);

        $groups = array();
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $groups[$row['topology_parent']][$row['topology_group']] = _($row['topology_name']);
        }

        $result->closeCursor();

        return $groups;
    }

    /**
     * Get menu color
     *
     * @param int $pageId The page id
     * @return string color
     */
    public function getColor($pageId)
    {
        switch ($pageId) {
            case '1':
                $color = '#2B9E93';
                break;
            case '2':
                $color = '#85B446';
                break;
            case '3':
                $color = '#E4932C';
                break;
            case '5':
                $color = '#17387B';
                break;
            case '6':
                $color = '#319ED5';
                break;
            default:
                $color = '#319ED5';
                break;
        }

        return $color;
    }
}
