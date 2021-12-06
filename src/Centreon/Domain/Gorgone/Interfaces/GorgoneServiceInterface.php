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

use Centreon\Domain\Gorgone\GorgoneException;

/**
 * Interface GorgoneServiceInterface
 *
 * @package Centreon\Domain\Gorgone\Interfaces
 */
interface GorgoneServiceInterface
{
    /**
     * Send a command to the Gorgone server and retrieve an instance of the
     * response which allow to get all action logs.
     *
     * @param CommandInterface $command Command to send
     * @return ResponseInterface Returns a response containing the command sent.
     * @throws GorgoneException
     */
    public function send(CommandInterface $command): ResponseInterface;

    /**
     * Retrieve the response according to the command token and the poller id.
     *
     * @param int $monitoringInstanceId Id of the poller for which the command was intended
     * @param string $token Token of the command returned by the Gorgone server
     * @return ResponseInterface
     */
    public function getResponseFromToken(int $monitoringInstanceId, string $token): ResponseInterface;
}
