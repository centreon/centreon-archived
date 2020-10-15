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

namespace Centreon\Domain\PlatformTopology;

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Exception\EntityNotFoundException;

/**
 * Service intended to register a new server to the platform topology
 *
 * @package Centreon\Domain\PlatformTopology
 */
class PlatformTopologyService implements PlatformTopologyServiceInterface
{
    /**
     * @var PlatformTopologyRepositoryInterface
     */
    private $platformTopologyRepository;

    /**
     * @var PlatformInformationServiceInterface
     */
    private $platformInformation;

    /**
     * @var ProxyServiceInterface
     */
    private $proxyService;

    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;

    /**
     * @var MonitoringServerServiceInterface
     */
    private $monitoringServerService;

    /**
     * @var PlatformTopologyRegisterRepositoryInterface
     */
    private $platformTopologyRegisterRepository;

    /**
     * PlatformTopologyService constructor.
     *
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     * @param PlatformInformationServiceInterface $platformInformationService
     * @param ProxyServiceInterface $proxyService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param MonitoringServerServiceInterface $monitoringServerService
     * @param PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository
     */
    public function __construct(
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        PlatformInformationServiceInterface $platformInformationService,
        ProxyServiceInterface $proxyService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        MonitoringServerServiceInterface $monitoringServerService,
        PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository
    ) {
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->platformInformation = $platformInformationService;
        $this->proxyService = $proxyService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->monitoringServerService = $monitoringServerService;
        $this->platformTopologyRegisterRepository = $platformTopologyRegisterRepository;
    }

