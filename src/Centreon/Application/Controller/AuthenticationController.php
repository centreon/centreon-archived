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

namespace Centreon\Application\Controller;

use Centreon\Domain\Security\Interfaces\AuthenticationServiceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @package Centreon\Application\Controller
 */
class AuthenticationController extends AbstractFOSRestController
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $auth;

    /**
     * LoginController constructor.
     *
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Entry point used to identify yourself and retrieve an authentication token.
     * (If view_response_listener = true, we need to write the following
     * annotation Rest\View(populateDefaultVars=false), otherwise it's not
     * necessary).
     *
     * @Rest\Post("/login")
     * @Rest\View(populateDefaultVars=false)
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function login(Request $request)
    {
        try {
            // We take this opportunity to delete all expired tokens
            $this->auth->deleteExpiredTokens();
        } catch (\Exception $ex) {
            // We don't propagate this error
        }
        $contentBody = json_decode($request->getContent(), true);
        $username = $contentBody['security']['credentials']['login'] ?? '';
        $password = $contentBody['security']['credentials']['password'] ?? '';
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

    /**
     * Entry point used to delete an existing authentication token.
     *
     * @Rest\Get("/logout")
     * @Rest\View(populateDefaultVars=false)
     *
     * @param Request $request
     * @return array
     * @throws \RestException
     */
    public function logout(Request $request)
    {
        try {
            // We take this opportunity to delete all expired tokens
            $this->auth->deleteExpiredTokens();
        } catch (\Exception $ex) {
            // We don't propagate this error
        }
        try {
            $token = $request->headers->get('X-AUTH-TOKEN');
            $this->auth->logout($token);
            return ['message' => 'Successful logout'];
        } catch (\Exception $ex) {
            throw new \RestException($ex->getMessage());
        }
    }
}
