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

namespace Centreon\Domain\Authentication\UseCase;

class AuthenticateResponse
{
    /**
     * @var bool
     */
    private $isAuthenticated = false;

    /**
     * @var bool
     */
    private $isUserFound = false;

    /**
     * @var bool
     */
    private $shouldAndCannotCreateUser = false;

    /**
     * Get authentication status
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * Set authentication status
     *
     * @param boolean $isAuthenticated
     * @return void
     */
    public function setAuthenticated(bool $isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
    }

    /**
     * Set if user is found or not
     *
     * @return boolean
     */
    public function isUserFound()
    {
        return $this->isUserFound;
    }

    /**
     * Get if user is found or not
     *
     * @param boolean $isUserFound
     * @return void
     */
    public function setUserFound(bool $isUserFound)
    {
        $this->isUserFound = $isUserFound;
    }

    /**
     * If a user should be created but cannot
     *
     * @return boolean
     */
    public function shouldAndCannotCreateUser()
    {
        return $this->shouldAndCannotCreateUser;
    }

    /**
     * Set if user should be created but cannot
     *
     * @param boolean $shouldAndCannotCreateUser
     * @return void
     */
    public function setShouldAndCannotCreateUser(bool $shouldAndCannotCreateUser)
    {
        $this->shouldAndCannotCreateUser = $shouldAndCannotCreateUser;
    }
}
