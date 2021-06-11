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

namespace Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\Interfaces;

interface MonitoringResourceValidatorInterface
{
    /**
     * This function will validate that the Monitoring Resource sent
     * it correctly formatted as expected.
     *
     * @param array<string, mixed> $monitoringResource
     * @return void
     */
    public function validateOrFail(array $monitoringResource): void;

    /**
     * @param string $type
     * @return boolean
     */
    public function isValidFor(string $type): bool;
}
