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


namespace Tests\Security\Infrastructure\Authentication\API\Model_2110;

use Security\Infrastructure\Authentication\API\Model_2110\ProvidersConfigurationsFactory;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Security\Infrastructure\Authentication\API\Model_2110
 */
class ProvidersConfigurationsFactoryTest extends TestCase
{
    /**
     * @var array<array<string, int|string|bool>> $responseData
     */
    private $responseData;

    /**
     * @var ProviderConfiguration[] $providersConfigurations
     */
    private $providersConfigurations;

    protected function setUp(): void
    {
        $this->responseData = [
            [
                'id' => 1,
                'type' => 'local',
                'name' => 'local',
                'centreon_base_uri' => '/centreon',
                'is_forced' => true,
                'is_active' => true,
                'authentication_uri' => '/centreon/authentication/providers/configurations/local',
            ],
            [
                'id' => 2,
                'type' => 'saml',
                'name' => 'okta',
                'centreon_base_uri' => '/centreon',
                'is_forced' => false,
                'is_active' => true,
                'authentication_uri' => '/centreon/authentication/providers/configurations/okta',
            ],
        ];

        $this->providersConfigurations = [
            (new ProviderConfiguration(1, 'local', 'local', true, true, '/centreon')),
            (new ProviderConfiguration(2, 'saml', 'okta', true, false, '/centreon')),
        ];
    }

    /**
     * Tests model is properly created
     */
    public function testCreateFromResponseWithAllProperties()
    {
        $response = new FindProvidersConfigurationsResponse();
        $response->setProvidersConfigurations($this->providersConfigurations);
        $providersConfigurations = ProvidersConfigurationsFactory::createFromResponse($response);

        $this->assertCount(2, $providersConfigurations);
        foreach ($providersConfigurations as $index => $providerConfiguration) {
            $this->assertEquals($this->responseData[$index]['id'], $providerConfiguration->id);
            $this->assertEquals($this->responseData[$index]['type'], $providerConfiguration->type);
            $this->assertEquals($this->responseData[$index]['name'], $providerConfiguration->name);
            $this->assertEquals(
                $this->responseData[$index]['centreon_base_uri'],
                $providerConfiguration->centreonBaseUri
            );
            $this->assertEquals($this->responseData[$index]['is_forced'], $providerConfiguration->isForced);
            $this->assertEquals($this->responseData[$index]['is_active'], $providerConfiguration->isActive);
            $this->assertEquals(
                $this->responseData[$index]['authentication_uri'],
                $providerConfiguration->authenticationUri
            );
        }
    }
}
