<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
declare(strict_types=1);

namespace Centreon\Infrastructure\Monitoring\MetaService\Repository\Model;

use Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetric;
use Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is designed to provide a way to create the MetaServiceMetric entity from the database.
 *
 * @package Centreon\Infrastructure\Monitoring\MetaService\Repository\Model
 */
class MetaServiceMetricFactoryRdb
{
    /**
     * Create a MetaServiceMetric entity from database data.
     *
     * @param array<string, mixed> $data
     * @return MetaServiceMetric
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): MetaServiceMetric
    {
        $metaServiceMetric = (new MetaServiceMetric($data['metric_name']))
            ->setId((int) $data['metric_id'])
            ->setUnit($data['unit_name'])
            ->setValue((float) $data['current_value']);

        // create the service monitoring resource type
        $monitoringResource = new MonitoringResource(
            (int) $data['service_id'],
            $data['service_description'],
            MonitoringResource::TYPE_SERVICE
        );

        $parentMonitoringResource = new MonitoringResource(
            (int) $data['host_id'],
            $data['host_name'],
            MonitoringResource::TYPE_HOST
        );

        $monitoringResource->setParent($parentMonitoringResource);

        $metaServiceMetric->setMonitoringResource($monitoringResource);

        return $metaServiceMetric;
    }
}
