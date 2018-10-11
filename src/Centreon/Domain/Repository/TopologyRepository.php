<?php

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

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
        $sql = file_get_contents(_CENTREON_PATH_ . '/src/Centreon/Infrastructure/Resources/sql/disablemenus.sql');
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
        $sql = file_get_contents(_CENTREON_PATH_ . '/src/Centreon/Infrastructure/Resources/sql/enablemenus.sql');
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Get Topologies according to ACL for user
     */
    public function getReactTopologiesPerUserWithAcl($user)
    {
        if (empty($user)){
            return [];
        }
        $topologyUrls = [];
        if ($user->admin){
            $sql = "SELECT topology_url FROM `topology` WHERE is_react = '1'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $topologyUrlsFromDB = $stmt->fetchAll();
            foreach ($topologyUrlsFromDB as $topologyUrl){
                $topologyUrls[] = $topologyUrl['topology_url'];
            }
        } else {
            if (count($user->access->getAccessGroups()) > 0){
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
                        $DBRESULT2->closeCursor();
                    }
                    $DBRESULT->closeCursor();

                    if (count($topology)) {
                        $query3 = "SELECT topology_url "
                            . "FROM topology FORCE INDEX (`PRIMARY`) "
                            . "WHERE topology_page IS NOT NULL "
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
}
