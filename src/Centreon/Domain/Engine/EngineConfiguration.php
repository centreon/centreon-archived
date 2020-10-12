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

namespace Centreon\Domain\Engine;

class EngineConfiguration
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null Illegal object name characters
     */
    private $illegalObjectNameCharacters;

    /**
     * @var int|null Monitoring server id
     */
    private $monitoringServerId;

    /**
     * @var string|null Engine configuration name
     */
    private $name;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return EngineConfiguration
     */
    public function setId(?int $id): EngineConfiguration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIllegalObjectNameCharacters(): ?string
    {
        return $this->illegalObjectNameCharacters;
    }

    /**
     * @param string|null $illegalObjectNameCharacters
     * @return EngineConfiguration
     */
    public function setIllegalObjectNameCharacters(?string $illegalObjectNameCharacters): EngineConfiguration
    {
        $this->illegalObjectNameCharacters = $illegalObjectNameCharacters;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMonitoringServerId(): ?int
    {
        return $this->monitoringServerId;
    }

    /**
     * @param int|null $monitoringServerId
     * @return EngineConfiguration
     */
    public function setMonitoringServerId(?int $monitoringServerId): EngineConfiguration
    {
        $this->monitoringServerId = $monitoringServerId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return EngineConfiguration
     */
    public function setName(?string $name): EngineConfiguration
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Remove all illegal characters from the given string.
     *
     * @param string $stringToAnalyse String for which we want to remove illegal characters
     * @param string|null $illegalCharacters String containing illegal characters
     * @return string Return the string without illegal characters
     */
    public static function removeIllegalCharacters(string $stringToAnalyse, ?string $illegalCharacters): string
    {
        if ($illegalCharacters === null) {
            return $stringToAnalyse;
        }
        $illegalCharacters = html_entity_decode($illegalCharacters);
        return str_replace(str_split($illegalCharacters), '', $stringToAnalyse);
    }

    /**
     * Find if the given string has an illegal character in it.
     *
     * @param string $stringToCheck String to analyse
     * @param string|null $illegalCharacters String containing illegal characters
     * @return bool Return true if illegal characters have been found
     */
    public static function hasIllegalCharacters(string $stringToCheck, ?string $illegalCharacters): bool
    {
        return $stringToCheck !== self::removeIllegalCharacters($stringToCheck, $illegalCharacters);
    }
}
