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

namespace Core\Infrastructure\Common\Repository;

/**
 * This class is design to provide all common methods to handle db records.
 *
 * @package Core\Infrastructure\Common\Repository
 */
trait DbFactoryUtilitiesTrait
{
    /**
     * Creates DateTime from timestamp
     *
     * @param int|null $timestamp
     * @return \DateTime|null
     */
    private function createDateTimeFromTimestamp(?int $timestamp): ?\DateTime
    {
        return ($timestamp !== null
            ? (new \DateTime())->setTimestamp($timestamp)
            : null
        );
    }

    /**
     * @param int|string|null $property
     * @return int|null
     */
    private function getIntOrNull($property): ?int
    {
        return ($property !== null) ? (int) $property : null;
    }

    /**
     * @param float|string|null $property
     * @return float|null
     */
    private function getFloatOrNull($property): ?float
    {
        return ($property !== null) ? (float) $property : null;
    }
}
