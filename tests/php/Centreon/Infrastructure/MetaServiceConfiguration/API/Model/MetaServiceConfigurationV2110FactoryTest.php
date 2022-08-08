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

namespace Tests\Centreon\Infrastructure\MetaServiceConfiguration\API\Model;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V2110\FindMetaServicesConfigurationsResponse;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V2110\FindOneMetaServiceConfigurationResponse;
use Centreon\Infrastructure\MetaServiceConfiguration\API\Model\MetaServiceConfigurationV2110Factory;
use Tests\Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfigurationTest;

/**
 * @package Tests\Centreon\Infrastructure\MetaServiceConfiguration\API\Model
 */
class MetaServiceConfigurationV2110FactoryTest extends TestCase
{
    /**
     * @var MetaServiceConfiguration
     */
    private $metaServiceConfiguration;

    protected function setUp(): void
    {
        $this->metaServiceConfiguration = MetaServiceConfigurationTest::createEntity();
    }

    /**
     * We check the format sent for the API request (v21.10) using the factory
     */
    public function testCreateAllFromResponse(): void
    {
        $response = new FindMetaServicesConfigurationsResponse();
        $response->setMetaServicesConfigurations([$this->metaServiceConfiguration]);
        $metaServiceConfigurationV21 = MetaServiceConfigurationV2110Factory::createAllFromResponse($response);

        $metaServiceConfiguration = $response->getMetaServicesConfigurations()[0];
        $this->assertEquals($metaServiceConfiguration['id'], $metaServiceConfigurationV21[0]->id);
        $this->assertEquals($metaServiceConfiguration['name'], $metaServiceConfigurationV21[0]->name);
        $this->assertEquals($metaServiceConfiguration['meta_display'], $metaServiceConfigurationV21[0]->output);
        $this->assertEquals(
            $metaServiceConfiguration['data_source_type'],
            $metaServiceConfigurationV21[0]->dataSourceType
        );
        $this->assertEquals($metaServiceConfiguration['regexp_str'], $metaServiceConfigurationV21[0]->regexpString);
        $this->assertEquals($metaServiceConfiguration['warning'], $metaServiceConfigurationV21[0]->warning);
        $this->assertEquals($metaServiceConfiguration['critical'], $metaServiceConfigurationV21[0]->critical);
        $this->assertEquals(
            $metaServiceConfiguration['meta_select_mode'],
            $metaServiceConfigurationV21[0]->metaSelectMode
        );
        $this->assertEquals($metaServiceConfiguration['is_activated'], $metaServiceConfigurationV21[0]->isActivated);
    }

    /**
     * We check the format sent for the API request (v21.10) using the factory
     */
    public function testCreateFromResponse(): void
    {
        $response = new FindOneMetaServiceConfigurationResponse();
        $response->setMetaServiceConfiguration($this->metaServiceConfiguration);
        $metaServiceConfigurationV21 = MetaServiceConfigurationV2110Factory::createOneFromResponse($response);

        $metaServiceConfiguration = $response->getMetaServiceConfiguration();
        $this->assertEquals($metaServiceConfiguration['id'], $metaServiceConfigurationV21->id);
        $this->assertEquals($metaServiceConfiguration['name'], $metaServiceConfigurationV21->name);
        $this->assertEquals($metaServiceConfiguration['meta_display'], $metaServiceConfigurationV21->output);
        $this->assertEquals(
            $metaServiceConfiguration['data_source_type'],
            $metaServiceConfigurationV21->dataSourceType
        );
        $this->assertEquals($metaServiceConfiguration['regexp_str'], $metaServiceConfigurationV21->regexpString);
        $this->assertEquals($metaServiceConfiguration['warning'], $metaServiceConfigurationV21->warning);
        $this->assertEquals($metaServiceConfiguration['critical'], $metaServiceConfigurationV21->critical);
        $this->assertEquals(
            $metaServiceConfiguration['meta_select_mode'],
            $metaServiceConfigurationV21->metaSelectMode
        );
        $this->assertEquals($metaServiceConfiguration['is_activated'], $metaServiceConfigurationV21->isActivated);
    }
}
