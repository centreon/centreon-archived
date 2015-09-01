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

namespace CentreonPerformance\Repository\Graph;

use Centreon\Internal\Di;
use CentreonPerformance\Repository\Graph;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonPerformance\Repository\GraphTemplate;

/**
 * Class for generate values for graph service
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
class Service extends Graph
{
    /**
     * Constructor
     *
     * @param int $serviceId The service ID
     * @param int $startTime The start time for graph
     * @param int $endTime The end time for graph
     */
    public function __construct($serviceId, $startTime, $endTime)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        /* Get the list of metrics */
        $query = "SELECT i.index_id, i.service_description, m.metric_id, m.metric_name, m.unit_name, m.warn, m.warn_low, m.crit, m.crit_low, m.min, m.max
            FROM rt_index_data i, rt_metrics m
            WHERE i.service_id = :service_id
                AND i.index_id = m.index_id
                AND m.hidden = '0'";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':service_id', $serviceId);
        $stmt->execute();

        /* List of service template */
        $svcTmpls = ServiceRepository::getListTemplates($serviceId);
        /* Get the graph template */
        $graphInfos = null;
        foreach ($svcTmpls as $svcTmplId) {
            $graphInfos = GraphTemplate::getByServiceTemplate($svcTmplId);
            if (count($graphInfos) > 0) {
                break;
            }
        }

        while ($row = $stmt->fetch()) {
            $metric = array(
                'id' => $row['metric_id'],
                'unit' => $row['unit_name'],
                'color' => null,
                'legend' => $row['metric_name'],
                'is_negative' => false,
                'graph_type' => 'line'
            );
            if (count($graphInfos) > 0 && isset($graphInfos['metrics'][$row['metric_name']])) {
                $metric = array_merge($metric, $graphInfos['metrics'][$row['metric_name']]);
            }
            $this->metrics[] = $metric;
        }

        parent::__construct($startTime, $endTime);
    }
}
