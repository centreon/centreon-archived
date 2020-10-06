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
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Application\PlatformTopology\PlatformTopologyHeliosFormat;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
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

    public const SERIALIZER_GROUP_HELIOS = ['platform_topology_helios'];

    /**
     * PlatformTopologyController constructor
     * @param PlatformTopologyServiceInterface $platformTopologyService
     */
    public function __construct(PlatformTopologyServiceInterface $platformTopologyService)
    {
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

        // get http request content
        $platformToAdd = json_decode((string) $request->getContent(), true);
        if (!is_array($platformToAdd)) {
            throw new PlatformTopologyException(
                _('Error when decoding sent data'),
                Response::HTTP_BAD_REQUEST
            );
        }

        // validate data consistency
        $this->validatePlatformTopologySchema(
            $platformToAdd,
            $this->getParameter('centreon_path')
                . 'config/json_validator/latest/Centreon/PlatformTopology/Register.json'
        );

        try {
            $platformTopology = (new PlatformTopology())
                ->setName($platformToAdd['name'])
                ->setAddress($platformToAdd['address'])
                ->setType($platformToAdd['type']);

            // Check for empty parent_address consistency
            if (
                empty($platformToAdd['parent_address'])
                && $platformTopology->getType() !== PlatformTopology::TYPE_CENTRAL
                && $platformTopology->getType() !== PlatformTopology::TYPE_REMOTE
            ) {
                throw new EntityNotFoundException(
                    sprintf(
                        _("Missing mandatory parent address, to link the platform : '%s'@'%s'"),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }

            if (isset($platformToAdd['parent_address'])) {
                // Check for same address and parent_address
                if ($platformToAdd['parent_address'] === $platformTopology->getAddress()) {
                    throw new PlatformTopologyConflictException(
                        sprintf(
                            _("Same address and parent_address for platform : '%s'@'%s'."),
                            $platformTopology->getName(),
                            $platformTopology->getAddress()
                        )
                    );
                }
                $platformTopology->setParentAddress($platformToAdd['parent_address']);
            }

            $this->platformTopologyService->addPlatformToTopology($platformTopology);

            return $this->view(null, Response::HTTP_CREATED);
        } catch (EntityNotFoundException $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (PlatformTopologyConflictException  $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Throwable $ex) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get the Topology of a platform with an adapted Helios Format.
     *
     * @return View
     */
    public function getPlatformTopologyHelios(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        // Get the entire topology of the platform as an array of PlatformTopology instances
        $platformCompleteTopology = $this->platformTopologyService->getPlatformCompleteTopology();
        $edges =  [];
        $topologiesHelios = [];

        //Format the PlatformTopology into a Json Graph Format, usable by Helios
        foreach ($platformCompleteTopology as $topology) {
            $topologyHelios = new PlatformTopologyHeliosFormat($topology);
            $topologiesHelios[] = $topologyHelios;
            if (!empty($topologyHelios->getRelation())) {
                $edges[] = $topologyHelios->getRelation();
            }
        }
        $context = (new Context())->setGroups(self::SERIALIZER_GROUP_HELIOS);
        return $this->view([
            'graph' => [
                'label' => 'centreon-topology',
                'metadata' => [
                    'version' => '1.0.0'
                ]
            ],
            'nodes' => $topologiesHelios,
            'edges' => $edges
        ], Response::HTTP_OK)->setContext($context);
    }
}
