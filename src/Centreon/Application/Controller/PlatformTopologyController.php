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

use Centreon\Domain\PlatformTopology\PlatformTopology;
use FOS\RestBundle\View\View;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is designed to manage platform topology API requests and register new servers.
 *
 * @package Centreon\Application\Controller
 */
class PlatformTopologyController extends AbstractController
{
    /**
     * @var array<string> Allowed server types
     */
    private const ALLOWED_TYPES = ["Central", "Poller", "Remote", "MAP", "MBI"];

    /**
     * @var PlatformTopologyServiceInterface
     */
    private $platformTopologyService;

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
            return $this->view([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => sprintf(
                    _('Error when decoding sent data')
                )
            ]);
        }

        // validate data consistency
        $this->validatePlatformTopologySchema(
            $platformToAdd,
            $this->getParameter('centreon_path')
            . 'config/json_validator/latest/Centreon/PlatformTopology/AddServer.json'
        );

        try {
            $setPlatformTopology = (new PlatformTopology())
                ->setServerName($platformToAdd['server_name'])
                ->setServerAddress($platformToAdd['address'])
                ->setServerType($platformToAdd['server_type'])
                ->setServerParentAddress($platformToAdd['server_parent']);

            $this->platformTopologyService->addPlatformToTopology($setPlatformTopology);

            return $this->view(null, Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            throw new PlatformTopologyException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
