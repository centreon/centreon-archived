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

namespace Tests\Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations;

use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\{
    FindProviderConfigurations,
    FindProviderConfigurationsPresenterInterface,
    ProviderResponse\LocalProviderResponse
};
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Security\ProviderConfiguration\Application\Repository\ReadProviderConfigurationsRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;

beforeEach(function () {
    $this->readProviderConfigurationRepository = $this->createMock(
        ReadProviderConfigurationsRepositoryInterface::class
    );
    $this->providerResponse = new LocalProviderResponse();
    $this->presenter = $this->createMock(FindProviderConfigurationsPresenterInterface::class);
    $this->localConfiguration = $this->createMock(Configuration::class);
    $this->readConfigurationRepository = $this->createMock(ReadConfigurationRepositoryInterface::class);

    $this->useCase = new FindProviderConfigurations(
        new \ArrayObject([$this->providerResponse]),
        $this->readConfigurationRepository
    );
});

it('returns error when there is an issue during configurations search', function () {
    $errorMessage = 'error during configurations search';

    $this->readConfigurationRepository
        ->expects($this->once())
        ->method('findConfigurations')
        ->willThrowException(new \Exception($errorMessage));

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse($errorMessage));

    ($this->useCase)($this->presenter);
});

it('presents an empty array when configurations are not found', function () {
    $this->readConfigurationRepository
        ->expects($this->once())
        ->method('findConfigurations')
        ->willReturn([]);

    $this->presenter
        ->expects($this->once())
        ->method('present')
        ->with([]);

    ($this->useCase)($this->presenter);
});

it('presents found configurations', function () {
    $this->readConfigurationRepository
        ->expects($this->once())
        ->method('findConfigurations')
        ->willReturn([$this->localConfiguration]);

    $this->localConfiguration
        ->expects($this->exactly(2))
        ->method('getType')
        ->willReturn('local');

    $this->localConfiguration
        ->expects($this->once())
        ->method('getId')
        ->willReturn(1);

    $this->localConfiguration
        ->expects($this->once())
        ->method('getName')
        ->willReturn('local');

    $this->localConfiguration
        ->expects($this->once())
        ->method('isActive')
        ->willReturn(true);

    $this->localConfiguration
        ->expects($this->once())
        ->method('isForced')
        ->willReturn(true);

    $providerResponse = new LocalProviderResponse();
    $providerResponse->id = 1;
    $providerResponse->type = 'local';
    $providerResponse->name = 'local';
    $providerResponse->isActive = true;
    $providerResponse->isForced = true;

    $this->presenter
        ->expects($this->once())
        ->method('present')
        ->with([$providerResponse]);

    ($this->useCase)($this->presenter);
});
