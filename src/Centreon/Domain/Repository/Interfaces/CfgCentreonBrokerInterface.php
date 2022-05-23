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

namespace Centreon\Domain\Repository\Interfaces;

interface CfgCentreonBrokerInterface
{
    /**
     * Get config id of central broker
     * It can be useful to add bam broker configuration
     * Or add an input to manage one peer retention
     *
     * @return int the config id of central broker
     */
    public function findCentralBrokerConfigId(): int;

    /**
     * Get config id of poller broker
     *
     * @param int $pollerId pollerId the poller id
     * @return int the config id of poller broker
     */
    public function findBrokerConfigIdByPollerId(int $pollerId): int;
}
