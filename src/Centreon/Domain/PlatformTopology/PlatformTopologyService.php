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

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
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
     * PlatformTopologyService constructor.
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     */
    public function __construct(PlatformTopologyRepositoryInterface $platformTopologyRepository)
    {
        $this->platformTopologyRepository = $platformTopologyRepository;
    }

    /**
     * @inheritDoc
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        /**
         * Search for already registered central or remote top level server on this platform
         * As only top level platform do not need parent_address and only one should be registered
         */
        if (PlatformTopology::TYPE_CENTRAL === $platformTopology->getType()) {
            // New unique Central top level platform case
            $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_CENTRAL);
            $this->setServerNagiosId($platformTopology);
        } elseif (PlatformTopology::TYPE_REMOTE === $platformTopology->getType()) {
            // Cannot add a Remote behind another Remote
            $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_REMOTE);

            if (null === $platformTopology->getParentAddress()) {
                // New unique Remote top level platform case
                $this->checkForAlreadyRegisteredPlatformType(PlatformTopology::TYPE_CENTRAL);
                $this->setServerNagiosId($platformTopology);
            }
        }

        $this->checkForAlreadyRegisteredSameNameOrAddress($platformTopology);
        $registeredParentInTopology = $this->searchForParentPlatformAndSetId($platformTopology);





        if ($registeredParentInTopology && true === $platformTopology->isLinkedToAnotherServer()) {
            // WIP
            // call the API on the n-1 server to register it too

            // DEBUG

            // Find the Central's data
            // $dataOfTheCentral = $this->platformTopologyRepository->

            $payload = json_encode([
                "name" => $registeredParentInTopology->getName(),
                "type" => $registeredParentInTopology->getType(),
                "address" => $registeredParentInTopology->getAddress(),
                "parent_address" => $registeredParentInTopology->getParentAddress()
            ]);
            throw new PlatformTopologyException(
                "payload : " . $payload
            );

        }



        // TODO
        // need to check the previous status code to be sure that the n-1 registered properly the platform
        // then continue the current process




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
     * @inheritDoc
     */
    public function checkForAlreadyRegisteredPlatformType(string $type): void
    {
        $foundAlreadyRegisteredPlatformByType = $this->platformTopologyRepository->findPlatformTopologyByType($type);
        if (null !== $foundAlreadyRegisteredPlatformByType) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A '%s' : '%s'@'%s' is already registered"),
                    $type,
                    $foundAlreadyRegisteredPlatformByType->getName(),
                    $foundAlreadyRegisteredPlatformByType->getAddress()
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function setServerNagiosId(PlatformTopology $platformTopology): void
    {
        $foundServerInNagiosTable = $this->platformTopologyRepository->findPlatformTopologyNagiosId(
            $platformTopology->getName()
        );

        if (null === $foundServerInNagiosTable) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("The %s type server : '%s'@'%s' does not match the one configured in Centreon or is disabled"),
                    $platformTopology->getType(),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }
        $platformTopology->setServerId($foundServerInNagiosTable->getId());
    }

    /**
     * @inheritDoc
     */
    public function checkForAlreadyRegisteredSameNameOrAddress(PlatformTopology $platformTopology): void
    {
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
     * @inheritDoc
     */
    public function searchForParentPlatformAndSetId(PlatformTopology $platformTopology): ?PlatformTopology
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

        // Check parent consistency
        if (
            PlatformTopology::TYPE_REMOTE !== $registeredParentInTopology->getType()
            && PlatformTopology::TYPE_CENTRAL !== $registeredParentInTopology->getType()
        ) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("Cannot register a '%s' platform behind a '%s' platform"),
                    $platformTopology->getType(),
                    $registeredParentInTopology->getType()
                )
            );
        }

        $platformTopology->setParentId($registeredParentInTopology->getId());

        // A remote needs to send the data to the Central too
        if (
            null !== $registeredParentInTopology->getServerId()
            && $registeredParentInTopology->getType() === PlatformTopology::TYPE_REMOTE
        ) {
            $platformTopology->setLinkedToAnotherServer(true);
            return $registeredParentInTopology;
        }
        return null;
    }
}
