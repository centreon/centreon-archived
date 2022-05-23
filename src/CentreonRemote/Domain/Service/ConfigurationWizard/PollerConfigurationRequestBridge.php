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

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use CentreonRemote\Domain\Value\PollerServer;
use CentreonRemote\Domain\Value\ServerWizardIdentity;
use Pimple\Container;

class PollerConfigurationRequestBridge
{

    /** @var CentreonDBAdapter */
    private $dbAdapter;

    /** @var PollerServer[] */
    private $pollers = [];

    /** @var PollerServer[] */
    private $additionalRemotes = [];


    public function __construct(Container $di)
    {
        $this->dbAdapter = $di[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db');
    }

    public function hasPollersForUpdating(): bool
    {
        return !empty($this->pollers);
    }

    /**
     * @return PollerServer[]
     */
    public function getLinkedPollersSelectedForUpdate(): array
    {
        return $this->pollers;
    }

    /**
     * @return PollerServer[]
     */
    public function getAdditionalRemoteServers(): array
    {
        return $this->additionalRemotes;
    }

    /**
     * Set linked pollers regarding wizard type (poller/remote server)
     */
    public function collectDataFromRequest(): void
    {
        $isRemoteServerWizard = (new ServerWizardIdentity)->requestConfigurationIsRemote();

        if ($isRemoteServerWizard) { // configure remote server
            // pollers linked to the remote server
            $linkedPollers = isset($_POST['linked_pollers']) ? (array) $_POST['linked_pollers'] : [];
        } else { // configure poller
            // if the poller is linked to a remote server
            $linkedPollers = isset($_POST['linked_remote_master']) ? [$_POST['linked_remote_master']] : [];
        }

        $this->pollers = $this->getPollersToLink($linkedPollers); // set and instantiate linked pollers
    }

    /**
     * Set linked Additonal Remote Servers regarding wizard type poller (poller/remote server)
     */
    public function collectDataFromAdditionalRemoteServers(): void
    {
        $isRemoteServerWizard = (new ServerWizardIdentity)->requestConfigurationIsRemote();

        $linkedRemotes = [];
        if (!$isRemoteServerWizard && isset($_POST['linked_remote_slaves'])) {
            $linkedRemotes = $_POST['linked_remote_slaves'];
        }

        $this->additionalRemotes = $this->getPollersToLink($linkedRemotes); // set and instantiate linked pollers
    }

    /**
     * Get pollers to link a set of poller information
     *
     * @param array<mixed> $pollers the pollers to get list of poller objects
     * @return PollerServer[] the pollers to link
     */
    private function getPollersToLink(array $pollers)
    {
        if (empty($pollers)) {
            return [];
        }

        $pollerIDs = [];

        if (is_array(reset($pollers))) {
            foreach ($pollers as $poller) {
                if (isset($poller['value'])) {
                    $pollerIDs[] = $poller['value'];
                }
            }
        } else {
            $pollerIDs = $pollers;
        }

        $idBindString = str_repeat('?,', count($pollerIDs));
        $idBindString = rtrim($idBindString, ',');
        $queryPollers = "SELECT id, name, ns_ip_address as ip FROM nagios_server WHERE id IN ({$idBindString})";

        $this->dbAdapter->query($queryPollers, $pollerIDs);
        $results = $this->dbAdapter->results();
        $data = [];

        foreach ($results as $result) {
            $poller = new PollerServer();
            $poller->setId($result->id);
            $poller->setName($result->name);
            $poller->setIp($result->ip);

            $data[] = $poller;
        }

        return $data;
    }

    /**
     * Get poller information from poller id
     *
     * @param int $pollerId the poller id to get
     * @return null|PollerServer
     */
    public function getPollerFromId(int $pollerId): ?PollerServer
    {
        $query = 'SELECT id, name, ns_ip_address as ip FROM nagios_server WHERE id = ?';

        $this->dbAdapter->query($query, [$pollerId]);
        $results = $this->dbAdapter->results();

        if (count($results)) {
            $remoteData = reset($results);
            $remoteServer = new PollerServer;
            $remoteServer->setId($remoteData->id);
            $remoteServer->setName($remoteData->name);
            $remoteServer->setIp($remoteData->ip);

            return $remoteServer;
        }

        return null;
    }
}
