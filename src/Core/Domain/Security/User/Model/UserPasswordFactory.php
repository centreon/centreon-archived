<?php

namespace Core\Domain\Security\User\Model;

use Core\Domain\Security\User\Model\User;
use Centreon\Domain\Common\Assertion\Assertion;
use Core\Domain\Security\User\Model\UserPassword;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Application\Security\Exception\UserPasswordException;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Api\Exception\ConfigurationException;

class UserPasswordFactory
{
    /**
     * Validate the security policy and create the User password.
     *
     * @param string $password
     * @param User $user
     * @param Configuration $securityPolicy
     * @return UserPassword
     * @throws AssertionException|ConfigurationException
     */
    public static function create(string $password, User $user, Configuration $securityPolicy): UserPassword
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
                    '/[' . Configuration::SPECIAL_CHARACTERS_LIST . ']/',
                    'UserPassword::passwordValue'
                );
            }
        } catch (AssertionException $ex) {
            //Throw a generic user password exception to avoid returning a plain password in the AssertionException.
            throw UserPasswordException::passwordDoesntMatchSecurityPolicy();
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

        return new UserPassword($user->getId(), $newPasswordValue, time());
    }
}
