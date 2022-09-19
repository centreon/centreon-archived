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

namespace Core\Security\Authentication\Application\UseCase\Login;

use Core\Application\Common\UseCase\ResponseStatusInterface;
use Exception;

final class LoginResponse implements ResponseStatusInterface
{
    /**
     * @param string $redirectUri
     * @param Exception|null $exception
     */
    public function __construct(private string $redirectUri, private ?Exception $exception = null)
    {
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @return Exception|null
     */
    public function getException(): ?Exception
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->redirectUri;
    }
}
