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

namespace Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model;

use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;

/**
 * This class is designed to provide a way to create the MetaServiceConfiguration entity from the database.
 *
 * @package Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model
 */
class MetaServiceConfigurationFactoryRdb
{
    /**
     * Create a MetaServiceConfiguration entity from database data.
     *
     * @param array<string, mixed> $data
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): MetaServiceConfiguration
    {
        $metaServiceConfiguration = (new MetaServiceConfiguration(
            $data['meta_name'],
            $data['calculation_type'],
            (int) $data['meta_select_mode']
        ))
            ->setId((int) $data['meta_id'])
            ->setActivated($data['meta_activate'] === '1')
            ->setOutput($data['meta_display'])
            ->setDataSourceType($data['data_source_type'])
            ->setRegexpString($data['regexp_str'])
            ->setMetric($data['metric'])
            ->setWarning($data['warning'])
            ->setCritical($data['critical']);
        return $metaServiceConfiguration;
    }
}
