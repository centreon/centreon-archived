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

namespace Centreon\Domain\Security;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Security\Interfaces\AuthenticationRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AuthenticationServiceInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;
    /**
     * @var UserProviderInterface
     */
    private $contactRepository;

    /**
     * @var string
     */
    private $generatedToken;

    /**
     * AuthenticationService constructor.
     *
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param ContactRepositoryInterface $contactRepository
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->authenticationRepository = $authenticationRepository;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @inheritDoc
     */
    public function findContactByCredentials(string $username, string $password): ?Contact
    {
        if ($this->authenticationRepository->isGoodCredentials($username, $password)) {
            return $this->contactRepository->findByName($username);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function generateToken(string $username): string
    {
        $contact = $this->contactRepository->findByName($username);
        if (is_null($contact)) {
            throw new NotFoundException('Contact not found');
        }

        $this->generatedToken = md5(bin2hex(random_bytes(128)));
        $this->authenticationRepository->addToken(
            $contact->getId(),
            $this->generatedToken
        );
        return $this->generatedToken;
    }

    /**
     * @inheritDoc
     */
    public function getGeneratedToken():string
    {
        return $this->generatedToken;
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredTokens(): int
    {
        return $this->authenticationRepository->deleteExpiredTokens();
    }

    /**
     * @inheritDoc
     */
    public function logout(string $authToken): bool
    {
        $token = $this->authenticationRepository->findToken($authToken);
        if (is_null($token)) {
            throw new \Exception('Token not found');
        }

        return $this->authenticationRepository->deleteTokenFromContact(
            $token->getContactId(),
            $token->getToken()
        );
    }
}
