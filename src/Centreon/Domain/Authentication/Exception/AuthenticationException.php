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

namespace Centreon\Domain\Authentication\Exception;

/**
 * This class is designed to contain all exceptions for the context of the authentication process.
 *
 * @package Centreon\Domain\Authentication\Exception
 */
class AuthenticationException extends \Exception
{
    /**
     * @return self
     */
    public static function invalidCredentials(): self
    {
        return new self(_('Invalid Credentials'));
    }

    /**
     * @return self
     */
    public static function notAuthenticated(): self
    {
        return new self(_('Authentication failed'));
    }

    /**
     * @return self
     */
    public static function userNotFound(): self
    {
        return new self(_('User cannot be retrieved from the provider'));
    }

    /**
     * @return self
     */
    public static function userNotFoundAndCannotBeCreated(): self
    {
        return new self(_('User not found and cannot be created'));
    }

    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function cannotLogout(\Throwable $ex): self
    {
        return new self(_('User cannot be logout'), 0, $ex);
    }

    public static function cannotStartLegacySession(): self
    {
        return new self(_('Unable to start Centreon legacy session'));
    }
}
