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

use Psr\Log\LoggerInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;

/**
 * This class is design to provide all the methods for recording events.
 *
 * @package Centreon\Domain\Log
 */
trait LoggerTrait
{
    /**
     * @var ContactInterface
     */
    private $loggerContact;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param ContactInterface $loggerContact
     * @required
     */
    public function setLoggerContact(ContactInterface $loggerContact): void
    {
        $this->loggerContact = $loggerContact;
    }

    /**
     * @param LoggerInterface $centreonLogger
     * @required
     */
    public function setLogger(LoggerInterface $centreonLogger)
    {
        $this->logger = $centreonLogger;
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::emergency()
     */
    private function emergency(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::alert()
     */
    private function alert(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->alert($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::critical()
     */
    private function critical(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->critical($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::error()
     */
    private function error(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->error($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::warning()
     */
    private function warning(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->warning($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::notice()
     */
    private function notice(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->notice($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::info()
     */
    private function info(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->info($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $callable
     * @see \Psr\Log\LoggerInterface::debug()
     */
    private function debug(string $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->debug($message, $context);
        }
    }

    /**
     * @param mixed $level
     * @param $message
     * @param array $context
     * @param callable|null $callable
     * @throws \Psr\Log\InvalidArgumentException
     * @see \Psr\Log\LoggerInterface::log()
     */
    private function log($level, $message, array $context = [], callable $callable = null): void
    {
        if ($this->logger !== null) {
            if ($callable !== null) {
                $context = array_merge($context, $callable());
            }
            $this->logger->log($level, $message, $context);
        }
    }
}
