<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Security\Api\LoginSession;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\LoginSession\LoginSession;
use Core\Application\Security\UseCase\LoginSession\LoginSessionPresenterInterface;
use Core\Application\Security\UseCase\LoginSession\LoginSessionRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Core\Domain\Security\Authentication\AuthenticationException;

class LoginSessionController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $providerConfigurationName
     * @param LoginSession $loginSession
     * @param LoginSessionPresenterInterface $presenter
     * @param SessionInterface $session
     * @return object
     */
    public function __invoke(
        Request $request,
        LoginSession $loginSession,
        LoginSessionPresenterInterface $presenter,
        SessionInterface $session,
    ): object {
        $this->validateDataSent($request, __DIR__ . '/LoginSessionSchema.json');

        $loginSessionRequest = $this->createLoginSessionRequest($request);

        try {
            $loginSession($presenter, $loginSessionRequest);
        } catch (AuthenticationException) {
            return $presenter->show();
        }

        $presenter->setResponseHeaders(
            ['Set-Cookie' => 'PHPSESSID=' . $session->getId()]
        );

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return LoginSessionRequest
     */
    private function createLoginSessionRequest(
        Request $request
    ): LoginSessionRequest {
        $requestData = json_decode((string) $request->getContent(), true);

        $loginSessionRequest = new LoginSessionRequest();
        $loginSessionRequest->login = $requestData['login'];
        $loginSessionRequest->password = $requestData['password'];
        $loginSessionRequest->baseUri = $this->getBaseUri();
        $referer = $request->headers->get('referer');
        if ($referer !== null) {
            $loginSessionRequest->refererQueryParameters = parse_url($referer, PHP_URL_QUERY) ?: null;
        } else {
            $loginSessionRequest->refererQueryParameters = null;
        }
        $loginSessionRequest->clientIp = $request->getClientIp();

        return $loginSessionRequest;
    }
}
