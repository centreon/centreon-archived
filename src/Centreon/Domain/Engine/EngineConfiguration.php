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

use Centreon\Domain\Common\Assertion\Assertion;

class EngineConfiguration
{
    public const NOTIFICATIONS_OPTION_DISABLED = 0,
                 NOTIFICATIONS_OPTION_ENABLED = 1,
                 NOTIFICATIONS_OPTION_DEFAULT = 2;

    private const AVAILABLE_NOTIFICATIONS_OPTION = [
        self::NOTIFICATIONS_OPTION_DISABLED,
        self::NOTIFICATIONS_OPTION_ENABLED,
        self::NOTIFICATIONS_OPTION_DEFAULT,
    ];

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
     * @var int
     */
    private $notificationsEnabledOption = self::NOTIFICATIONS_OPTION_ENABLED;

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
     * @return string Return the string without illegal characters
     */
    public function removeIllegalCharacters(string $stringToAnalyse): string
    {
        if ($this->illegalObjectNameCharacters === null) {
            return $stringToAnalyse;
        }
        $illegalCharacters = html_entity_decode($this->illegalObjectNameCharacters);
        return str_replace(str_split($illegalCharacters), '', $stringToAnalyse);
    }

    /**
     * Find if the given string has an illegal character in it.
     *
     * @param string $stringToCheck String to analyse
     * @return bool Return true if illegal characters have been found
     */
    public function hasIllegalCharacters(string $stringToCheck): bool
    {
        return $stringToCheck !== $this->removeIllegalCharacters($stringToCheck);
    }

    /**
     * @return int
     */
    public function getNotificationsEnabledOption(): int
    {
        return $this->notificationsEnabledOption;
    }

    /**
     * @param int $notificationsEnabledOption
     * @return self
     */
    public function setNotificationsEnabledOption(int $notificationsEnabledOption): self
    {
        Assertion::inArray(
            $notificationsEnabledOption,
            self::AVAILABLE_NOTIFICATIONS_OPTION,
            'Engine::notificationsEnabledOption',
        );

        $this->notificationsEnabledOption = $notificationsEnabledOption;

        return $this;
    }
}
