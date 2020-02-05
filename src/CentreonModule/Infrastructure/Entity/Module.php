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
     * @var array
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
     * @var string
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

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function addImage(string $image)
    {
        $this->images[] = $image;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    public function getVersionCurrent(): ?string
    {
        return $this->versionCurrent;
    }

    public function setVersionCurrent(string $versionCurrent)
    {
        $this->versionCurrent = $versionCurrent;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getStability(): string
    {
        return $this->stability;
    }

    public function setStability(string $stability)
    {
        $this->stability = $stability;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    public function getLicense(): ?array
    {
        return $this->license;
    }

    public function setLicense(array $license = null)
    {
        $this->license = $license;
    }

    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(string $lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    public function getReleaseNote(): ?string
    {
        return $this->releaseNote;
    }

    public function setReleaseNote(string $releaseNote)
    {
        $this->releaseNote = $releaseNote;
    }

    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    public function setInstalled(bool $value)
    {
        $this->isInstalled = $value;
    }

    public function isUpdated(): bool
    {
        return $this->isUpdated;
    }

    public function setUpdated(bool $value)
    {
        $this->isUpdated = $value;
    }
}
