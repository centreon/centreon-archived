<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\HostConfiguration;

/**
 * This class is designed to represent a hostgroup entity.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostGroup
{
    /**
     * @var int Id of the host group
     */
    private $id;

    /**
     * @var string Name of the host group
     */
    private $name;

    /**
     * @var string Alias of the host group
     */
    private $alias;

    /**
     * @var string Notes of the host group
     */
    private $notes;

    /**
     * @var string Url notes of the host group
     */
    private $notesUrl;

    /**
     * @var string Url action of the host group
     */
    private $actionUrl;

    /**
     * @var int Icon id linked to this host group
     */
    private $iconId;

    /**
     * @var int Map icon if linked to this host group
     */
    private $mapIconId;

    /**
     * @var int Delay of the RRD retention (in days)
     */
    private $rrdRetentionDelay;

    /**
     * @var string Geographic coordinates of the host group
     */
    private $geographicCoordinates;

    /**
     * @var string Comments of the host group
     */
    private $comments;

    /**
     * @var bool Indicates whether this host group is activate
     */
    private $isActivate = true;

    /**
     * @return int
     */
    public function getId (): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HostGroup
     */
    public function setId (int $id): HostGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return HostGroup
     */
    public function setName (string $name): HostGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias (): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return HostGroup
     */
    public function setAlias (string $alias): HostGroup
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes (): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return HostGroup
     */
    public function setNotes (string $notes): HostGroup
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotesUrl (): string
    {
        return $this->notesUrl;
    }

    /**
     * @param string $notesUrl
     * @return HostGroup
     */
    public function setNotesUrl (string $notesUrl): HostGroup
    {
        $this->notesUrl = $notesUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionUrl (): string
    {
        return $this->actionUrl;
    }

    /**
     * @param string $actionUrl
     * @return HostGroup
     */
    public function setActionUrl (string $actionUrl): HostGroup
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getIconId (): int
    {
        return $this->iconId;
    }

    /**
     * @param int $iconId
     * @return HostGroup
     */
    public function setIconId (int $iconId): HostGroup
    {
        $this->iconId = $iconId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMapIconId (): int
    {
        return $this->mapIconId;
    }

    /**
     * @param int $mapIconId
     * @return HostGroup
     */
    public function setMapIconId (int $mapIconId): HostGroup
    {
        $this->mapIconId = $mapIconId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRrdRetentionDelay (): int
    {
        return $this->rrdRetentionDelay;
    }

    /**
     * @param int $rrdRetentionDelay
     * @return HostGroup
     */
    public function setRrdRetentionDelay (int $rrdRetentionDelay): HostGroup
    {
        $this->rrdRetentionDelay = $rrdRetentionDelay;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeographicCoordinates (): string
    {
        return $this->geographicCoordinates;
    }

    /**
     * @param string $geographicCoordinates
     * @return HostGroup
     */
    public function setGeographicCoordinates (string $geographicCoordinates): HostGroup
    {
        $this->geographicCoordinates = $geographicCoordinates;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments (): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     * @return HostGroup
     */
    public function setComments (string $comments): HostGroup
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate (): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return HostGroup
     */
    public function setIsActivate (bool $isActivate): HostGroup
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
