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

namespace Security\Domain\Authentication\Exceptions;

/**
 * This class is designed to contain all exceptions for the context of the authentication service.
 *
 * @package Security\Domain\Authentication\Exceptions
 */
class AuthenticationServiceException extends \Exception
{
    /**
     * @param string $configurationName
     * @return self
     */
    public static function providerConfigurationNotFound(string $configurationName): self
    {
        return new self(sprintf(_('Provider configuration (%s) not found'), $configurationName));
    }

    /**
     * @return self
     */
    public static function refreshToken(): self
    {
        return new self(_('Error while refresh token'));
    }

    /**
     * @return self
     */
    public static function sessionExpired(): self
    {
        return new self(_('Your session has expired'));
    }

    /**
     * @return self
     */
    public static function providerNotFound(): self
    {
        return new self(_('Provider not found'));
    }

    /**
     * @return self
     */
    public static function sessionTokenNotFound(): self
    {
        return new self(_('Session token not found'));
    }

    /**
     * @return self
     */
    public static function sessionNotFound(): self
    {
        return new self(_('Session not found'));
    }
}
