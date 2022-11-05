<?php
/*
 * Copyright 2005-2016 Centreon
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

/**
 * Class that handles topology
 */
class CentreonTopologyService extends CentreonWebService
{
    private const ACL_ACCESS_NONE = 0;
    private const ACL_ACCESS_READ_WRITE = 1;
    private const ACL_ACCESS_READ_ONLY = 2;

    /**
     * List of topologies by contact groups webservice.
     *
     * @return array
     */
    public function getList(): array
    {
        $topologyPagesString = '';
        $pages = [];
        if (isset($this->arguments['accessGroups'])) {
            $accessGroupsString = implode(",", $this->arguments['accessGroups']);
            $topologyPagesString = $this->getTopologyStr(
                $this->getTopologyListByContactGroup($accessGroupsString)
            );
        }
        if (isset($this->arguments['allTopologies']) && $this->arguments['allTopologies'] === 'true') {
            $topologyPagesString = $this->getTopologyStr($this->getAllTopologyList());
        }
        if ($topologyPagesString !== '') {
            $pages = $this->buildTopologyTree($topologyPagesString);
        }
        if (isset($this->arguments['q']) && $this->arguments['q'] !== '') {
            $searchedPages = array_values(
                array_filter($pages, fn($page) => str_contains($page['text'], $this->arguments['q']))
            );
            $pages = $searchedPages;
        }
        return [
            "items" => $pages,
            "total" => count($pages)
        ];
    }

    /**
     * Get topologies string.
     *
     * @param array $topology
     * @return string
     */
    private function getTopologyStr(array $topology): string
    {
        return empty($topology) ? "''" : implode(',', array_keys($topology));
    }

    /**
     * Getting topology list linked to contact groups.
     *
     * @param string $aclCGroupString
     * @return array
     */
    private function getTopologyListByContactGroup(string $aclCGroupString): array
    {
        $topologyPage = [];
        $query = "SELECT DISTINCT acl_group_topology_relations.acl_topology_id "
                 . "FROM acl_group_topology_relations, acl_topology, acl_topology_relations, 
                    acl_group_contactgroups_relations "
                 . "WHERE acl_topology_relations.acl_topo_id = acl_topology.acl_topo_id "
                 . "AND acl_group_contactgroups_relations.acl_group_id = acl_group_topology_relations.acl_group_id "
                 . "AND acl_topology.acl_topo_activate = '1' "
                 . "AND acl_group_contactgroups_relations.cg_cg_id IN ("
                 . $aclCGroupString . ") ";
        $result = $this->pearDB->query($query);
        if ($result->rowCount() > 0) {
            $topology = array();
            $tmp_topo_page = array();
            $statement = $this->pearDB
                ->prepare(
                    "SELECT topology_topology_id, acl_topology_relations.access_right "
                    . "FROM acl_topology_relations, acl_topology "
                    . "WHERE acl_topology.acl_topo_activate = '1' "
                    . "AND acl_topology.acl_topo_id = acl_topology_relations.acl_topo_id "
                    . "AND acl_topology_relations.acl_topo_id = :acl_topology_id "
                    . "AND acl_topology_relations.access_right != 0"
                );
            while ($topo_group = $result->fetchRow()) {
                $statement->bindValue(':acl_topology_id', (int) $topo_group["acl_topology_id"], PDO::PARAM_INT);
                $statement->execute();
                while ($topo_page = $statement->fetchRow()) {
                    $topology[] = (int) $topo_page["topology_topology_id"];
                    if (!isset($tmp_topo_page[$topo_page['topology_topology_id']])) {
                        $tmp_topo_page[$topo_page["topology_topology_id"]] = $topo_page["access_right"];
                    } else {
                        if ($topo_page["access_right"] == self::ACL_ACCESS_READ_WRITE) {
                            $tmp_topo_page[$topo_page["topology_topology_id"]] = $topo_page["access_right"];
                        } elseif ($topo_page["access_right"] == self::ACL_ACCESS_READ_ONLY
                                  && $tmp_topo_page[$topo_page["topology_topology_id"]] == self::ACL_ACCESS_NONE
                        ) {
                            $tmp_topo_page[$topo_page["topology_topology_id"]] =
                                self::ACL_ACCESS_READ_ONLY;
                        }
                    }
                }
                $statement->closeCursor();
            }
            $result->closeCursor();

            if (count($topology)) {
                $query3 = "SELECT topology_page, topology_id "
                          . "FROM topology FORCE INDEX (`PRIMARY`) "
                          . "WHERE topology_page IS NOT NULL "
                          . "AND topology_id IN (" . implode(', ', $topology) . ") ";
                $DBRESULT3 = $this->pearDB->query($query3);
                while ($topo_page = $DBRESULT3->fetchRow()) {
                    $topologyPage[$topo_page["topology_page"]] =
                        $tmp_topo_page[$topo_page["topology_id"]];
                }
                $DBRESULT3->closeCursor();
            }
        }
        return $topologyPage;
    }

