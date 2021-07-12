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

namespace Tests\Security\Infrastructure\Authentication\Repository\Model;

use Security\Infrastructure\Repository\Model\ProviderConfigurationFactoryRdb;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Security\Infrastructure\Authentication\API\Model_2110
 */
class ProviderConfigurationFactoryRdbTest extends TestCase
{
    /**
     * @var array<string,int|string|bool> $dbData
     */
    private $dbData;

    protected function setUp(): void
    {
        $this->dbData = [
            'id' => 1,
            'type' => 'local',
            'name' => 'local',
            'is_active' => true,
            'is_forced' => true,
        ];
    }

    /**
     * Tests model is properly created
     */
    public function testCreateWithAllProperties()
    {
        $providerConfiguration = ProviderConfigurationFactoryRdb::create($this->dbData);

        $this->assertInstanceOf(ProviderConfiguration::class, $providerConfiguration);
        $this->assertEquals($this->dbData['id'], $providerConfiguration->getId());
        $this->assertEquals($this->dbData['type'], $providerConfiguration->getType());
        $this->assertEquals($this->dbData['name'], $providerConfiguration->getName());
        $this->assertEquals($this->dbData['is_active'], $providerConfiguration->isActive());
        $this->assertEquals($this->dbData['is_forced'], $providerConfiguration->isForced());
    }

     /**
     * Tests model is properly created
     */
    public function testCreateFromResponseWithMissingProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing mandatory parameter: 'is_forced'");

        $dbData = array_slice($this->dbData, 0, 4, true);
        ProviderConfigurationFactoryRdb::create($dbData);
    }
}
