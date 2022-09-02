<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonModule\Infrastructure\Entity;

use CentreonModule\Infrastructure\Source\SourceDataInterface;

class Module implements SourceDataInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array<int,string>
     */
    private $images = [];

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionCurrent;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $stability = 'stable';

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var array<string,string|bool>
     */
    private $license;

    /**
     * @var string
     */
    protected $lastUpdate;

    /**
     * @var string
     */
    protected $releaseNote;

    /**
     * @var bool
     */
    private $isInstalled = false;

    /**
     * @var bool
     */
    private $isUpdated = false;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array<int,string>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param string $image
     */
    public function addImage(string $image): void
    {
        $this->images[] = $image;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersionCurrent(): ?string
    {
        return $this->versionCurrent;
    }

    /**
     * @param string $versionCurrent
     */
    public function setVersionCurrent(string $versionCurrent): void
    {
        $this->versionCurrent = $versionCurrent;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getStability(): string
    {
        return $this->stability;
    }

    /**
     * @param string $stability
     */
    public function setStability(string $stability): void
    {
        $this->stability = $stability;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @return array<string,string|bool>|null
     */
    public function getLicense(): ?array
    {
        return $this->license;
    }

    /**
     * @param array<mixed>|null $license
     */
    public function setLicense(array $license = null): void
    {
        $this->license = $license;
    }

    /**
     * @return string|null
     */
    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    /**
     * @param string $lastUpdate
     */
    public function setLastUpdate(string $lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return string|null
     */
    public function getReleaseNote(): ?string
    {
        return $this->releaseNote;
    }

    /**
     * @param string $releaseNote
     */
    public function setReleaseNote(string $releaseNote): void
    {
        $this->releaseNote = $releaseNote;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setInstalled(bool $value): void
    {
        $this->isInstalled = $value;
    }

    /**
     * @return string
     */
    public function isUpdated(): bool
    {
        return $this->isUpdated;
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setUpdated(bool $value): void
    {
        $this->isUpdated = $value;
    }
}
