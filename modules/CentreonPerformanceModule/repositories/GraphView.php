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

        $queryService = "SELECT service_id as id, 'service' as type
            FROM cfg_graph_views_services
            WHERE graph_view_id = :view_id
            ORDER BY `order`";
        $stmt = $dbconn->prepare($queryService);
        $stmt->bindParam(':view_id', $viewId, \PDO::PARAM_INT);
        $stmt->execute();
        $graphs = array();
        while ($row = $stmt->fetch()) {
            $graphs[] = $row;
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
            WHERE m.index_id = i.id
                AND i.service_id IN (" . join(', ', $services) . ")";
        $stmt = $dbconn->query($query);
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = $row['metric_name'];
        }
        return array_unique($metrics);
    }
}
