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
 *Controller
 */
declare(strict_types=1);

namespace Security;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;

/**
 * This class has been defined to differentiate between contacts that have
 * identified by session and those by token. By session, all calls to the
 * **hasRole()** method will return TRUE.
 *
 * @package Security
 */
class ContactBySession extends Contact
{
    /**
     * @var array We need to redefine roles to be able to pass security in Controller
     * <b>IsGranted("ROLE_...")</b>
     */
    private $roles = [];

    /**
     * @var ContactInterface
     */
    private $contact;

    public function __construct(ContactInterface $contact)
    {
        $this->contact = $contact;
        $this->roles = [
            Contact::ROLE_API_REALTIME,
            Contact::ROLE_API_CONFIGURATION
        ];
    }

    public function hasRole(string $role): bool
    {
        return true;
    }

    public function getId(): int
    {
        return $this->contact->getId();
    }

    public function getName(): string
    {
        return $this->contact->getName();
    }

    public function getAlias(): string
    {
        return $this->contact->getAlias();
    }

    public function getLang(): string
    {
        return $this->contact->getLang();
    }

    public function getEmail(): string
    {
        return $this->contact->getEmail();
    }

    public function isAdmin(): bool
    {
        return $this->contact->isAdmin();
    }

    public function getTemplateId(): int
    {
        return $this->contact->getTemplateId();
    }

    public function isActive(): bool
    {
        return $this->contact->isActive();
    }

    public function getToken(): ?string
    {
        return $this->contact->getToken();
    }

    public function getEncodedPassword(): string
    {
        return $this->contact->getEncodedPassword();
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->contact->getPassword();
    }

    public function getSalt()
    {
        return $this->contact->getSalt();
    }

    public function getUsername()
    {
        return $this->contact->getUsername();
    }

    public function hasAccessToApiConfiguration(): bool
    {
        return true;
    }

    public function hasAccessToApiRealTime(): bool
    {
        return true;
    }
}
