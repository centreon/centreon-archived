<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Contact\Exception\ContactServiceException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;

class ContactService implements ContactServiceInterface
{
    /**
     * @param ContactRepositoryInterface $contactRepository
     */
    public function __construct(private ContactRepositoryInterface $contactRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function addUser(ContactInterface $contact): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updateUser(ContactInterface $contact): void
    {
    }

    /**
     * @inheritDoc
     */
    public function findContact(int $id): ?ContactInterface
    {
        return $this->contactRepository->findById($id);
    }

    /**
     * @inheritDoc
     */
    public function exists(ContactInterface $contact): bool
    {
        $contact = $this->contactRepository->findById($contact->getId());
        return $contact !== null;
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?ContactInterface
    {
        return $this->contactRepository->findByName($name);
    }

    /**
     * @inheritDoc
     */
    public function findBySession(string $session): ?ContactInterface
    {
        return $this->contactRepository->findBySession($session);
    }

    /**
     * @inheritDoc
     */
    public function findByAuthenticationToken(string $token): ?ContactInterface
    {
        try {
            return $this->contactRepository->findByAuthenticationToken($token);
        } catch (\Exception $ex) {
            throw ContactServiceException::errorWhileSearchingContact($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?ContactInterface
    {
        return $this->contactRepository->findByEmail($email);
    }
}
