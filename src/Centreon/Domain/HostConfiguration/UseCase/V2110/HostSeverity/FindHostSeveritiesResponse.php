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

namespace Centreon\Domain\HostConfiguration\UseCase\V2110\HostSeverity;

use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is a DTO for the FindHostSeverities use case.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindHostSeveritiesResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $hostSeverities = [];

    /**
     * @param HostSeverity[] $hostSeverities
     */
    public function setHostSeverities(array $hostSeverities): void
    {
        foreach ($hostSeverities as $hostSeverity) {
            $this->hostSeverities[] = [
                'id' => $hostSeverity->getId(),
                'name' => $hostSeverity->getName(),
                'alias' => $hostSeverity->getAlias(),
                'comments' => $hostSeverity->getComments(),
                'level' => $hostSeverity->getLevel(),
                'icon' => $this->imageToArray($hostSeverity->getIcon()),
                'is_activated' => $hostSeverity->isActivated()
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHostSeverities(): array
    {
        return $this->hostSeverities;
    }

    /**
     * @param Image|null $image
     * @return array|null
     */
    private function imageToArray(?Image $image): ?array
    {
        if ($image !== null) {
            return [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'path' => $image->getPath(),
                'comment' => $image->getComment()
            ];
        }
        return null;
    }
}
