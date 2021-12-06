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
    private $id;

    /**
     * @var string Resource name
     */
    private $name;

    /**
     * @var string Resource comment
     */
    private $comment;

    /**
     * @var string Resource path
     */
    private $path;

    /**
     * @var bool Indicates whether this resource is activate or not
     */
    private $isActivate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MonitoringServerResource
     */
    public function setId(int $id): MonitoringServerResource
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
     * @return MonitoringServerResource
     */
    public function setName(string $name): MonitoringServerResource
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return MonitoringServerResource
     */
    public function setComment(string $comment): MonitoringServerResource
    {
        $this->comment = $comment;
        return $this;
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
     * @return MonitoringServerResource
     */
    public function setPath(string $path): MonitoringServerResource
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return MonitoringServerResource
     */
    public function setIsActivate(bool $isActivate): MonitoringServerResource
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
