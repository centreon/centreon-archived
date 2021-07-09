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

use Centreon\Domain\Authentication\Model\Credentials;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Authentication\UseCase\Logout;
use Centreon\Domain\Authentication\UseCase\Authenticate;
use Centreon\Domain\Authentication\UseCase\LogoutRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurations;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;
use Security\Infrastructure\Authentication\API\Model_2110\ApiAuthenticationFactory;
use Security\Infrastructure\Authentication\API\Model_2110\ProvidersConfigurationsFactory;

/**
 * @package Centreon\Application\Controller
 */
class AuthenticationController extends AbstractController
{
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
        $authenticate->execute($request, $response);

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
                "message" => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request = new LogoutRequest($token);
        $logout->execute($request);

        return $this->view([
            'message' => 'Successful logout'
        ]);
    }

    /**
     * Returns the list of available providers.
     * @param FindProvidersConfigurations $findProviderConfigurations
     * @param FindProvidersConfigurationsResponse $response
     * @return View
     */
    public function findProvidersConfigurations(
        FindProvidersConfigurations $findProviderConfigurations,
        FindProvidersConfigurationsResponse $response
    ): View {
        $findProviderConfigurations->execute($response);
        return $this->view(ProvidersConfigurationsFactory::createFromResponse($response));
    }

    /**
     * @param Request $request
     * @param Authenticate $authenticate
     * @param string $providerConfigurationName
     * @param AuthenticateResponse $response
     * @return View
     */
    public function authentication(
        Request $request,
        Authenticate $authenticate,
        string $providerConfigurationName,
        AuthenticateResponse $response
    ): View {
        // submitted from form directly
        $data = $request->request->getIterator();
        $referer = $request->headers->get('referer');
        $clientIp = $request->getClientIp();
        if ($clientIp === null) {
            return $this->view(['Invalid address'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($data['login']) || empty($data['password'])) {
            return $this->view(['Missing credentials parameters'], Response::HTTP_BAD_REQUEST);
        }
        $credentials = new Credentials($data['login'], $data['password']);

        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            $providerConfigurationName,
            $this->getBaseUri(),
            $referer,
            $clientIp
        );

        $authenticate->execute($authenticateRequest, $response);
        return $this->view($response->getRedirectionUriApi());
    }
}
