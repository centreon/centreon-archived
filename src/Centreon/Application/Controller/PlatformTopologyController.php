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

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\MonitoringServer\MonitoringServerService;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use FOS\RestBundle\View\View;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
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
     * @var PlatformTopologyServiceInterface
     */
    private $platformTopologyService;

    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;

    /**
     * @var MonitoringServerServiceInterface
     */
    private $monitoringServerService;

    /**
     * PlatformTopologyController constructor
     * @param PlatformTopologyServiceInterface $platformTopologyService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param MonitoringServerServiceInterface $monitoringServerService
     */
    public function __construct(
        PlatformTopologyServiceInterface $platformTopologyService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        MonitoringServerServiceInterface $monitoringServerService
    ) {
        $this->platformTopologyService = $platformTopologyService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->monitoringServerService = $monitoringServerService;
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
            // check for illegal characters in name
            $this->checkName($platformToAdd['name']);

            $platformTopology = (new PlatformTopology())
                ->setName($platformToAdd['name'])
                ->setAddress($platformToAdd['address'])
                ->setType($platformToAdd['type']);

            // check for illegal characters in hostname
            if (null !== $platformToAdd['hostname']) {
                $this->checkName($platformToAdd['hostname']);
                $platformTopology->setHostname($platformToAdd['hostname']);
            }

            // Check for empty parent_address consistency and set it
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
     * Get engine configuration's illegal characters and check for illegal characters in hostname
     * @param string $stringToCheck
     * @throws EngineException
     * @throws PlatformTopologyException
     * @throws MonitoringServerException
     */
    private function checkName(string $stringToCheck): void
    {
        $monitoringServerName = $this->monitoringServerService->findLocalServer();
        if (null === $monitoringServerName) {
            throw new PlatformTopologyException(
                _('Unable to find local monitoring server name')
            );
        }
        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
            $monitoringServerName->getName()
        );
        if (null === $engineConfiguration) {
            throw new PlatformTopologyException(_('Unable to find the Engine configuration'));
        }

        $foundIllegalCharacters = $this->hasNonRfcCompliantCharacters(
            $stringToCheck,
            $engineConfiguration->getIllegalObjectNameCharacters()
        );
        if (true === $foundIllegalCharacters) {
            throw new PlatformTopologyException(
                sprintf(
                    _("At least one space or illegal character in '%s' was found in platform's name: '%s'"),
                    $engineConfiguration->getIllegalObjectNameCharacters(),
                    $stringToCheck
                )
            );
        }
    }

    /**
     * Find all non RFC compliant characters from the given string.
     *
     * @param string $stringToCheck String to analyse
     * @param string|null $illegalCharacters String containing illegal characters
     * @return bool Return true if illegal characters have been found
     */
    private function hasNonRfcCompliantCharacters(string $stringToCheck, ?string $illegalCharacters): bool
    {
        // Spaces are not RFC compliant and $illegalCharacters will not contains it
        $illegalCharacters .= ' ';

        return $stringToCheck !== EngineConfiguration::removeIllegalCharacters($stringToCheck, $illegalCharacters);
    }
}
