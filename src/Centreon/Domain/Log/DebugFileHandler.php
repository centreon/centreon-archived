<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Specific monolog handler used to take into account an activation status to log or not the messages.
 *
 * @package Centreon\Domain\Log
 */
class DebugFileHandler extends StreamHandler
{
    /**
     * @var bool Whether the messages can be processed
     */
    private $isActivate;

    /**
     * @param FormatterInterface $formatter Monolog formatter
     * @param string|resource $stream Resource or Log filename
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param false $useLocking Try to lock log file before doing any writes
     * @param bool $isActivate Whether the messages can be processed
     * @throws \InvalidArgumentException
     */
    public function __construct(
        FormatterInterface $formatter,
        $stream,
        bool $isActivate = false,
        int $filePermission = null,
        int $level = Logger::DEBUG,
        bool $useLocking = false,
        bool $bubble = true
    ) {
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
        $this->setFormatter($formatter);
        $this->isActivate = $isActivate;
    }

    /**
     * @param array<string, mixed> $record
     * @throws \LogicException
     */
    protected function write(array $record)
    {
        if ($this->isActivate) {
            parent::write($record);
        }
    }
}
