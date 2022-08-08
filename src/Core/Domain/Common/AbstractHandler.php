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

namespace Core\Domain\Common;

/**
 * Implements the chain of responsibility pattern
 */
class AbstractHandler implements HandlerInterface
{
    private ?HandlerInterface $nextHandle = null;

    /**
     * @param string|object $message
     * @return string|object|null
     */
    public function handle(mixed $message): mixed
    {
        return $this->nextHandle ? $this->nextHandle->handle($message) : $message;
    }

    /**
     * @param HandlerInterface $nextHandler
     * @return HandlerInterface
     */
    public function setNext(HandlerInterface $nextHandler): HandlerInterface
    {
        return $this->nextHandle = $nextHandler;
    }
}
