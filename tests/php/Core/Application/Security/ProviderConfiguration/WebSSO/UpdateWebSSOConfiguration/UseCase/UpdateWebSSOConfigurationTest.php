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

namespace Tests\Core\Application\Security\ProviderConfiguration\WebSSO\UpdateWebSSOConfiguration\UseCase;

use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Security\ProviderConfiguration\WebSSO\Repository\WriteWebSSOConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfiguration;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfigurationPresenterInterface;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfigurationRequest;
use Core\Domain\Security\ProviderConfiguration\WebSSO\Model\WebSSOConfigurationFactory;

beforeEach(function () {
    $this->repository = $this->createMock(WriteWebSSOConfigurationRepositoryInterface::class);
    $this->presenter = $this->createMock(UpdateWebSSOConfigurationPresenterInterface::class);
});

it('execute the use case correctly when all parameters are valid', function () {
    $updateWebSSOConfigurationRequest = new UpdateWebSSOConfigurationRequest();
    $updateWebSSOConfigurationRequest->isActive = true;
    $updateWebSSOConfigurationRequest->isForced = false;
    $updateWebSSOConfigurationRequest->trustedClientAddresses = [];
    $updateWebSSOConfigurationRequest->blacklistClientAddresses = [];
    $updateWebSSOConfigurationRequest->loginHeaderAttribute = 'HTTP_AUTH_USER';
    $updateWebSSOConfigurationRequest->patternMatchingLogin = '/@.*/';
    $updateWebSSOConfigurationRequest->patternReplaceLogin = 'sso_';

    $configuration = WebSSOConfigurationFactory::createFromRequest($updateWebSSOConfigurationRequest);

    $this->repository
        ->expects($this->once())
        ->method('updateConfiguration')
        ->with($configuration);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NoContentResponse());

    $useCase = new UpdateWebSSOConfiguration($this->repository);
    $useCase($this->presenter, $updateWebSSOConfigurationRequest);
});

//@todo: Tests useCase with Invalid Parameters