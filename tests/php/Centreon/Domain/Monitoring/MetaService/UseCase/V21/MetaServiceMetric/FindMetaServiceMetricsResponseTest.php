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

namespace Tests\Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric;

use Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric\FindMetaServiceMetricsResponse;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetricTest;

/**
 * @package Tests\Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric
 */
class FindMetaServiceMetricsResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindMetaServiceMetricsResponse();
        $metaServiceMetrics = $response->getMetaServiceMetrics();
        $this->assertCount(0, $metaServiceMetrics);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $metaServiceMetric = MetaServiceMetricTest::createMetaServiceMetricEntity();
        $metaServiceMetric->setMonitoringResource(MetaServiceMetricTest::createResourceEntity());
        $response = new FindMetaServiceMetricsResponse();
        $response->setMetaServiceMetrics([$metaServiceMetric]);
        $metaServiceMetrics = $response->getMetaServiceMetrics();
        $this->assertCount(1, $metaServiceMetrics);
        $this->assertEquals($metaServiceMetric->getId(), $metaServiceMetrics[0]['id']);
        $this->assertEquals($metaServiceMetric->getName(), $metaServiceMetrics[0]['name']);
        $this->assertEquals($metaServiceMetric->getValue(), $metaServiceMetrics[0]['value']);
        $this->assertEquals($metaServiceMetric->getUnit(), $metaServiceMetrics[0]['unit']);
    }
}
