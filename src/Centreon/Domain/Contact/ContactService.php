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
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\Local\ConfigurationException;

class ContactService implements ContactServiceInterface
{
    /**
     * @param ContactRepositoryInterface $contactRepository
     * @param ReadConfigurationRepositoryInterface $readProviderConfiguration
     */
    public function __construct(
        private ContactRepositoryInterface $contactRepository,
        private ReadConfigurationRepositoryInterface $readProviderConfigurationRepository,
    ) {
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
    public function isPasswordExpired(int $contactId): bool
    {
        $providerConfiguration = $this->readProviderConfigurationRepository->findConfiguration();
        if ($providerConfiguration === null) {
            throw ConfigurationException::notFound();
        }

        if ($providerConfiguration->getPasswordExpirationDelay() === null) {
            return false;
        }

        $contact = $this->findContact($contactId);
        if ($contact === null) {
            throw new ContactServiceException();
        }

        if (in_array($contact->getAlias(), $providerConfiguration->getPasswordExpirationExcludedUserAliases())) {
            return false;
        }


        // @todo get last password creation and compare it to password duration from security policy

        return false;
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
}
