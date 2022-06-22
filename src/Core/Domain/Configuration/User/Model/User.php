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

namespace Core\Domain\Configuration\User\Model;

class User extends NewUser
{
    public const MIN_ALIAS_LENGTH = 1,
                 MAX_ALIAS_LENGTH = 255,
                 MIN_NAME_LENGTH = 1,
                 MAX_NAME_LENGTH = 255,
                 MIN_EMAIL_LENGTH = 1,
                 MAX_EMAIL_LENGTH = 255,
                 MIN_THEME_LENGTH = 1,
                 MAX_THEME_LENGTH = 100,
                 THEME_LIGHT = 'light',
                 THEME_DARK = 'dark';

    /**
     * @param int $id
     * @param string $alias
     * @param string $name
     * @param string $email
     * @param bool $isAdmin
     * @param string $theme
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        private int $id,
        protected string $alias,
        protected string $name,
        protected string $email,
        protected bool $isAdmin,
        protected string $theme,
    ) {
        parent::__construct($alias, $name, $email);
        $this->setTheme($theme);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
