<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\MetaService\MetaServiceMetricService;
use Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric\FindMetaServiceMetrics;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetricTest;

/**
 * @package Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21
 */
class FindMetaServiceMetricsTest extends TestCase
{
    /**
     * @var MetaServiceMetricService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $metaServiceMetricService;
    /**
     * @var \Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetric
     */
    private $metaServiceMetric;

    protected function setUp(): void
    {
        $this->metaServiceMetricService = $this->createMock(MetaServiceMetricService::class);
        $this->metaServiceMetric = MetaServiceMetricTest::createMetaServiceMetricEntity();
        $this->metaServiceMetric->setResource(MetaServiceMetricTest::createResourceEntity());
    }

    /**
     * @return FindMetaServiceMetrics
     */
    private function createMetaServiceMetricUseCase(): FindMetaServiceMetrics
    {
        $contact = new Contact();
        $contact->setAdmin(true);

        return (new FindMetaServiceMetrics($this->metaServiceMetricService, $contact));
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->metaServiceMetricService
            ->expects($this->once())
            ->method('findWithoutAcl')
            ->willReturn([$this->metaServiceMetric]);

        $contact = new Contact();
        $contact->setAdmin(true);
        $findMetaServiceMetrics = new FindMetaServiceMetrics($this->metaServiceMetricService, $contact);
        $response = $findMetaServiceMetrics->execute(1);
        $this->assertCount(1, $response->getMetaServiceMetrics());
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->metaServiceMetricService
            ->expects($this->once())
            ->method('findWithAcl')
            ->willReturn([$this->metaServiceMetric]);

        $contact = new Contact();
        $contact->setAdmin(false);
        $findMetaServiceMetrics = new FindMetaServiceMetrics($this->metaServiceMetricService, $contact);
        $response = $findMetaServiceMetrics->execute(1);
        $this->assertCount(1, $response->getMetaServiceMetrics());
    }
}
