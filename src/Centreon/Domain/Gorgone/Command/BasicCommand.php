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

namespace Centreon\Domain\Gorgone\Command;

use Centreon\Domain\Gorgone\Interfaces\GorgoneCommandInterface;

Trait BasicCommand
{
    /**
     * @var string Token of the command assigned by the Gorgone server.
     */
    private $token;

    /**
     * @var int Poller id
     */
    private $pollerId;

    /**
     * We create a command for a specific poller.
     *
     * @param int $pollerId Poller id for which this command is intended
     */
    public function __construct(int $pollerId)
    {
        $this->pollerId = $pollerId;
    }

    /**
     * @return int
     * @see GorgoneCommandInterface::getPollerId()
     */
    public function getPollerId(): int
    {
        return $this->pollerId;
    }

    /**
     * @return string
     * @see GorgoneCommandInterface::getToken()
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @see GorgoneCommandInterface::setToken()
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Get the name of the command.
     *
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
