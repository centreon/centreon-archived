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
     * @param array<string,int|string|null> $data
     * @return MetaService
     */
    public static function createFromRecord(array $data): MetaService
    {
        /** @var string|null */
        $calculationType = $data['calculation_type'];
        $calculationType = self::normalizeCalculationType($calculationType);

        /** @var string|null */
        $output = $data['output'];

        /** @var string|null */
        $metric = $data['metric'];

        /** @var string|null */
        $regexSearchServices = $data['regexp_str'];

        return (new MetaService(
            (int) $data['id'],
            (string) $data['name'],
            $calculationType,
            (int) $data['meta_selection_mode'],
            self::normalizeDataSourceType((int) $data['data_source_type'])
        ))
        ->setWarningThreshold(self::getIntOrNull($data['warning']))
        ->setCriticalThreshold(self::getIntOrNull($data['critical']))
        ->setOutput($output)
        ->setMetric($metric)
        ->setActivated((int) $data['is_activated'] === 1)
        ->setRegexpSearchServices($regexSearchServices);
    }

    /**
     * This function will normalize the calculation type coming from the database
     *
     * @param string|null $calculationType
     * @return string
     */
    private static function normalizeCalculationType(?string $calculationType): string
    {
        return match ($calculationType) {
            'AVE' => MetaService::CALCULTATION_TYPE_AVERAGE,
            'MIN' => MetaService::CALCULTATION_TYPE_MINIMUM,
            'MAX' => MetaService::CALCULTATION_TYPE_MAXIMUM,
            'SOM' => MetaService::CALCULTATION_TYPE_SUM,
            default => MetaService::CALCULTATION_TYPE_AVERAGE
        };
    }

    /**
     * This function will normalize the data source type coming from the database
     *
     * @param int|null $dataSourceType
     * @return string
     */
    private static function normalizeDataSourceType(?int $dataSourceType): string
    {
        return match ($dataSourceType) {
            0 => MetaService::DATA_SOURCE_GAUGE,
            1 => MetaService::DATA_SOURCE_COUNTER,
            2 => MetaService::DATA_SOURCE_DERIVE,
            3 => MetaService::DATA_SOURCE_ABSOLUTE,
            default => MetaService::DATA_SOURCE_GAUGE
        };
    }
}
