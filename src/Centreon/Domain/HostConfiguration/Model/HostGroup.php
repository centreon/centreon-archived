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

namespace Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to represent a host group.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostGroup
{
    public const STATUS_ENABLE = 1,
        STATUS_DISABLE = 0,
        STATUS_DEFAULT = 2;
    
    private const AVAILABLE_STATUS = [
        self::STATUS_ENABLE, self::STATUS_DISABLE, self::STATUS_DEFAULT
    ];
    /**
     * @var int|null
     */
    private $id;
    
    /**
     * @var string|null
     */
    private $name;
    
    /**
     * @var string|null
     */
    private $alias;
    
    /**
     * @var string|null Define an optional URL that can be used to provide more information about the host.
     * <br>
     * This can be very useful if you want to make detailed information on the host template, emergency contact methods,
     * etc. available to other support staff.<br>
     * Any valid URL can be used.
     */
    private $notesUrl;
    
    /**
     * @var string|null Define an optional URL that can be used to provide more actions to be performed on the host.
     */
    private $actionUrl;
    
    /**
     * @var string|null Define an optional notes.
     */
    private $notes;
    
    /**
     * @var Image|null Define the image that should be associated with this group.
     */
    private $icon;
    
    /**
     * @var Image|null Define the map image that should be associated with this group.
     */
    private $iconMap;
    
    /**
     * @var string|null
     */
    private $rrd;
    
    /**
     * @var string|null
     */
    private $geoCoords;
    
    /**
     * @var string|null
     */
    private $comment;
    
    /**
     * @var bool Indicates whether the host group is activated or not.
     */
    private $isActivated = true;
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @param int|null $id
     * @return HostGroup
     */
    public function setId(?int $id): HostGroup
    {
        $this->id = $id;
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
     * @return HostGroup
     */
    public function setName(string $name): HostGroup
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }
    
    /**
     * @param string|null $alias
     * @return HostGroup
     */
    public function setAlias(?string $alias): HostGroup
    {
        $this->alias = $alias;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }
    
    /**
     * @param string|null $notes
     * @return HostGroup
     */
    public function setNotes(?string $notes): HostGroup
    {
        $this->notes = $notes;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getNotesUrl(): ?string
    {
        return $this->notesUrl;
    }
    
    /**
     * @param string|null $notesUrl
     * @return HostGroup
     */
    public function setNotesUrl(?string $notesUrl): HostGroup
    {
        $this->notesUrl = $notesUrl;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }
    
    /**
     * @param string|null $actionUrl
     * @return HostGroup
     */
    public function setActionUrl(?string $actionUrl): HostGroup
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }
    
    /**
     * @return Image|null
     */
    public function getIcon(): ?Image
    {
        return $this->icon;
    }
    
    /**
     * @param Image|null $icon
     * @return HostTemplate
     */
    public function setIcon(?Image $icon): HostGroup
    {
        $this->icon = $icon;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getIconMap(): ?string
    {
        return $this->iconMap;
    }
    
    /**
     * @param Image|null $iconMap
     * @return HostTemplate
     */
    public function setIconMap(?Image $iconMap): HostGroup
    {
        $this->iconMap = $iconMap;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getRrd(): ?string
    {
        return $this->rrd;
    }
    
    /**
     * @param string|null $rrd
     * @return HostGroup
     */
    public function setRrd(?string $rrd): HostGroup
    {
        $this->rrd = $rrd;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getGeoCoords(): ?string
    {
        return $this->geoCoords;
    }
    
    /**
     * @param string|null $geoCoords
     * @return HostGroup
     */
    public function setGeoCoords(?string $geoCoords): HostGroup
    {
        $this->geoCoords = $geoCoords;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    /**
     * @param string|null $comment
     * @return HostGroup
     */
    public function setComment(?string $comment): HostGroup
    {
        $this->comment = $comment;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return HostGroup
     */
    public function setActivated(bool $isActivated): HostGroup
    {
        $this->isActivated = $isActivated;
        return $this;
    }
}
