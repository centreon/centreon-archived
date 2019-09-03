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
