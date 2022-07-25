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

namespace Core\Security\Application\ProviderConfiguration\Local\UseCase\FindConfiguration;

use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Application\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfigurationPresenterInterface;
use Core\Security\Domain\ProviderConfiguration\Local\Model\Configuration;

class FindConfiguration
{
    use LoggerTrait;

    /**
     * @param ReadConfigurationRepositoryInterface $repository
     */
    public function __construct(private readonly ReadConfigurationRepositoryInterface $repository)
    {
    }

    /**
     * @param FindConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindConfigurationPresenterInterface $presenter): void
    {
        $this->debug('Searching for local provider configuration');

        try {
            $configuration = $this->repository->findConfiguration();
        } catch (\Throwable $e) {
            $this->critical($e->getMessage());
            $presenter->setResponseStatus(
                new FindConfigurationErrorResponse($e->getMessage())
            );
            return;
        }

        if ($configuration === null) {
            $this->critical(
                'Local provider configuration not found : check that your installation / upgrade went well. ' .
                'A local provider configuration is necessary to manage password security policy.'
            );
            $presenter->setResponseStatus(
                new FindConfigurationErrorResponse(
                    'Local provider configuration not found. Please verify that your installation is valid'
                )
            );
            return;
        }

        $presenter->present($this->createResponse($configuration));
    }

    public function createResponse(Configuration $configuration): FindConfigurationResponse
    {
        $response = new FindConfigurationResponse();
        $response->passwordMinimumLength = $configuration->getSecurityPolicy()->getPasswordMinimumLength();
        $response->hasUppercase = $configuration->getSecurityPolicy()->hasUppercase();
        $response->hasLowercase = $configuration->getSecurityPolicy()->hasLowercase();
        $response->hasNumber = $configuration->getSecurityPolicy()->hasNumber();
        $response->hasSpecialCharacter = $configuration->getSecurityPolicy()->hasSpecialCharacter();
        $response->canReusePasswords = $configuration->getSecurityPolicy()->canReusePasswords();
        $response->attempts = $configuration->getSecurityPolicy()->getAttempts();
        $response->blockingDuration = $configuration->getSecurityPolicy()->getBlockingDuration();
        $response->passwordExpirationDelay = $configuration->getSecurityPolicy()->getPasswordExpirationDelay();
        $response->passwordExpirationExcludedUserAliases =
            $configuration
                ->getSecurityPolicy()
                ->getPasswordExpirationExcludedUserAliases();
        $response->delayBeforeNewPassword = $configuration->getSecurityPolicy()->getDelayBeforeNewPassword();

        return $response;
    }
}