    /**
     * Get all topologies.
     *
     * @return array
     */
    private function getAllTopologyList(): array
    {
        $topologyPages = [];
        $query = "SELECT topology_page "
                 . "FROM topology "
                 . "WHERE topology_page IS NOT NULL ";
        $DBRES = $this->pearDB->query($query);
        while ($row = $DBRES->fetchRow()) {
            $topologyPages[$row['topology_page']] = self::ACL_ACCESS_READ_WRITE;
        }
        $DBRES->closeCursor();
        return $topologyPages;
    }

    /**
     * Building topology tree.
     *
     * @param string $topologiesStr
     * @return array
     */
    private function buildTopologyTree(string $topologiesStr): array
    {
        $acls = array_flip(explode(',', $topologiesStr));
        $pages = [];
        $createTopologyTree = function (array $topologies): array {
            ksort($topologies, SORT_ASC);
            $parentsLvl = [];

            // Classify topologies by parents
            foreach (array_keys($topologies) as $page) {
                if (strlen($page) == 1) {
                    // MENU level 1
                    if (!array_key_exists($page, $parentsLvl)) {
                        $parentsLvl[$page] = [];
                    }
                } elseif (strlen($page) == 3) {
                    // MENU level 2
                    $parentLvl1 = substr($page, 0, 1);
                    if (!array_key_exists($parentLvl1, $parentsLvl)) {
                        $parentsLvl[$parentLvl1] = [];
                    }
                    if (!array_key_exists($page, $parentsLvl[$parentLvl1])) {
                        $parentsLvl[$parentLvl1][$page] = [];
                    }
                } elseif (strlen($page) == 5) {
                    // MENU level 3
                    $parentLvl1 = substr($page, 0, 1);
                    $parentLvl2 = substr($page, 0, 3);
                    if (!array_key_exists($parentLvl1, $parentsLvl)) {
                        $parentsLvl[$parentLvl1] = [];
                    }
                    if (!array_key_exists($parentLvl2, $parentsLvl[$parentLvl1])) {
                        $parentsLvl[$parentLvl1][$parentLvl2] = [];
                    }
                    if (!in_array($page, $parentsLvl[$parentLvl1][$parentLvl2])) {
                        $parentsLvl[$parentLvl1][$parentLvl2][] = $page;
                    }
                }
            }

            return $parentsLvl;
        };

        /**
         * Check if at least one child can be shown
         */
        $oneChildCanBeShown = function () use (&$childrenLvl3, &$translatedPages): bool {
            $isCanBeShow = false;
            foreach ($childrenLvl3 as $topologyPage) {
                if ($translatedPages[$topologyPage]['show']) {
                    $isCanBeShow = true;
                    break;
                }
            }
            return $isCanBeShow;
        };

        $topologies = $createTopologyTree($acls);

        /**
         * Retrieve the name of all topologies available for this user
         */
        $aclResults = $this->pearDB->query(
            "SELECT topology_page, topology_name, topology_show "
            . "FROM topology "
            . "WHERE topology_page IN (" . $topologiesStr . ")"
        );

        $translatedPages = [];

        while ($acl = $aclResults->fetch(PDO::FETCH_ASSOC)) {
            $translatedPages[$acl['topology_page']] = [
                'i18n' => _($acl['topology_name']),
                'show' => ((int) $acl['topology_show'] === 1)
            ];
        }

        /**
         * Create flat tree for menu with the topologies names
         * [item1Id] = menu1 > submenu1 > item1
         * [item2Id] = menu2 > submenu2 > item2
         */
        foreach ($topologies as $parentLvl1 => $childrenLvl2) {
            $parentNameLvl1 = $translatedPages[$parentLvl1]['i18n'];
            foreach ($childrenLvl2 as $parentLvl2 => $childrenLvl3) {
                $parentNameLvl2 = $translatedPages[$parentLvl2]['i18n'];
                $isThirdLevelMenu = false;
                $parentLvl3 = null;

                if ($oneChildCanBeShown()) {
                    /**
                     * There is at least one child that can be shown then we can
                     * process the third level
                     */
                    foreach ($childrenLvl3 as $parentLvl3) {
                        if ($translatedPages[$parentLvl3]['show']) {
                            $parentNameLvl3 = $translatedPages[$parentLvl3]['i18n'];

                            if ($parentNameLvl2 === $parentNameLvl3) {
                                /**
                                 * The name between lvl2 and lvl3 are equals.
                                 * We keep only lvl1 and lvl3
                                 */
                                $pages[] = [
                                    "id" => $parentLvl3,
                                    "text" => $parentNameLvl1 . ' > ' . $parentNameLvl3
                                ];
                            } else {
                                $pages[] = [
                                    "id" => $parentLvl3,
                                    "text" => $parentNameLvl1 . ' > ' . $parentNameLvl2 . ' > ' . $parentNameLvl3
                                ];
                            }
                        }
                    }

                    $isThirdLevelMenu = true;
                }

                // select parent from level 2 if level 3 is missing
                $pageId = $parentLvl3 ?: $parentLvl2;

                if (!$isThirdLevelMenu && $translatedPages[$pageId]['show']) {
                    /**
                     * We show only first and second level
                     */
                    $pages[] = ["id" => $pageId, "text" => $parentNameLvl1 . ' > ' . $parentNameLvl2];
                }
            }
        }
        return $pages;
    }
}
