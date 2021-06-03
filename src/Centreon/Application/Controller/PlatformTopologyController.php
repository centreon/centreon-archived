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

namespace Centreon\Application\Controller;

use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\Contact\Contact;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyException;
use Centreon\Infrastructure\PlatformTopology\Repository\Model\PlatformJsonGraph;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;

/**
 * This controller is designed to manage platform topology API requests and register new servers.
 *
 * @package Centreon\Application\Controller
 */
class PlatformTopologyController extends AbstractController
{
    /**
     * @var PlatformTopologyServiceInterface
     */
    private $platformTopologyService;

    public const SERIALIZER_GROUP_JSON_GRAPH = ['platform_topology_json_graph'];

    /**
     * PlatformTopologyController constructor
     * @param PlatformTopologyServiceInterface $platformTopologyService
     */
    public function __construct(
        PlatformTopologyServiceInterface $platformTopologyService
    ) {
        $this->platformTopologyService = $platformTopologyService;
    }

    /**
     * Validate platform topology data according to json schema
     *
     * @param array<mixed> $platformToAdd data sent in json
     * @param string $schemaPath
     * @return void
     * @throws PlatformTopologyException
     */
    private function validatePlatformTopologySchema(array $platformToAdd, string $schemaPath): void
    {
        $platformTopologySchemaToValidate = Validator::arrayToObjectRecursive($platformToAdd);
        $validator = new Validator();
        $validator->validate(
            $platformTopologySchemaToValidate,
            (object) ['ref' => 'file://' . $schemaPath],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new PlatformTopologyException($message);
        }
    }

    /**
     * Entry point to register a new server
     *
     * @param Request $request
     * @return View
     * @throws PlatformTopologyException
     */
    public function addPlatformToTopology(Request $request): View
    {
        // check user rights
        $this->denyAccessUnlessGrantedForApiConfiguration();

        // Check Topology access to Configuration > Pollers page
        if (!$this->getUser()->hasTopologyRole(Contact::ROLE_CONFIGURATION_MONITORING_SERVER_READ_WRITE)) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        // get http request content
        $platformToAdd = json_decode((string) $request->getContent(), true);
        if (!is_array($platformToAdd)) {
            throw new PlatformTopologyException(
                _('Error when decoding sent data'),
                Response::HTTP_BAD_REQUEST
            );
        }

        /**
         * @var string $centreonPath
         */
        $centreonPath = $this->getParameter('centreon_path');
        // validate request payload consistency
        $this->validatePlatformTopologySchema(
            $platformToAdd,
            $centreonPath . 'config/json_validator/latest/Centreon/PlatformTopology/Register.json'
        );

        try {
            $platformTopology = (new PlatformPending())
                ->setName($platformToAdd['name'])
                ->setAddress($platformToAdd['address'])
                ->setType($platformToAdd['type'])
                ->setHostname($platformToAdd['hostname'])
                ->setParentAddress($platformToAdd['parent_address']);

            $this->platformTopologyService->addPendingPlatformToTopology($platformTopology);

            return $this->view(null, Response::HTTP_CREATED);
        } catch (EntityNotFoundException $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get the Topology of a platform with an adapted Json Graph Format.
     *
     * @return View
     * @throws PlatformTopologyException
     */
    public function getPlatformJsonGraph(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        // Check Topology access to Configuration > Pollers page
        $user= $this->getUser();
        if (
            !$user->hasTopologyRole(Contact::ROLE_CONFIGURATION_MONITORING_SERVER_READ)
            && !$user->hasTopologyRole(Contact::ROLE_CONFIGURATION_MONITORING_SERVER_READ_WRITE)
        ) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        try {
            $platformTopology = $this->platformTopologyService->getPlatformTopology();
            $edges = [];
            $nodes = [];

            //Format the PlatformTopology into a Json Graph Format
            foreach ($platformTopology as $platform) {
                $topologyJsonGraph = new PlatformJsonGraph($platform);
                if (!empty($topologyJsonGraph->getRelation())) {
                    $edges[] = $topologyJsonGraph->getRelation();
                }
                $nodes[$topologyJsonGraph->getId()] = $topologyJsonGraph;
            }
            $context = (new Context())->setGroups(self::SERIALIZER_GROUP_JSON_GRAPH);

            return $this->view(
                [
                    'graph' => [
                        'label' => 'centreon-topology',
                        'metadata' => [
                            'version' => '1.0.0'
                        ],
                        'nodes' => $nodes,
                        'edges' => $edges
                    ],
                ],
                Response::HTTP_OK
            )->setContext($context);
        } catch (EntityNotFoundException $e) {
            return $this->view(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete a platform from the topology.
     *
     * @param int $serverId
     * @return View
     */
    public function deletePlatform(int $serverId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        // Check Topology access to Configuration > Pollers page
        if (!$this->getUser()->hasTopologyRole(Contact::ROLE_CONFIGURATION_MONITORING_SERVER_READ_WRITE)) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        try {
            $this->platformTopologyService->deletePlatformAndReallocateChildren($serverId);
            return $this->view(null, Response::HTTP_NO_CONTENT);
        } catch (EntityNotFoundException $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
