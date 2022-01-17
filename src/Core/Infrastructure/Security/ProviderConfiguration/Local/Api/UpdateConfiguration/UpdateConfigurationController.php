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

namespace Core\Infrastructure\Security\ProviderConfiguration\local\Api\UpdateConfiguration;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Api\Exception\ConfigurationException;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfiguration;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfigurationRequest;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfigurationPresenterInterface;

class UpdateConfigurationController extends AbstractController
{
    /**
     * @param UpdateConfiguration $useCase
     * @param Request $request
     * @param UpdateConfigurationPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        UpdateConfiguration $useCase,
        Request $request,
        UpdateConfigurationPresenterInterface $presenter,
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->validateDataSent($request, __DIR__ . '/UpdateProviderConfigurationLocalSchema.json');
        $updateProviderConfigurationLocalRequest = $this->createUpdateProviderConfigurationLocalRequest($request);
        $useCase($presenter, $updateProviderConfigurationLocalRequest);

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return UpdateConfigurationRequest
     */
    private function createUpdateProviderConfigurationLocalRequest(
        Request $request,
    ): UpdateConfigurationRequest {
        $requestData = json_decode((string) $request->getContent(), true);
        $updateRequest = new UpdateConfigurationRequest();
        $updateRequest->passwordMinimumLength = $requestData['password_min_length'];
        $updateRequest->hasUppercase = $requestData['has_uppercase'];
        $updateRequest->hasLowercase = $requestData['has_lowercase'];
        $updateRequest->hasNumber = $requestData['has_number'];
        $updateRequest->hasSpecialCharacter = $requestData['has_special_character'];
        $updateRequest->attempts = $requestData['attempts'];
        $updateRequest->blockingDuration = $requestData['blocking_duration'];
        $updateRequest->passwordExpiration = $requestData['password_expiration'];
        $updateRequest->canReusePasswords = $requestData['can_reuse_passwords'];
        $updateRequest->delayBeforeNewPassword = $requestData['delay_before_new_password'];

        return $updateRequest;
    }

    /**
     * Validate the data sent.
     *
     * @param Request $request Request sent by client
     * @param string $jsonValidationFile Json validation file
     * @throws \Exception
     */
    private function validateDataSent(Request $request, string $jsonValidationFile): void
    {
        $receivedData = json_decode((string) $request->getContent(), true);
        if (!is_array($receivedData)) {
            throw new ConfigurationException('Error when decoding your sent data');
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
            throw new ConfigurationException($message);
        }
    }
}
