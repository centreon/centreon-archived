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

namespace Tests\Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration;

use Centreon\Domain\Repository\RepositoryException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration\{
    FindWebSSOConfiguration,
    FindWebSSOConfigurationResponse
};

beforeEach(function () {
    $this->repository = $this->createMock(ReadWebSSOConfigurationRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
});

it('should present a FindWebSSOConfigurationResponse when everything goes well', function () {
    $configuration = new WebSSOConfiguration(
        true,
        false,
        ['127.0.0.1'],
        [],
        'HTTP_AUTH_USER',
        null,
        null,
    );

    $useCase = new FindWebSSOConfiguration($this->repository);
    $presenter = new FindWebSSOConfigurationPresenterStub($this->presenterFormatter);

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($configuration);

    $useCase($presenter);
    expect($presenter->response)->toBeInstanceOf(FindWebSSOConfigurationResponse::class);
    expect($presenter->response->isActive)->toBeTrue();
    expect($presenter->response->isForced)->toBeFalse();
    expect($presenter->response->trustedClientAddresses)->toBe(['127.0.0.1']);
    expect($presenter->response->blacklistClientAddresses)->toBeEmpty();
    expect($presenter->response->loginHeaderAttribute)->toBe('HTTP_AUTH_USER');
    expect($presenter->response->patternMatchingLogin)->toBeNull();
    expect($presenter->response->patternReplaceLogin)->toBeNull();
});

it('should present a NotFoundResponse when no configuration are found in Data storage', function () {
    $useCase = new FindWebSSOConfiguration($this->repository);
    $presenter = new FindWebSSOConfigurationPresenterStub($this->presenterFormatter);

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn(null);

    $useCase($presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())
        ->toBe((new NotFoundResponse('WebSSOConfiguration'))->getMessage());
});

it('should present an ErrorResponse when an error occured during the finding process', function () {
    $useCase = new FindWebSSOConfiguration($this->repository);
    $presenter = new FindWebSSOConfigurationPresenterStub($this->presenterFormatter);
    $exceptionMessage = 'An error occured';
    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willThrowException(new RepositoryException($exceptionMessage));

    $useCase($presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe($exceptionMessage);
});
