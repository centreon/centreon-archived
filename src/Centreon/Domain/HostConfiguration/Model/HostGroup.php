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

namespace Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to represent a host group.
 *
 * @package Centreon\Domain\HostConfiguration\Model
 */
class HostGroup
{
    public const MAX_NAME_LENGTH = 200,
                 MAX_ALIAS_LENGTH = 200,
                 MAX_NOTES_LENGTH = 255,
                 MAX_NOTES_URL_LENGTH = 255,
                 MAX_ACTION_URL_LENGTH = 255,
                 MAX_GEO_COORDS_LENGTH = 32,
                 MIN_RRD_NUMBER = 1,
                 MAX_RRD_NUMBER = 2147483648,
                 MAX_COMMENTS_LENGTH = 65535;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null Define an optional string of notes pertaining to the host group.
     */
    private $notes;

    /**
     * @var string|null Define an optional URL that can be used to provide more information about the host group.
     * <br> Any valid URL can be used.
     * <br> This can be very useful if you want to make detailed information on the host group,
     * emergency contact methods, etc. available to other support staff.
     */
    private $notesUrl;

    /**
     * @var string|null Define an optional URL that can be used to provide more actions to be performed on
     * the host group. You will see the link to the action URL in the host group details.
     */
    private $actionUrl;

    /**
     * @var Image|null Define the image that should be associated with this host group.
     * This image will be displayed in the various places. The image will look best if it is 40x40 pixels in size.
     */
    private $icon;

    /**
     * @var Image|null Define an image that should be associated with this host group in the statusmap CGI
     * in monitoring engine. <br>
     * You can choose a JPEG, PNG, and GIF image. The GD2 image format is preferred, as other image formats
     * must be converted first when the statusmap image is generated. <br>
     * The image will look best if it is 40x40 pixels in size.
     */
    private $iconMap;

    /**
     * @var string|null Geographical coordinates use by Centreon Map module to position element on map. <br>
     * Define "Latitude,Longitude", for example for Paris coordinates set "48.51,2.20"
     */
    private $geoCoords;

    /**
     * @var int|null RRD retention duration (in days) of all the services that are in this host group.
     * If service is in multiple host groups, the highest retention value will be used.
     */
    private $rrd;

    /**
     * @var string|null Comments on this host group.
     */
    private $comment;

    /**
     * @var bool Indicates whether the host group is activated or not.
     */
    private $isActivated = true;

    /**
     * @param string $name Host Group name
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HostGroup
     */
    public function setId(int $id): HostGroup
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
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): HostGroup
    {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'HostGroup::name');
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
     * @throws \Assert\AssertionFailedException
     */
    public function setAlias(?string $alias): HostGroup
    {
        if ($alias !== null) {
            Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'HostGroup::alias');
        }
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
     * @throws \Assert\AssertionFailedException
     */
    public function setNotes(?string $notes): HostGroup
    {
        if ($notes !== null) {
            Assertion::maxLength($notes, self::MAX_NOTES_LENGTH, 'HostGroup::notes');
        }
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
     * @throws \Assert\AssertionFailedException
     */
    public function setNotesUrl(?string $notesUrl): HostGroup
    {
        if ($notesUrl !== null) {
            Assertion::maxLength($notesUrl, self::MAX_NOTES_URL_LENGTH, 'HostGroup::notesUrl');
        }
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
     * @throws \Assert\AssertionFailedException
     */
    public function setActionUrl(?string $actionUrl): HostGroup
    {
        if ($actionUrl !== null) {
            Assertion::maxLength($actionUrl, self::MAX_ACTION_URL_LENGTH, 'HostGroup::actionUrl');
        }
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
     * @return HostGroup
     */
    public function setIcon(?Image $icon): HostGroup
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return Image|null
     */
    public function getIconMap(): ?Image
    {
        return $this->iconMap;
    }

    /**
     * @param Image|null $iconMap
     * @return HostGroup
     */
    public function setIconMap(?Image $iconMap): HostGroup
    {
        $this->iconMap = $iconMap;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRrd(): ?int
    {
        return $this->rrd;
    }

    /**
     * @param int|null $rrd
     * @return $this
     * @throws \Assert\AssertionFailedException
     */
    public function setRrd(?int $rrd): HostGroup
    {
        if ($rrd !== null) {
            Assertion::min($rrd, self::MIN_RRD_NUMBER, 'HostGroup::rrd');
            Assertion::max($rrd, self::MAX_RRD_NUMBER, 'HostGroup::rrd');
        }
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
     * @throws \Assert\AssertionFailedException
     */
    public function setGeoCoords(?string $geoCoords): HostGroup
    {
        if ($geoCoords !== null) {
            Assertion::maxLength($geoCoords, self::MAX_GEO_COORDS_LENGTH, 'HostGroup::geoCoords');
        }
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
     * @throws \Assert\AssertionFailedException
     */
    public function setComment(?string $comment): HostGroup
    {
        if ($comment !== null) {
            Assertion::maxLength($comment, self::MAX_COMMENTS_LENGTH, 'HostGroup::comment');
        }
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
