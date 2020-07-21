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

namespace Centreon\Domain\PlatformTopology;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyInterface;

/**
 * Class designed to retrieve pending servers to be added using the wizard
 *
 */
class PlatformTopology implements PlatformTopologyInterface
{
    /**
     * @var string Server name
     */
    private $serverName;

    /**
     * @var int Server type
     *      0 = central
     *      1 = poller
     *      2 = remote server
     *      3 = map server
     *      4 = mbi server
     */
    private $serverType;

    /**
     * @var string Server IP address
     */
    private $serverAddress;

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     * @return $this
     */
    public function setServerName(string $serverName): self
    {
        $this->serverName = $serverName;
        return $this;
    }

    /**
     * @return int
     */
    public function getServerType(): int
    {
        return $this->serverType;
    }

    /**
     * @param int $serverType server type
     *      0 = central
     *      1 = poller
     *      2 = remote server
     *      3 = map server
     *      4 = mbi server
     *
     * @return self
     */
    public function setserverType(int $serverType): self
    {
        $this->serverType = $serverType;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerAddress(): string
    {
        return $this->serverAddress;
    }

    /**
     * @param string $serverAddress
     * @return self
     */
    public function setServerAddress(string $serverAddress): self
    {
        $this->serverAddress = $serverAddress;
        return $this;
    }
}

