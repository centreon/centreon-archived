<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Severity\RealTime\Application\UseCase\FindSeverity;

use Core\Application\RealTime\Common\RealTimeResponseTrait;
use Core\Severity\RealTime\Domain\Model\Severity;

class FindSeverityResponse
{
    use RealTimeResponseTrait;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $severities;

    /**
     * @param Severity[] $severities
     */
    public function __construct(array $severities)
    {
        $this->severities = $this->severitiesToArray($severities);
    }

    /**
     * @param Severity[] $severities
     * @return array<int, array<string, mixed>>
     */
    private function severitiesToArray(array $severities): array
    {
        return array_map(
            fn (Severity $severity) => [
                'id' => $severity->getId(),
                'name' => $severity->getName(),
                'level' => $severity->getLevel(),
                'type' => $severity->getTypeAsString(),
                'icon' => $this->iconToArray($severity->getIcon()),
            ],
            $severities
        );
    }
}
