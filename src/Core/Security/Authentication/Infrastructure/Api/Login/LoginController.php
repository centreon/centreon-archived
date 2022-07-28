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

namespace Core\Security\Authentication\Infrastructure\Api\Login;

use Centreon\Application\Controller\AbstractController;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Core\Security\Authentication\Application\UseCase\Login\LoginPresenterInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use FOS\RestBundle\View\View;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginController extends AbstractController
{
    use HttpUrlTrait;

    /**
     * @param Request $request
     * @param Login $useCase
     * @param LoginPresenter $presenter
     * @param SessionInterface $session
     * @return object
     */
    #[NoReturn] public function __invoke(Request                 $request,
                                         Login                   $useCase,
                                         LoginPresenterInterface $presenter,
                                         SessionInterface        $session,
                                         string                  $providerName): object
    {
        $payload = \json_decode($request->getContent(), true);
        $useCase(new LoginRequest($payload, $request->getClientIp(), $providerName), $presenter);
        $response = $presenter->getPresentedData();
        dd("ok");
        return View::createRedirect(
            $this->getBaseUrl() . $response->redirectUri,
            Response::HTTP_FOUND,
            ['Set-Cookie' => 'PHPSESSID=' . $session->getId()]
        );
        //if ($response->error )
//        $loginOpenIdSessionRequest = $this->createLoginOpenIdSessionRequest($request);
//        $useCase($loginOpenIdSessionRequest, $presenter);
//        $response = $presenter->getPresentedData();
//        if ($response->error !== null) {
//            return View::createRedirect(
//                $this->getBaseUrl() . '/login?authenticationError=' . $response->error,
//                Response::HTTP_FOUND
//            );
//        }
//
//        return View::createRedirect(
//            $this->getBaseUrl() . $response->redirectUri,
//            Response::HTTP_FOUND,
//            ['Set-Cookie' =>  'PHPSESSID=' . $session->getId()]
//        );
    }
}
