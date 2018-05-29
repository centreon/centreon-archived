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

use CentreonLegacy\Core\Menu\Menu;

require_once dirname(__FILE__) . "/webService.class.php";

class CentreonMenu extends CentreonWebService
{
    /**
     * Get the init menu on loading page
     *
     * Argument:
     *   page -> int - The current page
     *
     * Method: GET
     */
    public function getInitMenu()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }

        if (!isset($this->arguments['page']) || !is_numeric($this->arguments['page'])) {
            throw new \RestBadRequestException('Missing type argument or bad type page.');
        }
        $page = $this->arguments['page'];

        $menu = new Menu($this->pearDB, $_SESSION['centreon']->user);

        $parents = $menu->getParentsId($page);

        /* Build the base menu tree on getting the menu level one */
        $menuTree = $menu->getMenuChildren();
        for ($i = 0; $i < count($menuTree); $i++) {
            /* Default additional values */
            $menuTree[$i]['children'] = array();
            $menuTree[$i]['active'] = false;
            if ($menuTree[$i]['id'] === $parents['levelOne']) {
                $menuTree[$i]['active'] = true;
                /* Get level two menu if page has level 2 */
                if (!is_null($parents['levelTwo'])) {
                    $menuTree[$i]['children'] = $menu->getMenuChildren($parents['levelOne']);
                    for ($j = 0; $j < count($menuTree[$i]['children']); $j++) {
                        /* Default additional values */
                        $menuTree[$i]['children'][$j]['active'] = false;
                        $menuTree[$i]['children'][$j]['children'] = array();
                        if ($menuTree[$i]['children'][$j]['id'] === $parents['levelTwo']) {
                            $menuTree[$i]['children'][$j]['active'] = true;
                            $menuTree[$i]['children'][$j]['children'] = $menu->getMenuChildren($parents['levelTwo']);
                            /* Search the current activated */
                            for ($k = 0; $k < count($menuTree[$i]['children'][$j]['children']); $k++) {
                                $active = false;
                                /* Test if a group */
                                if (is_array($menuTree[$i]['children'][$j]['children'][$k]['children'])) {
                                    for ($l = 0;
                                        $l < count($menuTree[$i]['children'][$j]['children'][$k]['children']);
                                        $l++) {
                                        if ($menuTree[$i]['children'][$j]['children'][$k]['children'][$l]['id'] === $page) {
                                            $menuTree[$i]['children'][$j]['children'][$k]['children'][$l]['active'] = true;
                                        } else {
                                            $menuTree[$i]['children'][$j]['children'][$k]['children'][$l]['active'] = false;
                                        }
                                    }
                                } else if ($menuTree[$i]['children'][$j]['children'][$k]['id'] === $page) {
                                    $active = true;
                                }
                                $menuTree[$i]['children'][$j]['children'][$k]['active'] = $active;
                            }
                        }
                    }
                }
            }
        }
        return $menuTree;
    }

    /**
     * Get menu children
     *
     * Argument:
     *   parent -> int - The id of level parent
     *
     * Method: GET
     */
    public function getMenuChildren()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }

        if (!isset($this->arguments['parent']) || !is_numeric($this->arguments['parent'])) {
            throw new \RestBadRequestException('Missing type argument or bad type parent.');
        }

        $menu = new Menu($this->pearDB, $_SESSION['centreon']->user);

        return $menu->getMenuChildren($this->arguments['parent']);
    }
}
