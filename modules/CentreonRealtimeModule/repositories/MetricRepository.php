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

namespace CentreonRealtime\Repository;

use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository;
use CentreonRealtime\Models\Service as ServiceRealtime;
use CentreonRealtime\Models\IndexData as IndexData;
use CentreonRealtime\Models\Metric as Metrics;
use CentreonPerformance\Repository\Graph\Storage\Rrd;
use Centreon\Internal\Utils\HumanReadable;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package CentreonRealtime
 * @subpackage Repository
 */
class MetricRepository
{
    /**
     * 
     * @param type $serviceId
     * @return type
     */
    public static function getMetricsFromService($serviceId)
    {
        $rawMetricList = array();
        $finalMetricList = array();
        
        // Get Index Data
        $listOfIndexData = IndexData::getList(
            '*',
            '',
            -1,
            0,
            null,
            'ASC',
            array('service_id' => $serviceId),
            "AND"
        );
        
        foreach ($listOfIndexData as $indexData) {
            $rawMetricList = array_merge($rawMetricList, self::getMetricsFromIndexData($indexData['id']));
        }
        
        foreach ($rawMetricList as $metric) {
            $finalMetricList[$metric['metric_name']] = $metric;
        }
        
        return $finalMetricList;
    }
    
    /**
     * 
     * @param type $indexId
     * @return type
     */
    public static function getMetricsFromIndexData($indexId)
    {
        $listOfMetrics = Metrics::getList(
            '*',
            '',
            -1,
            0,
            null,
            'ASC',
            array('index_id' => $indexId),
            "AND"
        );
        
        return $listOfMetrics;
    }
    
    /**
     * 
     * @param type $metricId
     * @param type $startTime
     * @param type $endTime
     * @return type
     */
    public static function getMetricsValuesFromRrd($metricId, $startTime = null, $endTime = null, $unit = null)
    {
        $rrdHandler = new Rrd();
        if (!is_null($startTime) && !is_null($endTime)) {
            $rrdHandler->setPeriod($startTime, $endTime);
        }
        $datas = array_map(
            function($data) {
                $data = floatval($data);
                if (is_nan($data)) {
                    return null;
                }
                return $data;
            },
            array_values($rrdHandler->getValues($metricId))
        );
        
        $newUnit = "";
        if (!is_null($unit)) {
            $datas = HumanReadable::convertArray($datas, $unit, $newUnit, 2);
        }
        return array('datas' => $datas, 'unit' => $newUnit);
    }
}
