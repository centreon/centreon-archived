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

namespace Core\Security\Domain\ProviderConfiguration\Local\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\Application\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfigurationRequest;

class ConfigurationFactory
{
    /**
     * Create a Security Policy from the DTO.
     *
     * @param UpdateConfigurationRequest $request
     * @return Configuration
     * @throws AssertionException
     */
    public static function createFromRequest(UpdateConfigurationRequest $request): Configuration
    {
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

        return new Configuration($securityPolicy);
    }
}
