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

namespace CentreonRealtime\Listeners\CentreonRealtime;

use CentreonRealtime\Events\HostDetailData as HostDetailDataEvent;
use CentreonAdministration\Repository\DomainRepository;
use CentreonRealtime\Repository\ServiceRepository;
use CentreonRealtime\Repository\MetricRepository;
use Centreon\Internal\Di;

/**
 * Description of HostDetailData
 *
 * @author lionel
 */
class HostDetailData
{
    const DOMAIN_SYSTEM = 'System';
    const DOMAIN_HARDWARE = 'Hardware';
    const DOMAIN_NETWORK = 'Network';
    const DOMAIN_APPLICATION = 'Application';
    
    /**
     * 
     * @param HostDetailDataEvent $event
     */
    public static function execute(HostDetailDataEvent $event)
    {
        $hostId = $event->getHostId();
        $hostServicesByDomain = ServiceRepository::getServicesByDomainForHost($hostId);
        $domainList = array_keys($hostServicesByDomain[$hostId]);
        foreach ($domainList as $domain) {
            self::getDomainDatas($event, ucfirst($domain), $hostServicesByDomain[$hostId][$domain]);
        }
    }
    
    /**
     * 
     * @param HostDetailDataEvent $event
     * @param string $domainType
     */
    private static function getDomainDatas(HostDetailDataEvent $event, $domainType, $serviceList)
    {
        $parentDomain['name'] = $domainType;

        if ($domainType !== 'FileSystem') {
            $parentDomain = DomainRepository::getParent($domainType);
        }

        $normalizeServiceSet = array();
        foreach ($serviceList as $service) {
            $serviceMetricList = MetricRepository::getMetricsFromService($service['service_id']);
            $normalizedMetrics = DomainRepository::normalizeMetrics(
                    $domainType,
                    $service,
                    $serviceMetricList
                );
            if ($parentDomain['name'] === 'Application') {
                $normalizeServiceSet[] = $normalizedMetrics;
            } else {
                $normalizeServiceSet[$service['description']] = $normalizedMetrics;
            }
        }

        if (count($normalizeServiceSet) > 0) {
            if ($parentDomain['name'] === 'Application') {
                $normalizeServiceSet = array($domainType => $normalizeServiceSet);
            }
            $event->addHostDetailData(strtolower($parentDomain['name']), $normalizeServiceSet);
        }
    }
}
