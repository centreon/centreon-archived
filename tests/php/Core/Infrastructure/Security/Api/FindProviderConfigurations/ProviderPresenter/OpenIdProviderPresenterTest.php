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

namespace Tests\Core\Infrastructure\Security\Api\FindProviderConfigurations\ProviderPresenter;

use Core\Infrastructure\Security\Api\FindProviderConfigurations\ProviderPresenter\OpenIdProviderPresenter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Application\Security\UseCase\FindProviderConfigurations\ProviderResponse\LocalProviderResponse;
use Core\Application\Security\UseCase\FindProviderConfigurations\ProviderResponse\OpenIdProviderResponse;

beforeEach(function () {
    $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

    $this->localProviderResponse = new LocalProviderResponse();

    $this->openIdProviderResponse = new OpenIdProviderResponse();
    $this->openIdProviderResponse->id = 1;
    $this->openIdProviderResponse->baseUrl = '/oauth2';
    $this->openIdProviderResponse->authorizationEndpoint = '/authorization';
    $this->openIdProviderResponse->clientId = 'client1';
    $this->openIdProviderResponse->isActive = true;
    $this->openIdProviderResponse->isForced = true;

    $this->presenter = new OpenIdProviderPresenter($this->urlGenerator);
});

it('does not manage responses which are not from open id provider', function () {
    expect($this->presenter->isValidFor($this->localProviderResponse))->toBe(false);
});

it('manages open id provider response', function () {
    expect($this->presenter->isValidFor($this->openIdProviderResponse))->toBe(true);
});

it('presents properly response data', function () {
    $this->urlGenerator
        ->expects($this->once())
        ->method('generate')
        ->willReturn('/redirection_uri');

    $presentedData = $this->presenter->present($this->openIdProviderResponse);
    expect($presentedData['id'])->toBe(1);
    expect($presentedData['type'])->toBe('openid');
    expect($presentedData['name'])->toBe('openid');
    expect($presentedData['authentication_uri'])->toMatch(
        '/^\/oauth2\/authorization\?client_id=client1&response_type=code&redirect_uri=\/redirection_uri&state=/'
    );
    expect($presentedData['is_active'])->toBe(true);
    expect($presentedData['is_forced'])->toBe(true);
});
