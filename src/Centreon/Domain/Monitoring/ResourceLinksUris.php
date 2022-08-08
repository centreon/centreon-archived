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

namespace Centreon\Domain\Monitoring;

/**
 * Resource Links Uris model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceLinksUris
{
    /**
     * @var string|null
     */
    private $configuration;

    /**
     * @var string|null
     */
    private $logs;

    /**
     * @var string|null
     */
    private $reporting;

    /**
     * @return string|null
     */
    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    /**
     * @param string|null $configuration
     * @return self
     */
    public function setConfiguration(?string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogs(): ?string
    {
        return $this->logs;
    }

    /**
     * @param string|null $logs
     * @return self
     */
    public function setLogs(?string $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReporting(): ?string
    {
        return $this->reporting;
    }

    /**
     * @param string|null $reporting
     * @return self
     */
    public function setReporting(?string $reporting): self
    {
        $this->reporting = $reporting;

        return $this;
    }
}
