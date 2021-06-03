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

namespace Tests\Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model;

use Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model\MetaServiceConfigurationFactoryRdb;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model
 */
class MetaServiceConfigurationFactoryRdbTest extends TestCase
{
    /**
     * @var array<string, string|int> $rdbData
     */
    private $rdbData;

    protected function setUp(): void
    {
        $this->rdbData = [
            'meta_id' => 1,
            'meta_name' => 'meta-test',
            'calculation_type' => 'average',
            'data_source_type' => 'gauge',
            'meta_activate' => '1',
            'meta_display' => 'META: %s',
            'metric' => 'rta',
            'warning' => '5',
            'critical' => '10',
            'regexp_str' => '%Ping%',
            'meta_select_mode' => 1
        ];
    }

    /**
     * Tests the of the good creation of the MetaServiceConfiguration entity
     * We test all properties.
     */
    public function testAllPropertiesOnCreate(): void
    {
        $metaServiceConfiguration = MetaServiceConfigurationFactoryRdb::create($this->rdbData);
        $this->assertEquals($this->rdbData['meta_id'], $metaServiceConfiguration->getId());
        $this->assertEquals($this->rdbData['meta_name'], $metaServiceConfiguration->getName());
        $this->assertEquals($this->rdbData['calculation_type'], $metaServiceConfiguration->getCalculationType());
        $this->assertEquals($this->rdbData['data_source_type'], $metaServiceConfiguration->getDataSourceType());
        $this->assertEquals($this->rdbData['meta_display'], $metaServiceConfiguration->getOutput());
        $this->assertEquals($this->rdbData['metric'], $metaServiceConfiguration->getMetric());
        $this->assertEquals($this->rdbData['warning'], $metaServiceConfiguration->getWarning());
        $this->assertEquals($this->rdbData['critical'], $metaServiceConfiguration->getCritical());
        $this->assertEquals($this->rdbData['regexp_str'], $metaServiceConfiguration->getRegexpString());
        $this->assertEquals((bool) $this->rdbData['meta_activate'], $metaServiceConfiguration->isActivated());
    }
}
