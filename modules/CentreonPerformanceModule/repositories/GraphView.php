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

namespace CentreonPerformance\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\ServicetemplateRepository;

/**
 * Manage the list of views for graph
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphView
{
    /**
     * Add a new view
     *
     * @param string $name The name of the view
     * @param int privacy The privacy of the view : 0 - private, 1 - public
     * @return int The view ID
     */
    public static function add($name, $privacy = 0)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $ownerId = $_SESSION['user']->getId();

        $stmt = $dbconn->prepare("INSERT INTO cfg_graph_views
            (name, privacy, owner_id)
            VALUES (:name, :privacy, :owner_id)");
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->bindParam(':privacy', $privacy, \PDO::PARAM_INT);
        $stmt->bindParam(':owner_id', $ownerId, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = $dbconn->prepare("SELECT graph_view_id
            FROM cfg_graph_views
            WHERE name = :name
                AND owner_id = :owner_id");
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->bindParam(':owner_id', $ownerId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row === false) {
            throw new \Exception("Error when save the view for graph");
        }
        return $row['graph_view_id'];
    }

    /**
     * Update the list of graphs for a view
     *
     * @param int $viewId The view ID
     * @param array $graphs The list of graphs
     */
    public static function update($viewId, $graphs)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        /* Test if the contact can modify the view */
        $stmt = $dbconn->prepare("SELECT owner_id FROM cfg_graph_views WHERE graph_view_id = :view_id");
        $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if (false === $row || $row['owner_id'] != $_SESSION['user']->getId()) {
            // @todo better exception
            throw new \Exception("Permission denied");
        }

        /* Delete graph view for services */
        $stmt = $dbconn->prepare("DELETE FROM cfg_graph_views_services WHERE graph_view_id = :view_id");
        $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
        $stmt->execute();

        $order = 1;
        foreach ($graphs as $graph) {
            switch ($graph['type']) {
                case 'service':
                    $query = "INSERT INTO cfg_graph_views_services
                        (graph_view_id, service_id, `order`)
                        VALUES (:view_id, :obj_id, :order)";
                    break;
                default:
                    $query = null;
                    break;
            }
            if (false === is_null($query)) {
                $stmt = $dbconn->prepare($query);
                $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
                $stmt->bindParam(':obj_id', $graph['id'], \PDO::PARAM_INT);
                $stmt->bindParam(':order', $order, \PDO::PARAM_INT);
                $stmt->execute();
            }
            $order++;
        }
    }

    /**
     * Get the list of graph view
     *
     * @param bool $onlyPublic If return the graph view only public
     * @return array
     */
    public static function getList($onlyPublic = false)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $query = "SELECT graph_view_id, name
            FROM cfg_graph_views
            WHERE privacy = 1";
        if (false === $onlyPublic) {
            $query .= " OR (privacy = 0 AND owner_id = :owner_id)";
        }
        $query .= " ORDER BY name";

        $stmt = $dbconn->prepare($query);
        if (false === $onlyPublic) {
            $ownerId = $_SESSION['user']->getId();
            $stmt->bindParam(':owner_id', $ownerId, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $list = array();
        while ($row = $stmt->fetch()) {
            $list[$row['graph_view_id']] = $row['name'];
        }
        return $list;
    }

    /**
     * Return the list of graph for a view
     *
     * @param int $viewId The view id
     * @return array The list of graph
     */
    public static function getListGraph($viewId)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $queryService = "SELECT gs.service_id as id, h.host_name, s.service_description, 'service' as type
            FROM cfg_graph_views_services gs, cfg_services s, cfg_hosts h, cfg_hosts_services_relations hs
            WHERE gs.graph_view_id = :view_id
                AND gs.service_id = s.service_id
                AND gs.service_id = hs.service_service_id
                AND h.host_id = hs.host_host_id
            ORDER BY `order`";
        $stmt = $dbconn->prepare($queryService);
        $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
        $stmt->execute();
        $graphs = array();
        while ($row = $stmt->fetch()) {
            $graphs[] = array(
                'id' => $row['id'],
                'type' => $row['type'],
                'title' => $row['host_name'] . ' - ' . $row['service_description']
            );
        }
        return $graphs;
    }

    /**
     * Delete a view
     *
     * @param int $viewId The view id
     */
    public static function delete($viewId)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $stmt = $dbconn->prepare("DELETE FROM cfg_graph_views WHERE graph_view_id = :view_id");
        $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get the list of metric name by a service template id
     *
     * @param int $tmplId The service template id
     * @return array
     */
    public static function getMetricsNameByServiceTemplate($tmplId)
    {
        $services = ServicetemplateRepository::getServices($tmplId);
        if (count($services) == 0) {
            return array();
        }
        /* Check if all data are integer */
        array_map(function($value) {
            if (false === is_numeric($value)) {
                throw new \Exception('The value is not numeric');
            }
        }, $services);
        $dbconn = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $query = "SELECT m.metric_name
            FROM rt_metrics m, rt_index_data i
            WHERE m.index_id = i.index_id
                AND i.service_id IN (" . join(', ', $services) . ")";
        $stmt = $dbconn->query($query);
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = $row['metric_name'];
        }
        return array_unique($metrics);
    }

    /**
     * Get the list of services with metrics
     *
     * @param string $filter The filter search
     * @return array
     */
    public static function getServiceWithMetrics($filter = null)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT DISTINCT h.host_id, h.name, s.service_id, s.description
            FROM rt_hosts h, rt_services s, rt_index_data i, rt_metrics m
            WHERE h.host_id = s.host_id
                AND (h.name LIKE :name OR s.description LIKE :name)
                AND h.host_id = i.host_id
                AND s.service_id = i.service_id
                AND i.index_id = m.index_id
                ORDER BY h.name, s.description";

        if (is_null($filter) || $filter == '') {
            $filterStr = "%";
        } else {
            $filterStr = "%" . $filter . "%";
        }
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':name', $filterStr, \PDO::PARAM_STR);
        $stmt->execute();
        $list = array();
        while ($row = $stmt->fetch()) {
            $list[] = $row;
        }
        return $list;
    }
}
