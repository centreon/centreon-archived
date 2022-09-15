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

namespace Core\Security\User\Domain\Model;

use Core\Security\User\Domain\Model\User;
use Centreon\Domain\Common\Assertion\Assertion;
use Core\Security\User\Domain\Model\UserPassword;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\User\Domain\Exception\UserPasswordException;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Security\ProviderConfiguration\Infrastructure\Local\Api\Exception\ConfigurationException;

class UserPasswordFactory
{
    /**
     * Validate the security policy and create the User password.
     *
     * @param string $password
     * @param User $user
     * @param SecurityPolicy $securityPolicy
     * @return UserPassword
     * @throws AssertionException|ConfigurationException
     */
    public static function create(string $password, User $user, SecurityPolicy $securityPolicy): UserPassword
    {
        try {
            Assertion::minLength(
                $password,
                $securityPolicy->getPasswordMinimumLength(),
                'UserPassword::passwordValue'
            );
            if ($securityPolicy->hasNumber()) {
                Assertion::regex($password, '/[0-9]/', 'UserPassword::passwordValue');
            }
            if ($securityPolicy->hasUppercase()) {
                Assertion::regex($password, '/[A-Z]/', 'UserPassword::passwordValue');
            }
            if ($securityPolicy->hasLowercase()) {
                Assertion::regex($password, '/[a-z]/', 'UserPassword::passwordValue');
            }
            if ($securityPolicy->hasSpecialCharacter()) {
                Assertion::regex(
                    $password,
                    '/[' . SecurityPolicy::SPECIAL_CHARACTERS_LIST . ']/',
                    'UserPassword::passwordValue'
                );
            }
        } catch (AssertionException $ex) {
            //Throw a generic user password exception to avoid returning a plain password in the AssertionException.
            throw UserPasswordException::passwordDoesnotMatchSecurityPolicy();
        }

        //Verify that an old passwords is not reused
        if ($securityPolicy->canReusePasswords() === false) {
            foreach ($user->getOldPasswords() as $oldPassword) {
                if (password_verify($password, $oldPassword->getPasswordValue())) {
                    throw  new ConfigurationException(_('Old password usage is disable'));
                }
            }
        }
        $newPasswordValue = password_hash($password, \CentreonAuth::PASSWORD_HASH_ALGORITHM);

        return new UserPassword($user->getId(), $newPasswordValue, new \DateTimeImmutable());
    }
}
