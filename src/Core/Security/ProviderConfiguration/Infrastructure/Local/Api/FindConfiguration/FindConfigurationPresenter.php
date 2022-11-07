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

namespace Core\Security\ProviderConfiguration\Infrastructure\Local\Api\FindConfiguration;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\FindConfigurationResponse;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\FindConfigurationPresenterInterface;

class FindConfigurationPresenter extends AbstractPresenter implements FindConfigurationPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindConfigurationResponse $data
     */
    public function present(mixed $data): void
    {
        $presenterResponse = [
            'password_security_policy' => [
                'password_min_length' => $data->passwordMinimumLength,
                'has_uppercase' => $data->hasUppercase,
                'has_lowercase' => $data->hasLowercase,
                'has_number' => $data->hasNumber,
                'has_special_character' => $data->hasSpecialCharacter,
                'attempts' => $data->attempts,
                'blocking_duration' => $data->blockingDuration,
                'password_expiration' => [
                    'expiration_delay' => $data->passwordExpirationDelay,
                    'excluded_users' => $data->passwordExpirationExcludedUserAliases,
                ],
                'can_reuse_passwords' => $data->canReusePasswords,
                'delay_before_new_password' => $data->delayBeforeNewPassword,
            ]
        ];

        parent::present($presenterResponse);
    }
}
