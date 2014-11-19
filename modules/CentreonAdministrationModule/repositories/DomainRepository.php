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
 */

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Domain;
use CentreonRealtime\Repository\ServiceRepository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
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
    
    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters)
    {
        $givenParameters['parent_id'] = Domain::getIdByParameter('name', array('Application'));
        $givenParameters['isroot'] = 0;
        parent::create($givenParameters);
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
    
    public static function normalizeMetrics($domain, $metricList)
    {
        $normalizeMetricSet = array();
        $normalizeFunction = 'normalizeMetricsFor' . $domain;
        if (method_exists(__CLASS__, $normalizeFunction)) {
            $normalizeMetricSet = self::$normalizeFunction($metricList);
        }
        return $normalizeMetricSet;
    }
    
    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForNetwork($metricList)
    {
        $normalizeMetricSet = array();

        return $normalizeMetricSet;
    }
    
    public static function prepareRrdData($serviceData)
    {
        $data = array();
        $data['times'] = array_keys($serviceData[0]['data']);
        $data['times'] = array_map(function($time) {
            return $time * 1000;
        }, $data['times']);
        $data['metrics'] = array();
        /* Check unit for human readable */
        $units = array();
        foreach ($serviceData as $metric) {
            if (false === isset($units[$metric['unit']])) {
                $units[$metric['unit']] = 0;
            }
            $values = array_map(function($value) {
                if (strval($value) == "NAN") {
                    return 0;
                }
                return $value;
            }, $metric['data']);
            $factor = HumanReadable::getFactor($values);
            if ($units[$metric['unit']] < $factor) {
                $units[$metric['unit']] = $factor;
            }
        }

        /* Convert data for c3js */
        foreach ($serviceData as $metric) {
            $metric['data'] = array_values($metric['data']);
            $metric['data'] = array_map(function($data) {
                if (strval($data) == "NAN") {
                    return null;
                }
                return $data;
            }, $metric['data']);
            $metric['data'] = HumanReadable::convertArrayWithFactor($metric['data'], $metric['unit'], $units[$metric['unit']], 3);
            if (in_array($metric['unit'], array_keys(HumanReadable::$units))) {
                $metric['unit'] = HumanReadable::$units[$metric['unit']]['units'][$units[$metric['unit']]];
            }
            $data['metrics'][] = $metric;
        }
        return $data;
    }
    
    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForTraffic($metricList)
    {
        $normalizeMetricSet = array();
        $rrdHandler = new \CentreonPerformance\Repository\Graph\Storage\Rrd();
        //$currentTime = time();
        //$rrdHandler->setPeriod($currentTime, $currentTime - 60);

        if (isset($metricList['traffic_in'])) {
            $in = $metricList['traffic_in'];
            //$normalizeMetricSet['in'] = array_values($rrdHandler->getValues($in['metric_id']));
            $normalizeMetricSet['in'] = self::prepareRrdData($rrdHandler->getValues($in['metric_id']));
            if (is_null($in['max'])) {
                $in['max'] = $in['current_value'];
            }
            $normalizeMetricSet['in_max'] = $in['max'];
            $normalizeMetricSet['unit'] = $in['unit_name'];
        }

        if (isset($metricList['traffic_out'])) {
            $out = $metricList['traffic_out'];
            $normalizeMetricSet['out'] = array_values($rrdHandler->getValues($in['metric_id']));
            if (is_null($out['max'])) {
                $out['max'] = $out['current_value'];
            }
            $normalizeMetricSet['out_max'] = $out['max'];
            $normalizeMetricSet['unit'] = $out['unit_name'];
        }
        
        $normalizeMetricSet['status'] = '';

        return $normalizeMetricSet;
    }

    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForMemory($metricList)
    {
        $normalizeMetricSet = array();

        $metric = $metricList['used'];

        $normalizeMetricSet['current'] = $metric['current_value'];
        $normalizeMetricSet['max'] = $metric['max'];
        $normalizeMetricSet['unit'] = $metric['unit_name'];

        return $normalizeMetricSet;
    }

    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForFileSystem($metricList)
    {
        $normalizeMetricSet = array();

        $metric = $metricList['used'];

        $normalizeMetricSet['current'] = $metric['current_value'];
        $normalizeMetricSet['max'] = $metric['max'];
        $normalizeMetricSet['unit'] = $metric['unit_name'];

        return $normalizeMetricSet;
    }

    /**
     *
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForCpu($metricList)
    {
        $normalizeMetricSet = array();

        foreach ($metricList as $metricName => $metricData) {
            if (preg_match('/^cpu(\d+)/', $metricName)) {
                $normalizeMetricSet[$metricName] = $metricData['current_value'];
            }

        }
        return $normalizeMetricSet;
    }

    /**
     *
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForIO($metricList)
    {
        $normalizeMetricSet = array();
        
        $read = $metricList['read'];
        $write = $metricList['write'];
        
        $normalizeMetricSet['read'] = $read['current_value'];
        $normalizeMetricSet['write'] = $write['current_value'];
        $normalizeMetricSet['unit'] = $read['unit_name'];
        
        return $normalizeMetricSet;
    }
}
