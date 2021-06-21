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
     * @var string Provider's type
     */
    private $type;

    /**
     * @var string Provider configuration name
     */
    private $name;

    /**
     * @var string
     */
    private $centreonBaseUri = '/centreon';

    /**
     * @var bool is the provider is enabled ?
     */
    private $isActive;

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
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ProviderConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setType(string $type): ProviderConfiguration
    {
        Assertion::minLength($type, 1, 'ConfigurationProvider::type');
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ProviderConfiguration
     */
    public function setName(string $name): ProviderConfiguration
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get centreon base uri
     *
     * @return string
     */
    public function getCentreonBaseUri(): string
    {
        return $this->centreonBaseUri;
    }

    /**
     * Set centreon base uri
     *
     * @param string $centreonBaseUri
     * @return self
     */
    public function setCentreonBaseUri(string $centreonBaseUri): self
    {
        $this->centreonBaseUri = $centreonBaseUri;
        return $this;
    }

    /**
     * Get the provider's authentication uri (ex: https://www.okta.com/.../auth).
     *
     * @return string
     */
    public function getAuthenticationUri(): string
    {
        return $this->getCentreonBaseUri() . '/authentication/providers/configurations/'
            . $this->getName();
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
