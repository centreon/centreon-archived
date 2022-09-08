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

namespace Core\Security\Authentication\Infrastructure\Api\Login\Local;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Log\LoggerTrait;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{
    use HttpUrlTrait;
    use LoggerTrait;

    /**
     * @param Request $request
     * @param Login $useCase
     * @param LoginPresenter $presenter
     * @param SessionInterface $session
     * @return object
     */
    public function __invoke(
        Request $request,
        Login $useCase,
        LoginPresenter $presenter,
        SessionInterface $session
    ): object {
        $payload = json_decode($request->getContent(), true);

        $referer = $request->headers->get('referer') ?
            parse_url(
                $request->headers->get('referer'),
                PHP_URL_QUERY
            ) : null;

        $request = LoginRequest::createForLocal(
            $payload["login"] ?? null,
            $payload["password"] ?? null,
            $request->getClientIp(),
            $referer
        );

        try {
            $useCase($request, $presenter);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $presenter->setResponseHeaders(
            ['Set-Cookie' => 'PHPSESSID=' . $session->getId()]
        );

        return $presenter->show();
    }
}
