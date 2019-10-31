<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Downtime\Interfaces;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\DowntimeService;
use Centreon\Domain\Service\AbstractCentreonService;

interface DowntimeServiceInterface extends ContactFilterInterface
{

    /**
     * Find downtime of all hosts.
     *
     * @return Downtime[]
     */
    public function findHostDowntime(): array;

    /**
     * Find one downtime linked to a host.
     *
     * @param int $downtimeId Downtime id
     * @return Downtime|null Return NULL if the downtime has not been found
     * @throws \Exception
     */
    public function findOneDowntime(int $downtimeId): ?Downtime;

    /**
     * Find all downtimes.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntime(): array;

    /**
     * Find all downtimes linked to a host.
     *
     * @param int $hostId Host id for which we want to find host
     * @return Downtime[]
     */
    public function findDowntimesByHost(int $hostId): array;
}
