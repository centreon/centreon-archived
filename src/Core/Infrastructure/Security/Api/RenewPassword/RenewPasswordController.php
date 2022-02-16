<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Security\Api\RenewPassword;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\RenewPassword\RenewPassword;
use Core\Application\Security\UseCase\RenewPassword\RenewPasswordRequest;
use Core\Infrastructure\Security\Api\Exception\RenewPasswordApiException;
use Core\Application\Security\UseCase\RenewPassword\RenewPasswordPresenterInterface;

class RenewPasswordController extends AbstractController
{
    /**
     * @param RenewPassword $useCase
     * @param Request $request
     * @param RenewPasswordPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        RenewPassword $useCase,
        Request $request,
        RenewPasswordPresenterInterface $presenter,
        string $alias
    ): object {
        $this->validateDataSent($request, __DIR__ . '/RenewPasswordSchema.json');
        $renewPasswordRequest = $this->createRenewPasswordRequest($request, $alias);
        $useCase($presenter, $renewPasswordRequest);
        return $presenter->show();
    }

    /**
     * Validate the data sent.
     *
     * @param Request $request Request sent by client
     * @param string $jsonValidationFile Json validation file
     * @throws RenewPasswordApiException
     */
    private function validateDataSent(Request $request, string $jsonValidationFile): void
    {
        $receivedData = json_decode((string) $request->getContent(), true);
        if (!is_array($receivedData)) {
            throw new RenewPasswordApiException('Error when decoding your sent data');
        }
        $receivedData = Validator::arrayToObjectRecursive($receivedData);
        $validator = new Validator();
        $validator->validate(
            $receivedData,
            (object) [
                '$ref' => 'file://' . realpath(
                    $jsonValidationFile
                )
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new RenewPasswordApiException($message);
        }
    }

    /**
     * @param Request $request
     * @return RenewPasswordRequest
     */
    private function createRenewPasswordRequest(Request $request, string $userAlias): RenewPasswordRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        $renewPasswordRequest = new RenewPasswordRequest();
        $renewPasswordRequest->userAlias = $userAlias;
        $renewPasswordRequest->oldPassword = $requestData['old_password'];
        $renewPasswordRequest->newPassword = $requestData['new_password'];

        return $renewPasswordRequest;
    }
}
