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

namespace Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity;

use Centreon\Domain\HostConfiguration\Exception\HostSeverityException;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverityReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;

/**
 * This class is designed to represent a use case to find all host severities
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity
 */
class FindHostSeverities
{
    /**
     * @var HostSeverityReadRepositoryInterface
     */
    private $hostSeverityReadRepository;

    /**
     * @var string|null
     */
    private $mediaPath;

    /**
     * @param HostSeverityReadRepositoryInterface $configurationReadRepository
     */
    public function __construct(HostSeverityReadRepositoryInterface $configurationReadRepository)
    {
        $this->hostSeverityReadRepository = $configurationReadRepository;
    }

    /**
     * @param string|null $mediaPath
     * @return FindHostSeverities
     */
    public function setMediaPath(?string $mediaPath): FindHostSeverities
    {
        $this->mediaPath = $mediaPath;
        return $this;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostSeveritiesResponse
     * @throws \Assert\AssertionFailedException
     * @throws HostSeverityException
     */
    public function execute(): FindHostSeveritiesResponse
    {
        try {
            $hostSeverities = $this->hostSeverityReadRepository->findHostSeverities();
        } catch (\Exception $ex) {
            throw HostSeverityException::searchHostSeveritiesException($ex);
        }
        $this->updateMediaPaths($hostSeverities);
        $response = new FindHostSeveritiesResponse();
        $response->setHostSeverities($hostSeverities);
        return $response;
    }

    /**
     * Updated all media paths for all host severities.
     *
     * @param HostSeverity[] $hostSeverities
     * @throws \Assert\AssertionFailedException
     */
    private function updateMediaPaths(array $hostSeverities): void
    {
        if ($this->mediaPath !== null) {
            foreach ($hostSeverities as $hostSeverity) {
                $icon = $hostSeverity->getIcon();
                if (
                    $icon !== null
                    && $icon->getPath() !== null
                    && substr($icon->getPath(), 0, strlen($this->mediaPath)) !== $this->mediaPath
                ) {
                    $icon->setPath(
                        $this->mediaPath . DIRECTORY_SEPARATOR . $icon->getPath()
                    );
                }
            }
        }
    }
}
