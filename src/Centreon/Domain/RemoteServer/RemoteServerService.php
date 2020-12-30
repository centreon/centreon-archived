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

namespace Centreon\Domain\RemoteServer;

use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\Topology\Interfaces\TopologyRepositoryInterface;
use Security\Encryption;

class RemoteServerService implements RemoteServerServiceInterface
{

    /**
     * @var TopologyRepositoryInterface
     */
    private $topologyRepository;

    public function __construct(TopologyRepositoryInterface $topologyRepository) {
        $this->topologyRepository = $topologyRepository;
    }

    /**
     * @inheritDoc
     */
    public function encryptCentralApiCredentials(string $password): string
    {
        $secondKey = base64_encode("api_remote_credentials");
        $centreonEncryption = new Encryption();
        $centreonEncryption->setFirstKey($_ENV['APP_SECRET'])->setSecondKey($secondKey);
        return $centreonEncryption->crypt($password);
    }

    /**
     * @inheritDoc
     */
    public function convertCentralToRemote(): void
    {
        $this->topologyRepository->disableMenus();
    }
}