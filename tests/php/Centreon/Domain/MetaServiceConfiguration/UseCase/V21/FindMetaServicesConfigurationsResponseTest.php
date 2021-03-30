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

namespace Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21;

use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindMetaServicesConfigurationsResponse;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfigurationTest;

/**
 * @package Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21
 */
class FindMetaServicesConfigurationsResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindMetaServicesConfigurationsResponse();
        $metaServicesConfigurations = $response->getMetaServicesConfigurations();
        $this->assertCount(0, $metaServicesConfigurations);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $metaServiceConfiguration = MetaServiceConfigurationTest::createEntity();
        $response = new FindMetaServicesConfigurationsResponse();
        $response->setMetaServicesConfigurations([$metaServiceConfiguration]);
        $metaServiceConfigurations = $response->getMetaServicesConfigurations();
        $this->assertCount(1, $metaServiceConfigurations);
        $this->assertEquals($metaServiceConfiguration->getId(), $metaServiceConfigurations[0]['id']);
        $this->assertEquals($metaServiceConfiguration->getName(), $metaServiceConfigurations[0]['name']);
        $this->assertEquals($metaServiceConfiguration->getOutput(), $metaServiceConfigurations[0]['meta_display']);
        $this->assertEquals(
            $metaServiceConfiguration->getDataSourceType(),
            $metaServiceConfigurations[0]['data_source_type']
        );
        $this->assertEquals(
            $metaServiceConfiguration->getCalculationType(),
            $metaServiceConfigurations[0]['calcul_type']
        );
        $this->assertEquals(
            $metaServiceConfiguration->getMetaSelectMode(),
            $metaServiceConfigurations[0]['meta_select_mode']
        );
        $this->assertEquals($metaServiceConfiguration->getMetric(), $metaServiceConfigurations[0]['metric']);
        $this->assertEquals($metaServiceConfiguration->getWarning(), $metaServiceConfigurations[0]['warning']);
        $this->assertEquals($metaServiceConfiguration->isActivated(), $metaServiceConfigurations[0]['is_activated']);
        $this->assertEquals($metaServiceConfiguration->getCritical(), $metaServiceConfigurations[0]['critical']);
        $this->assertEquals($metaServiceConfiguration->getRegexpString(), $metaServiceConfigurations[0]['regexp_str']);
    }
}
