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

namespace Centreon\Domain\Gorgone;

use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Infrastructure\Gorgone\Interfaces\ConfigurationLoaderApiInterface;

/**
 * This class is designed to allow repositories to retrieve their configuration parameters.
 *
 * @package Centreon\Domain\Gorgone
 */
class ConfigurationLoader implements ConfigurationLoaderApiInterface
{
    private const DEFAULT_TIMEOUT = 2;
    private const GORGONE_API_ADDRESS = 'gorgone_api_address';
    private const GORGONE_API_PORT = 'gorgone_api_port';
    private const GORGONE_API_USERNAME = 'gorgone_api_username';
    private const GORGONE_API_PASSWORD = 'gorgone_api_password';
    private const GORGONE_API_SSL = 'gorgone_api_ssl';
    private const GORGONE_API_CERTIFICATE_SELF_SIGNED = 'gorgone_api_allow_self_signed';
    private const GORGONE_COMMAND_TIMEOUT = 'gorgone_cmd_timeout';

    /**
     * @var OptionServiceInterface
     */
    private $optionService;

    /**
     * @var array<string, string|null> Parameters of the Gorgone server
     */
    private $gorgoneParameters;

    /**
     * @var bool Indicates whether options are already loaded or not
     */
    private $isOptionsLoaded = false;

    /**
     * @param OptionServiceInterface $optionService
     */
    public function __construct(OptionServiceInterface $optionService)
    {
        $this->optionService = $optionService;
    }

    /**
     * @inheritDoc
     */
    public function getApiIpAddress(): ?string
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return $this->gorgoneParameters[self::GORGONE_API_ADDRESS] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getApiPort(): ?int
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return isset($this->gorgoneParameters[self::GORGONE_API_PORT])
            ? (int) $this->gorgoneParameters[self::GORGONE_API_PORT]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getApiUsername(): ?string
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return $this->gorgoneParameters[self::GORGONE_API_USERNAME] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getApiPassword(): ?string
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return $this->gorgoneParameters[self::GORGONE_API_PASSWORD] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function isApiConnectionSecure(): bool
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return (bool) ($this->gorgoneParameters[self::GORGONE_API_SSL] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function isSecureConnectionSelfSigned(): bool
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        return (bool) ($this->gorgoneParameters[self::GORGONE_API_CERTIFICATE_SELF_SIGNED] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function getCommandTimeout(): int
    {
        if (!$this->isOptionsLoaded) {
            $this->loadConfiguration();
        }
        $timeout = (int) ($this->gorgoneParameters[self::GORGONE_COMMAND_TIMEOUT] ?? self::DEFAULT_TIMEOUT);
        // Do not use a timeout at 0
        return $timeout > 0 ? $timeout : 1;
    }

    /**
     * Loads configuration of the Gorgone server
     *
     * @throws \Exception
     */
    private function loadConfiguration(): void
    {
        try {
            $options = $this->optionService->findSelectedOptions([
                self::GORGONE_API_ADDRESS,
                self::GORGONE_API_PORT,
                self::GORGONE_API_USERNAME,
                self::GORGONE_API_PASSWORD,
                self::GORGONE_API_SSL,
                self::GORGONE_API_CERTIFICATE_SELF_SIGNED,
                self::GORGONE_COMMAND_TIMEOUT
            ]);
            foreach ($options as $option) {
                $this->gorgoneParameters[$option->getName()] = $option->getValue();
            }
            $this->isOptionsLoaded = true;
        } catch (\Exception $ex) {
            $this->isOptionsLoaded = false;
            throw $ex;
        }
    }
}
