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

namespace Core\Security\Authentication\Infrastructure\Api\Login\OpenId;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Application\UseCase\Login\LoginResponse;
use Core\Application\Common\UseCase\ErrorAuthenticationConditionsResponse;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Application\UseCase\Login\PasswordExpiredResponse;
use Core\Security\Authentication\Application\UseCase\Login\ErrorAclConditionsResponse;

class LoginController extends AbstractController
{
    use HttpUrlTrait;

    /**
     * @param Request $request
     * @param Login $useCase
     * @param LoginPresenter $presenter
     * @param SessionInterface $session
     * @return object
     * @throws AuthenticationException
     */
    public function __invoke(
        Request $request,
        Login $useCase,
        LoginPresenter $presenter,
        SessionInterface $session
    ): object {
        $request = LoginRequest::createForOpenId(
            $request->getClientIp(),
            $request->query->get("code")
        );

        $useCase($request, $presenter);

        switch (true) {
            case is_a($presenter->getResponseStatus(), PasswordExpiredResponse::class)
                || is_a($presenter->getResponseStatus(), UnauthorizedResponse::class)
                || is_a($presenter->getResponseStatus(), ErrorResponse::class):
                return View::createRedirect(
                    $this->getBaseUrl() . '/login?authenticationError=' . $presenter->getResponseStatus()->getMessage()
                );
            case is_a($presenter->getResponseStatus(), ErrorAclConditionsResponse::class):
            case is_a($presenter->getResponseStatus(), ErrorAuthenticationConditionsResponse::class):
                return View::createRedirect(
                    $this->getBaseUrl() . '/authentication-denied'
                );
            default:
                /**
                 * @var LoginResponse
                 */
                $response = $presenter->getResponseStatus();
                return View::createRedirect(
                    $this->getBaseUrl() . $response->getRedirectUri(),
                    Response::HTTP_FOUND,
                    ['Set-Cookie' => 'PHPSESSID=' . $session->getId()]
                );
        }
    }
}
