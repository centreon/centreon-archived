<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Configuration\MetaService\Repository;

use Core\Domain\Configuration\Model\MetaService;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbMetaServiceFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $data
     * @return MetaService
     */
    public static function createFromRecord(array $data): MetaService
    {
        return (new MetaService(
            (int) $data['id'],
            $data['name'],
            self::normalizeCalculationType($data['calculation_type']),
            (int) $data['meta_selection_mode'],
            self::normalizeDataSourceType((int) $data['data_source_type'])
        ))
        ->setWarning(self::getIntOrNull($data['warning']))
        ->setCritical(self::getIntOrNull($data['critical']))
        ->setOutput($data['output'])
        ->setMetric($data['metric'])
        ->setActivated((int) $data['is_activated'] === 1)
        ->setRegexpSearchServices($data['regexp_search_services']);
    }

    /**
     * This function will normalize the calculation type coming from the database
     *
     * @param string|null $calculationType
     * @return string
     */
    private static function normalizeCalculationType(?string $calculationType): string
    {
        switch ($calculationType) {
            case 'AVE':
                return MetaService::CALCULTATION_TYPE_AVERAGE;
            case 'MIN':
                return MetaService::CALCULTATION_TYPE_MINIMUM;
            case 'MAX':
                return MetaService::CALCULTATION_TYPE_MAXIMUM;
            case 'SOM':
                return MetaService::CALCULTATION_TYPE_SUM;
            default:
                return MetaService::CALCULTATION_TYPE_AVERAGE;
        }
    }

    /**
     * This function will normalize the data source type coming from the database
     *
     * @param int|null $dataSourceType
     * @return string
     */
    private static function normalizeDataSourceType(?int $dataSourceType): string
    {
        switch ($dataSourceType) {
            case 0:
                return MetaService::DATA_SOURCE_GAUGE;
            case 1:
                return MetaService::DATA_SOURCE_COUNTER;
            case 2:
                return MetaService::DATA_SOURCE_DERIVE;
            case 3:
                return MetaService::DATA_SOURCE_ABSOLUTE;
            default:
                return MetaService::DATA_SOURCE_GAUGE;
        }
    }
}
