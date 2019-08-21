<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Contact;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class Contact implements UserInterface, ContactInterface
{
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
     * @var (Role|string)[]
     */
    private $roles;

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
        return ($this->isAdmin) ? ['ROLE_ADMIN'] : ['ROLE_USER'];
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
    public function eraseCredentials()
    {
        // Nothing to do. But we must to define this method
    }
}
