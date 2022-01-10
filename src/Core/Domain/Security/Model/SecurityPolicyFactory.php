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

namespace Core\Domain\Security\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyRequest;

class SecurityPolicyFactory
{
    /**
     * Create a Security Policy from the DTO.
     *
     * @param UpdateSecurityPolicyRequest $request
     * @return SecurityPolicy
     * @throws AssertionException
     */
    public static function createFromRequest(UpdateSecurityPolicyRequest $request): SecurityPolicy
    {
        return new SecurityPolicy(
            $request->passwordMinimumLength,
            $request->hasUppercase,
            $request->hasLowercase,
            $request->hasNumber,
            $request->hasSpecialCharacter,
            $request->canReusePasswords,
            $request->attempts,
            $request->blockingDuration,
            $request->passwordExpiration,
            $request->delayBeforeNewPassword
        );
    }
}
