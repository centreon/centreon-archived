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

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Authentication\UseCase\Logout;
use Centreon\Domain\Authentication\UseCase\LogoutRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Core\Domain\Security\Authentication\AuthenticationException;
use Security\Infrastructure\Authentication\API\Model_2110\ApiAuthenticationFactory;

/**
 * @package Centreon\Application\Controller
 */
class AuthenticationController extends AbstractController
{
    private const INVALID_CREDENTIALS_MESSAGE = 'Invalid credentials';

    /**
     * Entry point used to identify yourself and retrieve an authentication token.
     * (If view_response_listener = true, we need to write the following
     * annotation Rest\View(populateDefaultVars=false), otherwise it's not
     * necessary).
     *
     * @param Request $request
     * @param AuthenticateApi $authenticate
     * @param AuthenticateApiResponse $response
     * @return View
     */
    public function login(Request $request, AuthenticateApi $authenticate, AuthenticateApiResponse $response): View
    {
        $contentBody = json_decode((string) $request->getContent(), true);
        $login = $contentBody['security']['credentials']['login'] ?? '';
        $password = $contentBody['security']['credentials']['password'] ?? '';

        $request = new AuthenticateApiRequest($login, $password);

        try {
            $authenticate->execute($request, $response);
        } catch (AuthenticationException $e) {
            return $this->view(
                [
                    "code" => Response::HTTP_UNAUTHORIZED,
                    "message" => _(self::INVALID_CREDENTIALS_MESSAGE),
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->view(ApiAuthenticationFactory::createFromResponse($response));
    }

    /**
     * Entry point used to delete an existing authentication token.
     *
     * @param Request $request
     * @param Logout $logout
     * @return View
     * @throws \RestException
     */
    public function logout(Request $request, Logout $logout): View
    {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if ($token === null) {
            return $this->view([
                "code" => Response::HTTP_UNAUTHORIZED,
                "message" => _(self::INVALID_CREDENTIALS_MESSAGE)
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request = new LogoutRequest($token);
        $logout->execute($request);

        return $this->view([
            'message' => 'Successful logout'
        ]);
    }
}
