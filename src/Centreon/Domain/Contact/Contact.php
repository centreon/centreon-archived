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

namespace Centreon\Domain\Contact;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class Contact implements UserInterface, ContactInterface
{
    const ROLE_API_CONFIGURATION = 'ROLE_API_CONFIGURATION';
    const ROLE_API_REALTIME = 'ROLE_API_REALTIME';
    const ROLE_HOST_ACKNOWLEDGEMENT = 'ROLE_HOST_ACKNOWLEDGEMENT';
    const ROLE_HOST_DISACKNOWLEDGEMENT = 'ROLE_HOST_DISACKNOWLEDGEMENT';
    const ROLE_SERVICE_ACKNOWLEDGEMENT = 'ROLE_SERVICE_ACKNOWLEDGEMENT';
    const ROLE_SERVICE_DISACKNOWLEDGEMENT = 'ROLE_SERVICE_DISACKNOWLEDGEMENT';
    const ROLE_CANCEL_HOST_DOWNTIME = 'ROLE_CANCEL_HOST_DOWNTIME';
    const ROLE_CANCEL_SERVICE_DOWNTIME = 'ROLE_CANCEL_SERVICE_DOWNTIME';
    const ROLE_ADD_HOST_DOWNTIME = 'ROLE_ADD_HOST_DOWNTIME';
    const ROLE_ADD_SERVICE_DOWNTIME = 'ROLE_ADD_SERVICE_DOWNTIME';

    /**
     * @var int Id of contact
     */
    private $id;

    /**
     * @var string Name of contact
     */
    private $name;

    /**
     * @var string Alias of contact
     */
    private $alias;

    /**
     * @var string Language of contact
     */
    private $lang;

    /**
     * @var string Email of contact
     */
    private $email;

    /**
     * @var bool Is an admin contact ?
     */
    private $isAdmin;

    /**
     * @var int Id of the contact template
     */
    private $templateId;

    /**
     * @var bool Indicates whether this contact is enabled or disabled
     */
    private $isActive;

    /**
     * @var string|null Authentication Token
     */
    private $token;

    /**
     * @var string Encoded password
     */
    private $encodedPassword;

    /**
     * @var bool Indicates if this user has access to the configuration section of API
     */
    private $hasAccessToApiConfiguration;

    /**
     * @var bool Indicates if this user has access to the real time section of API
     */
    private $hasAccessToApiRealTime;

    /**
     * @var (Role|string)[]
     */
    private $roles = [];

    /**
     * @var string[] List of names of topology rules to which the contact can access
     */
    private $topologyRulesNames = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return self
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     * @return self
     */
    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     * @return self
     */
    public function setAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;
        if ($this->isAdmin) {
            $this->addRole(self::ROLE_API_REALTIME);
            $this->addRole(self::ROLE_API_CONFIGURATION);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    /**
     * @param int $templateId
     * @return self
     */
    public function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @return self
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getEncodedPassword(): string
    {
        return $this->encodedPassword;
    }

    /**
     * @param string|null $encodedPassword
     * @return self
     */
    public function setEncodedPassword(?string $encodedPassword): self
    {
        $this->encodedPassword = $encodedPassword;
        return $this;
    }

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
     * @return Role[]|string[] The user roles
     */
    public function getRoles()
    {
        return array_merge($this->roles, $this->topologyRulesNames);
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->token;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->name;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        // Nothing to do. But we must to define this method
    }

    /**
     * @return bool
     */
    public function hasAccessToApiConfiguration(): bool
    {
        return $this->hasAccessToApiConfiguration;
    }

    /**
     * @param bool $hasAccessToApiConfiguration
     * @return self
     */
    public function setAccessToApiConfiguration(bool $hasAccessToApiConfiguration): self
    {
        $this->hasAccessToApiConfiguration = $hasAccessToApiConfiguration;
        if ($this->hasAccessToApiRealTime) {
            $this->addRole(self::ROLE_API_CONFIGURATION);
        } else {
            $this->removeRole(self::ROLE_API_CONFIGURATION);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAccessToApiRealTime(): bool
    {
        return $this->hasAccessToApiRealTime;
    }

    /**
     * @param bool $hasAccessToApiRealTime
     * @return self
     */
    public function setAccessToApiRealTime(bool $hasAccessToApiRealTime): self
    {
        $this->hasAccessToApiRealTime = $hasAccessToApiRealTime;
        if ($this->hasAccessToApiRealTime) {
            $this->addRole(self::ROLE_API_REALTIME);
        } else {
            $this->removeRole(self::ROLE_API_REALTIME);
        }
        return $this;
    }

    /**
     * Indicates if this user has a role.
     *
     * @param string $role Role name to find
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Add a specific role to this user.
     *
     * @param string $roleName Role name to add
     */
    public function addRole(string $roleName): void
    {
        if (!in_array($roleName, $this->roles)) {
            $this->roles[] = $roleName;
        }
    }

    /**
     * Removes an existing roles.
     *
     * @param string $roleName Role name to remove
     */
    private function removeRole(string $roleName): void
    {
        unset($this->roles[$roleName]);
    }

    /**
     * Added a topology rule.
     *
     * @param string $topologyRuleName Topology rule name
     */
    public function addTopologyRule(string $topologyRuleName): void
    {
        if (!in_array($topologyRuleName, $this->topologyRulesNames)) {
            $this->topologyRulesNames[] = $topologyRuleName;
        }
    }
}
