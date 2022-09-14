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
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\EndpointCondition;
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

it('it should throw an exception with a bad endpoint type', function () {
    (new EndpointCondition('bad_type', $this->custom_relative_url));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidType()->getMessage());

it('it should throw an exception with a bad relative url', function () {
    (new EndpointCondition(EndpointCondition::CUSTOM, 'bad_relative_url'));
})->throws(InvalidEndpointException::class, InvalidEndpointException::invalidUrl()->getMessage());

it('it should return an EndpointCondition instance with a correct relative url', function () {
    $endpointCondition = new EndpointCondition(EndpointCondition::CUSTOM, $this->custom_relative_url);
    expect($endpointCondition->getUrl())->toBe($this->custom_relative_url);
});

it('it should return an EndpointCondition instance with a correct url', function () {
    $endpointCondition = new EndpointCondition(EndpointCondition::CUSTOM, $this->custom_url);
    expect($endpointCondition->getUrl())->toBe($this->custom_url);
});

it('it should return an EndpointCondition instance with an empty url if type is not custom', function () {
    $endpointCondition = new EndpointCondition(EndpointCondition::INTROSPECTION, $this->custom_url);
    expect($endpointCondition->getUrl())->toHaveLength(0);

    $endpointCondition = new EndpointCondition(EndpointCondition::USER_INFORMATION, $this->custom_url);
    expect($endpointCondition->getUrl())->toHaveLength(0);
});