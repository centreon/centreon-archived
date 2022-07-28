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

namespace Tests\Core\Security\ProviderConfiguration\Infrastructure\Api\FindProviderConfigurations\ProviderPresenter;

use Core\Security\ProviderConfiguration\Infrastructure\Api\FindProviderConfigurations\ProviderPresenter\{
    LocalProviderPresenter
};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\ProviderResponse\{
    LocalProviderResponse,
    OpenIdProviderResponse
};

beforeEach(function () {
    $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

    $this->localProviderResponse = new LocalProviderResponse();
    $this->localProviderResponse->id = 1;
    $this->localProviderResponse->type = 'local';
    $this->localProviderResponse->name = 'local';
    $this->localProviderResponse->isActive = true;
    $this->localProviderResponse->isForced = true;

    $this->openIdProviderResponse = new OpenIdProviderResponse();

    $this->presenter = new LocalProviderPresenter($this->urlGenerator);
});

it('does not manage responses which are not from local provider', function () {
    expect($this->presenter->isValidFor($this->openIdProviderResponse))->toBe(false);
});

it('manages local provider response', function () {
    expect($this->presenter->isValidFor($this->localProviderResponse))->toBe(true);
});

it('presents properly response data', function () {
    $this->urlGenerator
        ->expects($this->once())
        ->method('generate')
        ->willReturn('/authentication_uri');

    expect($this->presenter->present($this->localProviderResponse))
        ->toBe([
            'id' => 1,
            'type' => 'local',
            'name' => 'local',
            'authentication_uri' => '/authentication_uri',
            'is_active' => true,
            'is_forced' => true,
        ]);
});
