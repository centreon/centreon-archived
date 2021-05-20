<?php

namespace Centreon\Domain\Log;

use Psr\Log\LoggerInterface;

/**
 * @package Centreon\Domain\Log
 */
trait LoggerTrait
{
    /**
     * @var LoggerInterface
     */
    private $centreonLogger;

    /**
     * @param LoggerInterface $centreonLogger
     */
    public function setLogger(LoggerInterface $centreonLogger)
    {
        $this->centreonLogger = $centreonLogger;
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::emergency()
     */
    private function emergency(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->emergency($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::alert()
     */
    private function alert(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->alert($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::critical()
     */
    private function critical(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->critical($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::error()
     */
    private function error(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->error($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::warning()
     */
    private function warning(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->warning($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::notice()
     */
    private function notice(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->notice($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::info()
     */
    private function info(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->info($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @see \Psr\Log\LoggerInterface::debug()
     */
    private function debug(string $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->debug($message, $context);
        }
    }

    /**
     * @param mixed $level
     * @param $message
     * @param array $context
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @see \Psr\Log\LoggerInterface::log()
     */
    private function log($level, $message, array $context = []): void
    {
        if ($this->centreonLogger !== null) {
            $this->centreonLogger->log($level, $message, $context);
        }
    }
}