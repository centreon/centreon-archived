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
 *  For more information : contact@centreon.com
 */

declare(strict_types=1);

namespace Tests\Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration;

use Centreon\Domain\Repository\RepositoryException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\InvalidEndpointException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration\{
    FindWebSSOConfiguration,
    FindWebSSOConfigurationResponse
};

beforeEach(function () {
    $this->custom_relative_url = '/info';
    $this->custom_url = 'https://domain.com/info';
});

it('should throw an exception with a bad endpoint type', function () {
    (new Endpoint('bad_type', $this->custom_relative_url));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidType()->getMessage());

it('should throw an exception with a bad relative URL', function () {
    (new Endpoint(Endpoint::CUSTOM, 'bad_relative_url'));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidUrl()->getMessage());

it('should return an EndpointCondition instance with a correct relative URL', function () {
    $endpointCondition = new Endpoint(Endpoint::CUSTOM, $this->custom_relative_url);
    expect($endpointCondition->getUrl())->toBe($this->custom_relative_url);
});

it('should return an EndpointCondition instance with a correct URL', function () {
    $endpointCondition = new Endpoint(Endpoint::CUSTOM, $this->custom_url);
    expect($endpointCondition->getUrl())->toBe($this->custom_url);
});

it('should return an EndpointCondition instance with an empty URL if type is not custom', function () {
    $endpointCondition = new Endpoint(Endpoint::INTROSPECTION, $this->custom_url);
    expect($endpointCondition->getUrl())->toBeNull();

    $endpointCondition = new Endpoint(Endpoint::USER_INFORMATION, $this->custom_url);
    expect($endpointCondition->getUrl())->toBeNull();
});

it('should throw an exception with a null URL and a custom type', function () {
    (new Endpoint(Endpoint::CUSTOM, null));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidUrl()->getMessage());

it('should throw an exception with an empty URL and a custom type', function () {
    (new Endpoint(Endpoint::CUSTOM, ''));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidUrl()->getMessage());
