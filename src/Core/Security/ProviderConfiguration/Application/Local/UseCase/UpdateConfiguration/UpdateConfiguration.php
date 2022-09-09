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

namespace Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\ProviderConfiguration\Application\Local\Repository\WriteConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Local\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;

class UpdateConfiguration
{
    use LoggerTrait;

    /**
     * @param WriteConfigurationRepositoryInterface $writeConfigurationRepository
     * @param ReadUserRepositoryInterface $readUserRepository
     * @param ProviderAuthenticationFactoryInterface $providerFactory
     */
    public function __construct(
        private WriteConfigurationRepositoryInterface $writeConfigurationRepository,
        private ReadUserRepositoryInterface $readUserRepository,
        private ProviderAuthenticationFactoryInterface $providerFactory
    ) {
    }

    /**
     * @param UpdateConfigurationPresenterInterface $presenter
     * @param UpdateConfigurationRequest $request
     */
    public function __invoke(
        UpdateConfigurationPresenterInterface $presenter,
        UpdateConfigurationRequest $request
    ): void {
        $this->info('Updating Security Policy');

        try {
            $provider = $this->providerFactory->create(Provider::LOCAL);
            /** @var Configuration $configuration */
            $configuration = $provider->getConfiguration();

            $securityPolicy = new SecurityPolicy(
                $request->passwordMinimumLength,
                $request->hasUppercase,
                $request->hasLowercase,
                $request->hasNumber,
                $request->hasSpecialCharacter,
                $request->canReusePasswords,
                $request->attempts,
                $request->blockingDuration,
                $request->passwordExpirationDelay,
                $request->passwordExpirationExcludedUserAliases,
                $request->delayBeforeNewPassword
            );

            $excludedUserIds = $this->readUserRepository->findUserIdsByAliases(
                $request->passwordExpirationExcludedUserAliases
            );

            $configuration->setCustomConfiguration(new CustomConfiguration($securityPolicy));
            $this->writeConfigurationRepository->updateConfiguration($configuration, $excludedUserIds);

            $presenter->setResponseStatus(new NoContentResponse());
        } catch (AssertionException $ex) {
            $this->error('Unable to create Security Policy because one or several parameters are invalid');
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }
    }
}
