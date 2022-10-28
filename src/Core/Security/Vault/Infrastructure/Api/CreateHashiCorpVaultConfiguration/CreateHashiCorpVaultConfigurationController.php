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

namespace Core\Security\Vault\Infrastructure\Api\CreateHashiCorpVaultConfiguration;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Contact\Contact;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\Vault\Application\UseCase\CreateHashiCorpVaultConfiguration\{
    CreateHashiCorpVaultConfiguration,
    CreateHashiCorpVaultConfigurationPresenterInterface,
    CreateHashiCorpVaultConfigurationRequest
};
use Symfony\Component\HttpFoundation\Request;

final class CreateHashiCorpVaultConfigurationController extends AbstractController
{
    /**
     * @param CreateHashiCorpVaultConfiguration $useCase
     * @param Request $request
     * @param CreateHashiCorpVaultConfigurationPresenterInterface $presenter
     *
     * @return object
     */
    public function __invoke(
        CreateHashiCorpVaultConfiguration $useCase,
        Request $request,
        CreateHashiCorpVaultConfigurationPresenterInterface $presenter
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
         *  "address": string,
         *  "port": integer,
         *  "storage": string,
         *  "role_id": string,
         *  "secret_id": string
         * } $decodedRequest
         */
        $decodedRequest = $this->validateAndRetrieveDataSent(
            $request,
            __DIR__ . '/CreateHashiCorpVaultConfigurationSchema.json'
        );

        $createHashiCorpVaultConfigurationRequest = $this->createCreateHashiCorpVaultConfigurationRequest(
            $decodedRequest
        );

        $useCase($presenter, $createHashiCorpVaultConfigurationRequest);

        return $presenter->show();
    }

    /**
     * @param array{
     *  "name": string,
     *  "address": string,
     *  "port": integer,
     *  "storage": string,
     *  "role_id": string,
     *  "secret_id": string
     * } $decodedRequest
     *
     * @return CreateHashiCorpVaultConfigurationRequest
     */
    private function createCreateHashiCorpVaultConfigurationRequest(
        array $decodedRequest
    ): CreateHashiCorpVaultConfigurationRequest {
        $createHashiCorpVaultConfigurationRequest = new CreateHashiCorpVaultConfigurationRequest();
        $createHashiCorpVaultConfigurationRequest->name = $decodedRequest['name'];
        $createHashiCorpVaultConfigurationRequest->address = $decodedRequest['address'];
        $createHashiCorpVaultConfigurationRequest->port = $decodedRequest['port'];
        $createHashiCorpVaultConfigurationRequest->storage = $decodedRequest['storage'];
        $createHashiCorpVaultConfigurationRequest->roleId = $decodedRequest['role_id'];
        $createHashiCorpVaultConfigurationRequest->secretId = $decodedRequest['secret_id'];

        return $createHashiCorpVaultConfigurationRequest;
    }
}
