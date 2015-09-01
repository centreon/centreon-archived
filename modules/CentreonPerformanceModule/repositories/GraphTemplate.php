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

use CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;

/**
 * Repository for template graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphTemplate extends FormRepository
{
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'graphTemplate' => 'cfg_curve_config, graph_template_id, metric_name'
        ),
    );

   /**
     * Save the curves for graph template
     *
     * @param int $id The graph template id
     * @param string $action The action
     * @param array $params The parameters to save
     */
    public static function saveMetrics($id, $action = 'add', $listMetrics = array())
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        if ($action == 'update') {
            $query = "DELETE FROM cfg_curve_config WHERE graph_template_id = :tmpl_id";
            $stmt = $dbconn->prepare($query);
            $stmt->bindValue(':tmpl_id', $id, \PDO::PARAM_INT);
            $stmt->execute();
        }

        $query = "INSERT INTO cfg_curve_config (graph_template_id, metric_name, color, is_negative, fill)
            VALUES (:tmpl_id, :name, :color, :neg, :fill)";
        $stmt = $dbconn->prepare($query);

        /* Insert metrics */
        foreach ($listMetrics as $metric) {
            $stmt->bindValue(':tmpl_id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':name', $metric['metric_name'], \PDO::PARAM_STR);
            $stmt->bindValue(':color', $metric['metric_color'], \PDO::PARAM_STR);
            $stmt->bindValue(':neg', $metric['metric_negative'], \PDO::PARAM_INT);
            $stmt->bindValue(':fill', $metric['metric_fill'], \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * Get the list of metrics of a graph template
     *
     * @param int $id The graph template id
     * @return array
     */
    public static function getMetrics($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT metric_name, color, is_negative, fill
            FROM cfg_curve_config
            WHERE graph_template_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = $row;
        }
        return $metrics;
    }

    /**
     * Get the graph template information with a service template id 
     *
     * @param int $id The service template id
     * @return array
     */
    public static function getByServiceTemplate($svcTmplId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT gt.stackable, c.fill, c.metric_name, c.color, c.is_negative
            FROM cfg_graph_template gt, cfg_curve_config c
            WHERE gt.svc_tmpl_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $svcTmplId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            return array();
        }
        $metrics = array();
        while ($row = $stmt->fetchRow()) {
            $metrics[$row['metric_name']] = array(
                'line' => (0 == $row['fill']) ? 'line' : 'area',
                'color' => (is_null($row['color']) || $row['color'] === '') ? null : $row['color'],
                'is_negative' => (0 == $row['is_negative']) ? false : true
            );
        }
        $graphInfos = array(
            'stackable' => (0 === $row['stackable']) ? false : true ,
            'metrics' => $metrics
        );
        return $graphInfos;
    }
}
