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

use Centreon\Domain\Authentication\UseCase\Authenticate;
use Centreon\Domain\Authentication\UseCase\AuthenticateAPI;
use Centreon\Domain\Authentication\UseCase\AuthenticateAPIRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateRequest;
use FOS\RestBundle\View\View;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Centreon\Application\Controller
 */
class AuthenticationController extends AbstractController
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     * @throws \InvalidArgumentException
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Entry point used to identify yourself and retrieve an authentication token.
     * (If view_response_listener = true, we need to write the following
     * annotation Rest\View(populateDefaultVars=false), otherwise it's not
     * necessary).
     *
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function login(Request $request, AuthenticateAPI $authenticate)
    {
        $contentBody = json_decode($request->getContent(), true);
        $credentials = [
            "useralias" => $contentBody['security']['credentials']['login'] ?? '',
            "password" => $contentBody['security']['credentials']['password'] ?? ''
        ];

        $request =  new AuthenticateAPIRequest($credentials);
        $response = $authenticate->execute($request);
        if (!empty($response)) {
            return $this->view($response);
        }
        return $this->view([
            "code" => Response::HTTP_UNAUTHORIZED,
            "message" => 'Invalid credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Entry point used to delete an existing authentication token.
     *
     * @param Request $request
     * @return View
     * @throws \RestException
     */
    public function logout(Request $request)
    {
        try {
            // We take this opportunity to delete all expired tokens
            $this->authenticationService->deleteExpiredAPITokens();
        } catch (\Exception $ex) {
            // We don't propagate this error
        }

        try {
            $token = $request->headers->get('X-AUTH-TOKEN');
            $this->authenticationService->deleteSession($token);
        } catch (\Exception $ex) {
            throw new \RestException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return $this->view([
            'message' => 'Successful logout'
        ]);
    }

    /**
     * Provide the default connection url.
     *
     * @param Request $request
     * @return View
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \InvalidArgumentException
     */
    public function redirection(Request $request): View
    {
        $providers = $this->authenticationService->findProvidersConfigurations();
        foreach ($providers as $provider) {
            $provider->setCentreonBaseUri($this->getBaseUri());
            $redirectionUri = $provider->getAuthenticationUri();
            if ($provider->isForced()) {
                break;
            }
        }

        if ($request->headers->get('Content-Type') === 'application/json') {
            // Send redirection_uri in JSON format only for API request
            return View::create(['authentication_uri' => $redirectionUri]);
        } else {
            // Otherwise, we send a redirection response.
            $view = View::createRedirect($redirectionUri);
            $view->setHeader('Content-Type', 'text/html');
            return $view;
        }
    }

    /**
     * Returns the list of available providers.
     *
     * @return View
     */
    public function findProvidersConfigurations(): View
    {
        $providers = $this->authenticationService->findProvidersConfigurations();

        return View::create($providers);
    }

    /**
     * @param Request $request
     * @param Authenticate $authenticate
     * @param string $providerConfigurationName
     * @return View
     * @throws \InvalidArgumentException
     * @throws AuthenticationServiceException
     * @throws \Exception
     */
    public function authentication(
        Request $request,
        Authenticate $authenticate,
        string $providerConfigurationName
    ) {
        if ($request->getMethod() === 'GET') {
            // redirect from external idp
            $data = $request->query->getIterator();
        } else {
            // submitted from form directly
            $data = $request->request->getIterator();
        }

        $requestParameters = [];
        foreach ($data as $key => $value) {
            $requestParameters[$key] = $value;
        }

        $authenticateRequest = new AuthenticateRequest($requestParameters, $providerConfigurationName);
        $response = $authenticate->execute($authenticateRequest);
        if ($request->headers->get('Content-Type') === 'application/json') {
            // Send redirection_uri in JSON format only for API request
            return View::create(['redirect_uri' => $this->getBaseUri() . $response]);
        } else {
            // Otherwise, we send a redirection response.
            return View::createRedirect(
                $this->getBaseUri() . '/authentication/login',
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
        }
    }
}
