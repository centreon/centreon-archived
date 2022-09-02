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

namespace Core\Domain\Engine\Model;

use Core\Domain\Common\HandlerInterface;

class EngineCommandGenerator
{
    /**
     * @var HandlerInterface[]
     */
    private array $handlers = [];

    /**
     * @param \Traversable<HandlerInterface> $handlers
     */
    public function __construct(\Traversable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * Add a new command handler.
     *
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler): void
    {
        $lastHandler = (! empty($this->handlers)) ? $this->handlers[count($this->handlers) - 1] : null;
        $this->handlers[] = isset($lastHandler) ? $lastHandler->setNext($handler) : $handler;
    }

    /**
     * Gets the Engine command according to the different handlers who might want to modify it.
     *
     * @param string $command
     * @return string
     */
    public function getEngineCommand(string $command): string
    {
        return ! empty($this->handlers) ? (string) $this->handlers[0]->handle($command) : $command;
    }
}
