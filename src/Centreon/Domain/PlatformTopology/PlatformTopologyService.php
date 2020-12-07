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

use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;

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
    private $platformInformationService;

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
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    /**
     * Broker Retention Parameter
     */
    public const BROKER_PEER_RETENTION = "one_peer_retention_mode";

    /**
     * PlatformTopologyService constructor.
     *
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     * @param PlatformInformationServiceInterface $platformInformationService
     * @param ProxyServiceInterface $proxyService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param MonitoringServerServiceInterface $monitoringServerService
     * @param BrokerRepositoryInterface $brokerRepository
     * @param PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository
     */
    public function __construct(
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        PlatformInformationServiceInterface $platformInformationService,
        ProxyServiceInterface $proxyService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        MonitoringServerServiceInterface $monitoringServerService,
        BrokerRepositoryInterface $brokerRepository,
        PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository
    ) {
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->platformInformationService = $platformInformationService;
        $this->proxyService = $proxyService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->monitoringServerService = $monitoringServerService;
        $this->brokerRepository = $brokerRepository;
        $this->platformTopologyRegisterRepository = $platformTopologyRegisterRepository;
    }

    /**
     * @inheritDoc
     */
    public function addPlatformToTopology(Platform $platform): void
    {
        // check entity consistency
        $this->checkEntityConsistency($platform);

        /**
         * Search for already registered central or remote top level server on this platform
         * As only top level platform do not need parent_address and only one should be registered
         */
        if (Platform::TYPE_CENTRAL === $platform->getType()) {
            // New unique Central top level platform case
            $this->searchAlreadyRegisteredTopLevelPlatformByType(Platform::TYPE_CENTRAL);
            $this->searchAlreadyRegisteredTopLevelPlatformByType(Platform::TYPE_REMOTE);
            $this->setMonitoringServerId($platform);
        } elseif (Platform::TYPE_REMOTE === $platform->getType()) {
            // Cannot add a Remote behind another Remote
            $this->searchAlreadyRegisteredTopLevelPlatformByType(Platform::TYPE_REMOTE);
            if (null === $platform->getParentAddress()) {
                // New unique Remote top level platform case
                $this->searchAlreadyRegisteredTopLevelPlatformByType(Platform::TYPE_CENTRAL);
                $this->setMonitoringServerId($platform);
            }
        }

        $this->checkForAlreadyRegisteredSameNameOrAddress($platform);

        /**
         * @var Platform|null $registeredParentInTopology
         */
        $registeredParentInTopology = $this->findParentPlatform($platform);

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
            $foundPlatformInformation = $this->platformInformationService->getInformation();

            if (null === $foundPlatformInformation) {
                throw new PlatformException(
                    sprintf(
                        _("Platform : '%s'@'%s' mandatory data are missing. Please check the Remote Access form."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (false === $foundPlatformInformation->isRemote()) {
                throw new PlatformConflictException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not declared as a 'remote'."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getCentralServerAddress()) {
                throw new PlatformException(
                    sprintf(
                        _("The platform: '%s'@'%s' is not linked to a Central. Please use the wizard first."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (
                null === $foundPlatformInformation->getApiUsername()
                || null === $foundPlatformInformation->getApiCredentials()
            ) {
                throw new PlatformException(
                    sprintf(
                        _("Central's credentials are missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiScheme()) {
                throw new PlatformException(
                    sprintf(
                        _("Central's protocol scheme is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPort()) {
                throw new PlatformException(
                    sprintf(
                        _("Central's protocol port is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }
            if (null === $foundPlatformInformation->getApiPath()) {
                throw new PlatformException(
                    sprintf(
                        _("Central's path is missing on: '%s'@'%s'. Please check the Remote Access form."),
                        $platform->getName(),
                        $platform->getAddress()
                    )
                );
            }

            /**
             * Register this platform to its Central
             */
            try {
                $this->platformTopologyRegisterRepository->registerPlatformToParent(
                    $platform,
                    $foundPlatformInformation,
                    $this->proxyService->getProxy()
                );
            } catch (PlatformConflictException $ex) {
                throw $ex;
            } catch (RepositoryException $ex) {
                throw new PlatformException($ex->getMessage(), $ex->getCode(), $ex);
            } catch (\Exception $ex) {
                throw new PlatformException(_("Error from Central's register API"), 0, $ex);
            }
        }

        /*
         * Insert the platform into 'platform_topology' table
         */
        try {
            // add the new platform
            $this->platformTopologyRepository->addPlatformToTopology($platform);
        } catch (\Exception $ex) {
            throw new PlatformException(
                sprintf(
                    _("Error when adding in topology the platform : '%s'@'%s'"),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }
    }

    /**
     * Get engine configuration's illegal characters and check for illegal characters in hostname
     * @param string|null $stringToCheck
     * @throws EngineException
     * @throws PlatformException
     * @throws MonitoringServerException
     */
    private function checkName(?string $stringToCheck): void
    {
        if (null === $stringToCheck) {
            return;
        }

        $monitoringServerName = $this->monitoringServerService->findLocalServer();
        if (null === $monitoringServerName || null === $monitoringServerName->getName()) {
            throw new PlatformException(
                _('Unable to find local monitoring server name')
            );
        }

        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
            $monitoringServerName->getName()
        );
        if (null === $engineConfiguration) {
            throw new PlatformException(_('Unable to find the Engine configuration'));
        }

        $foundIllegalCharacters = EngineConfiguration::hasIllegalCharacters(
            $stringToCheck,
            $engineConfiguration->getIllegalObjectNameCharacters()
        );
        if (true === $foundIllegalCharacters) {
            throw new PlatformException(
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
     * @throws PlatformException
     */
    private function checkHostname(?string $stringToCheck): void
    {
        if (null === $stringToCheck) {
            return;
        }

        if (false === $this->isHostnameValid($stringToCheck)) {
            throw new PlatformException(
                sprintf(
                    _("At least one non RFC compliant character was found in platform's hostname: '%s'"),
                    $stringToCheck
                )
            );
        }
    }

    /**
     * Check consistency of each mandatory and optional parameters
     * @param Platform $platform
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     * @throws PlatformConflictException
     * @throws PlatformException
     */
    private function checkEntityConsistency(Platform $platform): void
    {
        // Check non RFC compliant characters in name and hostname
        if (null === $platform->getName()) {
            throw new EntityNotFoundException(_("Missing mandatory platform name"));
        }
        $this->checkName($platform->getName());
        $this->checkHostname($platform->getHostname());

        // Check empty platform's address
        if (null === $platform->getAddress()) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory platform address of: '%s'"),
                    $platform->getName()
                )
            );
        }

        // Check empty parent address vs type consistency
        if (
            null === $platform->getParentAddress()
            && !in_array(
                $platform->getType(),
                [Platform::TYPE_CENTRAL, Platform::TYPE_REMOTE],
                false
            )
        ) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory parent address, to link the platform : '%s'@'%s'"),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }

        // or Check for similar parent_address and address
        if ($platform->getParentAddress() === $platform->getAddress()) {
            throw new PlatformConflictException(
                sprintf(
                    _("Same address and parent_address for platform : '%s'@'%s'."),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }
    }

    /**
     * Used when parent_address is null, to check if this type of platform is already registered
     *
     * @param string $type platform type to find
     * @throws PlatformConflictException
     * @throws \Exception
     */
    private function searchAlreadyRegisteredTopLevelPlatformByType(string $type): void
    {
        $foundAlreadyRegisteredPlatformByType = $this->platformTopologyRepository->findTopPlatformByType($type);
        if (null !== $foundAlreadyRegisteredPlatformByType) {
            throw new PlatformConflictException(
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
     * @param Platform $platform
     * @throws PlatformConflictException
     * @throws \Exception
     */
    private function setMonitoringServerId(Platform $platform): void
    {
        $foundServerInNagiosTable = null;
        if (null !== $platform->getName()) {
            $foundServerInNagiosTable = $this->platformTopologyRepository->findLocalMonitoringIdFromName(
                $platform->getName()
            );
        }

        if (null === $foundServerInNagiosTable) {
            throw new PlatformConflictException(
                sprintf(
                    _("The server type '%s' : '%s'@'%s' does not match the one configured in Centreon or is disabled"),
                    $platform->getType(),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }
        $platform->setServerId($foundServerInNagiosTable->getId());
    }

    /**
     * Search for already registered platforms using same name or address
     *
     * @param Platform $platform
     * @throws PlatformConflictException
     * @throws EntityNotFoundException
     */
    private function checkForAlreadyRegisteredSameNameOrAddress(Platform $platform): void
    {
        // Two next checks are required for phpStan lvl8. But already done in the checkEntityConsistency method
        if (null === $platform->getName()) {
            throw new EntityNotFoundException(_("Missing mandatory platform name"));
        }
        if (null === $platform->getAddress()) {
            throw new EntityNotFoundException(
                sprintf(
                    _("Missing mandatory platform address of: '%s'"),
                    $platform->getName()
                )
            );
        }

        $isAlreadyRegistered = $this->platformTopologyRepository->isPlatformAlreadyRegisteredInTopology(
            $platform->getAddress(),
            $platform->getName()
        );

        if (true === $isAlreadyRegistered) {
            throw new PlatformConflictException(
                sprintf(
                    _("A platform using the name : '%s' or address : '%s' already exists"),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }
    }

    /**
     * Search for platform's parent ID in topology
     *
     * @param Platform $platform
     * @return Platform|null
     * @throws EntityNotFoundException
     * @throws PlatformConflictException
     * @throws \Exception
     */
    private function findParentPlatform(Platform $platform): ?Platform
    {
        if (null === $platform->getParentAddress()) {
            return null;
        }

        $registeredParentInTopology = $this->platformTopologyRepository->findPlatformByAddress(
            $platform->getParentAddress()
        );
        if (null === $registeredParentInTopology) {
            throw new EntityNotFoundException(
                sprintf(
                    _("No parent platform was found for : '%s'@'%s'"),
                    $platform->getName(),
                    $platform->getAddress()
                )
            );
        }

        // Avoid to link a remote to another remote
        if (
            Platform::TYPE_REMOTE === $platform->getType()
            && Platform::TYPE_REMOTE === $registeredParentInTopology->getType()
        ) {
            throw new PlatformConflictException(
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
                [Platform::TYPE_REMOTE, Platform::TYPE_CENTRAL],
                false
            )
        ) {
            throw new PlatformConflictException(
                sprintf(
                    _("Cannot register the '%s' platform : '%s'@'%s' behind a '%s' platform"),
                    $platform->getType(),
                    $platform->getName(),
                    $platform->getAddress(),
                    $registeredParentInTopology->getType()
                )
            );
        }

        $platform->setParentId($registeredParentInTopology->getId());

        // A platform behind a remote needs to send the data to the Central too
        if (
            null === $registeredParentInTopology->getParentId()
            && $registeredParentInTopology->getType() === Platform::TYPE_REMOTE
        ) {
            $registeredParentInTopology->setLinkedToAnotherServer(true);
            return $registeredParentInTopology;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPlatformTopology(): array
    {
        /**
         * @var Platform[] $platformTopology
         */
        $platformTopology = $this->platformTopologyRepository->getPlatformTopology();
        if ($platformTopology === null) {
            throw new EntityNotFoundException(_('No Platform Topology found.'));
        }

        foreach ($platformTopology as $platform) {
            //Set the parent address if the platform is not the top level
            if ($platform->getParentId() !== null) {
                $platformParent = $this->platformTopologyRepository->findPlatform(
                    $platform->getParentId()
                );
                $platform->setParentAddress($platformParent->getAddress());
            }

            //Set the broker relation type if the platform is completely registered
            if ($platform->getServerId() !== null) {
                $brokerConfigurations = $this->brokerRepository->findByMonitoringServerAndParameterName(
                    $platform->getServerId(),
                    self::BROKER_PEER_RETENTION
                );

                $platform->setRelation(PlatformRelation::NORMAL_RELATION);
                foreach ($brokerConfigurations as $brokerConfiguration) {
                    if ($brokerConfiguration->getConfigurationValue() === "yes") {
                        $platform->setRelation(PlatformRelation::PEER_RETENTION_RELATION);
                        break;
                    }
                }
            }
        }
        return $platformTopology;
    }

    /**
     * @inheritDoc
     */
    public function deletePlatform(int $serverId): void
    {
        try {
            if ($this->platformTopologyRepository->findPlatform($serverId) === null) {
                throw new EntityNotFoundException(_('Platform not found.'));
            }
            $this->platformTopologyRepository->deletePlatform($serverId);
        } catch (EntityNotFoundException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new PlatformException($ex->getMessage(), 0, $ex);
        }
    }
}
