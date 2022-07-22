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

namespace Centreon\Domain\MonitoringServer;

/**
 * This class is designed to represent resources of monitoring servers.
 *
 * @package Centreon\Domain\MonitoringServer
 */
class MonitoringServerResource
{
    /**
     * @var int Resource id
     */
    private ?int $id = null;

    /**
     * @var string Resource name
     */
    private ?string $name = null;

    /**
     * @var string Resource comment
     */
    private ?string $comment = null;

    /**
     * @var string Resource path
     */
    private ?string $path = null;

    /**
     * @var bool Indicates whether this resource is activate or not
     */
    private ?bool $isActivate = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): MonitoringServerResource
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MonitoringServerResource
    {
        $this->name = $name;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): MonitoringServerResource
    {
        $this->comment = $comment;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): MonitoringServerResource
    {
        $this->path = $path;
        return $this;
    }

    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    public function setIsActivate(bool $isActivate): MonitoringServerResource
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
