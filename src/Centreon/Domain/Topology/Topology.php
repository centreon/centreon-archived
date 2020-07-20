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

namespace Centreon\Domain\Configuration\PendingServer;

use Centreon\Domain\Configuration\PendingServer\Interfaces\PendingServerInterface;

/**
 * Class designed to retrieve pending servers to be added using the wizard
 *
 */
class PendingServer implements PendingServerInterface
{
    /**
     * @var string Server name
     */
    private $serverName;

    /**
     * @var int Server type
     *      0 = central,
     *      1 = poller,
     *      2 = remote server,
     *      3 = map server,
     *      4 = mbi server
     */
    private $serverType;

    /**
     * @var string Server IP address
     */
    private $serverAddress;

    /**
     * @var int Id of the server
     */
    private $serverId;

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @return int
     */
    public function getServerType(): int
    {
        return $this->serverType;
    }

    /**
     * @return string
     */
    public function getServerAddress(): string
    {
        return $this->serverAddress;
    }

    /**
     * @param int $serverAddress
     * @return self
     */
    public function setServerAddress(int $serverAddress): self
    {
        $this->serverAddress = $serverAddress;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getServerId(): ?int
    {
        return $this->serverId;
    }
}

