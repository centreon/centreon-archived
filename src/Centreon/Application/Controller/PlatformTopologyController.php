<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\PlatformTopology\Platform;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Infrastructure\PlatformTopology\Model\PlatformJsonGraph;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
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
     * @throws PlatformException
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
            throw new PlatformException($message);
        }
    }

    /**
     * Entry point to register a new server
     *
     * @param Request $request
     * @return View
     * @throws PlatformException
     */
    public function addPlatformToTopology(Request $request): View
    {
        // check user rights
        $this->denyAccessUnlessGrantedForApiConfiguration();

        // get http request content
        $platformToAdd = json_decode((string) $request->getContent(), true);
        if (!is_array($platformToAdd)) {
            throw new PlatformException(
                _('Error when decoding sent data'),
                Response::HTTP_BAD_REQUEST
            );
        }

        // validate request payload consistency
        $this->validatePlatformTopologySchema(
            $platformToAdd,
            $this->getParameter('centreon_path')
                . 'config/json_validator/latest/Centreon/PlatformTopology/Register.json'
        );

        try {
            $platformTopology = (new Platform())
                ->setName($platformToAdd['name'])
                ->setAddress($platformToAdd['address'])
                ->setType($platformToAdd['type'])
                ->setHostname($platformToAdd['hostname'])
                ->setParentAddress($platformToAdd['parent_address']);

            $this->platformTopologyService->addPlatformToTopology($platformTopology);

            return $this->view(null, Response::HTTP_CREATED);
        } catch (EntityNotFoundException $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (PlatformConflictException  $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Throwable $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get the Topology of a platform with an adapted Json Graph Format.
     *
     * @return View
     */
    public function getPlatformJsonGraph(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        try {
            /**
             * @var Platform[] $platformTopology
             */
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
