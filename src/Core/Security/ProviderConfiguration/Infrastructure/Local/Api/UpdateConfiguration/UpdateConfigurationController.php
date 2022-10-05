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

namespace Core\Security\ProviderConfiguration\Infrastructure\Local\Api\UpdateConfiguration;

use Centreon\Domain\Contact\Contact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Application\Controller\AbstractController;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\UpdateConfiguration;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\UpdateConfigurationRequest;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\{
    UpdateConfigurationPresenterInterface
};

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
        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (! $contact->hasTopologyRole(Contact::ROLE_ADMINISTRATION_AUTHENTICATION_READ_WRITE)) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }
        $this->validateDataSent($request, __DIR__ . '/UpdateConfigurationSchema.json');
        $updateConfigurationRequest = $this->createUpdateConfigurationRequest($request);
        $useCase($presenter, $updateConfigurationRequest);

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return UpdateConfigurationRequest
     */
    private function createUpdateConfigurationRequest(
        Request $request,
    ): UpdateConfigurationRequest {
        $jsonBody = (string) $request->getContent();
        $requestData = json_decode($jsonBody, true);
        $passwordPolicy = $requestData['password_security_policy'];
        $updateRequest = new UpdateConfigurationRequest();
        $updateRequest->passwordMinimumLength = $passwordPolicy['password_min_length'];
        $updateRequest->hasUppercase = $passwordPolicy['has_uppercase'];
        $updateRequest->hasLowercase = $passwordPolicy['has_lowercase'];
        $updateRequest->hasNumber = $passwordPolicy['has_number'];
        $updateRequest->hasSpecialCharacter = $passwordPolicy['has_special_character'];
        $updateRequest->attempts = $passwordPolicy['attempts'];
        $updateRequest->blockingDuration = $passwordPolicy['blocking_duration'];
        $updateRequest->passwordExpirationDelay = $passwordPolicy['password_expiration']['expiration_delay'];
        $updateRequest->passwordExpirationExcludedUserAliases =
            $passwordPolicy['password_expiration']['excluded_users'];
        $updateRequest->canReusePasswords = $passwordPolicy['can_reuse_passwords'];
        $updateRequest->delayBeforeNewPassword = $passwordPolicy['delay_before_new_password'];

        return $updateRequest;
    }
}
