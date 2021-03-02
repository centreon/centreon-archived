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

namespace Security\Domain\Authentication\Model;

use Centreon\Domain\Common\Assertion\Assertion;

/**
 * @package Security\Authentication\Model
 */
class ProviderConfiguration
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string Provider's name
     */
    private $providerName;

    /**
     * @var string Provider configuration name
     */
    private $configurationName;

    /**
     * @var array<string, mixed>
     */
    private $configuration;

    /**
     * @var bool is the provider forced ?
     */
    private $isForced;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProviderConfiguration
     */
    public function setId(int $id): ProviderConfiguration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     * @return ProviderConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setProviderName(string $providerName): ProviderConfiguration
    {
        Assertion::minLength($providerName, 1, 'ConfigurationProvider::providerName');
        $this->providerName = $providerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigurationName(): string
    {
        return $this->configurationName;
    }

    /**
     * @param string $configurationName
     * @return ProviderConfiguration
     */
    public function setConfigurationName(string $configurationName): ProviderConfiguration
    {
        $this->configurationName = $configurationName;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     * @return ProviderConfiguration
     */
    public function setConfiguration(array $configuration): ProviderConfiguration
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return self
     */
    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param boolean $isForced
     * @return self
     */
    public function setForced(bool $isForced): self
    {
        $this->isForced = $isForced;
        return $this;
    }
}
