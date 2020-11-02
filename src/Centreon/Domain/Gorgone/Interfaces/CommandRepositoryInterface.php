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

use Centreon\Infrastructure\Gorgone\CommandRepositoryException;

/**
 * Interface CommandRepositoryInterface
 * Describes management of external commands sent to gorgone
 *
 * @package Centreon\Domain\Gorgone\Interfaces
 */
interface CommandRepositoryInterface
{
    /**
     * Send a command to the Gorgone server.
     *
     * @param CommandInterface $command Command to send
     * @return string Returns a token that will be used to retrieve the response
     * @throws CommandRepositoryException
     * @throws \Exception
     */
    public function send(CommandInterface $command): string;
}
