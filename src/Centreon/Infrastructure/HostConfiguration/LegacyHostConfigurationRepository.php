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

namespace Centreon\Infrastructure\HostConfiguration;

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;

class LegacyHostConfigurationRepository implements HostConfigurationRepositoryInterface
{
    /**
     * @var \CentreonHost
     */
    private $centreonHostLegacy;

    /**
     * HostConfigurationRepositoryRDB constructor.
     * @param \CentreonHost $centreonHostLegacy
     */
    public function __construct (\CentreonHost $centreonHostLegacy)
    {
        $this->centreonHostLegacy = $centreonHostLegacy;
    }

    /**
     * @inheritDoc
     */
    public function addHost(Host $host): int
    {
        if ($host->getId() === null) {
            throw new RepositoryException('The host id can not be null');
        }
        // We convert the host object to an array to use the legacy repository method
        $convertedHost = $this->convertHostToArray($host);

        return (int) $this->centreonHostLegacy->insert($convertedHost);
    }

    private function convertHostToArray(Host $host): array
    {
        $extended = [
            'ehi_notes' => null,
            'ehi_notes_url' => null,
            'ehi_action_url' => null
        ];
        if ($host->getExtendedHost() !== null) {
            $extended = [
                'ehi_notes' => $host->getExtendedHost()->getNotes(),
                'ehi_notes_url' => $host->getExtendedHost()->getNotesUrl(),
                'ehi_action_url' => $host->getExtendedHost()->getActionsUrl()
            ];
        }
        return array_merge(
            [
                'host_name' => $host->getName(),
                'host_alias' => $host->getAlias(),
                'host_address' => $host->getIpAddress(),
                'host_comment' => $host->getComment(),
                'host_activate' => ($host->isActivate() ? 1 : 0)
            ], $extended);
    }
}
