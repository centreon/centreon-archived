<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
     * @var string[] names of the module's dependencies
     * @example ['centreon-license-manager']
     */
    private array $dependencies = [];

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

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param string $dependency
     */
    public function addDependency(string $dependency): void
    {
        $this->dependencies[] = $dependency;
    }

    /**
     * @param string[] $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = [];

        foreach ($dependencies as $dependency) {
            $this->addDependency($dependency);
        }
    }
}
