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

use Centreon\Application\Controller\AbstractController;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        Request          $request,
        Login            $useCase,
        LoginPresenter   $presenter,
        SessionInterface $session
    ): object
    {
        $request = LoginRequest::createForOpenId(
            Provider::OPENID,
            $request->getClientIp(),
            $request->query->get("code"));

        $useCase($request, $presenter);

        $response = $presenter->getPresentedData();
        if ($response->error !== null) {
            return View::createRedirect(
                $this->getBaseUrl() . '/login?authenticationError=' . $response->getError()->getMessage(),
                Response::HTTP_FOUND
            );
        }

        return View::createRedirect(
            $this->getBaseUrl() . $response->getRedirectUri(),
            Response::HTTP_FOUND,
            ['Set-Cookie' => 'PHPSESSID=' . $session->getId()]
        );
    }
}
