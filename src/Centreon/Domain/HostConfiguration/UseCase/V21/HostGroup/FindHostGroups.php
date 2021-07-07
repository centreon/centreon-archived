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

namespace Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;

/**
 * This class is designed to represent a use case to find all host groups
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup
 */
class FindHostGroups
{
    /**
     * @var string|null
     */
    private $mediaPath;
    /**
     * @var ContactInterface
     */
    private $contact;
    /**
     * @var HostGroupServiceInterface
     */
    private $hostGroupService;

    /**
     * @param HostGroupServiceInterface $hostGroupService
     * @param ContactInterface $contact
     */
    public function __construct(
        HostGroupServiceInterface $hostGroupService,
        ContactInterface $contact
    ) {
        $this->contact = $contact;
        $this->hostGroupService = $hostGroupService;
    }

    /**
     * @param string|null $mediaPath
     * @return FindHostGroups
     */
    public function setMediaPath(?string $mediaPath): FindHostGroups
    {
        $this->mediaPath = $mediaPath;
        return $this;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostGroupsResponse
     * @throws HostGroupException
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function execute(): FindHostGroupsResponse
    {
        $hostGroups = ($this->contact->isAdmin())
            ? $this->hostGroupService->findAllWithoutAcl()
            : $this->hostGroupService->findAllWithAcl();

        $this->updateMediaPaths($hostGroups);
        $response = new FindHostGroupsResponse();
        $response->setHostGroups($hostGroups);
        return $response;
    }

    /**
     * Updated all media paths for all host groups.
     *
     * @param HostGroup[] $hostGroups
     * @throws \Assert\AssertionFailedException
     */
    private function updateMediaPaths(array $hostGroups): void
    {
        if ($this->mediaPath !== null) {
            foreach ($hostGroups as $hostGroup) {
                $icon = $hostGroup->getIcon();
                if (
                    $icon !== null
                    && $icon->getPath() !== null
                    && substr($icon->getPath(), 0, strlen($this->mediaPath)) !== $this->mediaPath
                ) {
                    $icon->setPath(
                        $this->mediaPath . DIRECTORY_SEPARATOR . $icon->getPath()
                    );
                }
                $iconMap = $hostGroup->getIconMap();
                if (
                    $iconMap !== null
                    && $iconMap->getPath() !== null
                    && substr($iconMap->getPath(), 0, strlen($this->mediaPath)) !== $this->mediaPath
                ) {
                    $iconMap->setPath(
                        $this->mediaPath . DIRECTORY_SEPARATOR . $iconMap->getPath()
                    );
                }
            }
        }
    }
}
