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

namespace Centreon\Domain\Broker;

class BrokerConfiguration
{
    /**
     * Configuration Id
     * @var int|null
     */
    private $id;

    /**
     * Configuration Broker Key
     *
     * @var string|null
     */
    private $configurationKey;

    /**
     * Configuration Broker Value
     *
     * @var string
     */
    private $configurationValue;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getConfigurationKey(): ?string
    {
        return $this->configurationKey;
    }

    public function setConfigurationKey(?string $configurationKey): self
    {
        $this->configurationKey = $configurationKey;
        return $this;
    }

    public function getConfigurationValue(): ?string
    {
        return $this->configurationValue;
    }

    public function setConfigurationValue(?string $configurationValue): self
    {
        $this->configurationValue = $configurationValue;
        return $this;
    }
}
