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

namespace Core\Infrastructure\Security\Api\LoginOpenIdSession;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Application\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Core\Application\Security\UseCase\LoginOpenIdSession\LoginOpenIdSession;
use Core\Application\Security\UseCase\LoginOpenIdSession\LoginOpenIdSessionRequest;
use Core\Application\Security\UseCase\LoginOpenIdSession\LoginOpenIdSessionPresenterInterface;
use Core\Infrastructure\Common\Api\HttpUrlTrait;

class LoginOpenIdSessionController extends AbstractController
{
    use HttpUrlTrait;

    /**
     * @param Request $request
     * @param LoginOpenIdSession $useCase
     * @param LoginOpenIdSessionPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        Request $request,
        LoginOpenIdSession $useCase,
        LoginOpenIdSessionPresenterInterface $presenter,
        SessionInterface $session
    ): object {
        $loginOpenIdSessionRequest = $this->createLoginOpenIdSessionRequest($request);
        $useCase($loginOpenIdSessionRequest, $presenter);
        $response = $presenter->getData();
        if ($response->error !== null) {
            return View::createRedirect(
                $this->getBaseUrl() . '/login?authenticationError=' . $response->error,
                Response::HTTP_BAD_REQUEST
            );
        }
        return View::createRedirect(
            $this->getBaseUrl() . $response->redirectionUri,
            Response::HTTP_FOUND,
            ['Set-Cookie' =>  'PHPSESSID=' . $session->getId()]
        );
    }

    /**
     * @param string|null $authorizationCode
     * @return LoginOpenIdSessionRequest
     */
    private function createLoginOpenIdSessionRequest(Request $request): LoginOpenIdSessionRequest
    {
        $loginOpenIdSessionRequest = new LoginOpenIdSessionRequest();
        $loginOpenIdSessionRequest->authorizationCode = $request->query->get('code');
        $loginOpenIdSessionRequest->clientIp = $request->getClientIp();

        return $loginOpenIdSessionRequest;
    }
}
