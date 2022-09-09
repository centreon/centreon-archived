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

namespace Centreon\Domain\MetaServiceConfiguration\UseCase\V2110;

use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;

/**
 * This class is a DTO for the FindMetaServicesConfigurations use case.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\UseCase\V2110
 */
class FindOneMetaServiceConfigurationResponse
{
    /**
     * @var array<string, mixed>
     */
    private $metaServiceConfiguration;

    /**
     * @param MetaServiceConfiguration $metaServiceConfiguration
     */
    public function setMetaServiceConfiguration(MetaServiceConfiguration $metaServiceConfiguration): void
    {
        $this->metaServiceConfiguration = [
            'id' => $metaServiceConfiguration->getId(),
            'name' => $metaServiceConfiguration->getName(),
            'meta_display' => $metaServiceConfiguration->getOutput(),
            'meta_select_mode' => $metaServiceConfiguration->getMetaSelectMode(),
            'data_source_type' => $metaServiceConfiguration->getDataSourceType(),
            'calcul_type' => $metaServiceConfiguration->getCalculationType(),
            'regexp_str' => $metaServiceConfiguration->getRegexpString(),
            'metric' => $metaServiceConfiguration->getMetric(),
            'warning' => $metaServiceConfiguration->getWarning(),
            'critical' => $metaServiceConfiguration->getCritical(),
            'is_activated' => $metaServiceConfiguration->isActivated(),
        ];
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getMetaServiceConfiguration(): ?array
    {
        return $this->metaServiceConfiguration;
    }
}
