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

namespace Core\Application\Security\UseCase\UpdateSecurityPolicy;

class UpdateSecurityPolicyRequest
{
    private const MANDATORY_FIELDS = [
        'password_min_length',
        'has_uppercase',
        'has_lowercase',
        'has_number',
        'has_special_character',
        'attempts',
        'blocking_duration',
        'password_expiration',
        'can_reuse_passwords',
        'delay_before_new_password',
    ];

    /**
     * @var int
     */
    public int $passwordMinimumLength;

    /**
     * @var boolean
     */
    public bool $hasUppercase;

    /**
     * @var boolean
     */
    public bool $hasLowercase;

    /**
     * @var boolean
     */
    public bool $hasNumber;

    /**
     * @var boolean
     */
    public bool $hasSpecialCharacter;

    /**
     * @var boolean
     */
    public bool $canReusePassword;

    /**
     * @var int|null
     */
    public ?int $attempts;

    /**
     * @var int|null
     */
    public ?int $blockingDuration;

    /**
     * @var int|null
     */
    public ?int $passwordExpiration;

    /**
     * @var int|null
     */
    public ?int $delayBeforeNewPassword;

    /**
     * @param array<string,mixed> $requestData
     * @throws \InvalidArgumentException
     */
    public static function validateRequestOrFail(array $requestData): void
    {
        foreach (self::MANDATORY_FIELDS as $mandatoryFields) {
            if (!array_key_exists($mandatoryFields, $requestData)) {
                throw new \InvalidArgumentException(_('Bad Parameters'));
            }
        }
    }
}
