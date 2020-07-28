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

/**
 * Class intended to register a new server to the platform topology
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
        $foundPlatformTopology = $this->platformTopologyRepository->findPlatformInTopology(
            $platformTopology->getServerAddress(),
            $platformTopology->getServerName()
        );
        if (!empty($foundPlatformTopology)) {
            throw new PlatformTopologyException(
                sprintf(
                    _("The platform '%s@%s' already exists"),
                    $platformTopology->getServerName(),
                    $platformTopology->getServerAddress()
                )
            );
        }

        try {
            $this->platformTopologyRepository->addPlatformToTopology($platformTopology);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(
                sprintf(
                    _('Error when adding in topology the platform : %s @ %s'),
                    $platformTopology->getServerName(),
                    $platformTopology->getServerAddress()
                ),
                0,
                $ex
            );
        }
    }
}
