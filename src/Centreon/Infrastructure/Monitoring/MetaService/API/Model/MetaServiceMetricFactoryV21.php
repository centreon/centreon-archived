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

namespace Centreon\Infrastructure\Monitoring\MetaService\API\Model;

use Centreon\Infrastructure\Monitoring\MetaService\API\Model\MetaServiceMetricV21;
use Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric\FindMetaServiceMetricsResponse;

/**
 * This class is designed to create the MetaServiceMetricV21 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MetaService\API\Model
 */
class MetaServiceMetricFactoryV21
{
    /**
     * @param FindMetaServiceMetricsResponse $response
     * @return MetaServiceMetricV21[]
     */
    public static function createFromResponse(
        FindMetaServiceMetricsResponse $response
    ): array {
        $metaServiceMetrics = [];
        foreach ($response->getMetaServiceMetrics() as $metaServiceMetric) {
            $newMetaServiceMetric = new MetaServiceMetricV21();
            $newMetaServiceMetric->id = $metaServiceMetric['id'];
            $newMetaServiceMetric->name = $metaServiceMetric['name'];
            $newMetaServiceMetric->unitName = $metaServiceMetric['unit_name'];
            $newMetaServiceMetric->currentValue = $metaServiceMetric['current_value'];
            $newMetaServiceMetric->resource = $metaServiceMetric['resource'];

            $metaServiceMetrics[] = $newMetaServiceMetric;
        }
        return $metaServiceMetrics;
    }
}
