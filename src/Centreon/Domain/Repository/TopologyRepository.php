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

namespace Centreon\Domain\Repository;

use Centreon\Domain\Entity\Topology;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use CentreonUser;
use PDO;

class TopologyRepository extends ServiceEntityRepository
{
    const ACL_ACCESS_NONE = 0;
    const ACL_ACCESS_READ_WRITE = 1;
    const ACL_ACCESS_READ_ONLY = 2;

    /**
     * Disable Menus for a Master-to-Remote transition
     *
     * @return bool
     */
    public function disableMenus(): bool
    {
        $sql = file_get_contents(__DIR__ . '/../../Infrastructure/Resources/sql/disablemenus.sql');
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Enable Menus for a Remote-to-Master transition
     *
     * @return bool
     */
    public function enableMenus(): bool
    {
        $sql = file_get_contents(__DIR__ . '/../../Infrastructure/Resources/sql/enablemenus.sql');
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Get Topologies according to ACL for user
     * @todo refactor this into function below it
     */
    public function getReactTopologiesPerUserWithAcl($user)
    {
        if (empty($user)) {
            return [];
        }
        $topologyUrls = [];
        if ($user->admin) {
            $sql = "SELECT topology_url FROM `topology` WHERE is_react = '1'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $topologyUrlsFromDB = $stmt->fetchAll();
            foreach ($topologyUrlsFromDB as $topologyUrl) {
                $topologyUrls[] = $topologyUrl['topology_url'];
            }
        } else {
            if (count($user->access->getAccessGroups()) > 0) {
                $query = "SELECT DISTINCT acl_group_topology_relations.acl_topology_id "
                    . "FROM acl_group_topology_relations, acl_topology, acl_topology_relations "
                    . "WHERE acl_topology_relations.acl_topo_id = acl_topology.acl_topo_id "
                    . "AND acl_topology.acl_topo_activate = '1' "
                    . "AND acl_group_topology_relations.acl_group_id IN ("
                    . $user->access->getAccessGroupsString() . ") ";
                $DBRESULT = $this->db->query($query);

                if ($DBRESULT->rowCount()) {
                    $topology = array();
                    $tmp_topo_page = array();
                    while ($topo_group = $DBRESULT->fetchRow()) {
                        $query2 = "SELECT topology_topology_id, acl_topology_relations.access_right "
                            . "FROM acl_topology_relations, acl_topology "
                            . "WHERE acl_topology.acl_topo_activate = '1' "
                            . "AND acl_topology.acl_topo_id = acl_topology_relations.acl_topo_id "
                            . "AND acl_topology_relations.acl_topo_id = '" . $topo_group["acl_topology_id"] . "' ";
                        $DBRESULT2 = $this->db->query($query2);
                        while ($topo_page = $DBRESULT2->fetchRow()) {
                            $topology[] = (int)$topo_page["topology_topology_id"];
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
                        $DBRESULT2->closeCursor();
                    }
                    $DBRESULT->closeCursor();

                    if (count($topology)) {
                        $query3 = "SELECT topology_url "
                            . "FROM topology FORCE INDEX (`PRIMARY`) "
                            . "WHERE topology_url IS NOT NULL "
                            . "AND is_react = '1' "
                            . "AND topology_id IN (" . implode(', ', $topology) . ") ";
                        $DBRESULT3 = $this->db->query($query3);
                        while ($topo_page = $DBRESULT3->fetchRow()) {
                            $topologyUrls[] = $topo_page["topology_url"];
                        }
                        $DBRESULT3->closeCursor();
                    }
                }
            }
        }

        return $topologyUrls ?: [];
    }

    /**
     * Get list of topologies per user and filter by react pages if specified
     * @param CentreonUser $user
     * @return array
     */
    public function getTopologyList(CentreonUser $user): array
    {
        $topologies = [];

        //base query
        $query = 'SELECT topology_id, topology_name, topology_page, topology_url, topology_url_opt, '
            . 'topology_group, topology_order, topology_parent, is_react, readonly, topology_show, is_deprecated '
            . 'FROM ' . Topology::TABLE;

        $whereClause = false;
        if (!$user->access->admin) {
            $query .= ' WHERE topology_page IN (' . $user->access->getTopologyString() . ')  OR topology_page IS NULL';
            $whereClause = true;
        }

        if ($user->doesShowDeprecatedPages() === false) {
            $query .= ($whereClause === true ? ' AND ' : ' WHERE ')
                . 'is_deprecated = "0"';
        }

        $query .= ' ORDER BY topology_parent, topology_group, topology_order, topology_page';
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_CLASS, Topology::class);
        $topologies = $stmt->fetchAll();
        return $topologies;
    }

    /**
     * Find Topology entity by criteria
     *
     * @param array $params
     * @return Topology|null
     */
    public function findOneBy($params = []): ?Topology
    {
        $sql = static::baseSqlQueryForEntity();
        $collector = new StatementCollector;
        $isWhere = false;
        foreach ($params as $column => $value) {
            $key = ":{$column}Val";
            $sql .= (!$isWhere ? 'WHERE ' : 'AND ') . "`{$column}` = {$key} ";
            $collector->addValue($key, $value);
            $isWhere = true;
        }

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();
        if (!$stmt->rowCount()) {
            return null;
        }

        $stmt->setFetchMode(PDO::FETCH_CLASS, Topology::class);
        $entity = $stmt->fetch();

        return $entity;
    }


    /**
     * Part of SQL for extracting of BusinessActivity entity
     *
     * @return string
     */
    protected static function baseSqlQueryForEntity(): string
    {
        return "SELECT * FROM topology ";
    }
}