    /**
     * @inheritDoc
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        // check entity consistency
        $this->checkEntityConsistency($platformTopology);

        /**
         * Search for already registered central or remote top level server on this platform
         * As only top level platform do not need parent_address and only one should be registered
         */
        if (PlatformTopology::TYPE_CENTRAL === $platformTopology->getType()) {
            // New unique Central top level platform case
            $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformTopology::TYPE_CENTRAL);
            $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformTopology::TYPE_REMOTE);
            $this->setMonitoringServerId($platformTopology);
        } elseif (PlatformTopology::TYPE_REMOTE === $platformTopology->getType()) {
            // Cannot add a Remote behind another Remote
            $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformTopology::TYPE_REMOTE);
            if (null === $platformTopology->getParentAddress()) {
                // New unique Remote top level platform case
                $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformTopology::TYPE_CENTRAL);
                $this->setMonitoringServerId($platformTopology);
            }
        }

        $this->checkForAlreadyRegisteredSameNameOrAddress($platformTopology);

        /**
         * @var PlatformTopology|null $registeredParentInTopology
         */
        $registeredParentInTopology = $this->findParentPlatform($platformTopology);

        /**
         * The top level platform is defined as a Remote :
         * Getting data and calling the register request on the Central if the remote is already registered on it
         */
        if (
            null !== $registeredParentInTopology
            && true === $registeredParentInTopology->isLinkedToAnotherServer()
        ) {
            /**
             * Getting platform information's data
             * @var PlatformInformation|null $foundPlatformInformation
             */
            $foundPlatformInformation = $this->platformInformation->getInformation();

            if (null === $foundPlatformInformation) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Platform : '%s'@'%s' mandatory data are missing. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (false === $foundPlatformInformation->isRemote()) {
                throw new PlatformTopologyConflictException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not declared as a 'remote'."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getCentralServerAddress()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not linked to a Central. Please use the wizard first."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (
                null === $foundPlatformInformation->getApiUsername()
                || null === $foundPlatformInformation->getApiCredentials()
            ) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's credentials are missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiScheme()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's protocol scheme is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPort()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's protocol port is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPath()) {
                throw new PlatformTopologyException(
                    sprintf(
                        _("Central's path is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }

            /**
             * Register this platform to its Central
             */
            try {
                $this->platformTopologyRegisterRepository->registerPlatformTopologyToParent(
                    $platformTopology,
                    $foundPlatformInformation,
                    $this->proxyService->getProxy()
                );
            } catch (PlatformTopologyConflictException $ex) {
                throw $ex;
            } catch (RepositoryException $ex) {
                throw new PlatformTopologyException($ex->getMessage(), $ex->getCode(), $ex);
            } catch (\Exception $ex) {
                throw new PlatformTopologyException('', 0, $ex);
            }
        }

        /*
         * Insert the platform into 'platform_topology' table
         */
        try {
            // add the new platform
            $this->platformTopologyRepository->addPlatformToTopology($platformTopology);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(
                sprintf(
                    _("Error when adding in topology the platform : '%s'@'%s'"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
    }

    /**
     * Get engine configuration's illegal characters and check for illegal characters in hostname
     * @param string|null $stringToCheck
     * @throws EngineException
     * @throws PlatformTopologyException
     * @throws MonitoringServerException
     */
    private function checkName(?string $stringToCheck): void
    {
        if (null === $stringToCheck) {
            return;
        }

        $monitoringServerName = $this->monitoringServerService->findLocalServer();
        if (null === $monitoringServerName || null === $monitoringServerName->getName()) {
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

        $foundIllegalCharacters = EngineConfiguration::hasIllegalCharacters(
            $stringToCheck,
            $engineConfiguration->getIllegalObjectNameCharacters()
        );
        if (true === $foundIllegalCharacters) {
            throw new PlatformTopologyException(
                sprintf(
                    _("At least one illegal character in '%s' was found in platform's name: '%s'"),
                    $engineConfiguration->getIllegalObjectNameCharacters(),
                    $stringToCheck
                )
            );
        }
    }

    /**
     * Check for RFC 1123 & 1178 rules
     * More details are available in man hostname
     * @param string|null $stringToCheck
     * @return bool
     */
    private function isHostnameValid(?string $stringToCheck): bool
    {
        if (null === $stringToCheck) {
            // empty hostname is allowed and should not be blocked or throw an exception
            return true;
        }
        return (
            preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $stringToCheck)
            && preg_match("/^.{1,253}$/", $stringToCheck) // max 253 characters by hostname
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $stringToCheck) // max 63 characters by domain
        );
    }

    /**
     * Check hostname consistency
     * @param string|null $stringToCheck
     * @throws PlatformTopologyException
     */
    private function checkHostname(?string $stringToCheck): void
    {
        if (null === $stringToCheck) {
            return;
        }

        if (false === $this->isHostnameValid($stringToCheck)) {
            throw new PlatformTopologyException(
                sprintf(
                    _("At least one non RFC compliant character was found in platform's hostname: '%s'"),
                    $stringToCheck
                )
            );
        }
    }

    /**
     * Check consistency of each mandatory and optional parameters
     * @param PlatformTopology $platformTopology
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     * @throws PlatformTopologyConflictException
     * @throws PlatformTopologyException
     */
    private function checkEntityConsistency(PlatformTopology $platformTopology): void
    {
        // Check non RFC compliant characters in name and hostname
        if (null === $platformTopology->getName()) {
            throw new EntityNotFoundException(_("Missing mandatory platform name"));
        }
        $this->checkName($platformTopology->getName());
        $this->checkHostname($platformTopology->getHostname());

        // Check empty platform's address
        if (null === $platformTopology->getAddress()) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory platform address of: '%s'"),
                    $platformTopology->getName()
                )
            );
        }

        // Check empty parent address vs type consistency
        if (
            null === $platformTopology->getParentAddress()
            && !in_array(
                $platformTopology->getType(),
                [PlatformTopology::TYPE_CENTRAL, PlatformTopology::TYPE_REMOTE],
                false
            )
        ) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory parent address, to link the platform : '%s'@'%s'"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }

        // or Check for similar parent_address and address
        if ($platformTopology->getParentAddress() === $platformTopology->getAddress()) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("Same address and parent_address for platform : '%s'@'%s'."),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
    }

    /**
     * Used when parent_address is null, to check if this type of platform is already registered
     *
     * @param string $type platform type to find
     * @throws PlatformTopologyConflictException
     * @throws \Exception
     */
    private function searchAlreadyRegisteredTopLevelPlatformByType(string $type): void
    {
        $foundAlreadyRegisteredPlatformByType = $this->platformTopologyRepository->findPlatformTopologyByType($type);
        if (null !== $foundAlreadyRegisteredPlatformByType) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A '%s': '%s'@'%s' is already registered"),
                    $type,
                    $foundAlreadyRegisteredPlatformByType->getName(),
                    $foundAlreadyRegisteredPlatformByType->getAddress()
                )
            );
        }
    }

    /**
     * Search for platforms monitoring ID and set it as serverId
     *
     * @param PlatformTopology $platformTopology
     * @throws PlatformTopologyConflictException
     * @throws \Exception
     */
    private function setMonitoringServerId(PlatformTopology $platformTopology): void
    {
        $foundServerInNagiosTable = null;
        if (null !== $platformTopology->getName()) {
            $foundServerInNagiosTable = $this->platformTopologyRepository->findLocalMonitoringIdFromName(
                $platformTopology->getName()
            );
        }

        if (null === $foundServerInNagiosTable) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("The server type '%s' : '%s'@'%s' does not match the one configured in Centreon or is disabled"),
                    $platformTopology->getType(),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
        $platformTopology->setServerId($foundServerInNagiosTable->getId());
    }

    /**
     * Search for already registered platforms using same name or address
     *
     * @param PlatformTopology $platformTopology
     * @throws PlatformTopologyConflictException
     * @throws EntityNotFoundException
     */
    private function checkForAlreadyRegisteredSameNameOrAddress(PlatformTopology $platformTopology): void
    {
        // Two next checks are required for phpStan lvl8. But already done in the checkEntityConsistency method
        if (null === $platformTopology->getName()) {
            throw new EntityNotFoundException(_("Missing mandatory platform name"));
        }
        if (null === $platformTopology->getAddress()) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory platform address of: '%s'"),
                    $platformTopology->getName()
                )
            );
        }

        $isAlreadyRegistered = $this->platformTopologyRepository->isPlatformAlreadyRegisteredInTopology(
            $platformTopology->getAddress(),
            $platformTopology->getName()
        );

        if (true === $isAlreadyRegistered) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A platform using the name : '%s' or address : '%s' already exists"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
    }

    /**
     * Search for platform's parent ID in topology
     *
     * @param PlatformTopology $platformTopology
     * @return PlatformTopology|null
     * @throws EntityNotFoundException
     * @throws PlatformTopologyConflictException
     * @throws \Exception
     */
    private function findParentPlatform(PlatformTopology $platformTopology): ?PlatformTopology
    {
        if (null === $platformTopology->getParentAddress()) {
            return null;
        }

        $registeredParentInTopology = $this->platformTopologyRepository->findPlatformTopologyByAddress(
            $platformTopology->getParentAddress()
        );
        if (null === $registeredParentInTopology) {
            throw new EntityNotFoundException(
                sprintf(
                    _("No parent platform was found for : '%s'@'%s'"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }

        // Avoid to link a remote to another remote
        if (
            PlatformTopology::TYPE_REMOTE === $platformTopology->getType()
            && PlatformTopology::TYPE_REMOTE === $registeredParentInTopology->getType()
        ) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("Unable to link a 'remote': '%s'@'%s' to another remote platform"),
                    $registeredParentInTopology->getName(),
                    $registeredParentInTopology->getAddress()
                )
            );
        }

        // Check parent consistency, as the platform can only be linked to a remote or central type
        if (
            !in_array(
                $registeredParentInTopology->getType(),
                [PlatformTopology::TYPE_REMOTE, PlatformTopology::TYPE_CENTRAL],
                false
            )
        ) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("Cannot register the '%s' platform : '%s'@'%s' behind a '%s' platform"),
                    $platformTopology->getType(),
                    $platformTopology->getName(),
                    $platformTopology->getAddress(),
                    $registeredParentInTopology->getType()
                )
            );
        }

        $platformTopology->setParentId($registeredParentInTopology->getId());

        // A platform behind a remote needs to send the data to the Central too
        if (
            null === $registeredParentInTopology->getParentId()
            && $registeredParentInTopology->getType() === PlatformTopology::TYPE_REMOTE
        ) {
            $registeredParentInTopology->setLinkedToAnotherServer(true);
            return $registeredParentInTopology;
        }
        return null;
    }
}
