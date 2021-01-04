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

namespace Centreon\Domain\ConfigurationLoader;

use PHPUnit\Framework\TestCase;

class YamlConfigurationLoaderTest extends TestCase
{
    /**
     * This test is designed to test the ability to load files and test include calls.
     */
    public function testLoad()
    {
        $gcl = new YamlConfigurationLoader(__DIR__ . '/root_file.yaml');
        $configuration = $gcl->load();
        $this->assertArrayHasKey('name', $configuration);
        $this->assertEquals($configuration['name'], 'text1');

        $this->assertArrayHasKey('tab', $configuration);
        $this->assertIsArray($configuration['tab']);
        $this->assertArrayHasKey('key1', $configuration['tab']);
        $this->assertEquals($configuration['tab']['key1'], 'value1');
        $this->assertArrayHasKey('key2', $configuration['tab']);
        $this->assertEquals($configuration['tab']['key2'], 'value2');

        $this->assertArrayHasKey('child', $configuration);
        $this->assertIsArray($configuration['child']);
        $this->assertArrayHasKey('child_key', $configuration['child']);
        $this->assertEquals($configuration['child']['child_key'], 'value_child_key');
        $this->assertArrayHasKey('child_key2', $configuration['child']);
        $this->assertIsArray($configuration['child']['child_key2']);
        $this->assertArrayHasKey('loop', $configuration['child']['child_key2']);
        $this->assertEquals($configuration['child']['child_key2']['loop'], 'no loop');

        $this->assertArrayHasKey('extra', $configuration['child']['child_key2']);
        $this->assertIsArray($configuration['child']['child_key2']['extra']);
        $this->assertArrayHasKey(0, $configuration['child']['child_key2']['extra']);
        $this->assertArrayHasKey(1, $configuration['child']['child_key2']['extra']);
    }

    /**
     * This test is designed to detect a loop in file calls.
     */
    public function testLoadWithLoop()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/^Loop detected in file.*child5\.yaml$/");
        $gcl = new YamlConfigurationLoader(__DIR__ . '/root_file_with_loop.yaml');
        $gcl->load();
    }

    /***
     * This test is designed to test the Exception
     */
    public function testFileNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/^The configuration file '.*no_file\.yaml' does not exists$/");
        $gcl = new YamlConfigurationLoader(__DIR__ . '/no_file.yaml');
        $gcl->load();
    }
}
