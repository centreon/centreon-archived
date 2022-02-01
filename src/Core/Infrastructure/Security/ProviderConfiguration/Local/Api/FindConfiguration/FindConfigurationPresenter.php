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

namespace Core\Infrastructure\Security\ProviderConfiguration\Local\Api\FindConfiguration;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfigurationResponse;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfigurationPresenterInterface;

class FindConfigurationPresenter extends AbstractPresenter implements FindConfigurationPresenterInterface
{
    /**
     * @inheritDoc
     */
    public function present(FindConfigurationResponse $response): void
    {
        $presenterResponse = [
            'password_security_policy' => [
                'password_min_length' => $response->passwordMinimumLength,
                'has_uppercase' => $response->hasUppercase,
                'has_lowercase' => $response->hasLowercase,
                'has_number' => $response->hasNumber,
                'has_special_character' => $response->hasSpecialCharacter,
                'attempts' => $response->attempts,
                'blocking_duration' => $response->blockingDuration,
                'password_expiration' => $response->passwordExpiration,
                'can_reuse_passwords' => $response->canReusePasswords,
                'delay_before_new_password' => $response->delayBeforeNewPassword,
            ]
        ];

        $this->presenterFormatter->present($presenterResponse);
    }
}
