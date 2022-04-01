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

namespace Core\Infrastructure\Configuration\User\Api\PatchUser;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Configuration\User\UseCase\PatchUser\PatchUser;
use Core\Application\Configuration\User\UseCase\PatchUser\PatchUserPresenterInterface;
use Core\Application\Configuration\User\UseCase\PatchUser\PatchUserRequest;
use Symfony\Component\HttpFoundation\Request;

final class PatchUserController extends AbstractController
{
    use LoggerTrait;

    /**
     * @param Request $request
     * @param mixed $user
     * @param PatchUser $useCase
     * @param PatchUserPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        Request $request,
        PatchUser $useCase,
        mixed $user,
        PatchUserPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->debug('Validating request body');
        $this->validateDataSent($request, __DIR__ . '/PatchUserSchema.json');
        /**
         * @var Contact $currentUser
         */
        $currentUser = $this->getUser();
        $userId = is_numeric($user)
            ? (int) $user
            : $currentUser->getId();

        $useCase($this->createRequest($request, $userId), $presenter);

        return $presenter->show();
    }

    /**
     * @param Request $request
     * @param int $userId
     * @return PatchUserRequest
     */
    private function createRequest(Request $request, int $userId): PatchUserRequest
    {
        /**
         * @var array{theme: string} $requestData
         */
        $requestData = json_decode((string) $request->getContent(), true);
        $updateUserRequest = new PatchUserRequest();
        $updateUserRequest->theme = $requestData['theme'];
        $updateUserRequest->userId = $userId;
        return $updateUserRequest;
    }
}
