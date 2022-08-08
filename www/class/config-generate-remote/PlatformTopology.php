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

namespace ConfigGenerateRemote;

use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;
use Exception;
use PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class PlatformTopology extends AbstractObject
{
    protected $table = 'platform_topology';
    protected $generateFilename = 'platform_topology.infile';

    protected $attributesSelect = '
        id,
        address,
        hostname,
        name,
        type,
        parent_id,
        server_id
    ';
    protected $attributesWrite = [
        'id',
        'address',
        'hostname',
        'name',
        'type',
        'parent_id',
        'server_id'
    ];
    protected $stmtPlatformTopology = null;

    /**
     * Generate topology configuration from remote server id
     *
     * @param int $remoteServerId
     * @return void
     */
    private function generate(int $remoteServerId)
    {
        if (is_null($this->stmtPlatformTopology)) {
            $this->stmtPlatformTopology = $this->backendInstance->db->prepare(
                "SELECT $this->attributesSelect FROM platform_topology 
                WHERE server_id = :poller_id 
                OR parent_id = (SELECT id FROM platform_topology WHERE server_id = :poller_id )"
            );
        }
        $this->stmtPlatformTopology->bindParam(':poller_id', $remoteServerId, PDO::PARAM_INT);
        $this->stmtPlatformTopology->execute();

        $result = $this->stmtPlatformTopology->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $entry) {
            if ($entry['type'] === PlatformRegistered::TYPE_REMOTE) {
                $entry['parent_id'] = null;
            }
            $this->generateObjectInFile($entry);
        }
    }

    /**
     * Generate topology configuration from remote server id
     *
     * @param int $remoteServerId
     * @return void
     */
    public function generateFromRemoteServerId(int $remoteServerId)
    {
        $this->generate($remoteServerId);
    }
}
