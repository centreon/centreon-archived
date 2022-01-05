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

namespace Core\Infrastructure\Security\Api\UpdateSecurityPolicy;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Infrastructure\Security\Api\Exception\SecurityPolicyApiException;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicy;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyRequest;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;

class UpdateSecurityPolicyController extends AbstractController
{
    /**
     * @param UpdateSecurityPolicy $useCase
     * @param Request $request
     * @param UpdateSecurityPolicyPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        UpdateSecurityPolicy $useCase,
        Request $request,
        UpdateSecurityPolicyPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->validateDataSent($request, __DIR__ . '/UpdateSecurityPolicySchema.json');
        $updateSecurityPolicyRequest = $this->createUpdateSecurityPolicyRequest($request);
        $useCase($presenter, $updateSecurityPolicyRequest);

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return UpdateSecurityPolicyRequest
     */
    private function createUpdateSecurityPolicyRequest(Request $request): UpdateSecurityPolicyRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        $updateSecurityPolicyRequest = new UpdateSecurityPolicyRequest();
        $updateSecurityPolicyRequest->passwordMinimumLength = $requestData['password_min_length'];
        $updateSecurityPolicyRequest->hasUppercase = $requestData['has_uppercase'];
        $updateSecurityPolicyRequest->hasLowercase = $requestData['has_lowercase'];
        $updateSecurityPolicyRequest->hasNumber = $requestData['has_number'];
        $updateSecurityPolicyRequest->hasSpecialCharacter = $requestData['has_special_character'];
        $updateSecurityPolicyRequest->attempts = $requestData['attempts'];
        $updateSecurityPolicyRequest->blockingDuration = $requestData['blocking_duration'];
        $updateSecurityPolicyRequest->passwordExpiration = $requestData['password_expiration'];
        $updateSecurityPolicyRequest->canReusePassword = $requestData['can_reuse_passwords'];
        $updateSecurityPolicyRequest->delayBeforeNewPassword = $requestData['delay_before_new_password'];

        return $updateSecurityPolicyRequest;
    }

        /**
     * Validate and retrieve the data sent.
     *
     * @param Request $request Request sent by client
     * @param string $jsonValidationFile Json validation file
     * @throws \Exception
     */
    private function validateDataSent(Request $request, string $jsonValidationFile): void
    {
        $receivedData = json_decode((string) $request->getContent(), true);
        if (!is_array($receivedData)) {
            throw new SecurityPolicyApiException('Error when decoding your sent data');
        }
        $receivedData = Validator::arrayToObjectRecursive($receivedData);
        $validator = new Validator();
        $centreonPath = $this->getParameter('centreon_path');
        $validator->validate(
            $receivedData,
            (object) [
                '$ref' => 'file://' . realpath(
                    $centreonPath . $jsonValidationFile
                )
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new SecurityPolicyApiException($message);
        }
    }
}
