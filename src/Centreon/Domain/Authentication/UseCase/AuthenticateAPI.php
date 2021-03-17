<?php

namespace Centreon\Domain\Authentication\UseCase;

use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

class AuthenticateAPI
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param AuthenticateAPIRequest $request
     * @return array
     */
    public function execute(AuthenticateAPIRequest $request): array
    {
        try {
            $this->authenticationService->deleteExpiredAPITokens();
        } catch (\Exception $ex) {
            // We don't propagate this error.
        }
        $localProvider = $this->authenticationService->findProviderByConfigurationName('local');
        $localProvider->authenticate($request->getCredentials());
        $response = [];
        if ($localProvider->isAuthenticated()) {
            $contact = $localProvider->getUser();
            $token = md5(bin2hex(random_bytes(128)));

            $this->authenticationService->createAPIAuthenticationTokens(
                $token,
                $contact,
                $localProvider->getProviderToken($token),
                null
            );

            $response = [
                'contact' => [
                    'id' => $contact->getId(),
                    'name' => $contact->getName(),
                    'alias' => $contact->getAlias(),
                    'email' => $contact->getEmail(),
                    'is_admin' => $contact->isAdmin()
                ],
                'security' => [
                    'token' => $token
                ]
            ];
        }

        return $response;
    }
}
