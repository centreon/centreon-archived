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

namespace Core\Application\Platform\UseCase\AddPlatformsToTopology;

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\Configuration\Platform\PlatformFactory;
use Core\Application\Platform\Repository\ReadPlatformTopologyRepositoryInterface;
use Core\Domain\Configuration\Platform\NewPlatform;

class AddPlatformsToTopology
{
    use LoggerTrait;

    /**
     * @param ReadPlatformTopologyRepositoryInterface $readRepository
     */
    public function __construct(private ReadPlatformTopologyRepositoryInterface $readRepository)
    {
    }

    /**
     * @param AddPlatformsToTopologyPresenterInterface $presenter
     * @param AddPlatformsToTopologyRequest $request
     */
    public function invoke(
        AddPlatformsToTopologyPresenterInterface $presenter,
        AddPlatformsToTopologyRequest $request
    ): void {
        $platforms = [];
        foreach ($request->nodes as $node) {
            $platform = PlatformFactory::createNewPlatform($node);
            $this->setParentPlatform($request->nodes, $node['parent'], $platform);
            $platforms[] = $platform;
        }
    }

    /**
     * Retrieve the parent information into the request nodes
     *
     * @param string $address
     * @param array $nodes
     * @return array
     */
    private function findParentInNodes(string $address, array $nodes): array {
        foreach ($nodes as $key => $val) {
            if ($val['address'] === $address) {
                return $nodes[$key];
            }
        }

        return [];
    }

    /**
     * set Parent to the current platform
     *
     * @param array $nodes
     * @param string $parentAddress
     * @param NewPlatform $platform
     */
    private function setParentPlatform(array $nodes, string $parentAddress, NewPlatform &$platform): void
    {
        if ($parentAddress !== null) {
            $parent = $this->readRepository->findPlatformByAddress($parentAddress);
            if ($parent === null) {
                $parentNode = $this->findParentInNodes($parentAddress, $nodes);
                if (empty($parentNode)) {
                    $this->error('Parent not found, linking platform to Central');
                } else {
                    $platformParent = PlatformFactory::createNewPlatform($parentNode);
                    $platform->setParent($platformParent);
                }
            } else {
                $platform->setParent($parent);
            }
        }
    }
}