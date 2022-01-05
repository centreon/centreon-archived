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

namespace Core\Infrastructure\Security\Api\LogoutSession;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\LogoutSession\LogoutSession;
use Core\Application\Security\UseCase\LogoutSession\LogoutSessionRequest;

class LogoutSessionController extends AbstractController
{
    private const INVALID_CREDENTIALS_MESSAGE = 'Invalid credentials';

    /**
     * @param LogoutSession $useCase
     * @param Request $request
     * @return View
     */
    public function __invoke(LogoutSession $useCase, Request $request): View
    {
        $token = $request->cookies->get('PHPSESSID');
        if (!isset($token)) {
            return $this->view([
                "code" => Response::HTTP_UNAUTHORIZED,
                "message" => _(self::INVALID_CREDENTIALS_MESSAGE)
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request = new LogoutSessionRequest($token);
        $useCase($request);

        return $this->view(['message' => _('Successful logout')]); // should we create a presenter with static content ?
    }
}
