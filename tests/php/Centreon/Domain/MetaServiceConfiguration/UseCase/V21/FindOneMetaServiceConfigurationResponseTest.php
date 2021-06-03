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

namespace Tests\Centreon\Domain\Monitoring\MetaServiceConfiguration\UseCase\V21;

use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindOneMetaServiceConfigurationResponse;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfigurationTest;

/**
 * @package Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21
 */
class FindOneMetaServiceConfigurationResponseTest extends TestCase
{
    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $metaServiceConfiguration = MetaServiceConfigurationTest::createEntity();
        $response = new FindOneMetaServiceConfigurationResponse();
        $response->setMetaServiceConfiguration($metaServiceConfiguration);
        $metaServiceConfigurationResponse = $response->getMetaServiceConfiguration();
        $this->assertEquals($metaServiceConfiguration->getId(), $metaServiceConfigurationResponse['id']);
        $this->assertEquals($metaServiceConfiguration->getName(), $metaServiceConfigurationResponse['name']);
        $this->assertEquals($metaServiceConfiguration->getOutput(), $metaServiceConfigurationResponse['meta_display']);
        $this->assertEquals(
            $metaServiceConfiguration->getDataSourceType(),
            $metaServiceConfigurationResponse['data_source_type']
        );
        $this->assertEquals(
            $metaServiceConfiguration->getCalculationType(),
            $metaServiceConfigurationResponse['calcul_type']
        );
        $this->assertEquals(
            $metaServiceConfiguration->getMetaSelectMode(),
            $metaServiceConfigurationResponse['meta_select_mode']
        );
        $this->assertEquals($metaServiceConfiguration->getMetric(), $metaServiceConfigurationResponse['metric']);
        $this->assertEquals($metaServiceConfiguration->getWarning(), $metaServiceConfigurationResponse['warning']);
        $this->assertEquals(
            $metaServiceConfiguration->isActivated(),
            $metaServiceConfigurationResponse['is_activated']
        );
        $this->assertEquals($metaServiceConfiguration->getCritical(), $metaServiceConfigurationResponse['critical']);
        $this->assertEquals(
            $metaServiceConfiguration->getRegexpString(),
            $metaServiceConfigurationResponse['regexp_str']
        );
    }
}
