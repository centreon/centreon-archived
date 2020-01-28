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

namespace Centreon\Domain\Gorgone\Interfaces;

use Centreon\Domain\Gorgone\Command\EmptyCommand;
use Centreon\Domain\Gorgone\GorgoneResponse;

interface GorgoneServiceInterface
{
    /**
     * Send a command to the Gorgone server and retrieve an instance of the
     * response which allowed to get all action logs.
     *
     * @param GorgoneCommandInterface $command Command to send
     * @return GorgoneResponseInterface Returns a response containing the command sent.
     * @throws \Exception
     * @see GorgoneResponseInterface
     */
    public function send(GorgoneCommandInterface $command): GorgoneResponseInterface;

    public function getResponseFromToken (int $pollerId, string $token): GorgoneResponseInterface;
}
