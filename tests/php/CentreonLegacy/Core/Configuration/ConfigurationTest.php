<?php
/**
 * Copyright 2019 Centreon
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
 */

namespace CentreonLegacy\Core\Configuration;

use PHPUnit\Framework\TestCase;
use CentreonLegacy\Core\Configuration;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Source\WidgetSource;
use Symfony\Component\Finder\Finder;

/**
 * @group CentreonLegacy
 * @group CentreonLegacy\Configuration
 */
class ConfigurationTest extends TestCase
{

    public function setUp(): void
    {
        $this->configuration = [
            'opt1' => 'val1',
            'opt2' => 'val2',
        ];
        $this->centreonPath = 'path';

        $this->service = new Configuration\Configuration($this->configuration, $this->centreonPath, new Finder());
    }

    public function testGet()
    {
        $key = 'opt1';
        $value = $this->configuration[$key];

        $result = $this->service->get($key);

        $this->assertEquals($result, $value);
    }

    public function testGetWithCentreonPath()
    {
        $key = Configuration\Configuration::CENTREON_PATH;
        $value = $this->centreonPath;

        $result = $this->service->get($key);

        $this->assertEquals($result, $value);
    }

    public function testGetModulePath()
    {
        $value = $this->centreonPath . ModuleSource::PATH;
        $result = $this->service->getModulePath();

        $this->assertEquals($result, $value);
    }

    public function testGetWidgetPath()
    {
        $value = $this->centreonPath . WidgetSource::PATH;
        $result = $this->service->getWidgetPath();

        $this->assertEquals($result, $value);
    }
}
