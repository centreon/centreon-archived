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
     * Constructor
     *
     * @param CentreonDB $db The configuration database connection
     * @param CentreonUser $user The current user
     */
    public function __construct($db, $user = null)
    {
        $this->db = $db;
        if (!is_null($user) && !$user->is_admin) {
            $this->acl = ' AND topology_page IN (' . $user->access->getTopologyString() . ')';
        }
    }

    /**
     * Get the level one and level two parents for a page id
     *
     * For 20101 :
     * array(
     *   "levelTwo" => 201,
     *   "levelOne" => 2
     * )
     *
     * For 201 :
     * array(
     *   "levelTwo" => 201,
     *   "levelOne" => 2
     * )
     *
     * For 2 :
     * array(
     *   "levelTwo" => null,
     *   "levelOne" => 2
     * )
     *
     * @param int $pageId The page id to find parents
     * @return array The parents
     */
    public function getParentsId($pageId)
    {
        $firstParent = $this->getParentId($pageId);
        if (is_null($firstParent)) {
            return array(
                'levelOne' => $pageId,
                'levelTwo' => null
            );
        }
        $secondParent = $this->getParentId($firstParent);
        if (is_null($secondParent)) {
            return array(
                'levelOne' => $firstParent,
                'levelTwo' => $pageId
            );
        }
        return array(
            'levelOne' => $secondParent,
            'levelTwo' => $firstParent
        );
    }

    /**
     * Get the parent id for a page
     *
     * @param int $pageId The page id to find parent
     * @return int The parent page id
     */
    public function getParentId($pageId)
    {
        $query = 'SELECT topology_parent FROM topology WHERE topology_page = :page';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':page', $pageId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $parent = null;
        if ($row) {
            $parent = $row['topology_parent'];
        }
        $stmt->closeCursor();
        return $parent;
    }

    /**
     * Get the level three menu
     *
     * Without groups
     *
     * array(
     *   array(
     *     "id" => "201",
     *     "label" => "My menu",
     *     "url" => "http://page"
     *   )
     * )
     *
     * With groups
     *
     * array(
     *   array(
     *     "id" => "212",
     *     "label" => "",
     *     "children" => array(
     *       array(
     *         "id" => "201",
     *         "label" => "My menu",
     *         "url" => "http://page"
     *       )
     *     )
     *   )
     * )
     *
     * @param int $parentId The parent id
     * @return array The menu
     */
    public function getMenuChildren($parentId = null)
    {
        /* Load groups */
        $groups = $this->getGroups($parentId);

        $query = 'SELECT topology_name, topology_page, topology_url, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NOT NULL';
        if (is_null($parentId)) {
            $query .= ' AND topology_parent IS NULL';
        } else {
            $query .= ' AND topology_parent = :parent';
        }

        if (!is_null($this->acl)) {
            $query .= $this->acl;
        }
        $query .= ' ORDER BY topology_group, topology_order';
        $stmt = $this->db->prepare($query);
        if (!is_null($parentId)) {
            $stmt->bindParam(':parent', $parentId, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $menu = array();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $item = array(
                'id' => $row['topology_page'],
                'label' => $row['topology_name'],
                'url' => $row['topology_url']
            );
            if (is_null($row['topology_group'])) {
                $menu[] = $item;
            } else {
                /* Find the good group */
                $found = false;
                for ($i = 0; $i < count($menu); $i++) {
                    if ($menu[$i]['id'] === $row['topology_group'] && isset($menu[$i]['children'])) {
                        $found = true;
                        $menu[$i]['children'][] = $item;
                    }
                }
                /* If not found add the group with the menu item */
                if (!$found) {
                    if (isset($groups[$row['topology_group']])) {
                        $menu[] = array(
                            'id' => $row['topology_group'],
                            'label' => $groups[$row['topology_group']],
                            'children' => array($item)
                        );
                    } else {
                        $menu[] = $item;
                    }
                }
            }
        }
        $stmt->closeCursor();
        return $menu;
    }

    /**
     * Get the list of groups
     *
     * @param int $parentId The parent id
     * @return array The list of groups
     */
    public function getGroups($parentId = null)
    {
        $query = 'SELECT topology_name, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NULL AND topology_parent = :parent
            ORDER BY topology_group, topology_order';
        $stmt = $this->db->prepare($query);
        if (is_null($parentId)) {
            $stmt->bindValue(':parent', null, \PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':parent', $parentId, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $groups = array();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $groups[$row['topology_group']] = $row['topology_name'];
        }
        $stmt->closeCursor();

        return $groups;
    }
}
