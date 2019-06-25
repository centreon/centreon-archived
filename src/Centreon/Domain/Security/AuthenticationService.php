<?php

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
     * @var int
     */
    private $maxTokenPerContact = 5;

    /**
     * AuthenticationService constructor.
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
     * @param string $username
     * @param string $password
     * @return Contact|null
     * @throws \Exception
     */
    public function findContactByCredentials(string $username, string $password): ?Contact
    {
        if ($this->authenticationRepository->isGoodCredentials($username, $password)) {
            return $this->contactRepository->findByName($username);
        }
        return null;
    }

    /**
     * @param string $username
     * @return string
     * @throws \Exception
     */
    public function generateToken(string $username): string
    {
        $contact = $this->contactRepository->findByName($username);
        if (is_null($contact)) {
            throw new NotFoundException('Contact not found');
        }
        $tokens = $this->authenticationRepository->findTokenByContact($contact->getId());
        if (count($tokens) >= 1) {
            $this->authenticationRepository->deleteTokensByContact($contact->getId());
        }
        $this->generatedToken = md5(bin2hex(random_bytes(128)));
        $this->authenticationRepository->addToken(
            $contact->getId(),
            $this->generatedToken
        );
        return $this->generatedToken;
    }

    public function getGeneratedToken():string
    {
        return $this->generatedToken;
    }

    /**
     * @return int
     */
    public function getMaxTokenPerContact(): int
    {
        return $this->maxTokenPerContact;
    }

    /**
     * @param int $maxTokenPerContact
     */
    public function setMaxTokenPerContact(int $maxTokenPerContact): void
    {
        $this->maxTokenPerContact = $maxTokenPerContact;
    }
}
