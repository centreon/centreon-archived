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

namespace Core\Infrastructure\Configuration\Platform\Api\AddPlatformsToTopology;

use Centreon\Domain\Contact\Contact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Platform\UseCase\AddPlatformsToTopology\AddPlatformsToTopology;
use Core\Application\Platform\UseCase\AddPlatformsToTopology\AddPlatformsToTopologyRequest;
use Core\Application\Platform\UseCase\AddPlatformsToTopology\AddPlatformsToTopologyPresenterInterface;

class AddPlatformsToTopologyController extends AbstractController
{
    /**
     * @param AddPlatformsToTopology $useCase
     * @param AddPlatformsToTopologyPresenterInterface $presenter
     * @param Request $request
     * @return Respnse
     */
    public function __invoke(
        AddPlatformsToTopology $useCase,
        AddPlatformsToTopologyPresenterInterface $presenter,
        Request $request
    ): Response {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        /**
         * @var Contact
         */
        $user = $this->getUser();
        if(! $user->hasTopologyRole(Contact::ROLE_CONFIGURATION_MONITORING_SERVER_READ_WRITE)) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }
        
        $decodedRequest = json_decode($request->getContent(), true);
        $addPlatformsToTopologyRequest = $this->createAddPlatformsToTopologyRequest($decodedRequest);

        $useCase($presenter, $addPlatformsToTopologyRequest);

        return $presenter->show();
    }

    /**
     * Undocumented function
     *
     * @param array $request
     * @return AddPlatformsToTopologyRequest
     */
    private function createAddPlatformsToTopologyRequest(array $request): AddPlatformsToTopologyRequest
    {
        $addPlatformsToTopologyRequest = new AddPlatformsToTopologyRequest();
        $addPlatformsToTopologyRequest->nodes = $request["nodes"];
        
        return $addPlatformsToTopologyRequest;
    }
}