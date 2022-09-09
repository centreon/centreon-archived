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

namespace Core\Security\User\Application\UseCase\RenewPassword;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Security\ProviderConfiguration\Domain\Local\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\User\Domain\Model\UserPasswordFactory;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\User\Application\Repository\ReadUserRepositoryInterface;
use Core\Security\User\Application\Repository\WriteUserRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;

class RenewPassword
{
    use LoggerTrait;

    /**
     * @param ReadUserRepositoryInterface $readRepository
     * @param WriteUserRepositoryInterface $writeRepository
     * @param ReadConfigurationRepositoryInterface $readConfigurationRepository
     */
    public function __construct(
        private ReadUserRepositoryInterface $readRepository,
        private WriteUserRepositoryInterface $writeRepository,
        private ReadConfigurationRepositoryInterface $readConfigurationRepository
    ) {
    }

    /**
     * @param RenewPasswordPresenterInterface $presenter
     * @param RenewPasswordRequest $renewPasswordRequest
     */
    public function __invoke(
        RenewPasswordPresenterInterface $presenter,
        RenewPasswordRequest $renewPasswordRequest
    ): void {
        $this->info('Processing password renewal...');
        //Get User informations
        $user = $this->readRepository->findUserByAlias($renewPasswordRequest->userAlias);
        if ($user === null) {
            $this->error('No user could be found', [
                'user_alias' => $renewPasswordRequest->userAlias
            ]);
            $presenter->setResponseStatus(new NotFoundResponse('User'));
            return;
        }

        //Validate that old password matches the current user password
        if (password_verify($renewPasswordRequest->oldPassword, $user->getPassword()->getPasswordValue()) === false) {
            $this->notice('Credentials are invalid');
            $presenter->setResponseStatus(new UnauthorizedResponse('Invalid credentials'));
            return;
        }


        /** @var Configuration $providerConfiguration */
        $providerConfiguration = $this->readConfigurationRepository->getConfigurationByName(Provider::LOCAL);
        $this->info('Validate password against security policy');
        $newPassword = UserPasswordFactory::create(
            $renewPasswordRequest->newPassword,
            $user,
            $providerConfiguration->getCustomConfiguration()->getSecurityPolicy()
        );
        $user->setPassword($newPassword);

        $this->info('Updating user password', [
            'user_alias' => $user->getAlias()
        ]);
        $this->writeRepository->renewPassword($user);
        $presenter->setResponseStatus(new NoContentResponse());
    }
}
