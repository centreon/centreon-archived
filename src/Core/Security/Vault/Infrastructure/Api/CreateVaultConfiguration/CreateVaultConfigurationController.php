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

namespace Core\Security\Vault\Infrastructure\Api\CreateVaultConfiguration;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Contact\Contact;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\Vault\Application\UseCase\CreateVaultConfiguration\{
    CreateVaultConfiguration,
    CreateVaultConfigurationPresenterInterface,
    CreateVaultConfigurationRequest
};
use Symfony\Component\HttpFoundation\Request;

final class CreateVaultConfigurationController extends AbstractController
{
    /**
     * @param CreateVaultConfiguration $useCase
     * @param Request $request
     * @param CreateVaultConfigurationPresenterInterface $presenter
     *
     * @return object
     */
    public function __invoke(
        CreateVaultConfiguration $useCase,
        Request $request,
        CreateVaultConfigurationPresenterInterface $presenter
    ): object {
        /**
         * @var Contact $user
         */
        $user = $this->getUser();
        if (! $user->isAdmin()) {
            $presenter->setResponseStatus(
                new UnauthorizedResponse('Only admin user can create vault configuration')
            );

            return $presenter->show();
        }

        /**
         * @var array{
         *  "name": string,
         *  "type_id": integer,
         *  "address": string,
         *  "port": integer,
         *  "storage": string,
         *  "role_id": string,
         *  "secret_id": string
         * } $decodedRequest
         */
        $decodedRequest = $this->validateAndRetrieveDataSent(
            $request,
            __DIR__ . '/CreateVaultConfigurationSchema.json'
        );

        $createVaultConfigurationRequest = $this->createCreateVaultConfigurationRequest(
            $decodedRequest
        );

        $useCase($presenter, $createVaultConfigurationRequest);

        return $presenter->show();
    }

    /**
     * @param array{
     *  "name": string,
     *  "type_id": integer,
     *  "address": string,
     *  "port": integer,
     *  "storage": string,
     *  "role_id": string,
     *  "secret_id": string
     * } $decodedRequest
     *
     * @return CreateVaultConfigurationRequest
     */
    private function createCreateVaultConfigurationRequest(
        array $decodedRequest
    ): CreateVaultConfigurationRequest {
        $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();
        $createVaultConfigurationRequest->name = $decodedRequest['name'];
        $createVaultConfigurationRequest->typeId = $decodedRequest['type_id'];
        $createVaultConfigurationRequest->address = $decodedRequest['address'];
        $createVaultConfigurationRequest->port = $decodedRequest['port'];
        $createVaultConfigurationRequest->storage = $decodedRequest['storage'];
        $createVaultConfigurationRequest->roleId = $decodedRequest['role_id'];
        $createVaultConfigurationRequest->secretId = $decodedRequest['secret_id'];

        return $createVaultConfigurationRequest;
    }
}
