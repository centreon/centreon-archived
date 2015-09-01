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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Domain;
use CentreonRealtime\Repository\ServiceRepository;
use CentreonRealtime\Repository\MetricRepository;
use Centreon\Internal\Utils\Status as StatusUtils;
use Centreon\Internal\Utils\Tree as TreeUtils;
use Centreon\Internal\Utils\HumanReadable;
use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class DomainRepository extends \CentreonAdministration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_domains';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Domain';
    
    const DOMAIN_SYSTEM = 'System';
    const DOMAIN_HARDWARE = 'Hardware';
    const DOMAIN_NETWORK = 'Network';
    const DOMAIN_APPLICATION = 'Application';
    
    
    public static $objectClass = '\CentreonAdministration\Models\Domain';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'domain' => 'cfg_domains, domain_id, name'
        ),
    );
    
    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $parentId = Domain::getIdByParameter('name', array('Application'));
        $givenParameters['parent_id'] = $parentId[0];
        $givenParameters['isroot'] = 0;
        parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
    }
    
    /**
     * 
     * @param type $domain
     */
    public static function getParent($domain)
    {
        if (is_string($domain)) {
            $domainId = Domain::getIdByParameter('name', array($domain));
            $domain = $domainId[0];
        }
        
        $currentDomain = Domain::get($domain);
        
        $parentDomainId = Domain::getIdByParameter('domain_id', $currentDomain['parent_id']);
        
        if (count($parentDomainId) > 0) {
            $parent = Domain::get($parentDomainId[0]);
        } else {
            $parent = $currentDomain;
        }
        
        return $parent;
    }

    /**
     * Get list of objects
     *
     * @param string $searchStr
     * @return array
     */
    public static function getFormList($searchStr = "")
    {
        $db = Di::getDefault()->get('db_centreon');

        $sql = "SELECT root.domain_id as root_id, root.name as root_name, 
            child.name as child_name, child.domain_id as child_id 
            FROM cfg_domains root LEFT OUTER JOIN cfg_domains child ON child.parent_id = root.domain_id 
            WHERE root.parent_id IS NULL
            ORDER BY root_name, child_name";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $finalList = array();
        $previous = 0;
        foreach ($rows as $row) {
            if ($row['root_id'] != $previous) {
                $finalList[] = array(
                    'id' => $row['root_id'],
                    'text' => $row['root_name']
                );
            }
            if (!is_null($row['child_name'])) {
                $finalList[] = array(
                    'id' => $row['child_id'],
                    'text' => TreeUtils::formatChild($row['child_name'])
                );
            }
            $previous = $row['root_id'];
        }

        return $finalList;
    }
    
    /**
     * 
     * @param string $domain
     * @param boolean $withChildren
     * @return array
     */
    public static function getDomain($domain, $withChildren = false)
    {
        $domainList = array();
        $mainDomainId = Domain::getIdByParameter('name', array($domain));
        if (count($mainDomainId) > 0) {
            $domainList[] = Domain::get($mainDomainId[0]);
            if ($withChildren) {
                array_merge($domainList, Domain::getList('*', -1, 0, null, 'ASC', array('parent_id' => $mainDomainId[0]))); 
            }
        }
        return $domainList;
    }
    
    /**
     * 
     * @param type $domain
     * @param type $service
     * @param type $metricList
     * @return type
     */
    public static function normalizeMetrics($domain, $service, $metricList)
    {
        $normalizeMetricSet = array();
        $normalizeFunction = 'normalizeMetricsFor' . $domain;
        if (method_exists(__CLASS__, $normalizeFunction)) {
            $normalizeMetricSet = self::$normalizeFunction($domain, $service, $metricList);
        } else {
            $normalizeMetricSet = self::genericNormalizeMetrics($domain, $service, $metricList);
        }
        return $normalizeMetricSet;
    }
    
    /**
     * 
     * @param type $service
     * @param type $metricList
     */
    public static function genericNormalizeMetrics($domain, $service, $metricList)
    {
        $normalizeMetricSet = array();
        
        $explodedOutput = explode("\n", $service['output']);
        
        $normalizeMetricSet['id'] = $service['service_id'];
        $normalizeMetricSet['name'] = $service['description'];
        $normalizeMetricSet['output'] = $explodedOutput[0];
        $normalizeMetricSet['status'] = strtolower(StatusUtils::numToString($service['state'], StatusUtils::TYPE_SERVICE));
        
        return $normalizeMetricSet;
    }
    
    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForNetwork($domain, $service, $metricList)
    {
        $normalizeMetricSet = array();
        $endTime = time();
        $startTime = $endTime - 3600;

        if (isset($metricList['traffic_in'])) {
            $in = $metricList['traffic_in'];
            
            // unit
            $currentUnitExploded = explode('/', $in['unit_name']);
            
            // Get values
            $metricValuesForIn = MetricRepository::getMetricsValuesFromRrd(
                $in['metric_id'],
                $startTime,
                $endTime,
                $currentUnitExploded[0]
            );
            $normalizeMetricSet['in'] = $metricValuesForIn['datas'];
            
            // Max
            if (is_null($in['max'])) {
                $in['max'] = $in['current_value'];
            }
            $normalizeMetricSet['in_max'] = $in['max'];
            
            // Set Unit
            if (!empty($metricValuesForIn['unit'])) {
                $in['unit_name'] = $metricValuesForIn['unit'] . '/' . $currentUnitExploded[1];
            }
        } else {
            $normalizeMetricSet['in'] = array();
            $normalizeMetricSet['in_max'] = 0;
        }

        if (isset($metricList['traffic_out'])) {
            $out = $metricList['traffic_out'];
            
            // unit
            $currentUnitExploded = explode('/', $out['unit_name']);
            
            // Get values
            $metricValuesForout = MetricRepository::getMetricsValuesFromRrd(
                $out['metric_id'],
                $startTime,
                $endTime,
                $currentUnitExploded[0]
            );
            $normalizeMetricSet['out'] = $metricValuesForout['datas'];
            
            // Max
            if (is_null($out['max'])) {
                $out['max'] = $out['current_value'];
            }
            $normalizeMetricSet['out_max'] = $out['max'];
            
            // Set Unit
            if (!empty($metricValuesForout['unit'])) {
                $out['unit_name'] = $metricValuesForout['unit'] . '/' . $currentUnitExploded[1];
            }
            $normalizeMetricSet['unit'] = $out['unit_name'];
        } else {
            $normalizeMetricSet['out'] = array();
            $normalizeMetricSet['out_max'] = 0;
        }
        
        if (!isset($normalizeMetricSet['unit'])) {
            $normalizeMetricSet['unit'] = "b/s";
        }
        
        $normalizeMetricSet['status'] = strtolower(StatusUtils::numToString($service['state'], StatusUtils::TYPE_SERVICE));

        return $normalizeMetricSet;
    }
    
    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForTraffic($domain, $service, $metricList)
    {
        return self::normalizeMetricsForNetwork($domain, $service, $metricList);
    }

    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForMemory($domain, $service, $metricList)
    {
        return self::normalizeMetricsForStorage($domain, $service, $metricList);
    }

    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForFileSystem($domain, $service, $metricList)
    {
        return self::normalizeMetricsForStorage($domain, $service, $metricList);
    }

    /**
     *
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForCPU($domain, $service, $metricList)
    {
        /* avg is already in metric*/
        if (isset($metricList['total_cpu_avg'])) {
            return array($metricList['total_cpu_avg']['current_value']);
        }

        /* avg is not in metric table, we have to calculate it */
        $count = 0;
        $total = 0;
        foreach ($metricList as $metricName => $metricData) {
            if (preg_match('/^cpu(\d+)/', $metricName)) {
                $total += $metricData['current_value'];
                $count++;
            }

        }
        if ($count) {
            return array(round(($total / $count), 2));
        }
        return array();
    }

    /**
     *
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForIO($domain, $service, $metricList)
    {
        $normalizeMetricSet = array();

        if (!isset($metricList['write']) || !isset($metricList['read'])) {
            return array();
        }  

        $read = $metricList['read'];
        $write = $metricList['write'];
        
        $normalizeMetricSet['read'] = $read['current_value'];
        $normalizeMetricSet['write'] = $write['current_value'];
        $normalizeMetricSet['unit'] = $read['unit_name'];
        
        return $normalizeMetricSet;
    }
    
    /**
     * 
     * @param type $domain
     * @param type $service
     * @param type $metricList
     * @return string
     */
    public static function normalizeMetricsForStorage($domain, $service, $metricList)
    {
        $normalizeMetricSet = array();

        if (!isset($metricList['used'])) {
            return array();
        }

        $metric = $metricList['used'];

        $newUnit = "";
        $memoryValue = HumanReadable::convertArray(
            array($metric['current_value'], $metric['max']),
            $metric['unit_name'],
            $newUnit,
            2
        );
        $normalizeMetricSet['current'] = $memoryValue[0];
        $normalizeMetricSet['max'] = $memoryValue[1];
            
        if (!empty($newUnit)) {
            $metric['unit_name'] = $newUnit;
        }
        $normalizeMetricSet['unit'] = $metric['unit_name'];
        $normalizeMetricSet['status'] = strtolower(
            StatusUtils::numToString(
                $service['state'], 
                StatusUtils::TYPE_SERVICE
            )
        );

        return $normalizeMetricSet;
    }
}
