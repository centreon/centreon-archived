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

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicy;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyRequest;

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
        $request = $this->createUpdateSecurityPolicyRequestOrFail($request);
        $useCase($presenter, $request);

        return $presenter->show();
    }

    /**
     * Create a DTO from HTTP Request or throw an exception if the body is incorrect.
     *
     * @param Request $request
     * @return UpdateSecurityPolicyRequest
     * @throws \InvalidArgumentException
     */
    private function createUpdateSecurityPolicyRequestOrFail(Request $request): UpdateSecurityPolicyRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        UpdateSecurityPolicyRequest::validateRequestOrFail($requestData);
        $updateSecurityPolicyRequest = new UpdateSecurityPolicyRequest();
        $updateSecurityPolicyRequest->passwordMinimumLength = $requestData['password_length'];
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
}
