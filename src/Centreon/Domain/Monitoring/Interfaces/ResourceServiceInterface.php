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

namespace Centreon\Domain\Monitoring\Interfaces;

interface ResourceServiceInterface
{
    /**
     * Non-ok status in hard state , not acknowledged & not in downtime
     */
    public const STATE_UNHANDLED_PROBLEMS = 'unhandled_problems';

    /**
     * Non-ok status in hard state
     */
    public const STATE_RESOURCES_PROBLEMS = 'resources_problems';

    /**
     * All status & resources
     */
    public const STATE_ALL = 'all';

    /**
     * List of all states
     */
    public const STATES = [
        self::STATE_UNHANDLED_PROBLEMS,
        self::STATE_RESOURCES_PROBLEMS,
        self::STATE_ALL,
    ];

    /**
     * Find all resources.
     *
     * @param array $filterState
     * @return \Centreon\Domain\Monitoring\Resource[]
     * @throws \Exception
     */
    public function findResources(?array $filterState): array;
}
