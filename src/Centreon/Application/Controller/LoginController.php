<?php


namespace Centreon\Application\Controller;

use Centreon\Domain\Security\Interfaces\AuthenticationServiceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends AbstractFOSRestController
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $auth;

    public function __construct(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @Rest\Post("/login")
     *
     * si view_response_listener = true il faut mettre l'annotation suivante, sinon c'est inutile
     * @Rest\View(populateDefaultVars=false)
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $contentBody = json_decode($request->getContent(), true);
        $username = $contentBody['security']['credentials']['login'] ?? null;
        $password = $contentBody['security']['credentials']['password'] ?? null;
        $contact = $this->auth->findContactByCredentials($username, $password);
        if (null !== $contact) {
            return [
                    'contact'=> [
                        'id' => $contact->getId(),
                        'name' => $contact->getName(),
                        'alias' => $contact->getAlias(),
                        'email' => $contact->getEmail(),
                        'is_admin' => $contact->isAdmin()
                    ],
                    'security' => [
                        'token' => $this->auth->generateToken($contact->getAlias())
                    ]
                ];
        }
        throw new HttpException(401, "Invalid credentials");
    }
}