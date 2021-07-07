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

namespace Centreon\Domain\PlatformTopology;

use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;
use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use Centreon\Domain\PlatformTopology\Model\PlatformRelation;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerRepositoryInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;
use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryExceptionInterface;

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
     * @var RemoteServerRepositoryInterface
     */
    private $remoteServerRepository;

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
     * @param RemoteServerRepositoryInterface $remoteServerRepository
     */
    public function __construct(
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        PlatformInformationServiceInterface $platformInformationService,
        ProxyServiceInterface $proxyService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        MonitoringServerServiceInterface $monitoringServerService,
        BrokerRepositoryInterface $brokerRepository,
        PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository,
        RemoteServerRepositoryInterface $remoteServerRepository
    ) {
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->platformInformationService = $platformInformationService;
        $this->proxyService = $proxyService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->monitoringServerService = $monitoringServerService;
        $this->brokerRepository = $brokerRepository;
        $this->platformTopologyRegisterRepository = $platformTopologyRegisterRepository;
        $this->remoteServerRepository = $remoteServerRepository;
    }

    /**
     * @inheritDoc
     */
    public function addPendingPlatformToTopology(PlatformInterface $platformPending): void
    {
        // check entity consistency
        $this->checkEntityConsistency($platformPending);

        /**
         * Search for already registered central or remote top level server on this platform
         * As only top level platform do not need parent_address and only one should be registered
         */

        $this->checkForAlreadyRegisteredSameNameOrAddress($platformPending);

        switch ($platformPending->getType()) {
            case PlatformPending::TYPE_CENTRAL:
                // New unique Central top level platform case
                $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformPending::TYPE_CENTRAL);
                $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformPending::TYPE_REMOTE);
                $this->setMonitoringServerId($platformPending);
                break;
            case PlatformPending::TYPE_REMOTE:
                // Cannot add a Remote behind another Remote
                $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformPending::TYPE_REMOTE);
                if (null === $platformPending->getParentAddress()) {
                    // New unique Remote top level platform case
                    $this->searchAlreadyRegisteredTopLevelPlatformByType(PlatformPending::TYPE_CENTRAL);
                    $this->setMonitoringServerId($platformPending);
                }
                break;
        }

        /**
         * @var PlatformInterface|null $registeredParentInTopology
         */
        $registeredParentInTopology = $this->findParentPlatform($platformPending);

        /**
         * The top level platform is defined as a Remote :
         * Getting data and calling the register request on the Central if the remote is already registered on it
         */
        if (
            null !== $registeredParentInTopology
            && true === $registeredParentInTopology->isLinkedToAnotherServer()
        ) {
            $this->registerPlatformToParent($platformPending);
        }

        /*
         * Insert the platform into 'platform_topology' table
         */
        try {
            // add the new platform
            $this->platformTopologyRepository->addPlatformToTopology($platformPending);
        } catch (\Exception $ex) {
            throw PlatformTopologyException::errorWhenAddingThePlatform(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
    }

    /**
     * @param PlatformInterface $platformPending
     * @throws PlatformTopologyException
     * @throws PlatformInformationException
     */
    private function registerPlatformToParent(PlatformInterface $platformPending): void
    {
        /**
         * Getting platform information's data
         * @var PlatformInformation|null $foundPlatformInformation
         */
        $foundPlatformInformation = $this->platformInformationService->getInformation();
        if (null === $foundPlatformInformation) {
            throw PlatformTopologyException::missingMandatoryData(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (false === $foundPlatformInformation->isRemote()) {
            throw PlatformTopologyException::notTypeRemote(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (null === $foundPlatformInformation->getCentralServerAddress()) {
            throw PlatformTopologyException::platformNotLinkedToTheCentral(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (
            null === $foundPlatformInformation->getApiUsername()
            || null === $foundPlatformInformation->getApiCredentials()
        ) {
            throw PlatformTopologyException::missingCentralCredentials(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (null === $foundPlatformInformation->getApiScheme()) {
            throw PlatformTopologyException::missingCentralScheme(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (null === $foundPlatformInformation->getApiPort()) {
            throw PlatformTopologyException::missingCentralPort(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }
        if (null === $foundPlatformInformation->getApiPath()) {
            throw PlatformTopologyException::missingCentralPath(
                $platformPending->getName(),
                $platformPending->getAddress()
            );
        }

        /**
         * Register this platform to its Parent
         */
        try {
            $this->platformTopologyRegisterRepository->registerPlatformToParent(
                $platformPending,
                $foundPlatformInformation,
                $this->proxyService->getProxy()
            );
        } catch (PlatformTopologyException $ex) {
            throw $ex;
        } catch (PlatformTopologyRepositoryExceptionInterface $ex) {
            throw new PlatformTopologyException($ex->getMessage(), $ex->getCode(), $ex);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(_("Error from Central's register API"), 0, $ex);
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
            throw PlatformTopologyException::unableToFindMonitoringServerName();
        }

        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
            $monitoringServerName->getName()
        );
        if (null === $engineConfiguration) {
            throw PlatformTopologyException::unableToFindEngineConfiguration();
        }

        $foundIllegalCharacters = $engineConfiguration->hasIllegalCharacters($stringToCheck);
        if (true === $foundIllegalCharacters) {
            throw PlatformTopologyException::illegalCharacterFound(
                $engineConfiguration->getIllegalObjectNameCharacters(),
                $stringToCheck
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
            throw PlatformTopologyException::illegalRfcCharacterFound($stringToCheck);
        }
    }

    /**
     * Check consistency of each mandatory and optional parameters
     * @param PlatformInterface $platform
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     * @throws PlatformTopologyException
     */
    private function checkEntityConsistency(PlatformInterface $platform): void
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
                [PlatformPending::TYPE_CENTRAL, PlatformPending::TYPE_REMOTE],
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
            throw PlatformTopologyException::addressConflict(
                $platform->getName(),
                $platform->getAddress()
            );
        }
    }

    /**
     * Used when parent_address is null, to check if this type of platform is already registered
     *
     * @param string $type platform type to find
     * @throws PlatformTopologyException
     * @throws \Exception
     */
    private function searchAlreadyRegisteredTopLevelPlatformByType(string $type): void
    {
        $foundAlreadyRegisteredTopLevelPlatform = $this->platformTopologyRepository->findTopLevelPlatformByType($type);
        if (null !== $foundAlreadyRegisteredTopLevelPlatform) {
            throw PlatformTopologyException::platformAlreadySaved(
                $type,
                $foundAlreadyRegisteredTopLevelPlatform->getName(),
                $foundAlreadyRegisteredTopLevelPlatform->getAddress()
            );
        }
    }

    /**
     * Search for platforms monitoring ID and set it as serverId
     *
     * @param PlatformInterface $platform
     * @throws PlatformTopologyException
     * @throws \Exception
     */
    private function setMonitoringServerId(PlatformInterface $platform): void
    {
        $foundServerInNagiosTable = null;
        if (null !== $platform->getName()) {
            $foundServerInNagiosTable = $this->platformTopologyRepository->findLocalMonitoringIdFromName(
                $platform->getName()
            );
        }

        if (null === $foundServerInNagiosTable) {
            throw PlatformTopologyException::platformDoesNotMatchTheSavedOne(
                $platform->getType(),
                $platform->getName(),
                $platform->getAddress()
            );
        }
        $platform->setServerId($foundServerInNagiosTable->getId());
    }

    /**
     * Search for already registered platforms using same name or address
     *
     * @param PlatformInterface $platform
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    private function checkForAlreadyRegisteredSameNameOrAddress(PlatformInterface $platform): void
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

        $addressAlreadyRegistered = $this->platformTopologyRepository->findPlatformByAddress($platform->getAddress());
        $nameAlreadyRegistered = $this->platformTopologyRepository->findPlatformByName($platform->getName());
        if (null !== $nameAlreadyRegistered || null !== $addressAlreadyRegistered) {
            throw PlatformTopologyException::platformNameOrAddressAlreadyExist(
                $platform->getName(),
                $platform->getAddress()
            );
        }
    }

    /**
     * Search for platform's parent ID in topology
     *
     * @param PlatformInterface $platform
     * @return PlatformInterface|null
     * @throws EntityNotFoundException
     * @throws PlatformTopologyException
     * @throws \Exception
     */
    private function findParentPlatform(PlatformInterface $platform): ?PlatformInterface
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
            PlatformPending::TYPE_REMOTE === $platform->getType()
            && PlatformPending::TYPE_REMOTE === $registeredParentInTopology->getType()
        ) {
            throw PlatformTopologyException::unableToLinkARemoteToAnotherRemote(
                $registeredParentInTopology->getName(),
                $registeredParentInTopology->getAddress()
            );
        }

        // Check parent consistency, as the platform can only be linked to a remote or central type
        if (
            !in_array(
                $registeredParentInTopology->getType(),
                [PlatformPending::TYPE_REMOTE, PlatformPending::TYPE_CENTRAL],
                false
            )
        ) {
            throw PlatformTopologyException::inconsistentTypeToLinkThePlatformTo(
                $platform->getType(),
                $platform->getName(),
                $platform->getAddress(),
                $registeredParentInTopology->getType()
            );
        }

        $platform->setParentId($registeredParentInTopology->getId());

        // A platform behind a remote needs to send the data to the Central too
        if (
            null === $registeredParentInTopology->getParentId()
            && $registeredParentInTopology->getType() === PlatformPending::TYPE_REMOTE
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
        $platformTopology = $this->platformTopologyRepository->getPlatformTopology();
        if (empty($platformTopology)) {
            throw new EntityNotFoundException(_('No Platform Topology found.'));
        }

        foreach ($platformTopology as $platform) {
            //Set the parent address if the platform is not the top level
            if ($platform->getParentId() !== null) {
                $platformParent = $this->platformTopologyRepository->findPlatform(
                    $platform->getParentId()
                );
                if (null !== $platformParent) {
                    $platform->setParentAddress($platformParent->getAddress());
                }
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
            } else {
                $platform->setRelation(PlatformRelation::NORMAL_RELATION);
            }
        }

        return $platformTopology;
    }

    /**
     * @inheritDoc
     */
    public function deletePlatformAndReallocateChildren(int $serverId): void
    {
        try {
            if (($deletedPlatform = $this->platformTopologyRepository->findPlatform($serverId)) === null) {
                throw new EntityNotFoundException(_('Platform not found'));
            }
            $childPlatforms = $this->platformTopologyRepository->findChildrenPlatformsByParentId($serverId);

            if (!empty($childPlatforms)) {
                /**
                 * If at least one children platform was found,
                 * find the Top Parent platform and link children platform(s) to it.
                 */
                $topLevelPlatform = $this->findTopLevelPlatform();

                if ($topLevelPlatform === null) {
                    throw new EntityNotFoundException(_('No top level platform found to link the child platforms'));
                }

                /**
                 * Update children parent_id.
                 */
                foreach ($childPlatforms as $platform) {
                    $platform->setParentId($topLevelPlatform->getId());
                    $this->updatePlatformParameters($platform);
                }
            }

            /**
             * Delete the monitoring server and the topology.
             */
            if ($deletedPlatform->getServerId() !== null) {
                if ($deletedPlatform->getType() === PlatformPending::TYPE_REMOTE) {
                    $this->remoteServerRepository->deleteRemoteServerByAddress($deletedPlatform->getAddress());
                    $this->remoteServerRepository->deleteAdditionalRemoteServer($deletedPlatform->getServerId());
                }

                $this->monitoringServerService->deleteServer($deletedPlatform->getServerId());
            } else {
                $this->platformTopologyRepository->deletePlatform($deletedPlatform->getId());
            }
        } catch (EntityNotFoundException | PlatformTopologyException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(_('An error occurred while deleting the platform'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePlatformParameters(PlatformInterface $platform): void
    {
        try {
            $this->platformTopologyRepository->updatePlatformParameters($platform);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(_('An error occurred while updating the platform'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findTopLevelPlatform(): ?PlatformInterface
    {
        return $this->platformTopologyRepository->findTopLevelPlatform();
    }
}
