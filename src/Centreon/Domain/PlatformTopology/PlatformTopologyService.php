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
         * search for already registered central type
         */
        if (PlatformTopology::TYPE_CENTRAL === $platformTopology->getType()) {
            $foundCentralPlatformType = $this->platformTopologyRepository->findPlatformTopologyByType(
                PlatformTopology::TYPE_CENTRAL
            );
            // search for its nagios_server ID
            if (null !== $foundCentralPlatformType) {
                throw new PlatformTopologyConflictException(
                    sprintf(
                        _("A Central : '%s'@'%s' is already registered"),
                        $foundCentralPlatformType->getName(),
                        $foundCentralPlatformType->getAddress()
                    )
                );
            }
            $isCentralExistsInNagiosTable = $this->platformTopologyRepository->findPlatformTopologyNagiosId(
                $platformTopology->getName()
            );

            if (null === $isCentralExistsInNagiosTable) {
                throw new PlatformTopologyConflictException(
                    sprintf(
                        _("The Central type server : '%s'@'%s' does not match the one configured in Centreon"),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            $platformTopology->setServerId($isCentralExistsInNagiosTable->getId());
        }

        /**
         * search for already registered platforms using same name of address
         */
        $isAlreadyRegistered = $this->platformTopologyRepository->isPlatformAlreadyRegisteredInTopology(
            $platformTopology->getAddress(),
            $platformTopology->getName()
        );

        if ($isAlreadyRegistered === true) {
            throw new PlatformTopologyConflictException(
                sprintf(
                    _("A platform using the name : '%s' or address : '%s' already exists"),
                    $platformTopology->getName(),
                    $platformTopology->getAddress()
                )
            );
        }

        /**
         * search for parent platform ID in topology
         */
        if ($platformTopology->getParentAddress() !== null) {
            $foundPlatformTopology = $this->platformTopologyRepository->findPlatformTopologyByAddress(
                $platformTopology->getParentAddress()
            );
            if (null === $foundPlatformTopology) {
                throw new EntityNotFoundException(
                    sprintf(
                        _("No parent platform was found for : '%s'@'%s'"),
                        $platformTopology->getName(),
                        $platformTopology->getAddress()
                    )
                );
            }
            $platformTopology->setParentId($foundPlatformTopology->getId());
        }

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
}
