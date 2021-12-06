<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Contact\Interfaces;

use Centreon\Domain\Menu\Model\Page;

interface ContactInterface
{
    /**
     * @return int Returns the contact id
     */
    public function getId(): int;

    /**
     * Indicates whether the contact is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Indicates whether the contact is active.
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Indicates whether the contact is allowed to reach web application.
     *
     * @return bool
     */
    public function isAllowedToReachWeb(): bool;

    /**
     * Allow user or not to reach web application.
     *
     * @param bool $isAllowed
     * @return static
     */
    public function setAllowedToReachWeb(bool $isAllowed): static;

    /**
     * Contact name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Contact alias.
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Contact lang.
     *
     * @return string
     */
    public function getLang(): string;

    /**
     * Contact email.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Contact template id.
     *
     * @return int
     */
    public function getTemplateId(): int;

    /**
     * Contact token.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Contact encoded password.
     *
     * @return string
     */
    public function getEncodedPassword(): string;

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return array('ROLE_USER');
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array;

    /**
     * Contact timezone.
     *
     * @return \DateTimeZone
     */
    public function getTimezone(): \DateTimeZone;

    /**
     * Contact locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * Contact default page.
     *
     * @return Page|null
     */
    public function getDefaultPage(): ?Page;

    /**
     * @param Page|null $defaultPage
     * @return static
     */
    public function setDefaultPage(?Page $defaultPage): static;
}
