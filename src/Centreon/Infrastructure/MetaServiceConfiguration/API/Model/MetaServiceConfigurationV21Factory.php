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

namespace Centreon\Infrastructure\MetaServiceConfiguration\API\Model;

use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindMetaServicesConfigurationsResponse;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindOneMetaServiceConfigurationResponse;

/**
 * This class is designed to create the MetaServiceConfigurationV21 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MetaService\API\Model
 */
class MetaServiceConfigurationV21Factory
{
    /**
     * @param FindOneMetaServiceConfigurationResponse $response
     * @return \stdClass
     */
    public static function createOneFromResponse(
        FindOneMetaServiceConfigurationResponse $response
    ): \stdClass {
        $newMetaServiceConfiguration = self::createEmptyClass();
        $metaServiceConfiguration = $response->getMetaServiceConfiguration();
        $newMetaServiceConfiguration->id = $metaServiceConfiguration['id'];
        $newMetaServiceConfiguration->name = $metaServiceConfiguration['name'];
        $newMetaServiceConfiguration->isActivated = $metaServiceConfiguration['is_activated'];
        $newMetaServiceConfiguration->output = $metaServiceConfiguration['meta_display'];
        $newMetaServiceConfiguration->calculationType = $metaServiceConfiguration['calcul_type'];
        $newMetaServiceConfiguration->dataSourceType = $metaServiceConfiguration['data_source_type'];
        $newMetaServiceConfiguration->metaSelectMode = $metaServiceConfiguration['meta_select_mode'];
        $newMetaServiceConfiguration->regexpString = $metaServiceConfiguration['regexp_str'];
        $newMetaServiceConfiguration->metric = $metaServiceConfiguration['metric'];
        $newMetaServiceConfiguration->warning = $metaServiceConfiguration['warning'];
        $newMetaServiceConfiguration->critical = $metaServiceConfiguration['critical'];
        return $newMetaServiceConfiguration;
    }

    /**
     * @param FindMetaServicesConfigurationsResponse $response
     * @return \stdClass[]
     */
    public static function createAllFromResponse(
        FindMetaServicesConfigurationsResponse $response
    ): array {
        $metaServicesConfigurations = [];
        foreach ($response->getMetaServicesConfigurations() as $metaServiceConfiguration) {
            $newMetaServiceConfiguration = self::createEmptyClass();
            $newMetaServiceConfiguration->id = $metaServiceConfiguration['id'];
            $newMetaServiceConfiguration->name = $metaServiceConfiguration['name'];
            $newMetaServiceConfiguration->isActivated = $metaServiceConfiguration['is_activated'];
            $newMetaServiceConfiguration->output = $metaServiceConfiguration['meta_display'];
            $newMetaServiceConfiguration->calculationType = $metaServiceConfiguration['calcul_type'];
            $newMetaServiceConfiguration->dataSourceType = $metaServiceConfiguration['data_source_type'];
            $newMetaServiceConfiguration->metaSelectMode = $metaServiceConfiguration['meta_select_mode'];
            $newMetaServiceConfiguration->regexpString = $metaServiceConfiguration['regexp_str'];
            $newMetaServiceConfiguration->metric = $metaServiceConfiguration['metric'];
            $newMetaServiceConfiguration->warning = $metaServiceConfiguration['warning'];
            $newMetaServiceConfiguration->critical = $metaServiceConfiguration['critical'];

            $metaServicesConfigurations[] = $newMetaServiceConfiguration;
        }
        return $metaServicesConfigurations;
    }

    /**
     * @return \stdClass
     */
    private static function createEmptyClass(): \stdClass
    {
        return new class extends \stdClass
        {
            public $id;
            public $name;
            public $isActivated;
            public $output;
            public $calculationType;
            public $dataSourceType;
            public $metaSelectMode;
            public $regexpString;
            public $metric;
            public $warning;
            public $critical;
        };
    }
}
