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

use Centreon\Domain\Gorgone\Interfaces\CommandInterface;

class CommandFactory
{
    /**
     * @var array<CommandInterface> GorgoneCommandInterface[]
     */
    private static $commands = [];

    /**
     * @param CommandInterface $command Command to add
     */
    public static function addCommand(CommandInterface $command): void
    {
        static::$commands[$command->getName()] = $command;
    }

    /***
     * @param string $commandName Gorgone command name
     * @return CommandInterface
     */
    public static function create(string $commandName): CommandInterface
    {
        if (array_key_exists($commandName, static::$commands)) {
            return static::$commands[$commandName];
        } else {
            throw new \LogicException('Command not found');
        }
    }
}
