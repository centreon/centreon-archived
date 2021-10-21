<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 *
 */

namespace Centreon\Application\DataRepresenter\Topology;

use JsonSerializable;

class NavigationList implements JsonSerializable
{

    /**
     * @var array
     */
    private $entities;

    /**
     * Configurations from navigation.yml
     *
     * @var array
     */
    private $navConfig;

    /**
     * Construct
     *
     * @param array
     */
    public function __construct(array $entities, array $navConfig = [])
    {
        $this->navConfig = $navConfig;
        $this->entities = $entities;
    }

    public function getNavConfig() : array
    {
        return $this->navConfig;
    }

    /**
     * JSON serialization of entity
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $groups = $this->extractGroups($this->entities);
        $naviList = $this->generateLevels($this->entities, $groups);

        $navArray = $this->removeKeysFromArray($naviList);

        return $navArray;
    }

    /**
     * Get navigation items color for page
     *
     * @param  int $pageId The page id
     * @return string color
     */
    protected function getColor($pageId)
    {
        return (!empty($this->getNavConfig()[$pageId]['color']))
            ? $this->getNavConfig()[$pageId]['color']
            : $this->getNavConfig()['default']['color'];
    }

    /**
     * Get navigation items icons per page
     *
     * @param  int $pageId The page id
     * @return string icon name
     */
    protected function getIcon($pageId)
    {
        return (!empty($this->getNavConfig()[$pageId]['icon']))
            ? $this->getNavConfig()[$pageId]['icon']
            : $this->getNavConfig()['default']['icon'];
    }

    /**
     * Extract groups from full array of topologies
     *
     * @param  $entities array of topologies
     * @return array of topologies
     */
    private function extractGroups($entities)
    {
        $groups = [];
        foreach ($entities as $entity) {
            if (null === $entity->getTopologyPage() && $entity->getIsReact() == "0") {
                $groups[$entity->getTopologyParent()][$entity->getTopologyGroup()] = [
                    'name' => $entity->getTopologyName()
                ];
            }
        }
        return $groups;
    }

    /**
     * Generate level list of menu
     *
     * @param  $entities
     * @param  $groups
     * @return array
     */
    private function generateLevels($entities, $groups)
    {
        $naviList = [];

        foreach ($entities as $entity) {
            if (preg_match('/^(\d)$/', $entity->getTopologyPage(), $matches)) {
                $naviList[$entity->getTopologyId()] = [
                    'page' => $entity->getTopologyPage(),
                    'label' => $entity->getTopologyName(),
                    'menu_id' => $entity->getTopologyName(),
                    'url' => $entity->getTopologyUrl(),
                    'color' => static::getColor($entity->getTopologyPage()),
                    'icon' => static::getIcon($entity->getTopologyPage()),
                    'children' => [],
                    'options' => $entity->getTopologyUrlOpt(),
                    'is_react' => (bool)$entity->getIsReact(),
                    'show' => (bool)$entity->getTopologyShow()
                ];
            } elseif (
                preg_match('/^(\d)(\d\d)$/', $entity->getTopologyPage(), $matches)
                && !empty($naviList[$matches[1]])
            ) {
                $naviList[$matches[1]]['children'][$entity->getTopologyPage()] = [
                    'page' => $entity->getTopologyPage(),
                    'label' => $entity->getTopologyName(),
                    'url' => $entity->getTopologyUrl(),
                    'groups' => [],
                    'options' => $entity->getTopologyUrlOpt(),
                    'is_react' => (bool)$entity->getIsReact(),
                    'show' => (bool)$entity->getTopologyShow()
                ];
            } elseif (
                preg_match('/^(\d)(\d\d)(\d\d)$/', $entity->getTopologyPage(), $matches)
                && !empty($naviList[$matches[1]]['children'][$matches[1] . $matches[2]])
            ) { // level 3
                $levelTwo = $matches[1] . $matches[2];

                //level 3 items can be grouped for better display

                //make sure we skip groups (we extracted them above)
                if (!(is_null($entity->getTopologyPage()) && $entity->getIsReact() == '0')) {
                    //generate the array for the item
                    $levelThree = [
                        'page' => $entity->getTopologyPage(),
                        'label' => $entity->getTopologyName(),
                        'url' => $entity->getTopologyUrl(),
                        'options' => $entity->getTopologyUrlOpt(),
                        'is_react' => (bool)$entity->getIsReact(),
                        'show' => (bool)$entity->getTopologyShow()
                    ];

                    //check if topology has group index
                    if (!is_null($entity->getTopologyGroup())
                        && isset($groups[$levelTwo][$entity->getTopologyGroup()])) {
                        if (!isset($naviList[$matches[1]]['children'][$levelTwo]['groups']
                                [$entity->getTopologyGroup()])) {
                            $naviList[$matches[1]]['children'][$levelTwo]['groups'][$entity->getTopologyGroup()] = [
                                'label' => $groups[$levelTwo][$entity->getTopologyGroup()]['name'],
                                'children' => []
                            ];
                        }
                        array_push(
                            $naviList[$matches[1]]['children'][$levelTwo]['groups']
                                [$entity->getTopologyGroup()]['children'],
                            $levelThree
                        );
                    } else {
                        if (!isset($naviList[$matches[1]]['children'][$levelTwo]['groups']['default'])) {
                            $naviList[$matches[1]]['children'][$levelTwo]['groups']['default'] = [
                                'label' => 'Main Menu',
                                'children' => []
                            ];
                        }
                        array_push(
                            $naviList[$matches[1]]['children'][$levelTwo]['groups']['default']['children'],
                            $levelThree
                        );
                    }
                }
            }
        }

        return $naviList;
    }

    /**
     * Extract the array without keys to avoid serialization into objects
     *
     * @param  $naviList
     * @return array
     */
    private function removeKeysFromArray($naviList)
    {
        foreach ($naviList as $key => &$value) {
            if (!empty($value['children'])) {
                foreach ($value['children'] as $k => &$c) {
                    if (!empty($c['groups'])) {
                        $c['groups'] = array_values($c['groups']);
                    }
                }
                $value['children'] = array_values($value['children']);
            }
        }

        $naviList = array_values($naviList);

        return $naviList;
    }
}
